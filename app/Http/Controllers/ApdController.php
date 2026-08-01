<?php

namespace App\Http\Controllers;

use App\Models\ApdPickup;
use App\Models\ApdRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ApdController extends Controller
{
    private const SHOE_STATUSES = [
        'SHE',
        'WAREHOUSE',
        'LOGISTIK',
        'READY',
        'DIAMBIL',
    ];

    /**
     * Monitoring pengajuan APD, antrean sepatu READY,
     * dan riwayat pengambilan.
     */
    public function index(Request $request): View
    {
        $bulan = $this->validMonth(
            $request->input('bulan', now()->format('Y-m'))
        );

        [$tahun, $nomorBulan] = array_map(
            'intval',
            explode('-', $bulan)
        );

        $search = trim((string) $request->input('search', ''));
        $status = strtoupper(
            trim((string) $request->input('status', ''))
        );

        $query = ApdRequest::query()
            ->with('pickup')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->whereMonth('tanggal_pengajuan', $nomorBulan);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('nrp', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (
            $status !== ''
            && in_array($status, self::SHOE_STATUSES, true)
        ) {
            $query->where('status_sepatu', $status);
        }

        $records = $query
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate(10, ['*'], 'apd_page')
            ->withQueryString();

        /*
         * Hanya pengajuan Sepatu Safety dengan status READY
         * dan belum pernah diambil yang ditampilkan pada form
         * pengambilan.
         */
        $readyShoes = ApdRequest::query()
            ->where('item_sepatu_safety', true)
            ->where('status_sepatu', 'READY')
            ->whereDoesntHave('pickup')
            ->orderBy('nama')
            ->get();

        $pickups = ApdPickup::query()
            ->with('apdRequest')
            ->latest('tanggal_pengambilan')
            ->latest('id')
            ->paginate(8, ['*'], 'pickup_page')
            ->withQueryString();

        $stats = [
            'bulan' => ApdRequest::query()
                ->whereYear('tanggal_pengajuan', $tahun)
                ->whereMonth('tanggal_pengajuan', $nomorBulan)
                ->count(),

            'total' => ApdRequest::query()->count(),

            'ready' => ApdRequest::query()
                ->where('item_sepatu_safety', true)
                ->where('status_sepatu', 'READY')
                ->whereDoesntHave('pickup')
                ->count(),

            'diambil' => ApdPickup::query()->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.apd.monitoring',
            'records' => $records,
            'readyShoes' => $readyShoes,
            'pickups' => $pickups,
            'stats' => $stats,
            'bulan' => $bulan,
            'search' => $search,
            'status' => $status,
            'shoeStatuses' => self::SHOE_STATUSES,
            'openModal' => $request->input('open'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        ApdRequest::create(
            $this->requestPayload($request, $validated)
        );

        return redirect()
            ->route('apd.index', [
                'bulan' => Carbon::parse(
                    $validated['tanggal_pengajuan']
                )->format('Y-m'),
            ])
            ->with('success', 'Pengajuan APD berhasil disimpan.');
    }

    public function update(
        Request $request,
        ApdRequest $apdRequest
    ): RedirectResponse {
        $validated = $this->validateRequest($request);

        if ($apdRequest->pickup) {
            throw ValidationException::withMessages([
                'status_sepatu' =>
                    'Data yang sudah diambil tidak dapat diubah.',
            ]);
        }

        $apdRequest->update(
            $this->requestPayload($request, $validated)
        );

        return redirect()
            ->route('apd.index', [
                'bulan' => Carbon::parse(
                    $validated['tanggal_pengajuan']
                )->format('Y-m'),
            ])
            ->with('success', 'Pengajuan APD berhasil diperbarui.');
    }

    /**
     * Memperbarui posisi terakhir Sepatu Safety.
     */
    public function updateStatus(
        Request $request,
        ApdRequest $apdRequest
    ): RedirectResponse {
        $validated = $request->validate([
            'status_sepatu' => [
                'required',
                Rule::in([
                    'SHE',
                    'WAREHOUSE',
                    'LOGISTIK',
                    'READY',
                ]),
            ],
        ]);

        if (! $apdRequest->item_sepatu_safety) {
            throw ValidationException::withMessages([
                'status_sepatu' =>
                    'Pengajuan ini tidak memiliki Sepatu Safety.',
            ]);
        }

        if ($apdRequest->pickup) {
            throw ValidationException::withMessages([
                'status_sepatu' =>
                    'Sepatu sudah diambil dan status tidak dapat diubah.',
            ]);
        }

        $apdRequest->update([
            'status_sepatu' => $validated['status_sepatu'],
        ]);

        return back()->with(
            'success',
            'Status Sepatu Safety berhasil diperbarui.'
        );
    }

    /**
     * Menyimpan bukti pengambilan.
     *
     * Pengajuan yang dapat dipilih hanya:
     * - memilih Sepatu Safety;
     * - status READY;
     * - belum memiliki data pengambilan.
     */
    public function pickup(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'apd_request_id' => [
                    'required',
                    'integer',
                    'exists:apd_requests,id',
                ],
                'tanggal_pengambilan' => [
                    'required',
                    'date',
                ],
                'diambil_oleh' => [
                    'required',
                    'string',
                    'max:150',
                ],
                'petugas' => [
                    'nullable',
                    'string',
                    'max:150',
                ],
                'bukti_foto' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:8192',
                ],
                'keterangan' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'bukti_foto.required' =>
                    'Foto bukti dari kamera atau galeri wajib dipilih.',
                'bukti_foto.image' =>
                    'Bukti pengambilan harus berupa gambar.',
                'bukti_foto.max' =>
                    'Ukuran foto maksimal 8 MB.',
            ]
        );

        $file = $request->file('bukti_foto');
        $storedPath = null;

        try {
            DB::transaction(function () use (
                $validated,
                $file,
                &$storedPath
            ) {
                $apdRequest = ApdRequest::query()
                    ->lockForUpdate()
                    ->with('pickup')
                    ->findOrFail($validated['apd_request_id']);

                if (
                    ! $apdRequest->item_sepatu_safety
                    || $apdRequest->status_sepatu !== 'READY'
                    || $apdRequest->pickup
                ) {
                    throw ValidationException::withMessages([
                        'apd_request_id' =>
                            'Sepatu tidak lagi berstatus READY atau sudah diambil.',
                    ]);
                }

                $tanggal = Carbon::parse(
                    $validated['tanggal_pengambilan']
                );

                $folder = 'apd-pickups/'
                    .$tanggal->format('Y/m');

                $storedPath = $file->storeAs(
                    $folder,
                    Str::uuid().'.'.$file->extension(),
                    'public'
                );

                ApdPickup::create([
                    'apd_request_id' => $apdRequest->id,
                    'tanggal_pengambilan' =>
                        $validated['tanggal_pengambilan'],
                    'diambil_oleh' => $validated['diambil_oleh'],
                    'petugas' => $validated['petugas'] ?? null,
                    'photo_path' => $storedPath,
                    'photo_original_name' =>
                        $file->getClientOriginalName(),
                    'keterangan' =>
                        $validated['keterangan'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $apdRequest->update([
                    'status_sepatu' => 'DIAMBIL',
                    'picked_up_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            if (
                $storedPath
                && Storage::disk('public')->exists($storedPath)
            ) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('apd.index', [
                'bulan' => Carbon::parse(
                    $validated['tanggal_pengambilan']
                )->format('Y-m'),
            ])
            ->with(
                'success',
                'Pengambilan Sepatu Safety berhasil disimpan.'
            );
    }

    public function pickupPhoto(
        ApdPickup $apdPickup
    ): BinaryFileResponse {
        abort_unless(
            Storage::disk('public')->exists(
                $apdPickup->photo_path
            ),
            404,
            'Foto bukti tidak ditemukan.'
        );

        return response()->file(
            Storage::disk('public')->path(
                $apdPickup->photo_path
            )
        );
    }

    public function destroy(
        ApdRequest $apdRequest
    ): RedirectResponse {
        if ($apdRequest->pickup) {
            throw ValidationException::withMessages([
                'delete' =>
                    'Data yang sudah memiliki pengambilan tidak dapat dihapus.',
            ]);
        }

        $bulan = $apdRequest->tanggal_pengajuan
            ?->format('Y-m')
            ?? now()->format('Y-m');

        $apdRequest->delete();

        return redirect()
            ->route('apd.index', ['bulan' => $bulan])
            ->with('success', 'Pengajuan APD berhasil dihapus.');
    }

    private function validateRequest(Request $request): array
    {
        $validator = validator(
            $request->all(),
            [
                'tanggal_pengajuan' => ['required', 'date'],
                'nrp' => ['required', 'string', 'max:50'],
                'nama' => ['required', 'string', 'max:150'],
                'jabatan' => ['required', 'string', 'max:150'],
                'ukuran_sepatu' => [
                    Rule::requiredIf(
                        $request->boolean('item_sepatu_safety')
                    ),
                    'nullable',
                    'string',
                    'max:20',
                ],
                'item_helm' => ['nullable', 'boolean'],
                'item_sepatu_safety' => ['nullable', 'boolean'],
                'item_rompi' => ['nullable', 'boolean'],
                'item_kacamata' => ['nullable', 'boolean'],
                'item_ear_plug' => ['nullable', 'boolean'],
                'status_sepatu' => [
                    Rule::requiredIf(
                        $request->boolean('item_sepatu_safety')
                    ),
                    'nullable',
                    Rule::in([
                        'SHE',
                        'WAREHOUSE',
                        'LOGISTIK',
                        'READY',
                    ]),
                ],
            ],
            [
                'ukuran_sepatu.required' =>
                    'Ukuran sepatu wajib diisi jika Sepatu Safety dipilih.',
                'status_sepatu.required' =>
                    'Posisi sepatu wajib dipilih jika Sepatu Safety dipilih.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $hasItem = collect([
                'item_helm',
                'item_sepatu_safety',
                'item_rompi',
                'item_kacamata',
                'item_ear_plug',
            ])->contains(
                fn ($field) => $request->boolean($field)
            );

            if (! $hasItem) {
                $validator->errors()->add(
                    'items',
                    'Pilih minimal satu barang APD.'
                );
            }
        });

        return $validator->validate();
    }

    private function requestPayload(
        Request $request,
        array $validated
    ): array {
        $hasSafetyShoes = $request->boolean(
            'item_sepatu_safety'
        );

        return [
            'tanggal_pengajuan' =>
                $validated['tanggal_pengajuan'],
            'nrp' => $validated['nrp'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'ukuran_sepatu' => $hasSafetyShoes
                ? $validated['ukuran_sepatu']
                : null,
            'item_helm' => $request->boolean('item_helm'),
            'item_sepatu_safety' => $hasSafetyShoes,
            'item_rompi' => $request->boolean('item_rompi'),
            'item_kacamata' =>
                $request->boolean('item_kacamata'),
            'item_ear_plug' =>
                $request->boolean('item_ear_plug'),
            'status_sepatu' => $hasSafetyShoes
                ? $validated['status_sepatu']
                : null,
            'created_by' => auth()->id(),
        ];
    }

    private function validMonth(mixed $month): string
    {
        $month = (string) $month;

        return preg_match(
            '/^\d{4}-(0[1-9]|1[0-2])$/',
            $month
        )
            ? $month
            : now()->format('Y-m');
    }
}

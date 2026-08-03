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
    /**
     * Alur status barang APD.
     */
    private const ITEM_STATUSES = [
        'SHE',
        'WAREHOUSE',
        'LOGISTIK',
        'READY',
    ];

    /**
     * Pemetaan checkbox barang ke kolom statusnya.
     *
     * Kolom status_sepatu dipertahankan agar tetap kompatibel
     * dengan data dan kode lama.
     */
    private const ITEM_STATUS_FIELDS = [
        'helm' => [
            'selected' => 'item_helm',
            'status' => 'status_helm',
            'label' => 'Helm',
        ],
        'sepatu_safety' => [
            'selected' => 'item_sepatu_safety',
            'status' => 'status_sepatu',
            'label' => 'Sepatu Safety',
        ],
        'rompi' => [
            'selected' => 'item_rompi',
            'status' => 'status_rompi',
            'label' => 'Rompi',
        ],
        'kacamata' => [
            'selected' => 'item_kacamata',
            'status' => 'status_kacamata',
            'label' => 'Kacamata',
        ],
        'ear_plug' => [
            'selected' => 'item_ear_plug',
            'status' => 'status_ear_plug',
            'label' => 'Ear Plug',
        ],
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
            && in_array(
                $status,
                [...self::ITEM_STATUSES, 'DIAMBIL'],
                true
            )
        ) {
            $query->where(function ($subQuery) use ($status) {
                foreach (self::ITEM_STATUS_FIELDS as $definition) {
                    $subQuery->orWhere(
                        $definition['status'],
                        $status
                    );
                }
            });
        }

        $records = $query
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate(10, ['*'], 'apd_page')
            ->withQueryString();

        /*
         * Hanya Sepatu Safety berstatus READY dan belum pernah diambil
         * yang ditampilkan pada form serah terima.
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

        /*
         * Seluruh riwayat terakhir per NRP dikirim ke Blade.
         * Data ini dipakai untuk notifikasi langsung pada form.
         * Validasi utama tetap dilakukan kembali di server pada store/update.
         */
        $shoePickupHistoryForJs = ApdPickup::query()
            ->with('apdRequest')
            ->whereHas('apdRequest', function ($pickupQuery) {
                $pickupQuery->where('item_sepatu_safety', true);
            })
            ->latest('tanggal_pengambilan')
            ->latest('id')
            ->get()
            ->filter(fn (ApdPickup $pickup) =>
                filled($pickup->apdRequest?->nrp)
            )
            ->unique(fn (ApdPickup $pickup) =>
                strtoupper(trim($pickup->apdRequest->nrp))
            )
            ->mapWithKeys(function (ApdPickup $pickup) {
                $nrp = strtoupper(
                    trim($pickup->apdRequest->nrp)
                );

                return [
                    $nrp => [
                        'tanggal' => $pickup
                            ->tanggal_pengambilan
                            ?->format('d/m/Y'),
                        'nama' => $pickup->apdRequest->nama,
                    ],
                ];
            });

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
            'shoeStatuses' => [
                ...self::ITEM_STATUSES,
                'DIAMBIL',
            ],
            'openModal' => $request->input('open'),
            'shoePickupHistoryForJs' =>
                $shoePickupHistoryForJs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $this->rejectRepeatedSafetyShoe(
            $request,
            $validated['nrp']
        );

        $payload = $this->requestPayload(
            $request,
            $validated
        );
        $payload['created_by'] = auth()->id();

        ApdRequest::create($payload);

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
                'pengajuan' =>
                    'Data yang sudah memiliki pengambilan Sepatu Safety tidak dapat diedit melalui form utama. Status barang lain masih dapat diperbarui dari kolom Update Status.',
            ]);
        }

        $this->rejectRepeatedSafetyShoe(
            $request,
            $validated['nrp'],
            $apdRequest->id
        );

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
     * Memperbarui posisi salah satu barang yang dipilih.
     *
     * Kompatibilitas:
     * Request lama berisi status_sepatu tanpa item tetap diproses
     * sebagai Sepatu Safety.
     */
    public function updateStatus(
        Request $request,
        ApdRequest $apdRequest
    ): RedirectResponse {
        if (
            ! $request->filled('item')
            && $request->filled('status_sepatu')
        ) {
            $request->merge([
                'item' => 'sepatu_safety',
                'status' => $request->input('status_sepatu'),
            ]);
        }

        $validated = $request->validate([
            'item' => [
                'required',
                Rule::in(array_keys(self::ITEM_STATUS_FIELDS)),
            ],
            'status' => [
                'required',
                Rule::in(self::ITEM_STATUSES),
            ],
        ]);

        $definition =
            self::ITEM_STATUS_FIELDS[$validated['item']];

        if (! $apdRequest->{$definition['selected']}) {
            throw ValidationException::withMessages([
                'item' =>
                    "{$definition['label']} tidak dipilih pada pengajuan ini.",
            ]);
        }

        if (
            $validated['item'] === 'sepatu_safety'
            && $apdRequest->pickup
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Sepatu Safety sudah diambil dan statusnya tidak dapat diubah.',
            ]);
        }

        $apdRequest->update([
            $definition['status'] => $validated['status'],
        ]);

        return back()->with(
            'success',
            "Status {$definition['label']} berhasil diperbarui."
        );
    }

    /**
     * Menyimpan bukti pengambilan Sepatu Safety.
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
        $rules = [
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
        ];

        foreach (self::ITEM_STATUS_FIELDS as $definition) {
            $rules[$definition['selected']] = [
                'nullable',
                'boolean',
            ];

            $rules[$definition['status']] = [
                Rule::requiredIf(
                    $request->boolean($definition['selected'])
                ),
                'nullable',
                Rule::in(self::ITEM_STATUSES),
            ];
        }

        $validator = validator(
            $request->all(),
            $rules,
            [
                'ukuran_sepatu.required' =>
                    'Ukuran sepatu wajib diisi jika Sepatu Safety dipilih.',
                'status_helm.required' =>
                    'Posisi Helm wajib dipilih.',
                'status_sepatu.required' =>
                    'Posisi Sepatu Safety wajib dipilih.',
                'status_rompi.required' =>
                    'Posisi Rompi wajib dipilih.',
                'status_kacamata.required' =>
                    'Posisi Kacamata wajib dipilih.',
                'status_ear_plug.required' =>
                    'Posisi Ear Plug wajib dipilih.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $hasItem = collect(self::ITEM_STATUS_FIELDS)
                ->contains(
                    fn (array $definition) =>
                        $request->boolean(
                            $definition['selected']
                        )
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
        $payload = [
            'tanggal_pengajuan' =>
                $validated['tanggal_pengajuan'],
            'nrp' => trim($validated['nrp']),
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'ukuran_sepatu' =>
                $request->boolean('item_sepatu_safety')
                    ? $validated['ukuran_sepatu']
                    : null,
        ];

        foreach (self::ITEM_STATUS_FIELDS as $definition) {
            $selected = $request->boolean(
                $definition['selected']
            );

            $payload[$definition['selected']] = $selected;
            $payload[$definition['status']] = $selected
                ? $validated[$definition['status']]
                : null;
        }

        return $payload;
    }

    /**
     * Sepatu Safety yang sudah diambil tidak boleh diajukan lagi
     * oleh NRP yang sama.
     */
    private function rejectRepeatedSafetyShoe(
        Request $request,
        string $nrp,
        ?int $ignoreRequestId = null
    ): void {
        if (! $request->boolean('item_sepatu_safety')) {
            return;
        }

        $lastPickup = ApdPickup::query()
            ->with('apdRequest')
            ->whereHas(
                'apdRequest',
                function ($query) use ($nrp) {
                    $query
                        ->whereRaw(
                            'UPPER(TRIM(nrp)) = ?',
                            [strtoupper(trim($nrp))]
                        )
                        ->where('item_sepatu_safety', true);
                }
            )
            ->when(
                $ignoreRequestId,
                fn ($query) =>
                    $query->where(
                        'apd_request_id',
                        '!=',
                        $ignoreRequestId
                    )
            )
            ->latest('tanggal_pengambilan')
            ->latest('id')
            ->first();

        if (! $lastPickup) {
            return;
        }

        $tanggal = $lastPickup
            ->tanggal_pengambilan
            ?->format('d/m/Y')
            ?? '-';

        throw ValidationException::withMessages([
            'item_sepatu_safety' =>
                "Sepatu Safety tidak dapat diajukan kembali. Pengambilan terakhir tercatat pada {$tanggal}.",
        ]);
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
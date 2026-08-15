<?php

namespace App\Http\Controllers;

use App\Models\ApdPickup;
use App\Models\ApdRequest;
use App\Services\EmployeeMasterService;
use App\Services\SafetyShoeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        'REJECT',
    ];

    /**
     * Status Sepatu Safety yang dapat dipilih saat ekspor Excel.
     */
    private const EXPORT_SHOE_STATUSES = [
        'SHE',
        'WAREHOUSE',
        'LOGISTIK',
        'READY',
        'REJECT',
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
            'reject_date' => 'tanggal_reject_helm',
            'label' => 'Helm',
        ],
        'sepatu_safety' => [
            'selected' => 'item_sepatu_safety',
            'status' => 'status_sepatu',
            'reject_date' => 'tanggal_reject_sepatu',
            'label' => 'Sepatu Safety',
        ],
        'rompi' => [
            'selected' => 'item_rompi',
            'status' => 'status_rompi',
            'reject_date' => 'tanggal_reject_rompi',
            'label' => 'Rompi',
        ],
        'kacamata' => [
            'selected' => 'item_kacamata',
            'status' => 'status_kacamata',
            'reject_date' => 'tanggal_reject_kacamata',
            'label' => 'Kacamata',
        ],
        'ear_plug' => [
            'selected' => 'item_ear_plug',
            'status' => 'status_ear_plug',
            'reject_date' => 'tanggal_reject_ear_plug',
            'label' => 'Ear Plug',
        ],
    ];

    /**
     * Monitoring pengajuan APD, antrean sepatu READY,
     * dan riwayat pengambilan.
     */
    public function index(
        Request $request,
        SafetyShoeService $safetyShoes
    ): View
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
        $shoePickupHistoryForJs =
            $this->combinedShoeEligibilityMap($safetyShoes);

        /*
         * Ringkasan jadwal Sepatu Safety untuk dashboard MPE.
         * Hanya NRP yang mempunyai riwayat pengambilan yang dihitung.
         * Pencarian dan filter tahun dilakukan terhadap seluruh jadwal,
         * kemudian hasilnya dibagi 12 baris per halaman.
         */
        $shoeEligibilityRows = collect($shoePickupHistoryForJs)
            ->filter(fn (mixed $row): bool =>
                is_array($row)
                && ($row['available'] ?? false)
                && ($row['has_history'] ?? false)
            )
            ->map(function (array $row, string|int $nrp): array {
                return [
                    'nrp' => (string) ($row['nrp'] ?? $nrp),
                    'nama' => (string) ($row['nama'] ?? ''),
                    'tanggal' => (string) ($row['tanggal'] ?? '-'),
                    'tanggal_bisa_ajukan' => (string) (
                        $row['tanggal_bisa_ajukan'] ?? '-'
                    ),
                    'eligible_at' => (string) (
                        $row['eligible_at'] ?? ''
                    ),
                    'days_remaining' => max(
                        0,
                        (int) ($row['days_remaining'] ?? 0)
                    ),
                    'eligible' => (bool) ($row['eligible'] ?? false),
                    'is_stale' => (bool) ($row['is_stale'] ?? false),
                ];
            })
            ->values();

        $shoeUpcomingAll = $shoeEligibilityRows
            ->reject(fn (array $row): bool => $row['eligible'])
            ->sortBy('days_remaining')
            ->values();

        $shoeScheduleStats = [
            'ready_now' => $shoeEligibilityRows
                ->where('eligible', true)
                ->count(),
            'next_30_days' => $shoeUpcomingAll
                ->where('days_remaining', '<=', 30)
                ->count(),
            'next_90_days' => $shoeUpcomingAll
                ->where('days_remaining', '<=', 90)
                ->count(),
            'upcoming_total' => $shoeUpcomingAll->count(),
        ];

        $shoeSearch = trim((string) $request->input(
            'shoe_search',
            ''
        ));
        $shoeYear = trim((string) $request->input(
            'shoe_year',
            ''
        ));

        if (! preg_match('/^\d{4}$/', $shoeYear)) {
            $shoeYear = '';
        }

        $shoeScheduleYears = $shoeUpcomingAll
            ->pluck('eligible_at')
            ->filter()
            ->map(fn (string $date): string => substr($date, 0, 4))
            ->filter(fn (string $year): bool =>
                (bool) preg_match('/^\d{4}$/', $year)
            )
            ->unique()
            ->sort()
            ->values();

        $shoeUpcomingFiltered = $shoeUpcomingAll
            ->when(
                $shoeSearch !== '',
                function ($rows) use ($shoeSearch) {
                    $needle = Str::lower($shoeSearch);

                    return $rows->filter(
                        fn (array $row): bool => Str::contains(
                            Str::lower(
                                $row['nrp'].' '.$row['nama']
                            ),
                            $needle
                        )
                    );
                }
            )
            ->when(
                $shoeYear !== '',
                fn ($rows) => $rows->filter(
                    fn (array $row): bool =>
                        substr($row['eligible_at'], 0, 4)
                            === $shoeYear
                )
            )
            ->values();

        $shoePage = max(
            1,
            (int) $request->input('shoe_page', 1)
        );
        $shoePerPage = 12;

        $shoeUpcomingRows = new LengthAwarePaginator(
            $shoeUpcomingFiltered
                ->forPage($shoePage, $shoePerPage)
                ->values(),
            $shoeUpcomingFiltered->count(),
            $shoePerPage,
            $shoePage,
            [
                'path' => $request->url(),
                'pageName' => 'shoe_page',
            ]
        );
        $shoeUpcomingRows->appends(
            $request->except('shoe_page')
        );

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
            'exportShoeStatuses' => self::EXPORT_SHOE_STATUSES,
            'openModal' => $request->input('open'),
            'shoePickupHistoryForJs' =>
                $shoePickupHistoryForJs,
            'shoeScheduleStats' => $shoeScheduleStats,
            'shoeUpcomingRows' => $shoeUpcomingRows,
            'shoeScheduleYears' => $shoeScheduleYears,
            'shoeSearch' => $shoeSearch,
            'shoeYear' => $shoeYear,
        ]);
    }

    /**
     * Mencari satu karyawan berdasarkan NRP dari cache MASTER_DATABASE.
     * EmployeeMasterService akan mengambil Google Sheets bila cache belum ada
     * dan memakai backup terakhir bila Google sementara tidak dapat diakses.
     */
    public function employeeLookup(
        Request $request,
        EmployeeMasterService $employeeMaster,
        SafetyShoeService $safetyShoes
    ): JsonResponse {
        $validated = $request->validate([
            'nrp' => ['required', 'string', 'max:50'],
        ]);

        $needle = $this->normalizeEmployeeNrp(
            (string) $validated['nrp']
        );

        try {
            $snapshot = $employeeMaster->snapshot();
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'found' => false,
                'message' =>
                    'MASTER_DATABASE belum dapat dibaca. Hubungkan ulang Google Sheets atau coba lagi.',
            ], 503);
        }

        $employee = collect(
            $snapshot['employees'] ?? []
        )->first(
            fn (mixed $row): bool =>
                is_array($row)
                && $this->normalizeEmployeeNrp(
                    (string) ($row['nrp'] ?? '')
                ) === $needle
        );

        if (! is_array($employee)) {
            return response()->json([
                'found' => false,
                'message' =>
                    'NRP tidak ditemukan pada MASTER_DATABASE.',
            ], 404);
        }

        $shoeEligibility = null;

        try {
            $shoeEligibility = $this->eligibilityForNrp(
                $needle,
                $safetyShoes
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'found' => true,
            'employee' => [
                'nrp' => (string) ($employee['nrp'] ?? $needle),
                'nama' => (string) ($employee['nama'] ?? ''),
                'jabatan' => (string) ($employee['jabatan'] ?? ''),
            ],
            'stale' => (bool) data_get(
                $snapshot,
                'meta.is_stale',
                false
            ),
            'shoe_eligibility' => $shoeEligibility,
        ]);
    }

    /**
     * Mengunduh data Sepatu Safety berdasarkan bulan pengajuan
     * dan posisi barang yang dipilih pengguna.
     *
     * Format SpreadsheetML dapat dibuka langsung oleh Microsoft Excel
     * tanpa membutuhkan package tambahan.
     */
    public function exportShoes(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'bulan' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
            'status' => [
                'required',
                Rule::in(self::EXPORT_SHOE_STATUSES),
            ],
        ], [
            'bulan.regex' => 'Format bulan tidak valid.',
            'status.in' => 'Status Sepatu Safety tidak valid.',
        ]);

        [$tahun, $nomorBulan] = array_map(
            'intval',
            explode('-', $validated['bulan'])
        );

        $status = strtoupper($validated['status']);

        $rows = ApdRequest::query()
            ->with('pickup')
            ->where('item_sepatu_safety', true)
            ->whereYear('tanggal_pengajuan', $tahun)
            ->whereMonth('tanggal_pengajuan', $nomorBulan)
            ->where('status_sepatu', $status)
            ->orderBy('tanggal_pengajuan')
            ->orderBy('nama')
            ->get();

        $filename = sprintf(
            'monitoring-sepatu-%s-%s.xls',
            $validated['bulan'],
            strtolower($status)
        );

        return response()->streamDownload(
            function () use ($rows, $validated, $status): void {
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<?mso-application progid="Excel.Sheet"?>';
                echo '<Workbook '
                    .'xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
                    .'xmlns:o="urn:schemas-microsoft-com:office:office" '
                    .'xmlns:x="urn:schemas-microsoft-com:office:excel" '
                    .'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
                echo '<Styles>';
                echo '<Style ss:ID="Header">'
                    .'<Font ss:Bold="1"/>'
                    .'<Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/>'
                    .'<Borders>'
                    .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'</Borders>'
                    .'</Style>';
                echo '<Style ss:ID="Cell"><Borders>'
                    .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>'
                    .'</Borders></Style>';
                echo '</Styles>';
                echo '<Worksheet ss:Name="Sepatu Safety">';
                echo '<Table>';

                echo '<Row>';
                echo $this->excelXmlCell('MONITORING SEPATU SAFETY', 'Header');
                echo $this->excelXmlCell(
                    'Bulan '.$validated['bulan'].' | Status '.$status,
                    'Header'
                );
                echo '</Row>';

                $headers = [
                    'No',
                    'Tanggal Pengajuan',
                    'NRP',
                    'Nama',
                    'Jabatan',
                    'Ukuran Sepatu',
                    'Posisi Sepatu',
                    'Tanggal Reject',
                    'Tanggal Pengambilan',
                    'Diambil Oleh',
                    'Petugas',
                    'Keterangan',
                ];

                echo '<Row>';
                foreach ($headers as $header) {
                    echo $this->excelXmlCell($header, 'Header');
                }
                echo '</Row>';

                if ($rows->isEmpty()) {
                    echo '<Row>';
                    echo $this->excelXmlCell(
                        'Tidak ada data pada bulan dan status yang dipilih.',
                        'Cell'
                    );
                    echo '</Row>';
                } else {
                    foreach ($rows as $index => $item) {
                        echo '<Row>';
                        echo $this->excelXmlCell((string) ($index + 1));
                        echo $this->excelXmlCell(
                            $item->tanggal_pengajuan?->format('d/m/Y') ?? '-'
                        );
                        echo $this->excelXmlCell((string) $item->nrp);
                        echo $this->excelXmlCell((string) $item->nama);
                        echo $this->excelXmlCell((string) $item->jabatan);
                        echo $this->excelXmlCell(
                            (string) ($item->ukuran_sepatu ?: '-')
                        );
                        echo $this->excelXmlCell(
                            (string) ($item->status_sepatu ?: '-')
                        );
                        echo $this->excelXmlCell(
                            $item->tanggal_reject_sepatu
                                ?->format('d/m/Y')
                            ?? '-'
                        );
                        echo $this->excelXmlCell(
                            $item->pickup?->tanggal_pengambilan
                                ?->format('d/m/Y')
                            ?? '-'
                        );
                        echo $this->excelXmlCell(
                            (string) ($item->pickup?->diambil_oleh ?: '-')
                        );
                        echo $this->excelXmlCell(
                            (string) ($item->pickup?->petugas ?: '-')
                        );
                        echo $this->excelXmlCell(
                            (string) ($item->pickup?->keterangan ?: '-')
                        );
                        echo '</Row>';
                    }
                }

                echo '</Table>';
                echo '</Worksheet>';
                echo '</Workbook>';
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]
        );
    }

    public function store(
        Request $request,
        SafetyShoeService $safetyShoes
    ): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $this->rejectRepeatedSafetyShoe(
            $request,
            $validated['nrp'],
            $safetyShoes
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
        ApdRequest $apdRequest,
        SafetyShoeService $safetyShoes
    ): RedirectResponse {
        $apdRequest->loadMissing('pickup');

        /*
         * Data yang sudah diambil tetap dapat diedit. Status Sepatu Safety
         * dipertahankan sebagai DIAMBIL dan tidak dapat dikembalikan lewat
         * form pengajuan utama.
         */
        if ($apdRequest->pickup) {
            $request->merge([
                'item_sepatu_safety' => true,
                'status_sepatu' => 'DIAMBIL',
            ]);
        }

        $validated = $this->validateRequest(
            $request,
            $apdRequest
        );

        $this->rejectRepeatedSafetyShoe(
            $request,
            $validated['nrp'],
            $safetyShoes,
            $apdRequest
        );

        $payload = $this->requestPayload(
            $request,
            $validated
        );

        if ($apdRequest->pickup) {
            $payload['item_sepatu_safety'] = true;
            $payload['status_sepatu'] = 'DIAMBIL';
            $payload['tanggal_reject_sepatu'] = null;
        }

        $apdRequest->update($payload);

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
            'tanggal_reject' => [
                Rule::requiredIf(
                    fn (): bool => strtoupper(
                        trim((string) $request->input('status'))
                    ) === 'REJECT'
                ),
                'nullable',
                'date',
            ],
        ], [
            'tanggal_reject.required' =>
                'Tanggal reject wajib diisi saat status REJECT dipilih.',
            'tanggal_reject.date' =>
                'Tanggal reject harus berupa tanggal yang valid.',
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

        $rejectDate = $validated['status'] === 'REJECT'
            ? $validated['tanggal_reject']
            : null;

        $apdRequest->update([
            $definition['status'] => $validated['status'],
            $definition['reject_date'] => $rejectDate,
        ]);

        $message = "Status {$definition['label']} berhasil diperbarui.";

        if ($validated['status'] === 'REJECT') {
            $message .= ' Tanggal reject: '
                .Carbon::parse($rejectDate)->format('d/m/Y').'.';
        }

        return back()->with('success', $message);
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
                    'tanggal_reject_sepatu' => null,
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

    /**
     * Mengubah data riwayat pengambilan dan mengganti foto bila diperlukan.
     */
    public function updatePickup(
        Request $request,
        ApdPickup $apdPickup
    ): RedirectResponse {
        $validated = $request->validate([
            'tanggal_pengambilan' => ['required', 'date'],
            'diambil_oleh' => ['required', 'string', 'max:150'],
            'petugas' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'bukti_foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'bulan' => [
                'nullable',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
        ], [
            'bukti_foto.image' =>
                'Bukti pengambilan harus berupa gambar.',
            'bukti_foto.max' =>
                'Ukuran foto maksimal 8 MB.',
        ]);

        $oldPath = $apdPickup->photo_path;
        $newPath = null;
        $newOriginalName = null;

        try {
            if ($request->hasFile('bukti_foto')) {
                $file = $request->file('bukti_foto');
                $tanggal = Carbon::parse(
                    $validated['tanggal_pengambilan']
                );

                $newPath = $file->storeAs(
                    'apd-pickups/'.$tanggal->format('Y/m'),
                    Str::uuid().'.'.$file->extension(),
                    'public'
                );

                $newOriginalName = $file->getClientOriginalName();
            }

            DB::transaction(function () use (
                $apdPickup,
                $validated,
                $newPath,
                $newOriginalName
            ): void {
                $data = [
                    'tanggal_pengambilan' =>
                        $validated['tanggal_pengambilan'],
                    'diambil_oleh' => $validated['diambil_oleh'],
                    'petugas' => $validated['petugas'] ?? null,
                    'keterangan' => $validated['keterangan'] ?? null,
                ];

                if ($newPath) {
                    $data['photo_path'] = $newPath;
                    $data['photo_original_name'] = $newOriginalName;
                }

                $apdPickup->update($data);

                $apdPickup->apdRequest()->update([
                    'status_sepatu' => 'DIAMBIL',
                    'tanggal_reject_sepatu' => null,
                    'picked_up_at' => Carbon::parse(
                        $validated['tanggal_pengambilan']
                    )->startOfDay(),
                ]);
            });
        } catch (Throwable $exception) {
            if (
                $newPath
                && Storage::disk('public')->exists($newPath)
            ) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if (
            $newPath
            && $oldPath
            && $oldPath !== $newPath
            && Storage::disk('public')->exists($oldPath)
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        $bulan = $validated['bulan']
            ?? $apdPickup->apdRequest?->tanggal_pengajuan?->format('Y-m')
            ?? now()->format('Y-m');

        return redirect()
            ->route('apd.index', ['bulan' => $bulan])
            ->with('success', 'Data pengambilan berhasil diperbarui.');
    }

    /**
     * Menghapus satu riwayat pengambilan. Pengajuan tidak ikut dihapus dan
     * Sepatu Safety dikembalikan ke status READY.
     */
    public function destroyPickup(
        Request $request,
        ApdPickup $apdPickup
    ): RedirectResponse {
        $apdPickup->loadMissing('apdRequest');

        $photoPath = $apdPickup->photo_path;
        $bulan = $this->validMonth(
            $request->input(
                'bulan',
                $apdPickup->apdRequest?->tanggal_pengajuan
                    ?->format('Y-m')
                ?? now()->format('Y-m')
            )
        );

        DB::transaction(function () use ($apdPickup): void {
            $apdRequest = ApdRequest::query()
                ->lockForUpdate()
                ->find($apdPickup->apd_request_id);

            $apdPickup->delete();

            if ($apdRequest) {
                $apdRequest->update([
                    'status_sepatu' => 'READY',
                    'tanggal_reject_sepatu' => null,
                    'picked_up_at' => null,
                ]);
            }
        });

        if (
            $photoPath
            && Storage::disk('public')->exists($photoPath)
        ) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()
            ->route('apd.index', ['bulan' => $bulan])
            ->with(
                'success',
                'Riwayat pengambilan dihapus dan status sepatu dikembalikan menjadi READY.'
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
        $apdRequest->loadMissing('pickup');

        $bulan = $apdRequest->tanggal_pengajuan
            ?->format('Y-m')
            ?? now()->format('Y-m');

        $photoPath = $apdRequest->pickup?->photo_path;

        /*
         * Relasi apd_pickups menggunakan cascadeOnDelete, sehingga data
         * pengambilan ikut terhapus bersama pengajuan.
         */
        DB::transaction(function () use ($apdRequest): void {
            $apdRequest->delete();
        });

        if (
            $photoPath
            && Storage::disk('public')->exists($photoPath)
        ) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()
            ->route('apd.index', ['bulan' => $bulan])
            ->with(
                'success',
                'Pengajuan APD beserta riwayat pengambilannya berhasil dihapus.'
            );
    }

    private function validateRequest(
        Request $request,
        ?ApdRequest $existingRequest = null
    ): array {
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

            $allowedStatuses = self::ITEM_STATUSES;

            if (
                $definition['status'] === 'status_sepatu'
                && $existingRequest?->pickup
            ) {
                $allowedStatuses[] = 'DIAMBIL';
            }

            $rules[$definition['status']] = [
                Rule::requiredIf(
                    $request->boolean($definition['selected'])
                ),
                'nullable',
                Rule::in($allowedStatuses),
            ];

            $rules[$definition['reject_date']] = [
                Rule::requiredIf(
                    fn (): bool =>
                        $request->boolean($definition['selected'])
                        && strtoupper(
                            trim(
                                (string) $request->input(
                                    $definition['status']
                                )
                            )
                        ) === 'REJECT'
                ),
                'nullable',
                'date',
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
                'tanggal_reject_helm.required' =>
                    'Tanggal reject Helm wajib diisi.',
                'tanggal_reject_sepatu.required' =>
                    'Tanggal reject Sepatu Safety wajib diisi.',
                'tanggal_reject_rompi.required' =>
                    'Tanggal reject Rompi wajib diisi.',
                'tanggal_reject_kacamata.required' =>
                    'Tanggal reject Kacamata wajib diisi.',
                'tanggal_reject_ear_plug.required' =>
                    'Tanggal reject Ear Plug wajib diisi.',
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

            $isRejected = $selected
                && ($validated[$definition['status']] ?? null) === 'REJECT';

            $payload[$definition['reject_date']] = $isRejected
                ? $validated[$definition['reject_date']]
                : null;
        }

        return $payload;
    }

    /**
     * Sepatu Safety dapat diajukan kembali tepat satu tahun setelah
     * tanggal pengambilan terakhir pada spreadsheet atau database lokal.
     */
    private function rejectRepeatedSafetyShoe(
        Request $request,
        string $nrp,
        SafetyShoeService $safetyShoes,
        ?ApdRequest $currentRequest = null
    ): void {
        if (! $request->boolean('item_sepatu_safety')) {
            return;
        }

        if (
            $currentRequest?->item_sepatu_safety
            && $this->normalizeEmployeeNrp(
                (string) $currentRequest->nrp
            ) === $this->normalizeEmployeeNrp($nrp)
        ) {
            return;
        }

        try {
            $eligibility = $this->eligibilityForNrp(
                $nrp,
                $safetyShoes,
                $currentRequest?->id
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'item_sepatu_safety' =>
                    'Status pengambilan Sepatu Safety belum dapat diperiksa dari Google Sheets. Coba lagi setelah koneksi tersedia.',
            ]);
        }

        if (
            ! ($eligibility['has_history'] ?? false)
            || ($eligibility['eligible'] ?? false)
        ) {
            return;
        }

        $tanggalTerakhir = (string) (
            $eligibility['tanggal'] ?? '-'
        );
        $tanggalBisaAjukan = (string) (
            $eligibility['tanggal_bisa_ajukan'] ?? '-'
        );
        $sisaHari = max(
            0,
            (int) ($eligibility['days_remaining'] ?? 0)
        );

        throw ValidationException::withMessages([
            'item_sepatu_safety' =>
                "Sepatu Safety belum dapat diajukan. Pengambilan terakhir {$tanggalTerakhir}; dapat diajukan kembali pada {$tanggalBisaAjukan} ({$sisaHari} hari lagi).",
        ]);
    }

    /**
     * Menggabungkan tanggal spreadsheet dan pengambilan dari aplikasi.
     * Tanggal paling baru selalu menjadi dasar perhitungan satu tahun.
     */
    private function eligibilityForNrp(
        string $nrp,
        SafetyShoeService $safetyShoes,
        ?int $ignoreRequestId = null
    ): array {
        $normalizedNrp = $this->normalizeEmployeeNrp($nrp);
        $sheetEligibility = $safetyShoes->eligibilityFor(
            $normalizedNrp
        );

        $localPickup = ApdPickup::query()
            ->with('apdRequest')
            ->whereHas(
                'apdRequest',
                function ($query) use ($normalizedNrp) {
                    $query
                        ->whereRaw(
                            "UPPER(REPLACE(TRIM(nrp), ' ', '')) = ?",
                            [$normalizedNrp]
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

        if (! $localPickup?->tanggal_pengambilan) {
            return $sheetEligibility;
        }

        $localEligibility = array_merge(
            $safetyShoes->eligibilityFromDate(
                $localPickup->tanggal_pengambilan,
                (string) ($localPickup->apdRequest?->nama ?? '')
            ),
            [
                'nrp' => $normalizedNrp,
                'source' => 'apd_pickups',
            ]
        );

        $sheetDate = $sheetEligibility['last_taken_date'] ?? null;

        if (
            ! $sheetDate
            || Carbon::parse($localEligibility['last_taken_date'])
                ->gt(Carbon::parse($sheetDate))
        ) {
            return $localEligibility;
        }

        return $sheetEligibility;
    }

    /**
     * Data notifikasi form. Jika Google sementara gagal dibaca, riwayat
     * lokal tetap ditampilkan; validasi penyimpanan tetap memeriksa ulang.
     */
    private function combinedShoeEligibilityMap(
        SafetyShoeService $safetyShoes
    ): array {
        try {
            $eligibilityMap = $safetyShoes->eligibilityMap();
        } catch (Throwable $exception) {
            report($exception);
            $eligibilityMap = [];
        }

        $localPickups = ApdPickup::query()
            ->with('apdRequest')
            ->whereHas('apdRequest', function ($query) {
                $query->where('item_sepatu_safety', true);
            })
            ->latest('tanggal_pengambilan')
            ->latest('id')
            ->get()
            ->filter(fn (ApdPickup $pickup): bool =>
                filled($pickup->apdRequest?->nrp)
                && filled($pickup->tanggal_pengambilan)
            )
            ->unique(fn (ApdPickup $pickup): string =>
                $this->normalizeEmployeeNrp(
                    (string) $pickup->apdRequest->nrp
                )
            );

        foreach ($localPickups as $pickup) {
            $normalizedNrp = $this->normalizeEmployeeNrp(
                (string) $pickup->apdRequest->nrp
            );
            $localEligibility = array_merge(
                $safetyShoes->eligibilityFromDate(
                    $pickup->tanggal_pengambilan,
                    (string) $pickup->apdRequest->nama
                ),
                [
                    'nrp' => $normalizedNrp,
                    'source' => 'apd_pickups',
                ]
            );
            $sheetDate = $eligibilityMap[$normalizedNrp]
                ['last_taken_date'] ?? null;

            if (
                ! $sheetDate
                || Carbon::parse($localEligibility['last_taken_date'])
                    ->gt(Carbon::parse($sheetDate))
            ) {
                $eligibilityMap[$normalizedNrp] = $localEligibility;
            }
        }

        return $eligibilityMap;
    }

    /**
     * Normalisasi NRP mengikuti pola EmployeeMasterService.
     */
    private function normalizeEmployeeNrp(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace(
                '/\.0+$/',
                '',
                $value
            ) ?? $value;
        }

        return strtoupper(
            preg_replace('/\s+/', '', $value) ?? $value
        );
    }

    /**
     * Membuat satu sel SpreadsheetML dengan tipe String agar NRP dan ukuran
     * tidak diubah otomatis oleh Excel.
     */
    private function excelXmlCell(
        string $value,
        string $style = 'Cell'
    ): string {
        $escaped = htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );

        return '<Cell ss:StyleID="'.$style.'">'
            .'<Data ss:Type="String">'.$escaped.'</Data>'
            .'</Cell>';
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

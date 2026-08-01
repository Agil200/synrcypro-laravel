<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MinePermitController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD MINE PERMIT
    |--------------------------------------------------------------------------
    */

    public function dashboardMinePermit(
        Request $request,
        GoogleSheetsService $googleSheetsService
    ): View {
        $dashboardErrors = [];
        $lastSyncedShe = null;
        $lastSyncedInternal = null;

        $currentDate = now(
            config('app.timezone', 'Asia/Jakarta')
        );

        /*
         * ==============================================================
         * DATA MONITORING SHE
         * ==============================================================
         */
        try {
            $shePayload = $this->cachedSpreadsheet(
                request: $request,
                cacheKey: 'mine-permit.monitoring-she',
                loader: fn (): array =>
                    $googleSheetsService->getMonitoringSheValues()
            );

            $sheValues = $shePayload['values'];

            $lastSyncedShe = $this->formatSyncTime(
                $shePayload['synced_at']
            );

            $sheHeaders = $this->normalizeHeaders(
                $sheValues[0] ?? []
            );

            $sheColumns = [
                'timestamp' => $this->findColumn(
                    $sheHeaders,
                    ['timestamp'],
                    ['timestamp'],
                    0
                ),

                'nama' => $this->findColumn(
                    $sheHeaders,
                    [
                        'nama karyawan baru kapi',
                        'nama karyawan baru',
                    ],
                    ['karyawan baru'],
                    3
                ),

                'jabatan' => $this->findColumn(
                    $sheHeaders,
                    ['jabatan'],
                    ['jabatan'],
                    4
                ),

                'departemen' => $this->findColumn(
                    $sheHeaders,
                    ['departemen', 'department'],
                    ['departemen', 'department'],
                    5
                ),

                'perusahaan' => $this->findColumn(
                    $sheHeaders,
                    ['perusahaan'],
                    ['perusahaan'],
                    6
                ),

                'pengajuan' => $this->findColumn(
                    $sheHeaders,
                    ['pengajuan'],
                    [],
                    10
                ),

                'jenis' => $this->findColumn(
                    $sheHeaders,
                    ['jenis'],
                    [],
                    11
                ),

                'nrp' => $this->findColumn(
                    $sheHeaders,
                    ['nrp karyawan', 'nrp'],
                    ['nrp'],
                    13
                ),

                'status_she' => $this->findColumn(
                    $sheHeaders,
                    [
                        'column 19',
                        'status she',
                        'status proses she',
                    ],
                    ['column 19', 'status she'],
                    18
                ),
            ];

            $dashboardSheRows = collect($sheValues)
                ->skip(1)
                ->map(function (array $row) use (
                    $sheColumns
                ): array {
                    $timestampRaw = $this->cell(
                        $row,
                        $sheColumns['timestamp']
                    );

                    $timestamp = $this->parseSpreadsheetTimestamp(
                        $timestampRaw
                    );

                    $statusRaw = $this->cell(
                        $row,
                        $sheColumns['status_she']
                    );

                    $statusUpper = strtoupper($statusRaw);

                    $status = match (true) {
                        str_contains($statusUpper, 'GAGAL') =>
                            'GAGAL',

                        str_contains($statusUpper, 'SELESAI') =>
                            'SELESAI',

                        default =>
                            'PROSES',
                    };

                    return [
                        'timestamp' =>
                            $timestampRaw,

                        'timestamp_sort' =>
                            $timestamp?->timestamp ?? 0,

                        'year' =>
                            $timestamp?->year,

                        'month' =>
                            $timestamp?->month,

                        'nama' => $this->cell(
                            $row,
                            $sheColumns['nama']
                        ),

                        'jabatan' => $this->cell(
                            $row,
                            $sheColumns['jabatan']
                        ),

                        'departemen' => strtoupper(
                            $this->cell(
                                $row,
                                $sheColumns['departemen']
                            )
                        ),

                        'perusahaan' => $this->cell(
                            $row,
                            $sheColumns['perusahaan']
                        ),

                        'pengajuan' => $this->cell(
                            $row,
                            $sheColumns['pengajuan']
                        ),

                        'jenis' => $this->cell(
                            $row,
                            $sheColumns['jenis']
                        ),

                        'nrp' => $this->cell(
                            $row,
                            $sheColumns['nrp']
                        ),

                        'status_raw' =>
                            $statusRaw !== ''
                                ? $statusRaw
                                : 'PROSES',

                        'status' =>
                            $status,
                    ];
                })
                ->filter(function (array $row): bool {
                    return
                        $row['nama'] !== '' ||
                        $row['nrp'] !== '' ||
                        $row['pengajuan'] !== '';
                })
                ->filter(function (array $row): bool {
                    /*
                     * Konsisten dengan halaman Monitoring SHE.
                     */
                    return $row['departemen'] === 'PRODUKSI';
                })
                ->sortByDesc('timestamp_sort')
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $dashboardSheRows = collect();

            $dashboardErrors[] =
                'Monitoring SHE: ' .
                $exception->getMessage();
        }

        /*
         * ==============================================================
         * DATA MONITORING INTERNAL UPLOAD
         * ==============================================================
         */
        try {
            $internalPayload = $this->cachedSpreadsheet(
                request: $request,
                cacheKey: 'mine-permit.monitoring-internal-upload',
                loader: fn (): array =>
                    $googleSheetsService
                        ->getMonitoringInternalUploadValues()
            );

            $internalValues = $internalPayload['values'];

            $lastSyncedInternal = $this->formatSyncTime(
                $internalPayload['synced_at']
            );

            /*
             * Posisi asli tab DATA PERPANJANGAN:
             * E Timestamp, F Nama, G NRP, H Berlaku Permit,
             * I Jabatan/Unit, J Jabatan, K Versatility.
             */
            $internalColumns = [
                'timestamp' => 4,
                'nama' => 5,
                'nrp' => 6,
                'tanggal_berlaku' => 7,
                'jabatan_operator' => 8,
                'jabatan' => 9,
                'versatility' => 10,
                'ktp_awal' => 11,
                'simpol' => 12,
                'pasfoto_awal' => 13,
                'simper_depan' => 14,
                'simper_belakang' => 15,
                'sertifikat' => 16,
                'foto_ktp_sekarang' => 17,
                'pasfoto_sekarang' => 18,
                'foto_sib' => 19,
            ];

            $dashboardPermitRows = collect($internalValues)
                ->skip(1)
                ->map(function (array $row) use (
                    $internalColumns,
                    $currentDate
                ): array {
                    $timestampRaw = $this->cell(
                        $row,
                        $internalColumns['timestamp']
                    );

                    $permitRaw = $this->cell(
                        $row,
                        $internalColumns['tanggal_berlaku']
                    );

                    $uploadDate =
                        $this->parseInternalUploadTimestamp(
                            $timestampRaw
                        );

                    $permitDate =
                        $this->parsePermitDate(
                            $permitRaw
                        );

                    $permitYear = $this->extractPermitYear(
                        $permitRaw,
                        $permitDate
                    );

                    $documents = [
                        $this->cell(
                            $row,
                            $internalColumns['ktp_awal']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['simpol']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['pasfoto_awal']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['simper_depan']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['simper_belakang']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['sertifikat']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['foto_ktp_sekarang']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['pasfoto_sekarang']
                        ),

                        $this->cell(
                            $row,
                            $internalColumns['foto_sib']
                        ),
                    ];

                    $availableDocuments = collect($documents)
                        ->filter(function (string $value): bool {
                            return $this
                                ->documentEntry($value)['available'];
                        })
                        ->count();

                    $documentStatus =
                        $availableDocuments === count($documents)
                            ? 'LENGKAP'
                            : 'BELUM LENGKAP';

                    $daysRemaining = $permitDate !== null
                        ? (int) $currentDate
                            ->copy()
                            ->startOfDay()
                            ->diffInDays(
                                $permitDate
                                    ->copy()
                                    ->startOfDay(),
                                false
                            )
                        : null;

                    $permitStatus = match (true) {
                        $daysRemaining === null =>
                            'TIDAK DIKETAHUI',

                        $daysRemaining < 0 =>
                            'EXPIRED',

                        $daysRemaining <= 30 =>
                            'AKAN EXPIRED',

                        default =>
                            'AKTIF',
                    };

                    $jabatanOperator = $this->cell(
                        $row,
                        $internalColumns['jabatan_operator']
                    );

                    $jabatan = $this->cell(
                        $row,
                        $internalColumns['jabatan']
                    );

                    return [
                        'nama' => $this->cell(
                            $row,
                            $internalColumns['nama']
                        ),

                        'nrp' => $this->cell(
                            $row,
                            $internalColumns['nrp']
                        ),

                        'jabatan' =>
                            $jabatanOperator !== ''
                                ? $jabatanOperator
                                : $jabatan,

                        'versatility' => $this->cell(
                            $row,
                            $internalColumns['versatility']
                        ),

                        'tanggal_berlaku' =>
                            $permitDate?->format('d M Y')
                            ?? $permitRaw,

                        'permit_year' =>
                            $permitYear,

                        'upload_sort' =>
                            $uploadDate?->timestamp ?? 0,

                        'document_status' =>
                            $documentStatus,

                        'permit_status' =>
                            $permitStatus,

                        'days_remaining' =>
                            $daysRemaining,

                        'available_documents' =>
                            $availableDocuments,

                        'total_documents' =>
                            count($documents),
                    ];
                })
                ->filter(function (array $row): bool {
                    return
                        $row['nama'] !== '' ||
                        $row['nrp'] !== '';
                })
                ->sortByDesc('upload_sort')
                ->unique(function (array $row): string {
                    /*
                     * Dashboard menampilkan kondisi terbaru per karyawan,
                     * bukan menghitung pengajuan lama berulang kali.
                     */
                    return $row['nrp'] !== ''
                        ? 'NRP:' . $row['nrp']
                        : 'NAMA:' . strtoupper($row['nama']);
                })
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $dashboardPermitRows = collect();

            $dashboardErrors[] =
                'Internal Upload: ' .
                $exception->getMessage();
        }

        /*
         * ==============================================================
         * FILTER DASHBOARD
         * ==============================================================
         */
        $sheYears = $dashboardSheRows
            ->pluck('year')
            ->filter(
                fn ($year): bool =>
                    is_int($year) && $year >= 2000
            );

        $permitYears = $dashboardPermitRows
            ->pluck('permit_year')
            ->filter(
                fn ($year): bool =>
                    is_int($year) && $year >= 2000
            );

        $dataYears = $sheYears
            ->merge($permitYears)
            ->unique()
            ->sortDesc()
            ->values();

        $availableDashboardYears = $dataYears
            ->push($currentDate->year)
            ->unique()
            ->sortDesc()
            ->values();

        $requestedYear = trim(
            (string) $request->query('year', '')
        );

        if ($requestedYear === 'all') {
            $selectedDashboardYear = 'all';
        } elseif (
            ctype_digit($requestedYear) &&
            $availableDashboardYears->contains(
                (int) $requestedYear
            )
        ) {
            $selectedDashboardYear =
                (int) $requestedYear;
        } elseif (
            $dataYears->contains($currentDate->year)
        ) {
            $selectedDashboardYear =
                $currentDate->year;
        } elseif ($dataYears->isNotEmpty()) {
            $selectedDashboardYear =
                (int) $dataYears->first();
        } else {
            $selectedDashboardYear =
                $currentDate->year;
        }

        $dashboardMonths = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $requestedMonth = trim(
            (string) $request->query('month', 'all')
        );

        if (
            $requestedMonth !== 'all' &&
            (
                !ctype_digit($requestedMonth) ||
                !array_key_exists(
                    (int) $requestedMonth,
                    $dashboardMonths
                )
            )
        ) {
            $requestedMonth = 'all';
        }

        $selectedDashboardMonth =
            $requestedMonth === 'all'
                ? 'all'
                : (int) $requestedMonth;

        $filteredDashboardShe = $dashboardSheRows
            ->filter(function (array $row) use (
                $selectedDashboardYear,
                $selectedDashboardMonth
            ): bool {
                $matchesYear =
                    $selectedDashboardYear === 'all' ||
                    $row['year'] ===
                        $selectedDashboardYear;

                $matchesMonth =
                    $selectedDashboardMonth === 'all' ||
                    $row['month'] ===
                        $selectedDashboardMonth;

                return $matchesYear && $matchesMonth;
            })
            ->values();

        $filteredDashboardPermits = $dashboardPermitRows
            ->filter(function (array $row) use (
                $selectedDashboardYear
            ): bool {
                return
                    $selectedDashboardYear === 'all' ||
                    $row['permit_year'] ===
                        $selectedDashboardYear;
            })
            ->values();

        /*
         * ==============================================================
         * STATISTIK UTAMA
         * ==============================================================
         */
        $dashboardStats = [
            'total_pengajuan' =>
                $filteredDashboardShe->count(),

            'selesai' =>
                $filteredDashboardShe
                    ->where('status', 'SELESAI')
                    ->count(),

            'proses' =>
                $filteredDashboardShe
                    ->where('status', 'PROSES')
                    ->count(),

            'gagal' =>
                $filteredDashboardShe
                    ->where('status', 'GAGAL')
                    ->count(),

            'dokumen_belum_lengkap' =>
                $filteredDashboardPermits
                    ->where(
                        'document_status',
                        'BELUM LENGKAP'
                    )
                    ->count(),

            'permit_perlu_perhatian' =>
                $filteredDashboardPermits
                    ->whereIn(
                        'permit_status',
                        [
                            'AKAN EXPIRED',
                            'EXPIRED',
                        ]
                    )
                    ->count(),
        ];

        $permitStats = [
            'aktif' =>
                $filteredDashboardPermits
                    ->where('permit_status', 'AKTIF')
                    ->count(),

            'akan_expired' =>
                $filteredDashboardPermits
                    ->where(
                        'permit_status',
                        'AKAN EXPIRED'
                    )
                    ->count(),

            'expired' =>
                $filteredDashboardPermits
                    ->where('permit_status', 'EXPIRED')
                    ->count(),

            'tidak_diketahui' =>
                $filteredDashboardPermits
                    ->where(
                        'permit_status',
                        'TIDAK DIKETAHUI'
                    )
                    ->count(),
        ];

        $permitTotal = array_sum($permitStats);

        $permitDegrees = [
            'aktif' => $permitTotal > 0
                ? round(
                    ($permitStats['aktif'] / $permitTotal)
                    * 360,
                    2
                )
                : 0,

            'akan_expired' => $permitTotal > 0
                ? round(
                    (
                        $permitStats['akan_expired']
                        / $permitTotal
                    ) * 360,
                    2
                )
                : 0,

            'expired' => $permitTotal > 0
                ? round(
                    ($permitStats['expired'] / $permitTotal)
                    * 360,
                    2
                )
                : 0,
        ];

        /*
         * ==============================================================
         * TREN BULANAN
         * ==============================================================
         */
        if ($selectedDashboardYear === 'all') {
            $trendYear = $sheYears
                ->contains($currentDate->year)
                    ? $currentDate->year
                    : (
                        $sheYears->sortDesc()->first()
                        ?? $currentDate->year
                    );
        } else {
            $trendYear = $selectedDashboardYear;
        }

        $trendRows = $dashboardSheRows
            ->where('year', $trendYear);

        $monthlyTrend = collect($dashboardMonths)
            ->map(function (
                string $monthName,
                int $monthNumber
            ) use ($trendRows): array {
                $monthRows = $trendRows
                    ->where('month', $monthNumber);

                return [
                    'number' =>
                        $monthNumber,

                    'label' =>
                        mb_substr($monthName, 0, 3),

                    'total' =>
                        $monthRows->count(),

                    'selesai' =>
                        $monthRows
                            ->where('status', 'SELESAI')
                            ->count(),

                    'proses' =>
                        $monthRows
                            ->where('status', 'PROSES')
                            ->count(),

                    'gagal' =>
                        $monthRows
                            ->where('status', 'GAGAL')
                            ->count(),
                ];
            })
            ->values();

        $monthlyTrendMax = max(
            1,
            (int) $monthlyTrend
                ->max('total')
        );

        /*
         * ==============================================================
         * TABEL RINGKAS
         * ==============================================================
         */
        $latestSubmissions = $filteredDashboardShe
            ->take(10)
            ->values();

        $attentionPermits = $filteredDashboardPermits
            ->filter(function (array $row): bool {
                return in_array(
                    $row['permit_status'],
                    [
                        'AKAN EXPIRED',
                        'EXPIRED',
                    ],
                    true
                );
            })
            ->sortBy(function (array $row): array {
                $priority = match (
                    $row['permit_status']
                ) {
                    'EXPIRED' => 0,
                    'AKAN EXPIRED' => 1,
                    default => 2,
                };

                return [
                    $priority,
                    $row['days_remaining'] ?? PHP_INT_MAX,
                ];
            })
            ->take(10)
            ->values();

        return view('manpower', [
            'contentView' =>
                'manpower.mine-permit.dashboard',

            'dashboardStats' =>
                $dashboardStats,

            'permitStats' =>
                $permitStats,

            'permitDegrees' =>
                $permitDegrees,

            'permitTotal' =>
                $permitTotal,

            'monthlyTrend' =>
                $monthlyTrend,

            'monthlyTrendMax' =>
                $monthlyTrendMax,

            'trendYear' =>
                $trendYear,

            'latestSubmissions' =>
                $latestSubmissions,

            'attentionPermits' =>
                $attentionPermits,

            'availableDashboardYears' =>
                $availableDashboardYears,

            'dashboardMonths' =>
                $dashboardMonths,

            'selectedDashboardYear' =>
                $selectedDashboardYear,

            'selectedDashboardMonth' =>
                $selectedDashboardMonth,

            'dashboardErrors' =>
                $dashboardErrors,

            'lastSyncedShe' =>
                $lastSyncedShe,

            'lastSyncedInternal' =>
                $lastSyncedInternal,
        ]);
    }

    /*
     * Alias sementara agar route/menu Monitoring Mine Permit lama
     * dapat diarahkan ke Dashboard tanpa menghapus kode lama.
     */
    public function monitoringMinePermit(
        Request $request,
        GoogleSheetsService $googleSheetsService
    ): View {
        return $this->dashboardMinePermit(
            $request,
            $googleSheetsService
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MONITORING SHE
    |--------------------------------------------------------------------------
    */

    public function monitoringShe(
        Request $request,
        GoogleSheetsService $googleSheetsService
    ): View|StreamedResponse {
        $sheetError = null;
        $lastSyncedAt = null;

        $currentDate = now(
            config('app.timezone', 'Asia/Jakarta')
        );

        try {
            $payload = $this->cachedSpreadsheet(
                request: $request,
                cacheKey: 'mine-permit.monitoring-she',
                loader: fn (): array =>
                    $googleSheetsService->getMonitoringSheValues()
            );

            $values = $payload['values'];

            $lastSyncedAt = $this->formatSyncTime(
                $payload['synced_at']
            );

            $headers = $this->normalizeHeaders(
                $values[0] ?? []
            );

            /*
             * Mapping tab Pengajuan.
             */
            $columns = [
                'timestamp' => $this->findColumn(
                    $headers,
                    ['timestamp'],
                    ['timestamp'],
                    0
                ),

                'nama' => $this->findColumn(
                    $headers,
                    [
                        'nama karyawan baru kapi',
                        'nama karyawan baru',
                    ],
                    ['karyawan baru'],
                    3
                ),

                'jabatan' => $this->findColumn(
                    $headers,
                    ['jabatan'],
                    ['jabatan'],
                    4
                ),

                'departemen' => $this->findColumn(
                    $headers,
                    ['departemen', 'department'],
                    ['departemen', 'department'],
                    5
                ),

                'perusahaan' => $this->findColumn(
                    $headers,
                    ['perusahaan'],
                    ['perusahaan'],
                    6
                ),

                /*
                 * Kolom K berisi SIM DLT / SIB DLT.
                 */
                'pengajuan' => $this->findColumn(
                    $headers,
                    ['pengajuan'],
                    [],
                    10
                ),

                /*
                 * Kolom L berisi tipe/unit kendaraan.
                 */
                'jenis' => $this->findColumn(
                    $headers,
                    ['jenis'],
                    [],
                    11
                ),

                'nrp' => $this->findColumn(
                    $headers,
                    ['nrp karyawan', 'nrp'],
                    ['nrp'],
                    13
                ),

                'status_she' => $this->findColumn(
                    $headers,
                    [
                        'column 19',
                        'status she',
                        'status proses she',
                    ],
                    ['column 19', 'status she'],
                    18
                ),

                'keterangan' => $this->findColumn(
                    $headers,
                    ['keterangan'],
                    ['keterangan'],
                    24
                ),

                'status_bnn' => $this->findColumn(
                    $headers,
                    ['status bnn'],
                    ['status bnn'],
                    25
                ),
            ];

            $monitoringSheRows = collect($values)
                ->skip(1)
                ->map(function (array $row) use (
                    $columns,
                    $currentDate
                ): array {
                    $timestampRaw = $this->cell(
                        $row,
                        $columns['timestamp']
                    );

                    $timestamp = $this->parseSpreadsheetTimestamp(
                        $timestampRaw
                    );

                    $statusSheRaw = $this->cell(
                        $row,
                        $columns['status_she']
                    );

                    $statusUpper = strtoupper($statusSheRaw);

                    /*
                     * Nilai asli tetap ditampilkan pada badge.
                     * Normalisasi hanya digunakan untuk warna,
                     * statistik, dan filter.
                     */
                    $status = match (true) {
                        str_contains($statusUpper, 'GAGAL') =>
                            'GAGAL',

                        str_contains($statusUpper, 'SELESAI') =>
                            'SELESAI',

                        default =>
                            'PROSES',
                    };

                    return [
                        'timestamp' =>
                            $timestampRaw,

                        'timestamp_sort' =>
                            $timestamp?->timestamp ?? 0,

                        'timestamp_year' =>
                            $timestamp?->year,

                        'timestamp_month' =>
                            $timestamp?->month,

                        'is_today' =>
                            $timestamp !== null &&
                            $timestamp->isSameDay($currentDate),

                        'nama' => $this->cell(
                            $row,
                            $columns['nama']
                        ),

                        'jabatan' => $this->cell(
                            $row,
                            $columns['jabatan']
                        ),

                        'departemen' => strtoupper(
                            $this->cell(
                                $row,
                                $columns['departemen']
                            )
                        ),

                        'perusahaan' => $this->cell(
                            $row,
                            $columns['perusahaan']
                        ),

                        'pengajuan' => $this->cell(
                            $row,
                            $columns['pengajuan']
                        ),

                        'jenis' => $this->cell(
                            $row,
                            $columns['jenis']
                        ),

                        'nrp' => $this->cell(
                            $row,
                            $columns['nrp']
                        ),

                        'status_she' =>
                            $statusSheRaw !== ''
                                ? $statusSheRaw
                                : 'PROSES',

                        'keterangan' => $this->cell(
                            $row,
                            $columns['keterangan']
                        ),

                        'status_bnn' => $this->cell(
                            $row,
                            $columns['status_bnn']
                        ),

                        'status' =>
                            $status,
                    ];
                })
                ->filter(function (array $row): bool {
                    return
                        $row['nama'] !== '' ||
                        $row['nrp'] !== '' ||
                        $row['pengajuan'] !== '';
                })
                ->filter(function (array $row): bool {
                    /*
                     * Monitoring SHE saat ini khusus Departemen PRODUKSI.
                     * Hapus filter ini bila semua departemen ingin ditampilkan.
                     */
                    return $row['departemen'] === 'PRODUKSI';
                })
                ->sortByDesc('timestamp_sort')
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $monitoringSheRows = collect();

            $sheetError =
                'Data Google Spreadsheet belum dapat dibaca: ' .
                $exception->getMessage();
        }

        /*
         * Pilihan filter dibuat otomatis berdasarkan data Spreadsheet.
         */
        $availableYears = $monitoringSheRows
            ->pluck('timestamp_year')
            ->filter(
                fn ($year): bool =>
                    is_int($year) && $year >= 2000
            )
            ->push($currentDate->year)
            ->unique()
            ->sortDesc()
            ->values();

        $availablePengajuan = $monitoringSheRows
            ->pluck('pengajuan')
            ->filter(
                fn ($value): bool =>
                    trim((string) $value) !== ''
            )
            ->unique()
            ->sort()
            ->values();

        $monthOptions = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $search = strtolower(
            trim((string) $request->query('search', ''))
        );

        $selectedYear = trim(
            (string) $request->query('year', 'all')
        );

        if (
            $selectedYear !== 'all' &&
            (
                !ctype_digit($selectedYear) ||
                !$availableYears->contains(
                    (int) $selectedYear
                )
            )
        ) {
            $selectedYear = 'all';
        }

        $selectedMonth = trim(
            (string) $request->query('month', 'all')
        );

        if (
            $selectedMonth !== 'all' &&
            (
                !ctype_digit($selectedMonth) ||
                !array_key_exists(
                    (int) $selectedMonth,
                    $monthOptions
                )
            )
        ) {
            $selectedMonth = 'all';
        }

        $selectedStatus = strtolower(
            trim((string) $request->query(
                'status',
                'all'
            ))
        );

        if (
            !in_array(
                $selectedStatus,
                [
                    'all',
                    'proses',
                    'selesai',
                    'gagal',
                ],
                true
            )
        ) {
            $selectedStatus = 'all';
        }

        $selectedPengajuan = trim(
            (string) $request->query(
                'pengajuan',
                'all'
            )
        );

        if (
            $selectedPengajuan !== 'all' &&
            !$availablePengajuan->contains(
                $selectedPengajuan
            )
        ) {
            $selectedPengajuan = 'all';
        }

        /*
         * Seluruh filter diterapkan sebelum statistik dan pagination.
         * Dengan demikian kartu statistik selalu mengikuti hasil filter.
         */
        $filteredMonitoringSheRows = $monitoringSheRows
            ->filter(function (array $row) use (
                $search,
                $selectedYear,
                $selectedMonth,
                $selectedStatus,
                $selectedPengajuan
            ): bool {
                $matchesSearch =
                    $search === '' ||
                    str_contains(
                        strtolower(
                            implode(' ', [
                                $row['timestamp'],
                                $row['nama'],
                                $row['jabatan'],
                                $row['departemen'],
                                $row['perusahaan'],
                                $row['pengajuan'],
                                $row['jenis'],
                                $row['nrp'],
                                $row['status_she'],
                                $row['keterangan'],
                                $row['status_bnn'],
                            ])
                        ),
                        $search
                    );

                $matchesYear =
                    $selectedYear === 'all' ||
                    $row['timestamp_year'] ===
                        (int) $selectedYear;

                $matchesMonth =
                    $selectedMonth === 'all' ||
                    $row['timestamp_month'] ===
                        (int) $selectedMonth;

                $matchesStatus =
                    $selectedStatus === 'all' ||
                    strtolower($row['status']) ===
                        $selectedStatus;

                $matchesPengajuan =
                    $selectedPengajuan === 'all' ||
                    $row['pengajuan'] ===
                        $selectedPengajuan;

                return
                    $matchesSearch &&
                    $matchesYear &&
                    $matchesMonth &&
                    $matchesStatus &&
                    $matchesPengajuan;
            })
            ->values();

        $totalHasilFilter =
            $filteredMonitoringSheRows->count();

        $prosesPengajuanBulanIni =
            $filteredMonitoringSheRows
                ->filter(function (array $row) use (
                    $currentDate
                ): bool {
                    return
                        $row['timestamp_year'] ===
                            $currentDate->year &&
                        $row['timestamp_month'] ===
                            $currentDate->month;
                })
                ->count();

        $totalSelesai =
            $filteredMonitoringSheRows
                ->where('status', 'SELESAI')
                ->count();

        $totalGagal =
            $filteredMonitoringSheRows
                ->where('status', 'GAGAL')
                ->count();

        /*
         * Export memakai route Monitoring SHE yang sama.
         * Tidak perlu menambah route baru.
         */
        if ($request->query('export') === 'csv') {
            return $this->streamMonitoringSheCsv(
                $filteredMonitoringSheRows
            );
        }

        $perPage = $this->validatedPerPage(
            $request,
            [25, 50, 100],
            50
        );

        $monitoringShePaginator = $this->paginateCollection(
            $filteredMonitoringSheRows,
            $request,
            $perPage
        );

        return view('manpower', [
            'contentView' =>
                'manpower.mine-permit.monitoring-she',

            'monitoringShePaginator' =>
                $monitoringShePaginator,

            'totalMonitoringSheRows' =>
                $monitoringSheRows->count(),

            'totalHasilFilter' =>
                $totalHasilFilter,

            'prosesPengajuanBulanIni' =>
                $prosesPengajuanBulanIni,

            'totalSelesai' =>
                $totalSelesai,

            'totalGagal' =>
                $totalGagal,

            'availableYears' =>
                $availableYears,

            'availablePengajuan' =>
                $availablePengajuan,

            'monthOptions' =>
                $monthOptions,

            'selectedYear' =>
                $selectedYear,

            'selectedMonth' =>
                $selectedMonth,

            'selectedStatus' =>
                $selectedStatus,

            'selectedPengajuan' =>
                $selectedPengajuan,

            'search' =>
                $request->query('search', ''),

            'perPage' =>
                $perPage,

            'sheetError' =>
                $sheetError,

            'lastSyncedAt' =>
                $lastSyncedAt,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | MONITORING INTERNAL UPLOAD
    |--------------------------------------------------------------------------
    */

    public function monitoringInternalUpload(
        Request $request,
        GoogleSheetsService $googleSheetsService
    ): View {
        $sheetError = null;
        $lastSyncedAt = null;

        $currentDate = now(
            config('app.timezone', 'Asia/Jakarta')
        );

        try {
            $payload = $this->cachedSpreadsheet(
                request: $request,
                cacheKey: 'mine-permit.monitoring-internal-upload',
                loader: fn (): array =>
                    $googleSheetsService
                        ->getMonitoringInternalUploadValues()
            );

            $values = $payload['values'];

            $lastSyncedAt = $this->formatSyncTime(
                $payload['synced_at']
            );

            /*
             * Mapping tetap berdasarkan posisi asli tab DATA PERPANJANGAN.
             *
             * Google Sheets API tetap menghitung kolom tersembunyi A-D.
             * Karena itu:
             *
             * E / index 4  = Timestamp
             * F / index 5  = Nama Lengkap
             * G / index 6  = NRP Karyawan
             * H / index 7  = Tanggal Berlaku Permit
             * I / index 8  = Jabatan Operator - Unit
             * J / index 9  = Jabatan
             * K / index 10 = Versatility Unit
             * L / index 11 = KTP Awal
             * M / index 12 = SIMPOL Aktif
             * N / index 13 = Pasfoto Awal
             * O / index 14 = SIMPER Depan
             * P / index 15 = SIMPER Belakang
             * Q / index 16 = Sertifikat/Piagam
             * R / index 17 = Foto KTP Sekarang
             * S / index 18 = Pasfoto Sekarang
             * T / index 19 = Foto SIB DLT Sekarang
             * V / index 21 = Email Aktif
             *
             * Mapping ini mencegah kolom Timestamp tersembunyi lain
             * terbaca secara keliru.
             */
            $columns = [
                'timestamp' => 4,
                'nama' => 5,
                'nrp' => 6,
                'tanggal_berlaku' => 7,
                'jabatan_operator' => 8,
                'jabatan' => 9,
                'versatility' => 10,
                'ktp_awal' => 11,
                'simpol' => 12,
                'pasfoto_awal' => 13,
                'simper_depan' => 14,
                'simper_belakang' => 15,
                'sertifikat' => 16,
                'foto_ktp_sekarang' => 17,
                'pasfoto_sekarang' => 18,
                'foto_sib' => 19,
                'email' => 21,
            ];

            $allEmployees = collect($values)
                ->skip(1)
                ->map(function (array $row) use (
                    $columns,
                    $currentDate
                ): array {
                    $timestampRaw = $this->cell(
                        $row,
                        $columns['timestamp']
                    );

                    $permitRaw = $this->cell(
                        $row,
                        $columns['tanggal_berlaku']
                    );

                    $uploadDate =
                        $this->parseInternalUploadTimestamp(
                            $timestampRaw
                        );

                    $permitDate =
                        $this->parsePermitDate(
                            $permitRaw
                        );

                    $permitYear = $this->extractPermitYear(
                        $permitRaw,
                        $permitDate
                    );

                    $documents = [
                        'KTP Awal' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['ktp_awal']
                            )
                        ),

                        'SIMPOL Aktif' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['simpol']
                            )
                        ),

                        'Pasfoto Awal' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['pasfoto_awal']
                            )
                        ),

                        'SIMPER Depan' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['simper_depan']
                            )
                        ),

                        'SIMPER Belakang' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['simper_belakang']
                            )
                        ),

                        'Sertifikat/Piagam' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['sertifikat']
                            )
                        ),

                        'Foto KTP Sekarang' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['foto_ktp_sekarang']
                            )
                        ),

                        'Pasfoto Sekarang' => $this->documentEntry(
                            $this->cell(
                                $row,
                                $columns['pasfoto_sekarang']
                            )
                        ),

                        'Foto SIB DLT Sekarang' =>
                            $this->documentEntry(
                                $this->cell(
                                    $row,
                                    $columns['foto_sib']
                                )
                            ),
                    ];

                    $totalDokumen = count($documents);

                    $dokumenTerisi = collect($documents)
                        ->filter(
                            fn (array $document): bool =>
                                $document['available']
                        )
                        ->count();

                    $documentStatus =
                        $totalDokumen > 0 &&
                        $dokumenTerisi === $totalDokumen
                            ? 'lengkap'
                            : 'belum-lengkap';

                    $daysRemaining = $permitDate !== null
                        ? (int) $currentDate
                            ->copy()
                            ->startOfDay()
                            ->diffInDays(
                                $permitDate
                                    ->copy()
                                    ->startOfDay(),
                                false
                            )
                        : null;

                    $permitStatus = match (true) {
                        $daysRemaining === null =>
                            'tidak-diketahui',

                        $daysRemaining < 0 =>
                            'expired',

                        $daysRemaining <= 30 =>
                            'akan-expired',

                        default =>
                            'aktif',
                    };

                    $jabatanOperator = $this->cell(
                        $row,
                        $columns['jabatan_operator']
                    );

                    $jabatanTambahan = $this->cell(
                        $row,
                        $columns['jabatan']
                    );

                    return [
                        'nama' => $this->cell(
                            $row,
                            $columns['nama']
                        ),

                        'nrp' => $this->cell(
                            $row,
                            $columns['nrp']
                        ),

                        'jabatan' =>
                            $jabatanOperator !== ''
                                ? $jabatanOperator
                                : $jabatanTambahan,

                        'jabatan_tambahan' =>
                            $jabatanTambahan,

                        'versatility' => $this->cell(
                            $row,
                            $columns['versatility']
                        ),

                        'tanggal_berlaku' =>
                            $permitDate?->format('d M Y')
                            ?? $permitRaw,

                        'tanggal_berlaku_raw' =>
                            $permitRaw,

                        /*
                         * Contoh:
                         * 6/27/2026 8:11:15
                         * menjadi 27 Jun 2026, 08:11:15 WIB.
                         */
                        'uploaded_at' =>
                            $uploadDate !== null
                                ? $uploadDate->format(
                                    'd M Y, H:i:s'
                                ) . ' WIB'
                                : $timestampRaw,

                        'timestamp_raw' =>
                            $timestampRaw,

                        'email' => $this->cell(
                            $row,
                            $columns['email']
                        ),

                        'dokumen_terisi' =>
                            $dokumenTerisi,

                        'total_dokumen' =>
                            $totalDokumen,

                        'document_status' =>
                            $documentStatus,

                        'permit_status' =>
                            $permitStatus,

                        'days_remaining' =>
                            $daysRemaining,

                        'permit_year' =>
                            $permitYear,

                        'upload_year' =>
                            $uploadDate?->year,

                        'upload_month' =>
                            $uploadDate?->month,

                        'upload_sort' =>
                            $uploadDate?->timestamp ?? 0,

                        'documents' =>
                            $documents,
                    ];
                })
                ->filter(function (array $employee): bool {
                    return
                        $employee['nama'] !== '' ||
                        $employee['nrp'] !== '';
                })
                ->sortByDesc('upload_sort')
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $allEmployees = collect();

            $sheetError =
                'Data Google Spreadsheet belum dapat dibaca: ' .
                $exception->getMessage();
        }

        /*
         * Tahun yang benar-benar memiliki data.
         */
        $dataYears = $allEmployees
            ->pluck('permit_year')
            ->filter(
                fn ($year): bool =>
                    is_int($year) && $year >= 2000
            )
            ->unique()
            ->sortDesc()
            ->values();

        /*
         * Dropdown selalu menyertakan tahun kalender saat ini.
         * Contoh: ketika sistem memasuki 2027, pilihan Tahun 2027
         * otomatis muncul walaupun data 2027 belum tersedia.
         */
        $availableYears = $dataYears
            ->push($currentDate->year)
            ->unique()
            ->sortDesc()
            ->values();

        $requestedYear = trim(
            (string) $request->query('year', '')
        );

        if ($requestedYear === 'all') {
            $selectedYear = 'all';
        } elseif (
            ctype_digit($requestedYear) &&
            $availableYears->contains((int) $requestedYear)
        ) {
            $selectedYear = (int) $requestedYear;
        } elseif (
            /*
             * Tahun berjalan dipilih otomatis hanya jika benar-benar
             * memiliki data, supaya halaman tidak mendadak kosong.
             */
            $dataYears->contains($currentDate->year)
        ) {
            $selectedYear = $currentDate->year;
        } elseif ($dataYears->isNotEmpty()) {
            $selectedYear = (int) $dataYears->first();
        } else {
            $selectedYear = $currentDate->year;
        }

        $employeesForYear = $allEmployees
            ->filter(function (array $employee) use (
                $selectedYear
            ): bool {
                if ($selectedYear === 'all') {
                    return true;
                }

                return
                    $employee['permit_year'] ===
                    $selectedYear;
            })
            ->values();

        $search = strtolower(
            trim((string) $request->query('search', ''))
        );

        $selectedStatus = (string) $request->query(
            'status',
            'semua'
        );

        $filteredEmployees = $employeesForYear
            ->filter(function (array $employee) use (
                $search,
                $selectedStatus
            ): bool {
                $matchesSearch =
                    $search === '' ||
                    str_contains(
                        strtolower(
                            implode(' ', [
                                $employee['nama'],
                                $employee['nrp'],
                                $employee['jabatan'],
                                $employee['jabatan_tambahan'],
                                $employee['versatility'],
                                $employee['email'],
                                $employee['tanggal_berlaku'],
                                $employee['uploaded_at'],
                                $employee['timestamp_raw'],
                            ])
                        ),
                        $search
                    );

                $matchesStatus = match ($selectedStatus) {
                    'lengkap' =>
                        $employee['document_status'] ===
                        'lengkap',

                    'belum-lengkap' =>
                        $employee['document_status'] ===
                        'belum-lengkap',

                    'akan-expired' =>
                        $employee['permit_status'] ===
                        'akan-expired',

                    'expired' =>
                        $employee['permit_status'] ===
                        'expired',

                    default =>
                        true,
                };

                return $matchesSearch && $matchesStatus;
            })
            ->values();

        $totalData = $employeesForYear->count();

        $totalUploadBulanIni = $employeesForYear
            ->filter(function (array $employee) use (
                $currentDate
            ): bool {
                return
                    $employee['upload_year'] ===
                        $currentDate->year &&
                    $employee['upload_month'] ===
                        $currentDate->month;
            })
            ->count();

        $totalLengkap = $employeesForYear
            ->where('document_status', 'lengkap')
            ->count();

        $totalBelumLengkap = $employeesForYear
            ->where('document_status', 'belum-lengkap')
            ->count();

        $totalAkanExpired = $employeesForYear
            ->where('permit_status', 'akan-expired')
            ->count();

        $totalExpired = $employeesForYear
            ->where('permit_status', 'expired')
            ->count();

        $perPage = $this->validatedPerPage(
            $request,
            [18, 30, 60],
            30
        );

        $employeePaginator = $this->paginateCollection(
            $filteredEmployees,
            $request,
            $perPage
        );

        return view('manpower', [
            'contentView' =>
                'manpower.mine-permit.monitoring-internal-upload',

            'employeePaginator' =>
                $employeePaginator,

            'selectedStatus' =>
                $selectedStatus,

            'availableYears' =>
                $availableYears,

            'selectedYear' =>
                $selectedYear,

            'selectedYearLabel' =>
                $selectedYear === 'all'
                    ? 'Semua Tahun'
                    : (string) $selectedYear,

            'currentYear' =>
                $currentDate->year,

            'totalData' =>
                $totalData,

            'totalUploadBulanIni' =>
                $totalUploadBulanIni,

            'totalLengkap' =>
                $totalLengkap,

            'totalBelumLengkap' =>
                $totalBelumLengkap,

            'totalAkanExpired' =>
                $totalAkanExpired,

            'totalExpired' =>
                $totalExpired,

            'sheetError' =>
                $sheetError,

            'search' =>
                $request->query('search', ''),

            'perPage' =>
                $perPage,

            'lastSyncedAt' =>
                $lastSyncedAt,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */


    private function streamMonitoringSheCsv(
        Collection $rows
    ): StreamedResponse {
        $filename =
            'monitoring-she-' .
            now(
                config(
                    'app.timezone',
                    'Asia/Jakarta'
                )
            )->format('Y-m-d-His') .
            '.csv';

        return response()->streamDownload(
            function () use ($rows): void {
                $output = fopen('php://output', 'wb');

                if ($output === false) {
                    return;
                }

                /*
                 * BOM UTF-8 agar nama dan karakter khusus terbaca
                 * dengan benar ketika dibuka di Microsoft Excel.
                 */
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv(
                    $output,
                    [
                        'Timestamp',
                        'Nama Karyawan',
                        'Jabatan',
                        'Departemen',
                        'Perusahaan',
                        'Pengajuan',
                        'Jenis',
                        'NRP',
                        'Status SHE',
                        'Keterangan',
                        'Status BNN',
                    ],
                    ';'
                );

                foreach ($rows as $row) {
                    fputcsv(
                        $output,
                        [
                            $row['timestamp'],
                            $row['nama'],
                            $row['jabatan'],
                            $row['departemen'],
                            $row['perusahaan'],
                            $row['pengajuan'],
                            $row['jenis'],
                            $row['nrp'],
                            $row['status_she'],
                            $row['keterangan'],
                            $row['status_bnn'],
                        ],
                        ';'
                    );
                }

                fclose($output);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    private function cachedSpreadsheet(
        Request $request,
        string $cacheKey,
        callable $loader
    ): array {
        if ($request->has('refresh')) {
            Cache::forget($cacheKey);
        }

        $payload = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($loader): array {
                return [
                    'values' => $loader(),
                    'synced_at' => now()->toIso8601String(),
                ];
            }
        );

        return [
            'values' =>
                is_array($payload['values'] ?? null)
                    ? $payload['values']
                    : [],

            'synced_at' =>
                (string) ($payload['synced_at'] ?? ''),
        ];
    }

    private function normalizeHeaders(array $headerRow): array
    {
        return collect($headerRow)
            ->map(function ($header): string {
                $normalized = strtolower(
                    trim((string) $header)
                );

                $normalized = preg_replace(
                    '/[^a-z0-9]+/i',
                    ' ',
                    $normalized
                );

                return trim((string) $normalized);
            })
            ->values()
            ->all();
    }

    private function findColumn(
        array $headers,
        array $exactAliases,
        array $containsAliases,
        int $fallbackIndex
    ): int {
        foreach ($exactAliases as $alias) {
            $index = array_search(
                strtolower(trim($alias)),
                $headers,
                true
            );

            if ($index !== false) {
                return (int) $index;
            }
        }

        foreach ($headers as $index => $header) {
            foreach ($containsAliases as $alias) {
                $normalizedAlias = strtolower(
                    trim($alias)
                );

                if (
                    $normalizedAlias !== '' &&
                    str_contains($header, $normalizedAlias)
                ) {
                    return (int) $index;
                }
            }
        }

        return $fallbackIndex;
    }

    private function findAllColumns(
        array $headers,
        array $exactAliases,
        array $containsAliases
    ): array {
        $matches = [];

        foreach ($headers as $index => $header) {
            $matched = in_array(
                $header,
                array_map(
                    fn ($alias): string =>
                        strtolower(trim($alias)),
                    $exactAliases
                ),
                true
            );

            if (!$matched) {
                foreach ($containsAliases as $alias) {
                    $normalizedAlias = strtolower(
                        trim($alias)
                    );

                    if (
                        $normalizedAlias !== '' &&
                        str_contains(
                            $header,
                            $normalizedAlias
                        )
                    ) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $matches[] = (int) $index;
            }
        }

        return $matches;
    }

    private function cell(array $row, int $index): string
    {
        return trim(
            (string) ($row[$index] ?? '')
        );
    }

    private function documentEntry(string $value): array
    {
        $normalized = strtoupper(trim($value));

        $available =
            $normalized !== '' &&
            !in_array(
                $normalized,
                [
                    '-',
                    'N/A',
                    'NA',
                    'BELUM ADA',
                    'BELUM UPLOAD',
                    'TIDAK ADA',
                ],
                true
            );

        return [
            'available' => $available,

            'url' =>
                $available &&
                filter_var($value, FILTER_VALIDATE_URL)
                    ? $value
                    : null,
        ];
    }

    private function validatedPerPage(
        Request $request,
        array $allowed,
        int $default
    ): int {
        $perPage = (int) $request->query(
            'per_page',
            $default
        );

        return in_array($perPage, $allowed, true)
            ? $perPage
            : $default;
    }

    private function paginateCollection(
        Collection $items,
        Request $request,
        int $perPage,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $currentPage = max(
            1,
            (int) $request->query($pageName, 1)
        );

        $lastPage = max(
            1,
            (int) ceil($items->count() / $perPage)
        );

        $currentPage = min($currentPage, $lastPage);

        return new LengthAwarePaginator(
            $items
                ->forPage($currentPage, $perPage)
                ->values(),

            $items->count(),

            $perPage,

            $currentPage,

            [
                'path' => $request->url(),

                'query' => $request->except([
                    $pageName,
                    'refresh',
                ]),

                'pageName' => $pageName,
            ]
        );
    }

    private function formatSyncTime(string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->timezone(
                    config(
                        'app.timezone',
                        'Asia/Jakarta'
                    )
                )
                ->format('d M Y, H:i:s') .
                ' WIB';
        } catch (Throwable) {
            return null;
        }
    }

    private function parseSpreadsheetTimestamp(
        string $value
    ): ?Carbon {
        return $this->parseByFormats(
            $value,
            [
                'd/m/Y H:i:s',
                'd/m/Y H:i',
                'j/n/Y H:i:s',
                'j/n/Y H:i',
                'd/m/Y',
                'j/n/Y',
            ]
        );
    }

    private function parseInternalUploadTimestamp(
        string $value
    ): ?Carbon {
        $parsed = $this->parseByFormats(
            $value,
            [
                'm/d/Y H:i:s',
                'm/d/Y H:i',
                'n/j/Y H:i:s',
                'n/j/Y H:i',

                'd/m/Y H:i:s',
                'd/m/Y H:i',
                'j/n/Y H:i:s',
                'j/n/Y H:i',

                'Y-m-d H:i:s',
                'Y-m-d H:i',
            ]
        );

        if ($parsed !== null) {
            return $parsed;
        }

        try {
            return Carbon::parse(
                trim($value),
                config(
                    'app.timezone',
                    'Asia/Jakarta'
                )
            );
        } catch (Throwable) {
            return null;
        }
    }


    private function extractPermitYear(
        string $value,
        ?Carbon $parsedDate = null
    ): ?int {
        if ($parsedDate !== null) {
            return $parsedDate->year;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        /*
         * Contoh yang didukung:
         * 25-Mar-2026
         * 25-Mar-26
         * 25/03/2026
         * 03/25/2026
         */
        if (
            preg_match(
                '/(?:19|20)\d{2}/',
                $value,
                $matches
            )
        ) {
            return (int) $matches[0];
        }

        if (
            preg_match(
                '/(?:^|[^0-9])(\d{2})(?:\s*)$/',
                $value,
                $matches
            )
        ) {
            $shortYear = (int) $matches[1];

            return $shortYear >= 70
                ? 1900 + $shortYear
                : 2000 + $shortYear;
        }

        try {
            return Carbon::parse(
                $value,
                config(
                    'app.timezone',
                    'Asia/Jakarta'
                )
            )->year;
        } catch (Throwable) {
            return null;
        }
    }

    private function parsePermitDate(
        string $value
    ): ?Carbon {
        return $this->parseByFormats(
            $value,
            [
                'd-M-y',
                'j-M-y',
                'd-M-Y',
                'j-M-Y',
                'd/m/Y',
                'j/n/Y',
                'm/d/Y',
                'n/j/Y',
            ]
        );
    }

    private function parseByFormats(
        string $value,
        array $formats
    ): ?Carbon {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $timezone = config(
            'app.timezone',
            'Asia/Jakarta'
        );

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat(
                    $format,
                    $value,
                    $timezone
                );
            } catch (Throwable) {
                // Coba format berikutnya.
            }
        }

        return null;
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class ManpowerDashboardController extends Controller
{
    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /**
     * Menampilkan dashboard Manpower dari data yang tersimpan.
     *
     * Tabel yang belum tersedia tidak akan menyebabkan error 500.
     * Modul tersebut akan ditampilkan sebagai "Belum terhubung".
     */
    public function index(Request $request): View
    {
        Carbon::setLocale('id');

        $bulan = $this->validMonth(
            $request->input('bulan', now()->format('Y-m'))
        );

        $periode = Carbon::createFromFormat('Y-m', $bulan);
        $awalBulan = $periode->copy()->startOfMonth();
        $akhirBulan = $periode->copy()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Tabel utama yang sudah digunakan fitur aplikasi
        |--------------------------------------------------------------------------
        */

        $documentOutAvailable = Schema::hasTable('surat_keluar');
        $coachingAvailable = Schema::hasTable('coaching_counsellings');
        $stSpAvailable = Schema::hasTable('st_sp_records');

        $documentOutTotal = $this->safeCount('surat_keluar');
        $coachingTotal = $this->safeCount('coaching_counsellings');

        $teguranTotal = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('jenis', 'TEGURAN')
        );

        $peringatanTotal = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->whereIn(
                'jenis',
                [
                    'PERINGATAN PERTAMA',
                    'PERINGATAN KEDUA',
                    'PERINGATAN KETIGA',
                ]
            )
        );

        $stSpAktif = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('status', 'AKTIF')
        );

        $stSpExpired = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('status', 'EXPIRED')
        );

        /*
        |--------------------------------------------------------------------------
        | Tabel modul lain
        |--------------------------------------------------------------------------
        | Beberapa menu masih dapat memakai Google Sheets atau nama tabel yang
        | berbeda. Controller mencoba beberapa nama tabel umum secara aman.
        */

        $minePermitTable = $this->firstExistingTable([
            'mine_permits',
            'monitoring_mine_permits',
            'monitoring_she',
            'monitoring_internal_uploads',
        ]);

        $bastTable = $this->firstExistingTable([
            'bast_assets',
            'bast_asset',
            'berita_acara_assets',
            'asset_handover_records',
        ]);

        $apdTable = $this->firstExistingTable([
            'apd_requests',
            'monitoring_apds',
            'apd_records',
            'apd_submissions',
            'pengajuan_apds',
        ]);

        $mcuTable = $this->firstExistingTable([
            'mcu_fu_records',
            'mcu_records',
            'monitoring_mcu',
            'medical_follow_ups',
        ]);

        /*
         * Mine Permit pada aplikasi ini bersumber dari Google Sheets.
         * Tabel lokal hanya dipakai sebagai fallback untuk instalasi lama.
         */
        $minePermitSummary = $this->minePermitSummary(
            $minePermitTable,
            $awalBulan,
            $akhirBulan
        );

        $bnnSummary = $this->bnnSummary(
            $awalBulan,
            $akhirBulan
        );

        $mcuSummary = $this->mcuSummary(
            $mcuTable,
            $awalBulan,
            $akhirBulan
        );

        $minePermitTotal = $minePermitSummary['total'];
        $bnnTotal = $bnnSummary['total'] ?? 0;
        $bastTotal = $this->safeCount($bastTable);
        $apdTotal = $this->safeCount($apdTable);
        $mcuTotal = $mcuSummary['total'];

        /*
        |--------------------------------------------------------------------------
        | Data pada bulan yang dipilih
        |--------------------------------------------------------------------------
        */

        $documentOutBulan = $this->safeCountByPeriod(
            'surat_keluar',
            'tanggal_surat',
            $awalBulan,
            $akhirBulan
        );

        $coachingBulan = $this->safeCountByPeriod(
            'coaching_counsellings',
            'tanggal',
            $awalBulan,
            $akhirBulan
        );

        $teguranBulan = $this->safeCountByPeriod(
            'st_sp_records',
            'tanggal',
            $awalBulan,
            $akhirBulan,
            fn ($query) => $query->where('jenis', 'TEGURAN')
        );

        $peringatanBulan = $this->safeCountByPeriod(
            'st_sp_records',
            'tanggal',
            $awalBulan,
            $akhirBulan,
            fn ($query) => $query->whereIn(
                'jenis',
                [
                    'PERINGATAN PERTAMA',
                    'PERINGATAN KEDUA',
                    'PERINGATAN KETIGA',
                ]
            )
        );

        $minePermitBulan = $minePermitSummary['month'];
        $bnnBulan = $bnnSummary['month'];

        $bastBulan = $this->safeCountOptionalModuleByPeriod(
            $bastTable,
            $awalBulan,
            $akhirBulan
        );

        $apdBulan = $this->safeCountOptionalModuleByPeriod(
            $apdTable,
            $awalBulan,
            $akhirBulan
        );

        $mcuBulan = $mcuSummary['month'];

        $totalTersimpan = array_sum([
            $documentOutTotal,
            $coachingTotal,
            $teguranTotal,
            $peringatanTotal,
            $minePermitTotal,
            $bnnTotal,
            $bastTotal,
            $apdTotal,
            $mcuTotal,
        ]);

        $totalBulan = array_sum([
            $documentOutBulan,
            $coachingBulan,
            $teguranBulan,
            $peringatanBulan,
            $minePermitBulan,
            $bnnBulan,
            $bastBulan,
            $apdBulan,
            $mcuBulan,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Kartu seluruh fitur
        |--------------------------------------------------------------------------
        */

        $features = [
            [
                'title' => 'Mine Permit',
                'icon' => '⛏',
                'total' => $minePermitTotal,
                'month' => $minePermitBulan,
                'available' => $minePermitSummary['available'],
                'description' => match ($minePermitSummary['source']) {
                    'google_sheets' => 'Data pengajuan dari Google Sheets',
                    'local' => 'Data Mine Permit tersimpan',
                    default => 'Sumber data Mine Permit belum terhubung',
                },
                'url' => route('mine-permit.dashboard'),
                'tone' => 'orange',
            ],
            [
                'title' => 'Test BNN',
                'icon' => '⚕',
                'total' => $bnnSummary['total'],
                'month' => $bnnBulan,
                'available' => $bnnSummary['available'],
                'description' => $bnnSummary['available']
                    ? 'Peserta HADIR pada sheet PRO'
                    : 'Data BNN dari sheet PRO belum dapat dibaca',
                'url' => route('bnn.monitoring'),
                'tone' => 'purple',
            ],
            [
                'title' => 'Berita Acara Asset',
                'icon' => '▣',
                'total' => $bastTotal,
                'month' => $bastBulan,
                'available' => $bastTable !== null,
                'description' => $bastTable
                    ? 'Seluruh berita acara asset'
                    : 'Menu tersedia, tabel lokal belum terdeteksi',
                'url' => route('bast.index'),
                'tone' => 'blue',
            ],
            [
                'title' => 'Monitoring APD',
                'icon' => '♙',
                'total' => $apdTotal,
                'month' => $apdBulan,
                'available' => $apdTable !== null,
                'description' => $apdTable
                    ? 'Data pengajuan dan monitoring APD'
                    : 'Tabel APD belum terhubung',
                'url' => route('apd.index'),
                'tone' => 'green',
            ],
            [
                'title' => 'Coaching & Counselling',
                'icon' => '◉',
                'total' => $coachingTotal,
                'month' => $coachingBulan,
                'available' => $coachingAvailable,
                'description' => 'Data pembinaan berdasarkan NRP',
                'url' => route('cc-st-sp.coaching.index'),
                'tone' => 'cyan',
            ],
            [
                'title' => 'Surat Teguran',
                'icon' => '!',
                'total' => $teguranTotal,
                'month' => $teguranBulan,
                'available' => $stSpAvailable,
                'description' => 'Teguran yang sudah tersimpan',
                'url' => route('cc-st-sp.teguran.index'),
                'tone' => 'yellow',
            ],
            [
                'title' => 'Surat Peringatan',
                'icon' => '⚠',
                'total' => $peringatanTotal,
                'month' => $peringatanBulan,
                'available' => $stSpAvailable,
                'description' => 'SP pertama, kedua, dan ketiga',
                'url' => route('cc-st-sp.peringatan.index'),
                'tone' => 'red',
            ],
            [
                'title' => 'MCU & FU',
                'icon' => '✚',
                'total' => $mcuTotal,
                'month' => $mcuBulan,
                'available' => $mcuSummary['available'],
                'description' => match ($mcuSummary['source']) {
                    'google_sheets' => 'Data MCU & FU dari Google Sheets',
                    'local' => 'Data MCU dan tindak lanjut',
                    default => 'Sumber data MCU & FU belum terhubung',
                },
                'url' => route('mcu-fu.index'),
                'tone' => 'pink',
            ],
            [
                'title' => 'Document Out',
                'icon' => '↗',
                'total' => $documentOutTotal,
                'month' => $documentOutBulan,
                'available' => $documentOutAvailable,
                'description' => 'Dokumen dan surat keluar beserta PDF',
                'url' => route('document-out.index'),
                'tone' => 'navy',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Tren enam bulan
        |--------------------------------------------------------------------------
        */

        $trend = collect();

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $periode->copy()->subMonths($offset);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $documentOut = $this->safeCountByPeriod(
                'surat_keluar',
                'tanggal_surat',
                $start,
                $end
            );

            $coaching = $this->safeCountByPeriod(
                'coaching_counsellings',
                'tanggal',
                $start,
                $end
            );

            $teguran = $this->safeCountByPeriod(
                'st_sp_records',
                'tanggal',
                $start,
                $end,
                fn ($query) => $query->where('jenis', 'TEGURAN')
            );

            $peringatan = $this->safeCountByPeriod(
                'st_sp_records',
                'tanggal',
                $start,
                $end,
                fn ($query) => $query->whereIn(
                    'jenis',
                    [
                        'PERINGATAN PERTAMA',
                        'PERINGATAN KEDUA',
                        'PERINGATAN KETIGA',
                    ]
                )
            );

            $trend->push([
                'label' => $month->translatedFormat('M Y'),
                'document_out' => $documentOut,
                'coaching' => $coaching,
                'teguran' => $teguran,
                'peringatan' => $peringatan,
                'total' => (
                    $documentOut
                    + $coaching
                    + $teguran
                    + $peringatan
                ),
            ]);
        }

        $maxTrend = max(1, (int) $trend->max('total'));

        /*
        |--------------------------------------------------------------------------
        | Aktivitas terbaru
        |--------------------------------------------------------------------------
        */

        $recentActivities = $this->recentActivities();

        return view('manpower', [
            'contentView' => 'manpower.dashboard',
            'bulan' => $bulan,
            'periodeLabel' => $periode->translatedFormat('F Y'),
            'summary' => [
                'total' => $totalTersimpan,
                'month' => $totalBulan,
                'active' => $stSpAktif,
                'expired' => $stSpExpired,
            ],
            'features' => $features,
            'trend' => $trend,
            'maxTrend' => $maxTrend,
            'recentActivities' => $recentActivities,
            'stSpStatus' => [
                'active' => $stSpAktif,
                'expired' => $stSpExpired,
                'total' => $stSpAktif + $stSpExpired,
            ],
        ]);
    }

    private function validMonth(mixed $month): string
    {
        $month = (string) $month;

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            ? $month
            : now()->format('Y-m');
    }

    /**
     * Mengambil ringkasan Mine Permit dari sumber yang benar.
     *
     * Google Sheets menjadi sumber utama karena halaman Mine Permit juga
     * membaca sheet Monitoring SHE. Jika koneksi sheet sedang tidak tersedia,
     * controller tetap aman dan mencoba tabel lokal sebagai fallback.
     *
     * @return array{total: int, month: int, available: bool, source: ?string}
     */
    private function minePermitSummary(
        ?string $fallbackTable,
        Carbon $start,
        Carbon $end
    ): array {
        $sheetSummary = $this->safeMinePermitSheetSummary(
            $start,
            $end
        );

        if ($sheetSummary !== null) {
            return [
                'total' => $sheetSummary['total'],
                'month' => $sheetSummary['month'],
                'available' => true,
                'source' => 'google_sheets',
            ];
        }

        if ($fallbackTable !== null) {
            return [
                'total' => $this->safeCount($fallbackTable),
                'month' => $this->safeCountOptionalModuleByPeriod(
                    $fallbackTable,
                    $start,
                    $end
                ),
                'available' => true,
                'source' => 'local',
            ];
        }

        return [
            'total' => 0,
            'month' => 0,
            'available' => false,
            'source' => null,
        ];
    }

    /**
     * Menghitung hanya peserta BNN dengan KETERANGAN bernilai HADIR.
     *
     * @return array{total: ?int, month: int, available: bool}
     */
    private function bnnSummary(
        Carbon $start,
        Carbon $end
    ): array {
        try {
            if (! $this->googleSheets->hasStoredToken()) {
                return $this->unavailableBnnSummary();
            }

            $spreadsheetId = trim((string) config(
                'services.google_sheets.test_bnn_spreadsheet_id'
            ));

            $range = trim((string) config(
                'services.google_sheets.test_bnn_range',
                "'PRO'!A:AZ"
            ));

            if ($spreadsheetId === '' || $range === '') {
                return $this->unavailableBnnSummary();
            }

            $values = $this->googleSheets->getValues(
                $spreadsheetId,
                $range
            );

            $sheet = $this->tabularSheetData($values);
            $attendanceColumn = $this->findSheetColumn(
                $sheet['headers'],
                ['KETERANGAN']
            );

            if ($attendanceColumn === null) {
                return $this->unavailableBnnSummary();
            }

            $dateColumn = $this->findSheetColumn(
                $sheet['headers'],
                [
                    'TANGGAL PEMERIKSAAN',
                    'TGL PEMERIKSAAN',
                ]
            );

            $presentRows = array_values(array_filter(
                $sheet['rows'],
                fn (array $row): bool =>
                    $this->normalizeSheetHeader(
                        $row[$attendanceColumn] ?? ''
                    ) === 'HADIR'
            ));

            return [
                'total' => count($presentRows),
                'month' => $dateColumn === null
                    ? 0
                    : $this->countSheetRowsByPeriod(
                        $presentRows,
                        $dateColumn,
                        $start,
                        $end
                    ),
                'available' => true,
            ];
        } catch (Throwable) {
            return $this->unavailableBnnSummary();
        }
    }

    /**
     * @return array{total: null, month: int, available: false}
     */
    private function unavailableBnnSummary(): array
    {
        return [
            'total' => null,
            'month' => 0,
            'available' => false,
        ];
    }

    /**
     * @return array{total: int, month: int, available: bool, source: ?string}
     */
    private function mcuSummary(
        ?string $fallbackTable,
        Carbon $start,
        Carbon $end
    ): array {
        $sheetSummary = $this->safeMcuSheetSummary(
            $start,
            $end
        );

        if ($sheetSummary !== null) {
            return [
                'total' => $sheetSummary['total'],
                'month' => $sheetSummary['month'],
                'available' => true,
                'source' => 'google_sheets',
            ];
        }

        if ($fallbackTable !== null) {
            return [
                'total' => $this->safeCount($fallbackTable),
                'month' => $this->safeCountOptionalModuleByPeriod(
                    $fallbackTable,
                    $start,
                    $end
                ),
                'available' => true,
                'source' => 'local',
            ];
        }

        return [
            'total' => 0,
            'month' => 0,
            'available' => false,
            'source' => null,
        ];
    }

    /**
     * @return array{total: int, month: int}|null
     */
    private function safeMcuSheetSummary(
        Carbon $start,
        Carbon $end
    ): ?array {
        try {
            if (! $this->googleSheets->hasStoredToken()) {
                return null;
            }

            $spreadsheetId = trim((string) config(
                'services.google_sheets.mcu_fu_spreadsheet_id',
                '1egikgV_mfFYCepDl9hQnjgCvLx4H8JL5_NYw7I46pKU'
            ));

            $sheetId = trim((string) config(
                'services.google_sheets.mcu_fu_sheet_gid',
                '1692836561'
            ));

            $columns = trim((string) config(
                'services.google_sheets.mcu_fu_columns',
                'A:AZ'
            ));

            if ($spreadsheetId === '' || $sheetId === '') {
                return null;
            }

            $values = $this->googleSheets->getValuesBySheetId(
                $spreadsheetId,
                $sheetId,
                $columns
            );

            $sheet = $this->tabularSheetData($values);
            $identityColumn = $this->findSheetColumn(
                $sheet['headers'],
                [
                    'NRP',
                    'NIK',
                    'NAMA KARYAWAN',
                    'NAMA',
                ]
            );

            $records = $identityColumn === null
                ? $sheet['rows']
                : array_values(array_filter(
                    $sheet['rows'],
                    fn (array $row): bool =>
                        trim((string) ($row[$identityColumn] ?? '')) !== ''
                ));

            $dateColumn = $this->findMcuDateColumn(
                $sheet['headers'],
                $records
            );

            return [
                'total' => count($records),
                'month' => $dateColumn === null
                    ? 0
                    : $this->countSheetRowsByPeriod(
                        $records,
                        $dateColumn,
                        $start,
                        $end
                    ),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{total: int, month: int}|null
     */
    private function safeMinePermitSheetSummary(
        Carbon $start,
        Carbon $end
    ): ?array {
        try {
            if (! $this->googleSheets->hasStoredToken()) {
                return null;
            }

            $values = $this->googleSheets->getMonitoringSheValues();
            $sheet = $this->tabularSheetData($values);
            $dateColumn = $this->findSheetDateColumn(
                $sheet['headers'],
                $sheet['rows']
            );

            return [
                'total' => count($sheet['rows']),
                'month' => $dateColumn === null
                    ? 0
                    : $this->countSheetRowsByPeriod(
                        $sheet['rows'],
                        $dateColumn,
                        $start,
                        $end
                    ),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Menghapus baris kosong serta menemukan baris header secara defensif.
     *
     * @return array{headers: array<int, string>, rows: array<int, array>}
     */
    private function tabularSheetData(array $values): array
    {
        $rows = array_values(array_filter(
            $values,
            fn ($row) => is_array($row) && $this->sheetRowHasValue($row)
        ));

        if ($rows === []) {
            return [
                'headers' => [],
                'rows' => [],
            ];
        }

        $headerIndex = $this->findSheetHeaderRow($rows);
        $headers = array_map(
            fn ($header) => $this->normalizeSheetHeader($header),
            $rows[$headerIndex]
        );

        $dataRows = array_slice($rows, $headerIndex + 1);
        $dataRows = array_values(array_filter(
            $dataRows,
            fn ($row) => is_array($row) && $this->sheetRowHasValue($row)
        ));

        return [
            'headers' => $headers,
            'rows' => $dataRows,
        ];
    }

    private function findSheetHeaderRow(array $rows): int
    {
        $bestIndex = 0;
        $bestScore = -1;

        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            $score = 0;

            foreach ($row as $cell) {
                $header = $this->normalizeSheetHeader($cell);

                foreach (
                    [
                        'TANGGAL',
                        'PENGAJUAN',
                        'NAMA',
                        'NRP',
                        'STATUS',
                        'PERMIT',
                        'DEPARTEMEN',
                    ] as $keyword
                ) {
                    if (str_contains($header, $keyword)) {
                        $score++;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = (int) $index;
            }
        }

        return $bestIndex;
    }

    private function sheetRowHasValue(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function normalizeSheetHeader(mixed $value): string
    {
        $header = mb_strtoupper(trim((string) $value));
        $header = preg_replace('/[^A-Z0-9]+/u', ' ', $header) ?? '';

        return trim(preg_replace('/\s+/', ' ', $header) ?? '');
    }

    private function findSheetColumn(
        array $headers,
        array $candidates
    ): ?int {
        foreach ($candidates as $candidate) {
            $candidate = $this->normalizeSheetHeader($candidate);
            $index = array_search($candidate, $headers, true);

            if ($index !== false) {
                return (int) $index;
            }
        }

        return null;
    }

    private function findSheetDateColumn(
        array $headers,
        array $rows
    ): ?int {
        $exactCandidates = [
            'TANGGAL PENGAJUAN',
            'TGL PENGAJUAN',
            'TANGGAL REQUEST',
            'TGL REQUEST',
            'SUBMIT DATE',
            'DATE SUBMITTED',
            'TANGGAL SUBMIT',
            'TANGGAL',
            'DATE',
        ];

        foreach ($exactCandidates as $candidate) {
            $index = array_search($candidate, $headers, true);

            if ($index !== false) {
                return (int) $index;
            }
        }

        foreach ($headers as $index => $header) {
            $isSubmissionDate = str_contains($header, 'TANGGAL')
                && (
                    str_contains($header, 'PENGAJUAN')
                    || str_contains($header, 'REQUEST')
                    || str_contains($header, 'SUBMIT')
                    || str_contains($header, 'PERMIT')
                );

            if ($isSubmissionDate) {
                return (int) $index;
            }
        }

        return $this->guessSheetDateColumn($rows);
    }

    private function findMcuDateColumn(
        array $headers,
        array $rows
    ): ?int {
        $dateColumn = $this->findSheetColumn(
            $headers,
            [
                'TANGGAL MCU',
                'TANGGAL PEMERIKSAAN',
                'TANGGAL MEDICAL CHECK UP',
                'TANGGAL FOLLOW UP',
                'TANGGAL FU',
                'TANGGAL',
                'DATE',
            ]
        );

        if ($dateColumn !== null) {
            return $dateColumn;
        }

        foreach ($headers as $index => $header) {
            $isMcuDate = str_contains($header, 'TANGGAL')
                && (
                    str_contains($header, 'MCU')
                    || str_contains($header, 'PEMERIKSAAN')
                    || str_contains($header, 'MEDICAL')
                    || str_contains($header, 'FOLLOW')
                    || str_contains($header, 'FU')
                );

            if ($isMcuDate) {
                return (int) $index;
            }
        }

        return $this->guessSheetDateColumn($rows);
    }

    private function guessSheetDateColumn(array $rows): ?int
    {
        $scores = [];

        foreach (array_slice($rows, 0, 25) as $row) {
            foreach ($row as $index => $value) {
                if ($this->parseSheetDate($value) !== null) {
                    $scores[$index] = ($scores[$index] ?? 0) + 1;
                }
            }
        }

        if ($scores === []) {
            return null;
        }

        arsort($scores);
        $index = array_key_first($scores);

        return ($scores[$index] ?? 0) >= 2
            ? (int) $index
            : null;
    }

    private function countSheetRowsByPeriod(
        array $rows,
        int $dateColumn,
        Carbon $start,
        Carbon $end
    ): int {
        $count = 0;
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->endOfDay();

        foreach ($rows as $row) {
            $date = $this->parseSheetDate(
                $row[$dateColumn] ?? null
            );

            if ($date !== null && $date->betweenIncluded($start, $end)) {
                $count++;
            }
        }

        return $count;
    }

    private function parseSheetDate(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if (
            $value === ''
            || ! preg_match('/[\/-]|[A-Za-z]/', $value)
        ) {
            return null;
        }

        $value = str_ireplace(
            [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ],
            [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
            $value
        );

        foreach (
            [
                'd/m/Y H:i:s',
                'd/m/Y H:i',
                'd-m-Y H:i:s',
                'd-m-Y H:i',
                'Y-m-d H:i:s',
                'Y-m-d H:i',
                'd/m/Y',
                'j/n/Y',
                'd-m-Y',
                'j-n-Y',
                'Y-m-d',
                'Y/m/d',
                'm/d/Y',
                'd-M-Y',
            ] as $format
        ) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date;
                }
            } catch (Throwable) {
                // Coba format berikutnya.
            }
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    return $table;
                }
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    private function safeCount(?string $table): int
    {
        if (! $table) {
            return 0;
        }

        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) DB::table($table)->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountWhere(
        string $table,
        callable $callback
    ): int {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            $query = DB::table($table);
            $callback($query);

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountByPeriod(
        string $table,
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        ?callable $callback = null
    ): int {
        try {
            if (
                ! Schema::hasTable($table)
                || ! Schema::hasColumn($table, $dateColumn)
            ) {
                return 0;
            }

            $query = DB::table($table)->whereBetween(
                $dateColumn,
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            );

            if ($callback) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountOptionalModuleByPeriod(
        ?string $table,
        Carbon $start,
        Carbon $end
    ): int {
        if (! $table) {
            return 0;
        }

        foreach (
            [
                'tanggal',
                'tanggal_surat',
                'date',
                'created_at',
                'updated_at',
            ] as $column
        ) {
            try {
                if (Schema::hasColumn($table, $column)) {
                    return $this->safeCountByPeriod(
                        $table,
                        $column,
                        $start,
                        $end
                    );
                }
            } catch (Throwable) {
                return 0;
            }
        }

        return 0;
    }

    private function recentActivities(): Collection
    {
        $activities = collect();

        try {
            if (Schema::hasTable('surat_keluar')) {
                DB::table('surat_keluar')
                    ->select([
                        'id',
                        'tanggal_surat',
                        'nomor_surat',
                        'tujuan_surat',
                        'nama',
                    ])
                    ->orderByDesc('tanggal_surat')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal_surat,
                            'module' => 'Document Out',
                            'title' => $item->nomor_surat
                                ?: 'Dokumen tanpa nomor surat',
                            'description' => trim(
                                ($item->tujuan_surat ?: '-')
                                .' · '
                                .($item->nama ?: '-')
                            ),
                            'url' => route('document-out.index'),
                            'tone' => 'navy',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('apd_requests')) {
                DB::table('apd_requests')
                    ->select([
                        'id',
                        'tanggal_pengajuan',
                        'nrp',
                        'nama',
                        'status_sepatu',
                    ])
                    ->orderByDesc('tanggal_pengajuan')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal_pengajuan,
                            'module' => 'Monitoring APD',
                            'title' => $item->nama ?: 'Pengajuan APD',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · Status Sepatu '
                                .($item->status_sepatu ?: '-'),
                            'url' => route('apd.index'),
                            'tone' => 'green',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('coaching_counsellings')) {
                DB::table('coaching_counsellings')
                    ->select([
                        'id',
                        'tanggal',
                        'nrp',
                        'materi',
                        'dibuat_oleh',
                    ])
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal,
                            'module' => 'Coaching & Counselling',
                            'title' => $item->materi ?: 'Pembinaan',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · '
                                .($item->dibuat_oleh ?: '-'),
                            'url' => route(
                                'cc-st-sp.coaching.index'
                            ),
                            'tone' => 'cyan',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('st_sp_records')) {
                DB::table('st_sp_records')
                    ->select([
                        'id',
                        'tanggal',
                        'nrp',
                        'jenis_pelanggaran',
                        'jenis',
                        'status',
                    ])
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $isTeguran = $item->jenis === 'TEGURAN';

                        $activities->push([
                            'date' => $item->tanggal,
                            'module' => $isTeguran
                                ? 'Surat Teguran'
                                : 'Surat Peringatan',
                            'title' => $item->jenis
                                ?: 'ST/SP',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · '
                                .($item->jenis_pelanggaran ?: '-')
                                .' · '
                                .($item->status ?: '-'),
                            'url' => $isTeguran
                                ? route('cc-st-sp.teguran.index')
                                : route(
                                    'cc-st-sp.peringatan.index'
                                ),
                            'tone' => $isTeguran
                                ? 'yellow'
                                : 'red',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        return $activities
            ->sortByDesc(function (array $activity) {
                try {
                    return Carbon::parse(
                        $activity['date']
                    )->timestamp;
                } catch (Throwable) {
                    return 0;
                }
            })
            ->take(10)
            ->values();
    }
}
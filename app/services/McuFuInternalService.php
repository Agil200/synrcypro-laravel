<?php

namespace App\Services;

use Carbon\Carbon;
use RuntimeException;

class McuFuInternalService
{
    private const SPREADSHEET_ID =
        '11uKwAXRyLCo0XjQ3_sWP7FwpsT0-Vg1Kp7oUGIP_yww';

    private const SHEET_ID = 1456862017;

    private const COLUMNS = 'A:N';

    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard MCU & FU Internal
    |--------------------------------------------------------------------------
    |
    | Filter Tahun/Bulan menggunakan JADWAL MCU sebagai tanggal acuan.
    | Default Tahun ditentukan Controller = tahun berjalan.
    */

    public function dashboard(
        ?int $year = null,
        ?int $month = null,
        ?string $jabatan = null
    ): array {
        $allRows = $this->rows();

        $availableYears = $this->availableYears(
            $allRows,
            'jadwal_mcu'
        );

        $availableJabatan = $this->uniqueValues(
            $allRows,
            'jabatan'
        );

        $rows = $this->filterByDate(
            $allRows,
            'jadwal_mcu',
            $year,
            $month
        );

        $rows = $this->filterByExactValue(
            $rows,
            'jabatan',
            $jabatan
        );

        return [
            'summary' => [
                'total_data' => count($rows),

                'mcu_done' => $this->countExact(
                    $rows,
                    'status_mcu',
                    'DONE'
                ),

                'fit_to_work' => $this->countContains(
                    $rows,
                    'hasil_mcu',
                    'FIT TO WORK'
                ),

                'hasil_follow_up' => $this->countContains(
                    $rows,
                    'hasil_mcu',
                    'FOLLOW UP'
                ),

                'fu_active' => collect($rows)
                    ->filter(function (array $row): bool {
                        return
                            $this->filled($row['follow_up_1'] ?? '') ||
                            $this->filled($row['follow_up_2'] ?? '') ||
                            $this->filled($row['follow_up_3'] ?? '');
                    })
                    ->count(),

                'fu_completed' => $this->countContains(
                    $rows,
                    'status_fu',
                    'COMPLETED'
                ),
            ],

            'hasil_mcu' => $this->distribution(
                $rows,
                'hasil_mcu'
            ),

            'status_mcu' => $this->distribution(
                $rows,
                'status_mcu'
            ),

            'status_fu' => $this->distribution(
                $rows,
                'status_fu'
            ),

            'jabatan' => $this->distribution(
                $rows,
                'jabatan',
                10
            ),

            'follow_up' => $this->followUpDistribution(
                $rows
            ),

            /*
             * Detail tujuan/spesialis per tahap Follow Up.
             * Contoh: BEDAH, MATA, SYARAF, PENYAKIT DALAM, JANTUNG.
             */
            'follow_up_1_detail' => $this->distribution(
                $rows,
                'follow_up_1'
            ),

            'follow_up_2_detail' => $this->distribution(
                $rows,
                'follow_up_2'
            ),

            'follow_up_3_detail' => $this->distribution(
                $rows,
                'follow_up_3'
            ),

            'filters' => [
                'year' => $year,
                'month' => $month,
                'jabatan' => $jabatan,
                'years' => $availableYears,
                'jabatan_options' => $availableJabatan,
                'date_field' => 'JADWAL MCU',
                'total_all' => count($allRows),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Options dropdown website
    |--------------------------------------------------------------------------
    |
    | Value dropdown diambil dari value existing di Spreadsheet.
    */

    public function options(?array $rows = null): array
    {
        $rows ??= $this->rows();

        return [
            'hasil_mcu' => $this->uniqueValues(
                $rows,
                'hasil_mcu'
            ),

            'follow_up' => collect([
                ...$this->uniqueValues($rows, 'follow_up_1'),
                ...$this->uniqueValues($rows, 'follow_up_2'),
                ...$this->uniqueValues($rows, 'follow_up_3'),
            ])
                ->filter()
                ->unique(
                    fn ($value) =>
                        mb_strtoupper((string) $value)
                )
                ->sort()
                ->values()
                ->all(),

            'status_fu' => $this->uniqueValues(
                $rows,
                'status_fu'
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil semua row sheet MCU&FU
    |--------------------------------------------------------------------------
    */

    public function rows(): array
    {
        $values = $this->googleSheets->getValuesBySheetId(
            self::SPREADSHEET_ID,
            self::SHEET_ID,
            self::COLUMNS
        );

        if ($values === []) {
            return [];
        }

        $headerRowIndex = $this->findHeaderRowIndex(
            $values
        );

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header sheet MCU&FU tidak ditemukan.'
            );
        }

        $headers = array_pad(
            $values[$headerRowIndex] ?? [],
            14,
            ''
        );

        $indexes = $this->resolveColumnIndexes(
            $headers
        );

        $result = [];

        foreach ($values as $rowIndex => $row) {
            if ($rowIndex <= $headerRowIndex) {
                continue;
            }

            $row = array_pad(
                is_array($row) ? $row : [],
                14,
                ''
            );

            $nrp = $this->cell(
                $row,
                $indexes['nrp']
            );

            $nama = $this->cell(
                $row,
                $indexes['nama']
            );

            if (
                trim($nrp) === '' &&
                trim($nama) === ''
            ) {
                continue;
            }

            $result[] = [
                'sheet_row' => $rowIndex + 1,

                'nrp' => $nrp,

                'nama' => $nama,

                'jabatan' => $this->cell(
                    $row,
                    $indexes['jabatan']
                ),

                // D — manual
                'exp_mcu' => $this->cell(
                    $row,
                    $indexes['exp_mcu']
                ),

                // E — non manual website
                'expired_sim_dlt' => $this->cell(
                    $row,
                    $indexes['expired_sim_dlt']
                ),

                // F — manual
                'jadwal_mcu' => $this->cell(
                    $row,
                    $indexes['jadwal_mcu']
                ),

                // G — non manual website
                'status_mcu' => $this->cell(
                    $row,
                    $indexes['status_mcu']
                ),

                // H — manual
                'hasil_mcu' => $this->cell(
                    $row,
                    $indexes['hasil_mcu']
                ),

                // I-K — manual
                'follow_up_1' => $this->cell(
                    $row,
                    $indexes['follow_up_1']
                ),

                'follow_up_2' => $this->cell(
                    $row,
                    $indexes['follow_up_2']
                ),

                'follow_up_3' => $this->cell(
                    $row,
                    $indexes['follow_up_3']
                ),

                // L — manual
                'jadwal_fu' => $this->cell(
                    $row,
                    $indexes['jadwal_fu']
                ),

                // M — manual
                'status_fu' => $this->cell(
                    $row,
                    $indexes['status_fu']
                ),

                // N — non manual website
                'status_fu_info' => $this->cell(
                    $row,
                    $indexes['status_fu_info']
                ),
            ];
        }

        return $result;
    }

    public function findRow(
        int $sheetRow,
        ?array $rows = null
    ): array {
        if ($sheetRow < 2) {
            throw new RuntimeException(
                'Sheet row MCU & FU tidak valid.'
            );
        }

        $rows ??= $this->rows();

        $row = collect($rows)
            ->first(
                fn (array $item): bool =>
                    (int) ($item['sheet_row'] ?? 0) === $sheetRow
            );

        if (!is_array($row)) {
            throw new RuntimeException(
                "Data MCU & FU pada sheet row {$sheetRow} tidak ditemukan."
            );
        }

        return $row;
    }

    /*
    |--------------------------------------------------------------------------
    | Update MCU
    |--------------------------------------------------------------------------
    |
    | HANYA:
    | D = EXP MCU
    | F = JADWAL MCU
    | H = HASIL MCU
    */

    public function updateMcu(
        int $sheetRow,
        array $data
    ): array {
        $before = $this->findRow(
            $sheetRow
        );

        $expMcu = $this->sheetDate(
            $data['exp_mcu'] ?? null
        );

        $jadwalMcu = $this->sheetDate(
            $data['jadwal_mcu'] ?? null
        );

        $hasilMcu = trim(
            (string) ($data['hasil_mcu'] ?? '')
        );

        $this->googleSheets->updateValues(
            self::SPREADSHEET_ID,
            "'MCU&FU'!D{$sheetRow}",
            [$expMcu]
        );

        $this->googleSheets->updateValues(
            self::SPREADSHEET_ID,
            "'MCU&FU'!F{$sheetRow}",
            [$jadwalMcu]
        );

        $this->googleSheets->updateValues(
            self::SPREADSHEET_ID,
            "'MCU&FU'!H{$sheetRow}",
            [$hasilMcu]
        );

        $after = $before;
        $after['exp_mcu'] = $expMcu;
        $after['jadwal_mcu'] = $jadwalMcu;
        $after['hasil_mcu'] = $hasilMcu;

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Update Follow Up
    |--------------------------------------------------------------------------
    |
    | HANYA:
    | I = FOLLOW UP 1
    | J = FOLLOW UP 2
    | K = FOLLOW UP 3
    | L = JADWAL FU
    | M = STATUS FU
    */

    public function updateFollowUp(
        int $sheetRow,
        array $data
    ): array {
        $before = $this->findRow(
            $sheetRow
        );

        $values = [
            trim((string) ($data['follow_up_1'] ?? '')),
            trim((string) ($data['follow_up_2'] ?? '')),
            trim((string) ($data['follow_up_3'] ?? '')),
            $this->sheetDate($data['jadwal_fu'] ?? null),
            trim((string) ($data['status_fu'] ?? '')),
        ];

        $this->googleSheets->updateValues(
            self::SPREADSHEET_ID,
            "'MCU&FU'!I{$sheetRow}:M{$sheetRow}",
            $values
        );

        $after = $before;
        $after['follow_up_1'] = $values[0];
        $after['follow_up_2'] = $values[1];
        $after['follow_up_3'] = $values[2];
        $after['jadwal_fu'] = $values[3];
        $after['status_fu'] = $values[4];

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Date helpers
    |--------------------------------------------------------------------------
    */

    public function htmlDate(
        mixed $value
    ): string {
        $date = $this->parseDate(
            $value
        );

        return $date?->format('Y-m-d') ?? '';
    }

    public function dateParts(
        mixed $value
    ): ?array {
        $date = $this->parseDate(
            $value
        );

        if ($date === null) {
            return null;
        }

        return [
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('n'),
        ];
    }

    private function sheetDate(
        mixed $value
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)
                ->format('d-M-y');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function parseDate(
        mixed $value
    ): ?Carbon {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return null;
        }

        foreach (
            [
                'd-M-y',
                'd-M-Y',
                'd/m/Y',
                'd/m/y',
                'Y-m-d',
                'm/d/Y',
                'm/d/y',
            ] as $format
        ) {
            try {
                $date = Carbon::createFromFormat(
                    $format,
                    $value
                );

                if ($date !== false) {
                    return $date;
                }
            } catch (\Throwable) {
                // Coba format berikutnya.
            }
        }

        try {
            return Carbon::parse(
                $value
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function availableYears(
        array $rows,
        string $field
    ): array {
        $years = collect($rows)
            ->map(function (array $row) use ($field): ?int {
                $parts = $this->dateParts(
                    $row[$field] ?? ''
                );

                return $parts['year'] ?? null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $currentYear = (int) now()
            ->format('Y');

        if (!in_array($currentYear, $years, true)) {
            $years[] = $currentYear;

            rsort(
                $years,
                SORT_NUMERIC
            );
        }

        return $years;
    }

    private function filterByDate(
        array $rows,
        string $field,
        ?int $year,
        ?int $month
    ): array {
        if ($year === null && $month === null) {
            return $rows;
        }

        return collect($rows)
            ->filter(function (array $row) use (
                $field,
                $year,
                $month
            ): bool {
                $parts = $this->dateParts(
                    $row[$field] ?? ''
                );

                if ($parts === null) {
                    return false;
                }

                if (
                    $year !== null &&
                    $parts['year'] !== $year
                ) {
                    return false;
                }

                if (
                    $month !== null &&
                    $parts['month'] !== $month
                ) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function filterByExactValue(
        array $rows,
        string $field,
        ?string $value
    ): array {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return $rows;
        }

        $needle = mb_strtoupper(
            $value
        );

        return collect($rows)
            ->filter(
                fn (array $row): bool =>
                    mb_strtoupper(
                        trim(
                            (string) ($row[$field] ?? '')
                        )
                    ) === $needle
            )
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Header mapping
    |--------------------------------------------------------------------------
    */

    private function findHeaderRowIndex(
        array $values
    ): ?int {
        foreach (
            array_slice($values, 0, 15, true)
            as $index => $row
        ) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = array_map(
                fn ($value) => $this->normalizeHeader(
                    (string) $value
                ),
                $row
            );

            if (
                in_array('NRP', $normalized, true) &&
                in_array('NAMA', $normalized, true) &&
                in_array('JABATAN', $normalized, true)
            ) {
                return (int) $index;
            }
        }

        return null;
    }

    private function resolveColumnIndexes(
        array $headers
    ): array {
        $normalized = array_map(
            fn ($value) => $this->normalizeHeader(
                (string) $value
            ),
            $headers
        );

        $statusFuIndexes = [];

        foreach ($normalized as $index => $header) {
            if ($header === 'STATUS FU') {
                $statusFuIndexes[] = (int) $index;
            }
        }

        return [
            'nrp' => $this->findColumn($normalized, 'NRP', 0),
            'nama' => $this->findColumn($normalized, 'NAMA', 1),
            'jabatan' => $this->findColumn($normalized, 'JABATAN', 2),
            'exp_mcu' => $this->findColumn($normalized, 'EXP MCU', 3),
            'expired_sim_dlt' => $this->findColumn($normalized, 'EXPIRED SIM DLT', 4),
            'jadwal_mcu' => $this->findColumn($normalized, 'JADWAL MCU', 5),
            'status_mcu' => $this->findColumn($normalized, 'STATUS MCU', 6),
            'hasil_mcu' => $this->findColumn($normalized, 'HASIL MCU', 7),
            'follow_up_1' => $this->findColumn($normalized, 'FOLLOW UP 1', 8),
            'follow_up_2' => $this->findColumn($normalized, 'FOLLOW UP 2', 9),
            'follow_up_3' => $this->findColumn($normalized, 'FOLLOW UP 3', 10),
            'jadwal_fu' => $this->findColumn($normalized, 'JADWAL FU', 11),

            // M
            'status_fu' => $statusFuIndexes[0] ?? 12,

            // N
            'status_fu_info' => $statusFuIndexes[1] ?? 13,
        ];
    }

    private function findColumn(
        array $headers,
        string $needle,
        int $fallback
    ): int {
        $index = array_search(
            $needle,
            $headers,
            true
        );

        return $index === false
            ? $fallback
            : (int) $index;
    }

    private function normalizeHeader(
        string $value
    ): string {
        $value = preg_replace(
            '/\s+/u',
            ' ',
            trim($value)
        );

        return strtoupper(
            $value ?? ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard helpers
    |--------------------------------------------------------------------------
    */

    private function uniqueValues(
        array $rows,
        string $field
    ): array {
        return collect($rows)
            ->pluck($field)
            ->map(
                fn ($value) => trim(
                    (string) $value
                )
            )
            ->filter()
            ->unique(
                fn ($value) =>
                    mb_strtoupper((string) $value)
            )
            ->sort()
            ->values()
            ->all();
    }

    private function distribution(
        array $rows,
        string $field,
        ?int $limit = null
    ): array {
        $counts = [];

        foreach ($rows as $row) {
            $value = trim(
                (string) ($row[$field] ?? '')
            );

            if ($value === '') {
                continue;
            }

            $key = strtoupper(
                $value
            );

            if (!isset($counts[$key])) {
                $counts[$key] = [
                    'label' => $value,
                    'total' => 0,
                ];
            }

            $counts[$key]['total']++;
        }

        uasort(
            $counts,
            static fn (array $a, array $b): int =>
                $b['total'] <=> $a['total']
        );

        $items = array_values(
            $counts
        );

        if (
            $limit !== null &&
            $limit > 0
        ) {
            $items = array_slice(
                $items,
                0,
                $limit
            );
        }

        $sum = array_sum(
            array_column(
                $items,
                'total'
            )
        );

        $max = collect($items)
            ->max('total') ?? 0;

        return array_map(
            static function (array $item) use (
                $sum,
                $max
            ): array {
                $item['percent'] =
                    $max > 0
                        ? round(
                            ($item['total'] / $max) * 100,
                            2
                        )
                        : 0;

                $item['share'] =
                    $sum > 0
                        ? round(
                            ($item['total'] / $sum) * 100,
                            2
                        )
                        : 0;

                return $item;
            },
            $items
        );
    }

    private function followUpDistribution(
        array $rows
    ): array {
        $counts = [
            'FOLLOW UP 1' => 0,
            'FOLLOW UP 2' => 0,
            'FOLLOW UP 3' => 0,
        ];

        foreach ($rows as $row) {
            if ($this->filled($row['follow_up_1'] ?? '')) {
                $counts['FOLLOW UP 1']++;
            }

            if ($this->filled($row['follow_up_2'] ?? '')) {
                $counts['FOLLOW UP 2']++;
            }

            if ($this->filled($row['follow_up_3'] ?? '')) {
                $counts['FOLLOW UP 3']++;
            }
        }

        $max = max(
            $counts ?: [0]
        );

        $sum = array_sum(
            $counts
        );

        return collect($counts)
            ->map(
                static fn (
                    int $total,
                    string $label
                ): array => [
                    'label' => $label,
                    'total' => $total,

                    'percent' =>
                        $max > 0
                            ? round(
                                ($total / $max) * 100,
                                2
                            )
                            : 0,

                    'share' =>
                        $sum > 0
                            ? round(
                                ($total / $sum) * 100,
                                2
                            )
                            : 0,
                ]
            )
            ->values()
            ->all();
    }

    private function countExact(
        array $rows,
        string $field,
        string $value
    ): int {
        return collect($rows)
            ->filter(
                fn (array $row): bool =>
                    strtoupper(
                        trim(
                            (string) ($row[$field] ?? '')
                        )
                    ) === strtoupper(
                        $value
                    )
            )
            ->count();
    }

    private function countContains(
        array $rows,
        string $field,
        string $needle
    ): int {
        return collect($rows)
            ->filter(
                fn (array $row): bool =>
                    str_contains(
                        strtoupper(
                            trim(
                                (string) ($row[$field] ?? '')
                            )
                        ),
                        strtoupper(
                            $needle
                        )
                    )
            )
            ->count();
    }

    private function filled(
        mixed $value
    ): bool {
        return trim(
            (string) $value
        ) !== '';
    }

    private function cell(
        array $row,
        int $index
    ): string {
        return trim(
            (string) ($row[$index] ?? '')
        );
    }
}
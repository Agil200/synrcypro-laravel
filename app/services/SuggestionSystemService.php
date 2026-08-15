<?php

namespace App\Services;

use Carbon\Carbon;
use RuntimeException;
use Throwable;

class SuggestionSystemService
{
    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil data utama Suggestion System
    |--------------------------------------------------------------------------
    |
    | READ ONLY.
    | Tidak melakukan append / update ke Google Sheets.
    |
    */

    public function getData(): array
    {
        $spreadsheetId = trim(
            (string) config(
                'admin_all.suggestion_system.spreadsheet_id'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID Suggestion System belum diatur.'
            );
        }

        $databaseRange = trim(
            (string) config(
                'admin_all.suggestion_system.ranges.database',
                "'DATABASE_SS'!A:AZ"
            )
        );

        $accessRange = trim(
            (string) config(
                'admin_all.suggestion_system.ranges.access_atasan',
                "'ACCESS_ATASAN'!A:AZ"
            )
        );

        $databaseValues = $this->googleSheets->getValues(
            $spreadsheetId,
            $databaseRange
        );

        $accessValues = $this->googleSheets->getValues(
            $spreadsheetId,
            $accessRange
        );

        $database = $this->makeTable($databaseValues);
        $accessAtasan = $this->makeTable($accessValues);

        $activeAccess = array_values(
            array_filter(
                $accessAtasan['rows'],
                fn (array $row): bool =>
                    strtoupper(
                        trim((string) ($row['STATUS'] ?? ''))
                    ) === 'AKTIF'
            )
        );

        return [
            'database' => $database,

            'access_atasan' => [
                'headers' => $accessAtasan['headers'],
                'rows' => $accessAtasan['rows'],
                'total' => $accessAtasan['total'],

                'active_rows' => $activeAccess,
                'active_total' => count($activeAccess),

                'status_column_found' =>
                    in_array(
                        'STATUS',
                        $accessAtasan['headers'],
                        true
                    ),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve akses Suggestion System berdasarkan email login
    |--------------------------------------------------------------------------
    */

    public function resolveAccess(
        array $accessRows,
        ?string $email
    ): array {
        $email = strtolower(trim((string) $email));

        $default = [
            'allowed' => false,
            'access' => null,
            'name' => null,
            'nrp' => null,
            'position' => null,
            'department' => null,
            'email' => $email,
            'status' => null,
            'source' => 'ACCESS_ATASAN',
            'message' => 'User tidak terdaftar pada ACCESS_ATASAN.',
        ];

        if ($email === '') {
            $default['message'] =
                'Email user login tidak ditemukan.';

            return $default;
        }

        foreach ($accessRows as $row) {
            $rowEmail = strtolower(
                trim((string) ($row['EMAIL'] ?? ''))
            );

            if ($rowEmail === '' || $rowEmail !== $email) {
                continue;
            }

            $status = strtoupper(
                trim((string) ($row['STATUS'] ?? ''))
            );

            $access = strtoupper(
                trim((string) ($row['AKSES'] ?? ''))
            );

            $result = [
                'allowed' => false,
                'access' => $access ?: null,
                'name' => trim(
                    (string) ($row['NAMA_KARYAWAN'] ?? '')
                ),
                'nrp' => trim(
                    (string) ($row['NRP'] ?? '')
                ),
                'position' => trim(
                    (string) ($row['JABATAN'] ?? '')
                ),
                'department' => trim(
                    (string) ($row['DEPARTEMEN'] ?? '')
                ),
                'email' => $rowEmail,
                'status' => $status,
                'source' => 'ACCESS_ATASAN',
                'message' => null,
            ];

            if ($status !== 'AKTIF') {
                $result['message'] =
                    'Akses Suggestion System tidak aktif.';

                return $result;
            }

            if (!in_array(
                $access,
                ['ADMIN', 'GL', 'SH'],
                true
            )) {
                $result['message'] =
                    'Nilai AKSES tidak dikenali.';

                return $result;
            }

            $result['allowed'] = true;
            $result['message'] = 'Akses aktif.';

            return $result;
        }

        return $default;
    }

    /*
    |--------------------------------------------------------------------------
    | Bangun analytics dashboard Suggestion System
    |--------------------------------------------------------------------------
    |
    | Filter utama:
    | - bulan / tahun berdasarkan SUBMIT_AT
    |
    | Drill-down tabel:
    | - status
    | - NRP
    |
    | Semua proses di tahap ini READ ONLY.
    |
    */

    public function buildDashboard(
        array $rows,
        ?int $month = null,
        ?int $year = null,
        ?string $status = null,
        ?string $nrp = null
    ): array {
        $month = $this->validMonth($month);
        $year = $this->validYear($year);

        $status = strtoupper(trim((string) $status));
        $nrp = trim((string) $nrp);

        $availableYears = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $date = $this->parseSubmitAt(
                (string) ($row['SUBMIT_AT'] ?? '')
            );

            if ($date === null) {
                continue;
            }

            $availableYears[] = (int) $date->year;
        }

        $availableYears = array_values(
            array_unique($availableYears)
        );

        rsort($availableYears);

        /*
         * Base analytics hanya terpengaruh filter bulan / tahun.
         * Klik chart status atau Top 10 tidak mengubah angka chart,
         * hanya mengarahkan tabel data ke detail yang dipilih.
         */
        $periodRows = array_values(
            array_filter(
                $rows,
                function (mixed $row) use ($month, $year): bool {
                    if (!is_array($row)) {
                        return false;
                    }

                    $date = $this->parseSubmitAt(
                        (string) ($row['SUBMIT_AT'] ?? '')
                    );

                    if ($date === null) {
                        return false;
                    }

                    if (
                        $month !== null
                        && (int) $date->month !== $month
                    ) {
                        return false;
                    }

                    if (
                        $year !== null
                        && (int) $date->year !== $year
                    ) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $statusMap = [];

        foreach ($periodRows as $row) {
            $key = strtoupper(
                trim((string) ($row['STATUS'] ?? ''))
            );

            if ($key === '') {
                $key = 'TANPA_STATUS';
            }

            if (!isset($statusMap[$key])) {
                $statusMap[$key] = [
                    'key' => $key,
                    'label' => $this->statusLabel($key),
                    'count' => 0,
                ];
            }

            $statusMap[$key]['count']++;
        }

        $statusChart = array_values($statusMap);

        usort(
            $statusChart,
            fn (array $a, array $b): int =>
                $b['count'] <=> $a['count']
        );

        /*
         * Top 10 dihitung berdasarkan NRP.
         * Nama hanya sebagai label agar nama yang sama tidak tercampur.
         */
        $people = [];

        foreach ($periodRows as $row) {
            $rowNrp = trim(
                (string) ($row['NRP'] ?? '')
            );

            $name = trim(
                (string) ($row['NAMA_KARYAWAN'] ?? '')
            );

            if ($rowNrp === '' && $name === '') {
                continue;
            }

            $key = $rowNrp !== ''
                ? 'NRP:' . $rowNrp
                : 'NAME:' . strtoupper($name);

            if (!isset($people[$key])) {
                $people[$key] = [
                    'nrp' => $rowNrp,
                    'name' => $name !== ''
                        ? $name
                        : 'Tanpa Nama',
                    'count' => 0,
                ];
            }

            $people[$key]['count']++;
        }

        $topNames = array_values($people);

        usort(
            $topNames,
            function (array $a, array $b): int {
                $countCompare =
                    $b['count'] <=> $a['count'];

                if ($countCompare !== 0) {
                    return $countCompare;
                }

                return strcmp(
                    $a['name'],
                    $b['name']
                );
            }
        );

        $topNames = array_slice($topNames, 0, 10);

        /*
         * Data detail mengikuti filter bulan/tahun,
         * kemudian optional drill-down status / NRP.
         */
        $detailRows = array_values(
            array_filter(
                $periodRows,
                function (array $row) use (
                    $status,
                    $nrp
                ): bool {
                    if ($status !== '') {
                        $rowStatus = strtoupper(
                            trim(
                                (string) ($row['STATUS'] ?? '')
                            )
                        );

                        if ($rowStatus === '') {
                            $rowStatus = 'TANPA_STATUS';
                        }

                        if ($rowStatus !== $status) {
                            return false;
                        }
                    }

                    if ($nrp !== '') {
                        $rowNrp = trim(
                            (string) ($row['NRP'] ?? '')
                        );

                        if ($rowNrp !== $nrp) {
                            return false;
                        }
                    }

                    return true;
                }
            )
        );

        usort(
            $detailRows,
            function (array $a, array $b): int {
                return $this->submitTimestamp($b)
                    <=> $this->submitTimestamp($a);
            }
        );

        return [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'status' => $status !== ''
                    ? $status
                    : null,
                'nrp' => $nrp !== ''
                    ? $nrp
                    : null,
            ],

            'available_years' => $availableYears,

            'total' => count($periodRows),

            'status_chart' => $statusChart,

            'top_names' => $topNames,

            'rows' => $detailRows,

            'data_total' => count($detailRows),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Monitoring Data SS
    |--------------------------------------------------------------------------
    |
    | READ ONLY.
    | Filter:
    | - Bulan / Tahun berdasarkan SUBMIT_AT
    | - Status
    | - NRP
    | - Pencarian No SS / NRP / Nama / Departemen / Lokasi / Judul
    |
    */

    public function buildMonitoring(
        array $rows,
        ?int $month = null,
        ?int $year = null,
        ?string $status = null,
        ?string $nrp = null,
        ?string $search = null
    ): array {
        $month = $this->validMonth($month);
        $year = $this->validYear($year);

        $status = strtoupper(trim((string) $status));
        $nrp = trim((string) $nrp);
        $search = trim((string) $search);

        $availableYears = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $date = $this->parseSubmitAt(
                (string) ($row['SUBMIT_AT'] ?? '')
            );

            if ($date !== null) {
                $availableYears[] = (int) $date->year;
            }
        }

        $availableYears = array_values(
            array_unique($availableYears)
        );

        rsort($availableYears);

        /*
         * Base rows mengikuti bulan/tahun/NRP/search.
         * Filter status diterapkan setelah summary status dihitung
         * supaya card status tetap menunjukkan distribusi periode aktif.
         */
        $baseRows = array_values(
            array_filter(
                $rows,
                function (mixed $row) use (
                    $month,
                    $year,
                    $nrp,
                    $search
                ): bool {
                    if (!is_array($row)) {
                        return false;
                    }

                    $date = $this->parseSubmitAt(
                        (string) ($row['SUBMIT_AT'] ?? '')
                    );

                    if ($date === null) {
                        return false;
                    }

                    if (
                        $month !== null
                        && (int) $date->month !== $month
                    ) {
                        return false;
                    }

                    if (
                        $year !== null
                        && (int) $date->year !== $year
                    ) {
                        return false;
                    }

                    if ($nrp !== '') {
                        $rowNrp = trim(
                            (string) ($row['NRP'] ?? '')
                        );

                        if ($rowNrp !== $nrp) {
                            return false;
                        }
                    }

                    if (
                        $search !== ''
                        && !$this->matchesMonitoringSearch(
                            $row,
                            $search
                        )
                    ) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $statusCounts = [];

        foreach ($baseRows as $row) {
            $key = strtoupper(
                trim((string) ($row['STATUS'] ?? ''))
            );

            if ($key === '') {
                $key = 'TANPA_STATUS';
            }

            if (!isset($statusCounts[$key])) {
                $statusCounts[$key] = [
                    'key' => $key,
                    'label' => $this->statusLabel($key),
                    'count' => 0,
                ];
            }

            $statusCounts[$key]['count']++;
        }

        $statusOptions = array_values($statusCounts);

        usort(
            $statusOptions,
            fn (array $a, array $b): int =>
                $b['count'] <=> $a['count']
        );

        /*
         * Ringkasan tahap workflow.
         * Dihitung dari baseRows supaya mengikuti Bulan/Tahun/NRP/Search,
         * tetapi tidak berubah hanya karena dropdown STATUS dipilih.
         */
        $stageCounts = [
            'submitted' => 0,
            'gl_qcc' => 0,
            'sh' => 0,
            'dh_pm' => 0,
            'selesai' => 0,
        ];

        foreach ($baseRows as $row) {
            $mainStatus = strtoupper(
                trim((string) ($row['STATUS'] ?? ''))
            );

            $glQccStatus = strtoupper(
                trim((string) ($row['STATUS_GL_QCC'] ?? ''))
            );

            $shStatus = strtoupper(
                trim((string) ($row['STATUS_SH'] ?? ''))
            );

            $dhPmStatus = strtoupper(
                trim((string) ($row['STATUS_DH_PM'] ?? ''))
            );

            if ($mainStatus === 'SUBMITTED') {
                $stageCounts['submitted']++;
            }

            if (
                $mainStatus === 'VERIFIED_GL_QCC'
                || in_array(
                    $glQccStatus,
                    ['VERIFIED', 'APPROVED'],
                    true
                )
            ) {
                $stageCounts['gl_qcc']++;
            }

            if (
                in_array(
                    $shStatus,
                    ['VERIFIED', 'APPROVED'],
                    true
                )
            ) {
                $stageCounts['sh']++;
            }

            if (
                in_array(
                    $dhPmStatus,
                    ['VERIFIED', 'APPROVED'],
                    true
                )
            ) {
                $stageCounts['dh_pm']++;
            }

            if (
                in_array(
                    $mainStatus,
                    ['SELESAI', 'DONE', 'COMPLETED'],
                    true
                )
            ) {
                $stageCounts['selesai']++;
            }
        }

        $filteredRows = array_values(
            array_filter(
                $baseRows,
                function (array $row) use ($status): bool {
                    if ($status === '') {
                        return true;
                    }

                    $rowStatus = strtoupper(
                        trim((string) ($row['STATUS'] ?? ''))
                    );

                    if ($rowStatus === '') {
                        $rowStatus = 'TANPA_STATUS';
                    }

                    return $rowStatus === $status;
                }
            )
        );

        usort(
            $filteredRows,
            function (array $a, array $b): int {
                return $this->submitTimestamp($b)
                    <=> $this->submitTimestamp($a);
            }
        );

        return [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'status' => $status !== ''
                    ? $status
                    : null,
                'nrp' => $nrp !== ''
                    ? $nrp
                    : null,
                'q' => $search !== ''
                    ? $search
                    : null,
            ],

            'available_years' => $availableYears,
            'status_options' => $statusOptions,
            'stage_counts' => $stageCounts,

            'period_total' => count($baseRows),
            'filtered_total' => count($filteredRows),

            'rows' => $filteredRows,
        ];
    }

    private function matchesMonitoringSearch(
        array $row,
        string $search
    ): bool {
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return true;
        }

        $fields = [
            'NO_SS',
            'NRP',
            'NAMA_KARYAWAN',
            'DEPARTEMEN',
            'LOKASI',
            'JUDUL_SS',
        ];

        foreach ($fields as $field) {
            $value = mb_strtolower(
                trim((string) ($row[$field] ?? ''))
            );

            if (
                $value !== ''
                && str_contains($value, $needle)
            ) {
                return true;
            }
        }

        return false;
    }

    private function validMonth(?int $month): ?int
    {
        if (
            $month === null
            || $month < 1
            || $month > 12
        ) {
            return null;
        }

        return $month;
    }

    private function validYear(?int $year): ?int
    {
        if (
            $year === null
            || $year < 2000
            || $year > 2100
        ) {
            return null;
        }

        return $year;
    }

    private function parseSubmitAt(
        string $value
    ): ?Carbon {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $formats = [
            'n/j/Y H:i:s',
            'n/j/Y G:i:s',
            'm/d/Y H:i:s',
            'Y-m-d H:i:s',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat(
                    $format,
                    $value
                );

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

    private function submitTimestamp(
        array $row
    ): int {
        $date = $this->parseSubmitAt(
            (string) ($row['SUBMIT_AT'] ?? '')
        );

        return $date?->timestamp ?? 0;
    }

    private function statusLabel(
        string $status
    ): string {
        return match ($status) {
            'SUBMITTED' =>
                'Submitted',

            'VERIFIED_GL_QCC' =>
                'Verified GL / QCC',

            'APPROVED_SH' =>
                'Approved SH',

            'REJECTED_GL_QCC' =>
                'Rejected GL / QCC',

            'REJECTED_SH' =>
                'Rejected SH',

            'SELESAI', 'COMPLETED', 'DONE' =>
                'Selesai',

            'TANPA_STATUS' =>
                'Tanpa Status',

            default =>
                ucwords(
                    strtolower(
                        str_replace('_', ' ', $status)
                    )
                ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Ubah data mentah Sheets menjadi table associative
    |--------------------------------------------------------------------------
    */

    private function makeTable(array $values): array
    {
        if ($values === []) {
            return [
                'headers' => [],
                'rows' => [],
                'total' => 0,
            ];
        }

        $rawHeaders = array_shift($values);

        if (!is_array($rawHeaders)) {
            $rawHeaders = [];
        }

        $headers = [];

        foreach ($rawHeaders as $index => $header) {
            $key = $this->normalizeHeader(
                (string) $header,
                $index
            );

            /*
             * Cegah key duplicate jika ada nama kolom sama.
             */
            $originalKey = $key;
            $counter = 2;

            while (in_array($key, $headers, true)) {
                $key = $originalKey . '_' . $counter;
                $counter++;
            }

            $headers[] = $key;
        }

        $rows = [];

        foreach ($values as $row) {
            if (!is_array($row)) {
                continue;
            }

            /*
             * Abaikan row yang benar-benar kosong.
             */
            $hasValue = false;

            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    $hasValue = true;
                    break;
                }
            }

            if (!$hasValue) {
                continue;
            }

            $item = [];

            foreach ($headers as $index => $header) {
                $item[$header] =
                    $row[$index] ?? '';
            }

            $rows[] = $item;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'total' => count($rows),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalisasi nama header Google Sheet
    |--------------------------------------------------------------------------
    */

    private function normalizeHeader(
        string $header,
        int $index
    ): string {
        $header = strtoupper(trim($header));

        $header = preg_replace(
            '/[^A-Z0-9]+/',
            '_',
            $header
        ) ?? '';

        $header = trim($header, '_');

        if ($header === '') {
            return 'COLUMN_' . ($index + 1);
        }

        return $header;
    }
}

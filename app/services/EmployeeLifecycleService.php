<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class EmployeeLifecycleService
{
    private const PENDING_KEY =
        'mcu-fu.employee-lifecycle.pending.v1';

    private const PENDING_TTL_SECONDS = 21600;

    private const STATUS_OPTIONS = [
        'NEW HIRE',
        'EXISTING DATA',
        'RESIGN',
        'MUTASI',
        'TERMINATED',
    ];

    public function __construct(
        private readonly EmployeeMasterService $employeeMaster,
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Status final
    |--------------------------------------------------------------------------
    */

    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    /*
    |--------------------------------------------------------------------------
    | Lookup NRP
    |--------------------------------------------------------------------------
    */

    public function findByNrp(
        string $nrp
    ): ?array {
        $nrp = $this->normalizeNrp($nrp);

        if ($nrp === '') {
            return null;
        }

        foreach ($this->employees() as $employee) {
            if (
                $this->normalizeNrp(
                    (string) ($employee['nrp'] ?? '')
                ) === $nrp
            ) {
                return $employee;
            }
        }

        return null;
    }

    public function employees(): array
    {
        $snapshot = $this->employeeMaster->snapshot();

        $employees = is_array(
            $snapshot['employees'] ?? null
        )
            ? $snapshot['employees']
            : [];

        return $this->applyPending(
            $employees
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MCU & FU Internal Bukit Asam eligibility
    |--------------------------------------------------------------------------
    |
    | ACTIVE:
    | - NEW HIRE
    | - EXISTING DATA
    |
    | HIDE:
    | - RESIGN
    | - MUTASI
    | - TERMINATED
    |
    | Legacy status AKTIF/ACTIVE/kosong tetap ditoleransi agar rollout
    | tidak menghilangkan data existing secara massal.
    |
    | Jika SITE sudah terisi dan bukan Bukit Asam, row disembunyikan.
    | Jika SITE lama masih kosong, row tetap ditampilkan untuk compatibility.
    */

    public function activeForMcuFu(
        string $nrp
    ): bool {
        $employee = $this->findByNrp(
            $nrp
        );

        /*
         * Jika NRP belum ditemukan di MASTER jangan langsung hide.
         * Ini mencegah issue mapping/cache menghilangkan data MCU existing.
         */
        if ($employee === null) {
            return true;
        }

        $status = $this->normalizeText(
            (string) (
                $employee['status_karyawan'] ?? ''
            )
        );

        if (
            in_array(
                $status,
                [
                    'RESIGN',
                    'MUTASI',
                    'TERMINATED',
                    'PHK',
                    'TERMINATION',
                    'NON AKTIF',
                    'INACTIVE',
                ],
                true
            )
        ) {
            return false;
        }

        $department = $this->normalizeText(
            (string) (
                $employee['departemen'] ?? ''
            )
        );

        if (
            $this->meaningful($department)
            && !str_contains(
                $department,
                'PRODUKSI'
            )
            && !str_contains(
                $department,
                'PRODUCTION'
            )
        ) {
            return false;
        }

        $site = $this->normalizeText(
            (string) (
                $employee['site'] ?? ''
            )
        );

        if (!$this->meaningful($site)) {
            return true;
        }

        if (
            str_contains(
                $site,
                'BUKIT ASAM'
            )
            || in_array(
                $site,
                [
                    'BA',
                    'SITE BA',
                    'BUKITASAM',
                ],
                true
            )
        ) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Tambah Karyawan
    |--------------------------------------------------------------------------
    |
    | Tidak append langsung ke MASTER_DATABASE.
    | Submit lewat UPDATE_DATA_KARYAWAN existing.
    */

    public function submitNewEmployee(
        array $data,
        ?string $userName = null,
        ?string $userEmail = null
    ): array {
        $nrp = $this->normalizeNrp(
            (string) ($data['nrp'] ?? '')
        );

        if ($nrp === '') {
            throw new RuntimeException(
                'NRP wajib diisi.'
            );
        }

        $status = $this->validatedStatus(
            (string) (
                $data['status_karyawan']
                ?? 'NEW HIRE'
            )
        );

        if (
            !in_array(
                $status,
                ['NEW HIRE', 'EXISTING DATA'],
                true
            )
        ) {
            throw new RuntimeException(
                'Karyawan baru hanya dapat memakai status NEW HIRE atau EXISTING DATA.'
            );
        }

        /*
         * Duplicate protection menggunakan synchronize() agar cek NRP
         * dilakukan terhadap Google MASTER terbaru, bukan cache lama.
         */
        $live = $this->employeeMaster
            ->synchronize();

        $metaStatus = strtolower(
            trim(
                (string) (
                    $live['meta']['status']
                    ?? ''
                )
            )
        );

        if ($metaStatus !== 'synced') {
            throw new RuntimeException(
                'MASTER_DATABASE belum dapat diverifikasi secara live. Penambahan dibatalkan agar NRP tidak duplikat.'
            );
        }

        $liveEmployees = is_array(
            $live['employees'] ?? null
        )
            ? $live['employees']
            : [];

        if (
            $this->findInEmployees(
                $liveEmployees,
                $nrp
            ) !== null
            || $this->pendingByNrp($nrp) !== null
        ) {
            throw new RuntimeException(
                "NRP {$nrp} sudah terdaftar pada MASTER_DATABASE."
            );
        }

        $payload = [
            'timestamp' => now()->format(
                'Y-m-d H:i:s'
            ),
            'action' =>
                $this->lifecycleActionForStatus(
                    $status,
                    true
                ),
            'nrp' => $nrp,
            'nama' => trim(
                (string) ($data['nama'] ?? '')
            ),
            'jabatan' => trim(
                (string) ($data['jabatan'] ?? '')
            ),
            'departemen' => trim(
                (string) (
                    $data['departemen']
                    ?? 'PRODUKSI'
                )
            ),
            'site' => trim(
                (string) (
                    $data['site']
                    ?? 'BUKIT ASAM'
                )
            ),
            'status_karyawan' => $status,
            'status_baru' => $status,
            'tanggal_efektif' => now()
                ->format('Y-m-d'),
            'catatan' => trim(
                (string) ($data['catatan'] ?? '')
            ),
            'source' => 'SYNRGYPRO MCU & FU',
            'user_name' => trim(
                (string) ($userName ?? '')
            ),
            'user_email' => trim(
                (string) ($userEmail ?? '')
            ),
        ];

        if ($payload['nama'] === '') {
            throw new RuntimeException(
                'Nama karyawan wajib diisi.'
            );
        }

        /*
         * 1. Data karyawan -> UPDATE_DATA_KARYAWAN.
         */
        $dataRow = $this->buildConfiguredRow(
            'services.google_sheets.update_data_spreadsheet_id',
            'services.google_sheets.update_data_range',
            $payload,
            ['nrp', 'nama']
        );

        $this->googleSheets
            ->appendEmployeeDataUpdate(
                $dataRow
            );

        /*
         * 2. Status -> UPDATE_STATUS_KARYAWAN jika konfigurasi tersedia.
         *    Jika tab status belum dikonfigurasi, data update tetap sah.
         */
        $statusWritten = false;

        try {
            $statusRow = $this->buildConfiguredRow(
                'services.google_sheets.update_status_spreadsheet_id',
                'services.google_sheets.update_status_range',
                $payload,
                ['nrp', 'status']
            );

            $this->googleSheets
                ->appendEmployeeStatusUpdate(
                    $statusRow
                );

            $statusWritten = true;
        } catch (Throwable $exception) {
            report($exception);
        }

        $pending = [
            'nrp' => $nrp,
            'nama' => $payload['nama'],
            'jabatan' => $payload['jabatan'],
            'departemen' => $payload['departemen'],
            'site' => $payload['site'],
            'status_karyawan' => $status,
            '_pending_master_sync' => true,
            '_pending_action' => 'CREATE',
        ];

        $this->rememberPending(
            $pending
        );

        /*
         * Buang fresh cache saja. Backup existing tidak dihapus.
         */
        Cache::forget(
            'database.master.employees.fresh.v1'
        );

        return [
            'nrp' => $nrp,
            'status' => $status,
            'data_update_written' => true,
            'status_update_written' =>
                $statusWritten,
            'pending_master_sync' => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status / Mutasi
    |--------------------------------------------------------------------------
    |
    | Status lewat UPDATE_STATUS_KARYAWAN.
    | Jika site berubah (khususnya MUTASI), site juga dikirim melalui
    | UPDATE_DATA_KARYAWAN agar mekanisme existing dapat memproses perubahan.
    */

    public function submitLifecycleUpdate(
        array $data,
        ?string $userName = null,
        ?string $userEmail = null
    ): array {
        $nrp = $this->normalizeNrp(
            (string) ($data['nrp'] ?? '')
        );

        if ($nrp === '') {
            throw new RuntimeException(
                'NRP wajib diisi.'
            );
        }

        $statusNew = $this->validatedStatus(
            (string) ($data['status_baru'] ?? '')
        );

        $live = $this->employeeMaster
            ->synchronize();

        $metaStatus = strtolower(
            trim(
                (string) (
                    $live['meta']['status']
                    ?? ''
                )
            )
        );

        if ($metaStatus !== 'synced') {
            throw new RuntimeException(
                'MASTER_DATABASE belum dapat diverifikasi secara live. Update status dibatalkan.'
            );
        }

        $liveEmployees = is_array(
            $live['employees'] ?? null
        )
            ? $live['employees']
            : [];

        $employee = $this->findInEmployees(
            $liveEmployees,
            $nrp
        );

        if ($employee === null) {
            throw new RuntimeException(
                "NRP {$nrp} belum terdaftar. Gunakan Tambah Karyawan terlebih dahulu."
            );
        }

        $siteOld = trim(
            (string) (
                $employee['site'] ?? ''
            )
        );

        $siteNew = trim(
            (string) (
                $data['site_baru'] ?? ''
            )
        );

        if ($statusNew === 'MUTASI' && $siteNew === '') {
            throw new RuntimeException(
                'Site tujuan wajib diisi untuk status MUTASI.'
            );
        }

        if ($siteNew === '') {
            $siteNew = $siteOld;
        }

        $payload = [
            'timestamp' => now()->format(
                'Y-m-d H:i:s'
            ),
            'action' =>
                $this->lifecycleActionForStatus(
                    $statusNew
                ),
            'nrp' => $nrp,
            'nama' => trim(
                (string) (
                    $employee['nama'] ?? ''
                )
            ),
            'jabatan' => trim(
                (string) (
                    $employee['jabatan'] ?? ''
                )
            ),
            'departemen' => trim(
                (string) (
                    $employee['departemen']
                    ?? ''
                )
            ),
            'site' => $siteNew,
            'site_lama' => $siteOld,
            'site_baru' => $siteNew,
            'status_karyawan' => $statusNew,
            'status_lama' => trim(
                (string) (
                    $employee['status_karyawan']
                    ?? ''
                )
            ),
            'status_baru' => $statusNew,
            'tanggal_efektif' => trim(
                (string) (
                    $data['tanggal_efektif']
                    ?? now()->format('Y-m-d')
                )
            ),
            'catatan' => trim(
                (string) ($data['catatan'] ?? '')
            ),
            'source' => 'SYNRGYPRO MCU & FU',
            'user_name' => trim(
                (string) ($userName ?? '')
            ),
            'user_email' => trim(
                (string) ($userEmail ?? '')
            ),
        ];

        $statusRow = $this->buildConfiguredRow(
            'services.google_sheets.update_status_spreadsheet_id',
            'services.google_sheets.update_status_range',
            $payload,
            ['nrp', 'status']
        );

        $this->googleSheets
            ->appendEmployeeStatusUpdate(
                $statusRow
            );

        $dataWritten = false;

        if (
            $this->normalizeText($siteNew)
            !== $this->normalizeText($siteOld)
        ) {
            try {
                $dataRow = $this->buildConfiguredRow(
                    'services.google_sheets.update_data_spreadsheet_id',
                    'services.google_sheets.update_data_range',
                    $payload,
                    ['nrp']
                );

                $this->googleSheets
                    ->appendEmployeeDataUpdate(
                        $dataRow
                    );

                $dataWritten = true;
            } catch (Throwable $exception) {
                /*
                 * Status sudah berhasil masuk ke pipeline status.
                 * Error site update tetap dilaporkan untuk log.
                 */
                report($exception);
            }
        }

        $pending = array_merge(
            $employee,
            [
                'nrp' => $nrp,
                'site' => $siteNew,
                'status_karyawan' => $statusNew,
                '_pending_master_sync' => true,
                '_pending_action' => 'LIFECYCLE',
            ]
        );

        $this->rememberPending(
            $pending
        );

        Cache::forget(
            'database.master.employees.fresh.v1'
        );

        return [
            'nrp' => $nrp,
            'nama' => $payload['nama'],
            'status_lama' =>
                $payload['status_lama'],
            'status_baru' => $statusNew,
            'site_lama' => $siteOld,
            'site_baru' => $siteNew,
            'status_update_written' => true,
            'data_update_written' => $dataWritten,
            'pending_master_sync' => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pending overlay
    |--------------------------------------------------------------------------
    |
    | UPDATE_* sheet dapat diproses oleh formula/Apps Script secara async.
    | Overlay membuat MCU/FU langsung mengikuti perubahan setelah submit,
    | tanpa mengubah MASTER_DATABASE secara langsung.
    */

    private function applyPending(
        array $employees
    ): array {
        $pending = Cache::get(
            self::PENDING_KEY,
            []
        );

        if (
            !is_array($pending)
            || $pending === []
        ) {
            return $employees;
        }

        $now = now()->timestamp;

        $byNrp = [];

        foreach ($employees as $employee) {
            if (!is_array($employee)) {
                continue;
            }

            $nrp = $this->normalizeNrp(
                (string) (
                    $employee['nrp'] ?? ''
                )
            );

            if ($nrp === '') {
                continue;
            }

            $byNrp[$nrp] = $employee;
        }

        $changedPending = false;

        foreach ($pending as $nrp => $entry) {
            if (!is_array($entry)) {
                unset($pending[$nrp]);
                $changedPending = true;
                continue;
            }

            $expiresAt = (int) (
                $entry['_expires_at'] ?? 0
            );

            if (
                $expiresAt > 0
                && $expiresAt <= $now
            ) {
                unset($pending[$nrp]);
                $changedPending = true;
                continue;
            }

            $employee = $entry['employee'] ?? null;

            if (!is_array($employee)) {
                unset($pending[$nrp]);
                $changedPending = true;
                continue;
            }

            /*
             * Jika MASTER sudah mencerminkan pending status/site,
             * overlay dapat dibuang.
             */
            $master = $byNrp[$nrp] ?? null;

            if (
                is_array($master)
                && $this->pendingAlreadyApplied(
                    $master,
                    $employee
                )
            ) {
                unset($pending[$nrp]);
                $changedPending = true;
                continue;
            }

            $byNrp[$nrp] = array_merge(
                is_array($master)
                    ? $master
                    : [],
                $employee
            );
        }

        if ($changedPending) {
            Cache::put(
                self::PENDING_KEY,
                $pending,
                now()->addSeconds(
                    self::PENDING_TTL_SECONDS
                )
            );
        }

        return array_values(
            $byNrp
        );
    }

    private function pendingAlreadyApplied(
        array $master,
        array $pending
    ): bool {
        foreach (
            [
                'nama',
                'jabatan',
                'departemen',
                'site',
                'status_karyawan',
            ] as $field
        ) {
            $pendingValue = $this->normalizeText(
                (string) (
                    $pending[$field] ?? ''
                )
            );

            if (!$this->meaningful($pendingValue)) {
                continue;
            }

            $masterValue = $this->normalizeText(
                (string) (
                    $master[$field] ?? ''
                )
            );

            if ($masterValue !== $pendingValue) {
                return false;
            }
        }

        return true;
    }

    private function rememberPending(
        array $employee
    ): void {
        $nrp = $this->normalizeNrp(
            (string) ($employee['nrp'] ?? '')
        );

        if ($nrp === '') {
            return;
        }

        $pending = Cache::get(
            self::PENDING_KEY,
            []
        );

        if (!is_array($pending)) {
            $pending = [];
        }

        $pending[$nrp] = [
            'employee' => $employee,
            '_expires_at' =>
                now()->timestamp
                + self::PENDING_TTL_SECONDS,
        ];

        Cache::put(
            self::PENDING_KEY,
            $pending,
            now()->addSeconds(
                self::PENDING_TTL_SECONDS
            )
        );
    }

    private function pendingByNrp(
        string $nrp
    ): ?array {
        $pending = Cache::get(
            self::PENDING_KEY,
            []
        );

        if (!is_array($pending)) {
            return null;
        }

        $entry = $pending[$nrp] ?? null;

        if (!is_array($entry)) {
            return null;
        }

        return is_array(
            $entry['employee'] ?? null
        )
            ? $entry['employee']
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Header-aware writer UPDATE_* sheets
    |--------------------------------------------------------------------------
    |
    | Tidak mengasumsikan urutan kolom.
    | Header sheet dibaca terlebih dahulu, kemudian payload ditempatkan pada
    | kolom yang cocok. Ini jauh lebih aman daripada hardcode A/B/C dst.
    */

    private function buildConfiguredRow(
        string $spreadsheetConfig,
        string $rangeConfig,
        array $payload,
        array $required
    ): array {
        $spreadsheetId = trim(
            (string) config(
                $spreadsheetConfig
            )
        );

        $range = trim(
            (string) config(
                $rangeConfig
            )
        );

        if (
            $spreadsheetId === ''
            || $range === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi sheet pipeline karyawan belum lengkap.'
            );
        }

        $values = $this->googleSheets
            ->getValues(
                $spreadsheetId,
                $range
            );

        if ($values === []) {
            throw new RuntimeException(
                'Sheet pipeline karyawan belum memiliki header.'
            );
        }

        $headerIndex =
            $this->detectPipelineHeaderRow(
                $values
            );

        if ($headerIndex === null) {
            throw new RuntimeException(
                'Header sheet pipeline karyawan tidak dapat dideteksi.'
            );
        }

        $headers = array_map(
            fn ($value): string =>
                trim((string) $value),
            is_array($values[$headerIndex])
                ? $values[$headerIndex]
                : []
        );

        $row = array_fill(
            0,
            count($headers),
            ''
        );

        $mappedFields = [];

        foreach ($headers as $index => $header) {
            $field =
                $this->pipelineFieldForHeader(
                    $header
                );

            if (
                $field === null
                || !array_key_exists(
                    $field,
                    $payload
                )
            ) {
                continue;
            }

            $row[$index] =
                $payload[$field];

            $mappedFields[$field] = true;

            /*
             * Alias status generik dianggap memenuhi required status.
             */
            if (
                in_array(
                    $field,
                    [
                        'status_karyawan',
                        'status_baru',
                    ],
                    true
                )
            ) {
                $mappedFields['status'] = true;
            }
        }

        foreach ($required as $field) {
            if (
                !isset(
                    $mappedFields[$field]
                )
            ) {
                throw new RuntimeException(
                    "Kolom wajib {$field} tidak ditemukan pada header sheet pipeline."
                );
            }
        }

        return $row;
    }

    private function detectPipelineHeaderRow(
        array $values
    ): ?int {
        $limit = min(
            10,
            count($values)
        );

        for (
            $index = 0;
            $index < $limit;
            $index++
        ) {
            $row = is_array($values[$index])
                ? $values[$index]
                : [];

            $mapped = [];

            foreach ($row as $header) {
                $field =
                    $this->pipelineFieldForHeader(
                        (string) $header
                    );

                if ($field !== null) {
                    $mapped[$field] = true;
                }
            }

            if (
                isset($mapped['nrp'])
                && (
                    isset($mapped['nama'])
                    || isset($mapped['status_karyawan'])
                    || isset($mapped['status_baru'])
                )
            ) {
                return $index;
            }
        }

        return null;
    }

    private function pipelineFieldForHeader(
        string $header
    ): ?string {
        $header = $this->normalizeHeader(
            $header
        );

        if ($header === '') {
            return null;
        }

        $aliases = [
            'timestamp' => [
                'TIMESTAMP',
                'WAKTU',
                'TANGGAL INPUT',
                'WAKTU INPUT',
            ],
            'action' => [
                'ACTION',
                'AKSI',
                'JENIS UPDATE',
                'TIPE UPDATE',
                'JENIS PERUBAHAN',
                'JENIS_PERUBAHAN',
            ],
            'nrp' => [
                'NRP',
                'NRP KARYAWAN',
                'NRP_KARYAWAN',
                'NIK KARYAWAN',
            ],
            'nama' => [
                'NAMA',
                'NAMA KARYAWAN',
                'NAMA LENGKAP',
                'NAMA LENGKAP KARYAWAN',
                'NAMA_LENGKAP_KARYAWAN',
            ],
            'jabatan' => [
                'JABATAN',
                'POSISI',
                'JABATAN KARYAWAN',
            ],
            'departemen' => [
                'DEPARTEMEN',
                'DEPARTMENT',
                'DIVISI',
            ],
            'site_lama' => [
                'SITE LAMA',
                'LOKASI LAMA',
            ],
            'site_baru' => [
                'SITE BARU',
                'SITE TUJUAN',
                'LOKASI BARU',
                'LOKASI TUJUAN',
            ],
            'site' => [
                'SITE',
                'LOKASI KERJA',
                'WORK LOCATION',
            ],
            'status_lama' => [
                'STATUS LAMA',
                'STATUS KARYAWAN LAMA',
            ],
            'status_baru' => [
                'STATUS BARU',
                'STATUS KARYAWAN BARU',
            ],
            'status_karyawan' => [
                'STATUS KARYAWAN',
                'STATUS_KARYAWAN',
                'STATUS',
            ],
            'tanggal_efektif' => [
                'TANGGAL EFEKTIF',
                'TGL EFEKTIF',
                'EFFECTIVE DATE',
            ],
            'catatan' => [
                'CATATAN',
                'KETERANGAN',
                'NOTE',
                'NOTES',
                'ALASAN',
                'ALASAN KETERANGAN',
                'ALASAN_KETERANGAN',
            ],
            'source' => [
                'SOURCE',
                'SUMBER',
                'SUMBER UPDATE',
            ],
            'user_name' => [
                'USER',
                'USER NAME',
                'NAMA USER',
                'UPDATED BY',
                'DIUPDATE OLEH',
                'DIUPDATE_OLEH',
            ],
            'user_email' => [
                'USER EMAIL',
                'EMAIL USER',
                'UPDATED BY EMAIL',
            ],
        ];

        /*
         * Exact match.
         */
        foreach ($aliases as $field => $items) {
            foreach ($items as $alias) {
                if (
                    $header ===
                    $this->normalizeHeader(
                        $alias
                    )
                ) {
                    return $field;
                }
            }
        }

        /*
         * Safe contains untuk header panjang/form response.
         */
        foreach ($aliases as $field => $items) {
            foreach ($items as $alias) {
                $alias = $this->normalizeHeader(
                    $alias
                );

                if (
                    mb_strlen($alias) >= 5
                    && str_contains(
                        $header,
                        $alias
                    )
                ) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function findInEmployees(
        array $employees,
        string $nrp
    ): ?array {
        foreach ($employees as $employee) {
            if (!is_array($employee)) {
                continue;
            }

            if (
                $this->normalizeNrp(
                    (string) (
                        $employee['nrp'] ?? ''
                    )
                ) === $nrp
            ) {
                return $employee;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping JENIS_PERUBAHAN
    |--------------------------------------------------------------------------
    */

    private function lifecycleActionForStatus(
        string $status,
        bool $isNewEmployee = false
    ): string {
        $status = $this->normalizeText(
            $status
        );

        return match ($status) {
            'NEW HIRE' =>
                'NEW HIRE',

            'EXISTING DATA' =>
                $isNewEmployee
                    ? 'TAMBAH KARYAWAN'
                    : 'UPDATE STATUS',

            'RESIGN' =>
                'RESIGN',

            'MUTASI' =>
                'MUTASI',

            'TERMINATED' =>
                'TERMINATED',

            default =>
                'UPDATE STATUS',
        };
    }

    private function validatedStatus(
        string $status
    ): string {
        $status = $this->normalizeText(
            $status
        );

        if (
            !in_array(
                $status,
                self::STATUS_OPTIONS,
                true
            )
        ) {
            throw new RuntimeException(
                'Status karyawan tidak valid.'
            );
        }

        return $status;
    }

    private function meaningful(
        string $value
    ): bool {
        return $value !== ''
            && $value !== '-'
            && $value !== 'BELUM DATA';
    }

    private function normalizeNrp(
        string $value
    ): string {
        $value = trim($value);

        if (
            preg_match(
                '/^\d+\.0+$/',
                $value
            )
        ) {
            $value = preg_replace(
                '/\.0+$/',
                '',
                $value
            ) ?? $value;
        }

        return preg_replace(
            '/\s+/',
            '',
            $value
        ) ?? $value;
    }

    private function normalizeText(
        string $value
    ): string {
        return strtoupper(
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    str_replace(
                        ['_', '-'],
                        ' ',
                        $value
                    )
                ) ?? ''
            )
        );
    }

    private function normalizeHeader(
        string $value
    ): string {
        $value = strtoupper(
            trim($value)
        );

        $value = preg_replace(
            '/[^A-Z0-9]+/',
            ' ',
            $value
        ) ?? '';

        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                $value
            ) ?? ''
        );
    }
}
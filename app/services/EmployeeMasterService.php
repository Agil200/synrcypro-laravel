<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class EmployeeMasterService
{
    private const CACHE_KEY =
        'database.master.employees.fresh.v1';

    private const BACKUP_KEY =
        'database.master.employees.backup.v1';

    private const META_KEY =
        'database.master.employees.meta.v1';

    private const LOCK_KEY =
        'database.master.employees.sync.lock.v1';

    private const DURABLE_BACKUP_FILE =
        'database-cache/master-employees-backup.json';

    /**
     * Field penting yang menentukan status kelengkapan detail karyawan.
     */
    private const DETAIL_REQUIRED_FIELDS = [
        'nama',
        'jabatan',
        'departemen',
        'perusahaan',
        'site',
        'no_hp',
        'email',
        'status_tinggal',
    ];

    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Snapshot untuk halaman Database Karyawan
    |--------------------------------------------------------------------------
    | Cache segar digunakan terlebih dahulu.
    | API hanya dipanggil jika cache segar belum tersedia.
    | Jika API gagal, backup cache terakhir tetap ditampilkan.
    */

    public function snapshot(): array
    {
        $freshEmployees = Cache::get(
            self::CACHE_KEY
        );

        if (is_array($freshEmployees)) {
            return [
                'employees' => $freshEmployees,
                'meta' => $this->meta(
                    isStale: false
                ),
            ];
        }

        try {
            return $this->refresh(
                force: false
            );
        } catch (Throwable $exception) {
            return $this->staleSnapshot(
                $exception
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Sinkronisasi manual melalui tombol UI
    |--------------------------------------------------------------------------
    */

    public function synchronize(): array
    {
        try {
            return $this->refresh(
                force: true
            );
        } catch (Throwable $exception) {
            return $this->staleSnapshot(
                $exception
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Informasi sumber
    |--------------------------------------------------------------------------
    */

    public function sourceUrl(): ?string
    {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.master_database_spreadsheet_id'
            )
        );

        if ($spreadsheetId === '') {
            return null;
        }

        return
            'https://docs.google.com/spreadsheets/d/' .
            rawurlencode($spreadsheetId) .
            '/edit';
    }

    public function isGoogleConnected(): bool
    {
        return $this->googleSheets
            ->hasStoredToken();
    }


    /*
    |--------------------------------------------------------------------------
    | Diagnostik mapping MASTER_DATABASE
    |--------------------------------------------------------------------------
    | Method ini membaca Google Sheets secara langsung dan hanya dipakai
    | pada halaman pemeriksaan mapping. Halaman Database Karyawan biasa
    | tetap menggunakan cache.
    */

    public function mappingDiagnostics(): array
    {
        $values = $this->googleSheets
            ->getMasterDatabaseValues();

        if (count($values) < 2) {
            throw new RuntimeException(
                'MASTER_DATABASE belum memiliki baris data.'
            );
        }

        $headerRowIndex =
            $this->detectHeaderRow($values);

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header NRP dan NAMA tidak ditemukan pada 10 baris pertama.'
            );
        }

        $rawHeaders = array_map(
            fn ($value): string =>
                trim((string) $value),
            is_array($values[$headerRowIndex])
                ? $values[$headerRowIndex]
                : []
        );

        $normalizedHeaders = array_map(
            fn (string $value): string =>
                $this->normalizeHeader($value),
            $rawHeaders
        );

        $columns = $this->resolveColumns(
            $normalizedHeaders
        );

        $aliases = $this->columnAliases();
        $fieldLabels = $this->fieldLabels();
        $fieldMappings = [];

        foreach ($aliases as $field => $fieldAliases) {
            /*
             * Gedung/Kamar bukan kolom wajib dari spreadsheet.
             * Field ini dapat diturunkan dari Gedung + Kamar.
             */
            if ($field === 'gedung_kamar') {
                $gedungIndex = (int) (
                    $columns['gedung'] ?? -1
                );

                $kamarIndex = (int) (
                    $columns['kamar'] ?? -1
                );

                $isDerived =
                    $gedungIndex >= 0 &&
                    $kamarIndex >= 0;

                $fieldMappings[] = [
                    'field' => $field,
                    'label' =>
                        $fieldLabels[$field] ??
                        'Gedung/Kamar Gabungan',

                    'mapping_status' =>
                        $isDerived
                            ? 'derived'
                            : 'missing',

                    'column_index' => -1,
                    'column_letter' =>
                        $isDerived
                            ? (
                                $this->columnLetter(
                                    $gedungIndex
                                ) .
                                ' + ' .
                                $this->columnLetter(
                                    $kamarIndex
                                )
                            )
                            : '-',

                    'raw_header' =>
                        $isDerived
                            ? (
                                (
                                    $rawHeaders[
                                        $gedungIndex
                                    ] ?? 'GEDUNG'
                                ) .
                                ' + ' .
                                (
                                    $rawHeaders[
                                        $kamarIndex
                                    ] ?? 'KAMAR'
                                )
                            )
                            : '-',

                    'normalized_header' =>
                        $isDerived
                            ? 'DITURUNKAN OTOMATIS'
                            : '-',

                    'matched' => false,
                    'derived' => $isDerived,
                    'aliases' => [
                        'Dihitung otomatis dari Gedung + Kamar',
                    ],
                    'samples' =>
                        $isDerived
                            ? $this->sampleCombinedRoomValues(
                                $values,
                                $headerRowIndex,
                                $gedungIndex,
                                $kamarIndex
                            )
                            : [],
                ];

                continue;
            }

            $columnIndex = (int) (
                $columns[$field] ?? -1
            );

            $sampleValues = [];

            if ($columnIndex >= 0) {
                $sampleValues =
                    $this->sampleColumnValues(
                        $values,
                        $headerRowIndex,
                        $columnIndex
                    );
            }

            $fieldMappings[] = [
                'field' => $field,
                'label' =>
                    $fieldLabels[$field] ??
                    strtoupper($field),

                'mapping_status' =>
                    $columnIndex >= 0
                        ? 'direct'
                        : 'missing',

                'column_index' => $columnIndex,
                'column_letter' =>
                    $columnIndex >= 0
                        ? $this->columnLetter(
                            $columnIndex
                        )
                        : '-',

                'raw_header' =>
                    $columnIndex >= 0
                        ? (
                            $rawHeaders[$columnIndex] ??
                            '-'
                        )
                        : '-',

                'normalized_header' =>
                    $columnIndex >= 0
                        ? (
                            $normalizedHeaders[
                                $columnIndex
                            ] ?? '-'
                        )
                        : '-',

                'matched' => $columnIndex >= 0,
                'derived' => false,
                'aliases' => $fieldAliases,
                'samples' => $sampleValues,
            ];
        }

        $normalized = $this->normalizeValues(
            $values
        );

        $employees = collect(
            $normalized['employees']
        );

        $quality = [];

        foreach (
            [
                'nama',
                'jabatan',
                'departemen',
                'perusahaan',
                'site',
                'no_hp',
                'email',
                'tanggal_lahir',
                'status_karyawan',
                'status_tinggal',
                'gedung',
                'kamar',
                'gedung_kamar',
                'foto',
            ] as $field
        ) {
            $emptyCount = $employees
                ->filter(
                    fn (array $employee): bool =>
                        trim(
                            (string) (
                                $employee[$field] ??
                                ''
                            )
                        ) === '' ||
                        trim(
                            (string) (
                                $employee[$field] ??
                                ''
                            )
                        ) === '-'
                )
                ->count();

            $quality[] = [
                'field' => $field,
                'label' =>
                    $fieldLabels[$field] ??
                    strtoupper($field),

                'empty_count' => $emptyCount,
                'filled_count' =>
                    max(
                        0,
                        $employees->count() -
                        $emptyCount
                    ),

                'total' => $employees->count(),
                'filled_percentage' =>
                    $employees->count() > 0
                        ? round(
                            (
                                (
                                    $employees->count() -
                                    $emptyCount
                                ) /
                                $employees->count()
                            ) * 100,
                            1
                        )
                        : 0,
            ];
        }

        $usedIndexes = collect($columns)
            ->filter(
                fn ($index): bool =>
                    (int) $index >= 0
            )
            ->map(
                fn ($index): int =>
                    (int) $index
            )
            ->unique()
            ->values()
            ->all();

        $unmappedHeaders = [];

        foreach ($rawHeaders as $index => $header) {
            if (
                trim($header) === '' ||
                in_array(
                    (int) $index,
                    $usedIndexes,
                    true
                )
            ) {
                continue;
            }

            $unmappedHeaders[] = [
                'column_index' => (int) $index,
                'column_letter' =>
                    $this->columnLetter(
                        (int) $index
                    ),
                'raw_header' => $header,
                'normalized_header' =>
                    $normalizedHeaders[$index] ??
                    '',
                'samples' =>
                    $this->sampleColumnValues(
                        $values,
                        $headerRowIndex,
                        (int) $index
                    ),
            ];
        }

        $mappingCollection = collect(
            $fieldMappings
        );

        $directMappedCount =
            $mappingCollection
                ->where(
                    'mapping_status',
                    'direct'
                )
                ->count();

        $derivedFieldCount =
            $mappingCollection
                ->where(
                    'mapping_status',
                    'derived'
                )
                ->count();

        $unavailableFieldCount =
            $mappingCollection
                ->where(
                    'mapping_status',
                    'missing'
                )
                ->count();

        return [
            'source' => 'Google Sheets',
            'range' => trim(
                (string) config(
                    'services.google_sheets.master_database_range'
                )
            ),
            'header_row' => $headerRowIndex + 1,
            'raw_row_count' =>
                max(
                    0,
                    count($values) -
                    ($headerRowIndex + 1)
                ),
            'mapped_employee_count' =>
                $employees->count(),
            'duplicate_rows' =>
                (int) (
                    $normalized['duplicate_rows'] ??
                    0
                ),
            'field_count' =>
                count($fieldMappings),
            'direct_mapped_count' =>
                $directMappedCount,
            'derived_field_count' =>
                $derivedFieldCount,
            'unavailable_field_count' =>
                $unavailableFieldCount,
            /*
             * Kompatibilitas untuk kode lama.
             */
            'matched_field_count' =>
                $directMappedCount,
            'missing_field_count' =>
                $unavailableFieldCount,
            'field_mappings' => $fieldMappings,
            'quality' => $quality,
            'cache' => $this->cacheDiagnostics(),
            'raw_headers' => collect(
                $rawHeaders
            )->map(
                fn (
                    string $header,
                    int $index
                ): array => [
                    'column_index' => $index,
                    'column_letter' =>
                        $this->columnLetter($index),
                    'raw_header' =>
                        $header !== ''
                            ? $header
                            : '(kosong)',
                    'normalized_header' =>
                        $normalizedHeaders[$index] ??
                        '',
                ]
            )->values()->all(),
            'unmapped_headers' => $unmappedHeaders,
            'checked_at' =>
                now()->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Diagnostik cache dan fallback
    |--------------------------------------------------------------------------
    */

    public function cacheDiagnostics(): array
    {
        $freshEmployees = Cache::get(
            self::CACHE_KEY
        );

        $backupEmployees = Cache::get(
            self::BACKUP_KEY
        );

        $durableBackup =
            $this->readDurableBackup();

        $meta = Cache::get(
            self::META_KEY,
            []
        );

        $freshExists =
            is_array($freshEmployees);

        $cacheBackupExists =
            is_array($backupEmployees) &&
            $backupEmployees !== [];

        $durableExists =
            is_array($durableBackup) &&
            is_array(
                $durableBackup['employees'] ??
                null
            );

        $freshCount =
            $freshExists
                ? count($freshEmployees)
                : 0;

        $cacheBackupCount =
            $cacheBackupExists
                ? count($backupEmployees)
                : 0;

        $durableCount =
            $durableExists
                ? count(
                    $durableBackup['employees']
                )
                : 0;

        $effectiveBackup =
            $cacheBackupExists
                ? $backupEmployees
                : (
                    $durableExists
                        ? $durableBackup['employees']
                        : []
                );

        $effectiveBackupCount =
            count($effectiveBackup);

        $sameData = false;

        if (
            $freshExists &&
            $effectiveBackup !== []
        ) {
            $freshHash = hash(
                'sha256',
                json_encode(
                    $freshEmployees,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ) ?: ''
            );

            $backupHash = hash(
                'sha256',
                json_encode(
                    $effectiveBackup,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ) ?: ''
            );

            $sameData =
                hash_equals(
                    $freshHash,
                    $backupHash
                );
        }

        return [
            'driver' =>
                (string) config(
                    'cache.default',
                    'file'
                ),
            'fresh_exists' => $freshExists,
            'backup_exists' =>
                $cacheBackupExists ||
                $durableExists,
            'cache_backup_exists' =>
                $cacheBackupExists,
            'durable_backup_exists' =>
                $durableExists,
            'meta_exists' => is_array($meta),
            'fresh_count' => $freshCount,
            'backup_count' =>
                $effectiveBackupCount,
            'cache_backup_count' =>
                $cacheBackupCount,
            'durable_backup_count' =>
                $durableCount,
            'backup_source' =>
                $cacheBackupExists
                    ? 'cache'
                    : (
                        $durableExists
                            ? 'storage'
                            : null
                    ),
            'same_data' => $sameData,
            'fallback_ready' =>
                $effectiveBackupCount > 0,
            'status' =>
                is_array($meta)
                    ? (
                        $meta['status'] ??
                        'unknown'
                    )
                    : 'not_available',
            'synced_at' =>
                is_array($meta)
                    ? (
                        $meta['synced_at'] ??
                        null
                    )
                    : null,
            'expires_at' =>
                is_array($meta)
                    ? (
                        $meta['expires_at'] ??
                        null
                    )
                    : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Uji fallback tanpa menghapus cache dan tanpa memanggil Google
    |--------------------------------------------------------------------------
    */

    public function testFallbackCache(): array
    {
        $backupEmployees = Cache::get(
            self::BACKUP_KEY
        );

        $source = 'cache';

        if (
            !is_array($backupEmployees) ||
            $backupEmployees === []
        ) {
            $durableBackup =
                $this->readDurableBackup();

            $backupEmployees =
                $durableBackup['employees'] ??
                [];

            $source = 'storage';
        }

        if (
            !is_array($backupEmployees) ||
            $backupEmployees === []
        ) {
            throw new RuntimeException(
                'Backup belum tersedia. Jalankan sinkronisasi berhasil satu kali terlebih dahulu.'
            );
        }

        $sampleNrps = collect(
            $backupEmployees
        )
            ->take(3)
            ->pluck('nrp')
            ->filter()
            ->values()
            ->all();

        return [
            'status' => 'passed',
            'source' => $source,
            'employees_count' =>
                count($backupEmployees),
            'sample_nrps' => $sampleNrps,
            'tested_at' =>
                now()->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh cache
    |--------------------------------------------------------------------------
    */

    private function refresh(bool $force): array
    {
        return Cache::lock(
            self::LOCK_KEY,
            60
        )->block(
            10,
            function () use ($force): array {
                if (!$force) {
                    $freshEmployees = Cache::get(
                        self::CACHE_KEY
                    );

                    if (is_array($freshEmployees)) {
                        return [
                            'employees' =>
                                $freshEmployees,

                            'meta' =>
                                $this->meta(
                                    isStale: false
                                ),
                        ];
                    }
                }

                $values = $this->googleSheets
                    ->getMasterDatabaseValues();

                $normalized = $this->normalizeValues(
                    $values
                );

                $employees =
                    $normalized['employees'];

                if ($employees === []) {
                    throw new RuntimeException(
                        'MASTER_DATABASE tidak menghasilkan data karyawan.'
                    );
                }

                $ttlSeconds = max(
                    60,
                    (int) config(
                        'services.google_sheets.master_database_cache_ttl_seconds',
                        3600
                    )
                );

                $syncedAt = now();

                $meta = [
                    'status' => 'synced',
                    'source' => 'Google Sheets',
                    'range' => trim(
                        (string) config(
                            'services.google_sheets.master_database_range'
                        )
                    ),
                    'synced_at' =>
                        $syncedAt->toIso8601String(),

                    'expires_at' =>
                        $syncedAt
                            ->copy()
                            ->addSeconds($ttlSeconds)
                            ->toIso8601String(),

                    'source_rows' =>
                        $normalized['source_rows'],

                    'mapped_rows' =>
                        count($employees),

                    'duplicate_rows' =>
                        $normalized['duplicate_rows'],

                    'header_row' =>
                        $normalized['header_row'],

                    'missing_fields' =>
                        $normalized[
                            'missing_fields'
                        ] ?? [],

                    'is_stale' => false,
                    'error' => null,
                ];

                Cache::put(
                    self::CACHE_KEY,
                    $employees,
                    now()->addSeconds($ttlSeconds)
                );

                /*
                 * Backup cache cepat. Cache ini bisa hilang jika
                 * administrator menjalankan cache:clear.
                 */
                Cache::forever(
                    self::BACKUP_KEY,
                    $employees
                );

                /*
                 * Backup tahan lama di storage/app. Backup ini tetap ada
                 * meskipun cache Laravel dibersihkan.
                 */
                try {
                    $this->writeDurableBackup(
                        $employees,
                        $meta
                    );

                    $meta['durable_backup_status'] =
                        'ready';

                    $meta['durable_backup_file'] =
                        self::DURABLE_BACKUP_FILE;
                } catch (Throwable $backupException) {
                    /*
                     * Kegagalan menulis backup tidak boleh membatalkan
                     * sinkronisasi Google yang sudah berhasil.
                     */
                    $meta['durable_backup_status'] =
                        'failed';

                    $meta['durable_backup_error'] =
                        $backupException->getMessage();
                }

                Cache::forever(
                    self::META_KEY,
                    $meta
                );

                return [
                    'employees' => $employees,
                    'meta' => $meta,
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fallback cache terakhir
    |--------------------------------------------------------------------------
    */

    private function staleSnapshot(
        Throwable $exception
    ): array {
        $backupEmployees = Cache::get(
            self::BACKUP_KEY
        );

        $backupMeta = Cache::get(
            self::META_KEY,
            []
        );

        $fallbackSource = 'cache';

        if (
            !is_array($backupEmployees) ||
            $backupEmployees === []
        ) {
            $durableBackup =
                $this->readDurableBackup();

            if ($durableBackup !== null) {
                $backupEmployees =
                    $durableBackup['employees'];

                $backupMeta =
                    $durableBackup['meta'];

                $fallbackSource =
                    'storage';

                Cache::forever(
                    self::BACKUP_KEY,
                    $backupEmployees
                );

                Cache::forever(
                    self::META_KEY,
                    $backupMeta
                );
            }
        }

        if (
            !is_array($backupEmployees) ||
            $backupEmployees === []
        ) {
            return [
                'employees' => [],
                'meta' => [
                    'status' => 'error',
                    'source' => 'Google Sheets',
                    'range' => trim(
                        (string) config(
                            'services.google_sheets.master_database_range'
                        )
                    ),
                    'synced_at' => null,
                    'expires_at' => null,
                    'source_rows' => 0,
                    'mapped_rows' => 0,
                    'duplicate_rows' => 0,
                    'header_row' => null,
                    'missing_fields' => [],
                    'is_stale' => false,
                    'fallback_available' => false,
                    'fallback_source' => null,
                    'error' => $exception->getMessage(),
                ],
            ];
        }

        if (!is_array($backupMeta)) {
            $backupMeta = [];
        }

        $meta = array_merge(
            $this->meta(
                isStale: true
            ),
            $backupMeta,
            [
                'status' => 'stale',
                'mapped_rows' =>
                    count($backupEmployees),
                'is_stale' => true,
                'fallback_available' => true,
                'fallback_source' =>
                    $fallbackSource,
                'error' =>
                    $exception->getMessage(),
            ]
        );

        return [
            'employees' => $backupEmployees,
            'meta' => $meta,
        ];
    }

    private function writeDurableBackup(
        array $employees,
        array $meta
    ): void {
        $payload = [
            'version' => 1,
            'saved_at' =>
                now()->toIso8601String(),
            'employees' => $employees,
            'meta' => $meta,
        ];

        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_THROW_ON_ERROR
        );

        Storage::disk('local')->put(
            self::DURABLE_BACKUP_FILE,
            $json
        );
    }

    private function readDurableBackup(): ?array
    {
        $disk = Storage::disk('local');

        if (!$disk->exists(
            self::DURABLE_BACKUP_FILE
        )) {
            return null;
        }

        try {
            $payload = json_decode(
                (string) $disk->get(
                    self::DURABLE_BACKUP_FILE
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            return null;
        }

        $employees =
            $payload['employees'] ?? null;

        $meta =
            $payload['meta'] ?? [];

        if (
            !is_array($employees) ||
            $employees === []
        ) {
            return null;
        }

        return [
            'employees' => $employees,
            'meta' =>
                is_array($meta)
                    ? $meta
                    : [],
        ];
    }

    private function meta(bool $isStale): array
    {
        $meta = Cache::get(
            self::META_KEY,
            []
        );

        if (!is_array($meta)) {
            $meta = [];
        }

        return array_merge(
            [
                'status' =>
                    $isStale
                        ? 'stale'
                        : 'cached',

                'source' =>
                    'Google Sheets',

                'range' =>
                    trim(
                        (string) config(
                            'services.google_sheets.master_database_range'
                        )
                    ),

                'synced_at' => null,
                'expires_at' => null,
                'source_rows' => 0,
                'mapped_rows' => 0,
                'duplicate_rows' => 0,
                'header_row' => null,
                'missing_fields' => [],
                'fallback_available' => false,
                'fallback_source' => null,
                'durable_backup_status' => null,
                'durable_backup_file' =>
                    self::DURABLE_BACKUP_FILE,
                'error' => null,
            ],
            $meta,
            [
                'is_stale' => $isStale,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalisasi data spreadsheet
    |--------------------------------------------------------------------------
    */

    private function normalizeValues(
        array $values
    ): array {
        if (count($values) < 2) {
            throw new RuntimeException(
                'MASTER_DATABASE belum memiliki baris data.'
            );
        }

        $headerRowIndex =
            $this->detectHeaderRow($values);

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header NRP dan NAMA tidak ditemukan pada MASTER_DATABASE.'
            );
        }

        $headers = array_map(
            fn ($value): string =>
                $this->normalizeHeader(
                    (string) $value
                ),
            $values[$headerRowIndex]
        );

        $columns = $this->resolveColumns(
            $headers
        );

        if (
            $columns['nrp'] < 0 ||
            $columns['nama'] < 0
        ) {
            throw new RuntimeException(
                'Kolom wajib NRP atau NAMA tidak ditemukan.'
            );
        }

        $employeesByNrp = [];
        $duplicates = 0;
        $sourceRows = 0;

        for (
            $rowIndex = $headerRowIndex + 1;
            $rowIndex < count($values);
            $rowIndex++
        ) {
            $row = is_array($values[$rowIndex])
                ? $values[$rowIndex]
                : [];

            $nrp = $this->normalizeNrp(
                $this->cell(
                    $row,
                    $columns['nrp']
                )
            );

            $nama = trim(
                $this->cell(
                    $row,
                    $columns['nama']
                )
            );

            if ($nrp === '' && $nama === '') {
                continue;
            }

            $sourceRows++;

            if ($nrp === '') {
                /*
                 * Data tanpa NRP tidak aman untuk proses join.
                 */
                continue;
            }

            $combinedRoom = trim(
                $this->cell(
                    $row,
                    $columns['gedung_kamar']
                )
            );

            $gedung = trim(
                $this->cell(
                    $row,
                    $columns['gedung']
                )
            );

            $kamar = trim(
                $this->cell(
                    $row,
                    $columns['kamar']
                )
            );

            if (
                $combinedRoom !== '' &&
                $gedung === '' &&
                $kamar === ''
            ) {
                [$gedung, $kamar] =
                    $this->splitRoom(
                        $combinedRoom
                    );
            }

            $employee = [
                'nrp' => $nrp,
                'nama' =>
                    $nama !== ''
                        ? $nama
                        : '-',

                'jabatan' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['jabatan']
                        )
                    ),

                'departemen' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['departemen']
                        )
                    ),

                'perusahaan' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['perusahaan']
                        )
                    ),

                'site' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['site']
                        )
                    ),

                'no_hp' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['no_hp']
                        )
                    ),

                'email' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['email']
                        )
                    ),

                'tanggal_lahir' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['tanggal_lahir']
                        )
                    ),

                'status_karyawan' =>
                    strtoupper(
                        $this->valueOrDash(
                            $this->cell(
                                $row,
                                $columns['status_karyawan']
                            )
                        )
                    ),

                'status_tinggal' =>
                    $this->normalizeResidence(
                        $this->cell(
                            $row,
                            $columns['status_tinggal']
                        ),
                        $gedung,
                        $kamar
                    ),

                'gedung' =>
                    $this->valueOrDash(
                        $gedung
                    ),

                'kamar' =>
                    $this->valueOrDash(
                        $kamar
                    ),

                'gedung_kamar' =>
                    $this->combineRoom(
                        $gedung,
                        $kamar
                    ),

                'foto' =>
                    $this->valueOrDash(
                        $this->cell(
                            $row,
                            $columns['foto']
                        )
                    ),

                'source_row' =>
                    $rowIndex + 1,
            ];

            if (array_key_exists(
                $nrp,
                $employeesByNrp
            )) {
                $duplicates++;

                $employeesByNrp[$nrp] =
                    $this->mergeNonEmpty(
                        $employeesByNrp[$nrp],
                        $employee
                    );

                continue;
            }

            $employeesByNrp[$nrp] =
                $employee;
        }

        $employees = array_map(
            fn (array $employee): array =>
                $this->finalizeEmployee($employee),
            array_values($employeesByNrp)
        );

        usort(
            $employees,
            fn (array $left, array $right): int =>
                strnatcasecmp(
                    (string) $left['nama'],
                    (string) $right['nama']
                )
        );

        $missingFields = collect(
            $columns
        )
            ->filter(
                fn ($columnIndex): bool =>
                    (int) $columnIndex < 0
            )
            ->keys()
            ->reject(
                fn (string $field): bool =>
                    $field === 'gedung_kamar' &&
                    (int) (
                        $columns['gedung'] ?? -1
                    ) >= 0 &&
                    (int) (
                        $columns['kamar'] ?? -1
                    ) >= 0
            )
            ->values()
            ->all();

        return [
            'employees' => $employees,
            'source_rows' => $sourceRows,
            'duplicate_rows' => $duplicates,
            'header_row' => $headerRowIndex + 1,
            'missing_fields' => $missingFields,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Definisi alias kolom MASTER_DATABASE
    |--------------------------------------------------------------------------
    */

    private function columnAliases(): array
    {
        return [
            'nrp' => [
                'NRP',
                'NRP KARYAWAN',
                'NIK',
                'NIK KARYAWAN',
                'ID KARYAWAN',
            ],

            'nama' => [
                'NAMA',
                'NAMA KARYAWAN',
                'NAMA LENGKAP',
                'NAMA LENGKAP KARYAWAN',
            ],

            'jabatan' => [
                'JABATAN',
                'JABATAN KARYAWAN',
                'POSISI',
                'POSITION',
            ],

            'departemen' => [
                'DEPARTEMEN',
                'DEPARTMENT',
                'DIVISI',
                'BAGIAN',
                'SECTION',
            ],

            'perusahaan' => [
                'PERUSAHAAN',
                'COMPANY',
                'COMP',
            ],

            'site' => [
                'SITE',
                'LOKASI KERJA',
                'AREA KERJA',
                'WORK LOCATION',
            ],

            'no_hp' => [
                'NOMOR AKTIF CLEAR',
                'NOMOR WHATSAPP FINAL',
                'NOMOR WHATSAPP',
                'NO WHATSAPP',
                'WHATSAPP',
                'NOMOR WA',
                'NO WA',
                'NO HP AKTIF',
                'NOMOR HP',
                'NO HP',
                'NO HP KARYAWAN',
                'NOMOR HANDPHONE',
                'HANDPHONE',
                'KONTAK',
            ],

            'email' => [
                'EMAIL',
                'EMAIL AKTIF',
                'EMAIL KARYAWAN',
                'ALAMAT EMAIL',
            ],

            'tanggal_lahir' => [
                'TANGGAL LAHIR',
                'TGL LAHIR',
                'DATE OF BIRTH',
                'DOB',
            ],

            'status_karyawan' => [
                'STATUS KARYAWAN',
                'STATUS KEPEGAWAIAN',
                'EMPLOYMENT STATUS',
                'STATUS',
            ],

            'status_tinggal' => [
                'STATUS TINGGAL',
                'STATUS TEMPAT TINGGAL',
                'TEMPAT TINGGAL',
                'STATUS MESS',
                'MESS NON MESS',
                'DOMISILI',
            ],

            'gedung' => [
                'NOMOR GEDUNG',
                'NO GEDUNG',
                'GEDUNG',
                'GEDUNG MESS',
                'BLOK',
                'BLOK MESS',
            ],

            'kamar' => [
                'KAMAR',
                'NO KAMAR',
                'NOMOR KAMAR',
                'KAMAR MESS',
            ],

            'gedung_kamar' => [
                'GEDUNG KAMAR',
                'GEDUNG/KAMAR',
                'MESS GEDUNG KAMAR',
            ],

            'foto' => [
                'PASS FOTO MENGGUNAKAN SERAGAM KERJA',
                'PASFOTO MENGGUNAKAN SERAGAM KERJA',
                'PASSFOTO MENGGUNAKAN SERAGAM KERJA',
                'PASS FOTO SERAGAM',
                'PASFOTO SERAGAM',
                'FOTO KARYAWAN',
                'PAS FOTO',
                'FOTO',
            ],
        ];
    }

    private function fieldLabels(): array
    {
        return [
            'nrp' => 'NRP',
            'nama' => 'Nama Karyawan',
            'jabatan' => 'Jabatan/Posisi',
            'departemen' => 'Departemen',
            'perusahaan' => 'Perusahaan',
            'site' => 'Site',
            'no_hp' => 'Nomor HP',
            'email' => 'Email',
            'tanggal_lahir' => 'Tanggal Lahir',
            'status_karyawan' => 'Status Karyawan',
            'status_tinggal' => 'Status Tinggal',
            'gedung' => 'Gedung',
            'kamar' => 'Kamar',
            'gedung_kamar' => 'Gedung/Kamar Gabungan',
            'foto' => 'Pas Foto',
        ];
    }

    private function resolveColumns(
        array $headers
    ): array {
        $columns = [];

        foreach (
            $this->columnAliases()
            as $field => $aliases
        ) {
            $columns[$field] =
                $this->findColumn(
                    $headers,
                    $aliases
                );
        }

        return $columns;
    }

    private function sampleColumnValues(
        array $values,
        int $headerRowIndex,
        int $columnIndex,
        int $limit = 5
    ): array {
        $samples = [];

        $lastIndex = min(
            count($values) - 1,
            $headerRowIndex + 40
        );

        for (
            $rowIndex = $headerRowIndex + 1;
            $rowIndex <= $lastIndex;
            $rowIndex++
        ) {
            $row = is_array($values[$rowIndex])
                ? $values[$rowIndex]
                : [];

            $value = trim(
                (string) (
                    $row[$columnIndex] ??
                    ''
                )
            );

            if (
                $value === '' ||
                in_array(
                    $value,
                    $samples,
                    true
                )
            ) {
                continue;
            }

            $samples[] = $value;

            if (count($samples) >= $limit) {
                break;
            }
        }

        return $samples;
    }

    private function columnLetter(
        int $zeroBasedIndex
    ): string {
        $number = $zeroBasedIndex + 1;
        $letter = '';

        while ($number > 0) {
            $remainder =
                ($number - 1) % 26;

            $letter =
                chr(65 + $remainder) .
                $letter;

            $number =
                intdiv(
                    $number - 1,
                    26
                );
        }

        return $letter;
    }

    private function detectHeaderRow(
        array $values
    ): ?int {
        $limit = min(
            count($values),
            10
        );

        for ($index = 0; $index < $limit; $index++) {
            $row = is_array($values[$index])
                ? $values[$index]
                : [];

            $headers = array_map(
                fn ($value): string =>
                    $this->normalizeHeader(
                        (string) $value
                    ),
                $row
            );

            $nrpColumn = $this->findColumn(
                $headers,
                [
                    'NRP',
                    'NRP KARYAWAN',
                    'NIK',
                    'NIK KARYAWAN',
                    'ID KARYAWAN',
                ]
            );

            $nameColumn = $this->findColumn(
                $headers,
                [
                    'NAMA',
                    'NAMA KARYAWAN',
                    'NAMA LENGKAP',
                    'NAMA LENGKAP KARYAWAN',
                ]
            );

            if (
                $nrpColumn >= 0 &&
                $nameColumn >= 0
            ) {
                return $index;
            }
        }

        return null;
    }

    private function findColumn(
        array $headers,
        array $aliases
    ): int {
        $normalizedAliases = array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn (string $alias): string =>
                            $this->normalizeHeader($alias),
                        $aliases
                    ),
                    fn (string $alias): bool =>
                        $alias !== ''
                )
            )
        );

        /*
         * Prioritas utama: nama header sama persis.
         */
        foreach ($headers as $index => $header) {
            if (
                in_array(
                    $header,
                    $normalizedAliases,
                    true
                )
            ) {
                return (int) $index;
            }
        }

        /*
         * Fallback aman:
         * header boleh mengandung alias yang lebih spesifik.
         *
         * Arah sebaliknya sengaja DILARANG. Sebelumnya header
         * generik "NO" dianggap cocok dengan alias "NO KAMAR",
         * sehingga nomor urut baris terbaca sebagai nomor kamar.
         */
        foreach ($headers as $index => $header) {
            if (
                $this->isUnsafeGenericHeader(
                    $header
                )
            ) {
                continue;
            }

            foreach ($normalizedAliases as $alias) {
                if (
                    mb_strlen($alias) >= 4 &&
                    str_contains(
                        $header,
                        $alias
                    )
                ) {
                    return (int) $index;
                }
            }
        }

        return -1;
    }

    private function isUnsafeGenericHeader(
        string $header
    ): bool {
        return in_array(
            $header,
            [
                'NO',
                'NOMOR',
                'URUT',
                'NO URUT',
                'NOMOR URUT',
            ],
            true
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

    private function cell(
        array $row,
        int $index
    ): string {
        if ($index < 0) {
            return '';
        }

        return trim(
            (string) ($row[$index] ?? '')
        );
    }

    private function normalizeNrp(
        string $value
    ): string {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match(
            '/^\d+\.0+$/',
            $value
        )) {
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

    private function valueOrDash(
        string $value
    ): string {
        $value = trim($value);

        return $value !== ''
            ? $value
            : '-';
    }

    private function normalizeResidence(
        string $value,
        string $gedung,
        string $kamar
    ): string {
        $normalized = strtoupper(
            trim($value)
        );

        if (
            str_contains(
                $normalized,
                'NON MESS'
            ) ||
            str_contains(
                $normalized,
                'NON-MESS'
            )
        ) {
            return 'NON MESS';
        }

        if (
            $normalized === 'MESS' ||
            str_contains(
                $normalized,
                'TINGGAL DI MESS'
            )
        ) {
            return 'MESS';
        }

        if (
            trim($gedung) !== '' ||
            trim($kamar) !== ''
        ) {
            return 'MESS';
        }

        return $normalized !== ''
            ? $normalized
            : '-';
    }

    private function combineRoom(
        string $gedung,
        string $kamar
    ): string {
        $gedung = trim($gedung);
        $kamar = trim($kamar);

        if ($gedung === '' && $kamar === '') {
            return '-';
        }

        return
            ($gedung !== '' ? $gedung : '-') .
            ' / ' .
            ($kamar !== '' ? $kamar : '-');
    }

    private function sampleCombinedRoomValues(
        array $values,
        int $headerRowIndex,
        int $gedungIndex,
        int $kamarIndex,
        int $limit = 5
    ): array {
        $samples = [];

        $lastIndex = min(
            count($values) - 1,
            $headerRowIndex + 40
        );

        for (
            $rowIndex = $headerRowIndex + 1;
            $rowIndex <= $lastIndex;
            $rowIndex++
        ) {
            $row = is_array($values[$rowIndex])
                ? $values[$rowIndex]
                : [];

            $combined = $this->combineRoom(
                trim(
                    (string) (
                        $row[$gedungIndex] ??
                        ''
                    )
                ),
                trim(
                    (string) (
                        $row[$kamarIndex] ??
                        ''
                    )
                )
            );

            if (
                $combined === '-' ||
                in_array(
                    $combined,
                    $samples,
                    true
                )
            ) {
                continue;
            }

            $samples[] = $combined;

            if (count($samples) >= $limit) {
                break;
            }
        }

        return $samples;
    }

    private function splitRoom(
        string $value
    ): array {
        $parts = preg_split(
            '/\s*[\/|]\s*/',
            $value,
            2
        ) ?: [];

        return [
            trim((string) ($parts[0] ?? '')),
            trim((string) ($parts[1] ?? '')),
        ];
    }

    /**
     * Menambahkan field turunan untuk modal detail karyawan.
     */
    private function finalizeEmployee(
        array $employee
    ): array {
        $missingFields = [];

        foreach (
            self::DETAIL_REQUIRED_FIELDS
            as $field
        ) {
            $value = $employee[$field] ?? null;

            if ($this->isMissingEmployeeValue($value)) {
                $missingFields[] = $field;
            }
        }

        $requiredCount = count(
            self::DETAIL_REQUIRED_FIELDS
        );

        $filledCount = max(
            0,
            $requiredCount - count($missingFields)
        );

        $photo = $this->photoMetadata(
            $employee['foto'] ?? null
        );

        $employee['foto_url'] =
            $photo['source_url'];

        $employee['foto_open_url'] =
            $photo['open_url'];

        $employee['foto_preview_url'] =
            $photo['preview_url'];

        $employee['foto_preview_candidates'] =
            $photo['preview_candidates'];

        $employee['foto_drive_id'] =
            $photo['drive_file_id'];

        $employee['foto_source_type'] =
            $photo['source_type'];

        $employee['foto_available'] =
            $photo['source_url'] !== null;

        $whatsappNumber =
            $this->normalizeWhatsappNumber(
                (string) ($employee['no_hp'] ?? '')
            );

        $employee['whatsapp_number'] =
            $whatsappNumber !== ''
                ? $whatsappNumber
                : null;

        $employee['whatsapp_url'] =
            $whatsappNumber !== ''
                ? 'https://wa.me/' . $whatsappNumber
                : null;

        $email = trim(
            (string) ($employee['email'] ?? '')
        );

        $employee['email_url'] =
            $email !== '' &&
            $email !== '-' &&
            filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
                ? 'mailto:' . $email
                : null;

        $employee['missing_fields'] =
            $missingFields;

        $employee['missing_field_labels'] =
            array_map(
                fn (string $field): string =>
                    $this->fieldLabels()[$field] ??
                    strtoupper($field),
                $missingFields
            );

        $employee['is_complete'] =
            $missingFields === [];

        $employee['kelengkapan_status'] =
            $employee['is_complete']
                ? 'LENGKAP'
                : 'BELUM LENGKAP';

        $employee['completion_percentage'] =
            $requiredCount > 0
                ? (int) round(
                    ($filledCount / $requiredCount) *
                    100
                )
                : 100;

        return $employee;
    }

    /**
     * Nilai kosong dan tanda "-" dianggap belum lengkap.
     */
    private function isMissingEmployeeValue(
        mixed $value
    ): bool {
        if ($value === null) {
            return true;
        }

        $normalized = trim(
            (string) $value
        );

        return $normalized === '' ||
            $normalized === '-';
    }

    /**
     * Menyiapkan seluruh informasi pas foto untuk tampilan modal.
     *
     * Satu foto dapat mempunyai beberapa kandidat URL preview. Browser akan
     * mencobanya secara berurutan sehingga format tautan Google Drive yang
     * berbeda tetap dapat ditampilkan tanpa mengubah data sumber.
     */
    private function photoMetadata(
        mixed $value
    ): array {
        $source = $this->normalizePhotoSource(
            $value
        );

        $empty = [
            'source_url' => null,
            'open_url' => null,
            'preview_url' => null,
            'preview_candidates' => [],
            'drive_file_id' => null,
            'source_type' => 'missing',
        ];

        if ($source === null) {
            return $empty;
        }

        $fileId = $this->googleDriveFileId(
            $source
        );

        if ($fileId !== null) {
            $encodedId = rawurlencode($fileId);

            $previewCandidates = array_values(
                array_unique([
                    'https://drive.google.com/thumbnail?id=' .
                    $encodedId .
                    '&sz=w1200',

                    'https://lh3.googleusercontent.com/d/' .
                    $encodedId .
                    '=w1200',

                    'https://drive.google.com/uc?export=view&id=' .
                    $encodedId,
                ])
            );

            return [
                'source_url' =>
                    'https://drive.google.com/file/d/' .
                    $encodedId .
                    '/view',

                'open_url' =>
                    'https://drive.google.com/file/d/' .
                    $encodedId .
                    '/view',

                'preview_url' =>
                    $previewCandidates[0] ?? null,

                'preview_candidates' =>
                    $previewCandidates,

                'drive_file_id' => $fileId,
                'source_type' => 'google_drive',
            ];
        }

        if (!$this->isSafeHttpUrl($source)) {
            return $empty;
        }

        return [
            'source_url' => $source,
            'open_url' => $source,
            'preview_url' => $source,
            'preview_candidates' => [$source],
            'drive_file_id' => null,
            'source_type' => 'external_url',
        ];
    }

    /**
     * Kompatibilitas dengan field lama yang hanya memerlukan satu URL.
     */
    private function photoPreviewUrl(
        ?string $url
    ): ?string {
        return $this->photoMetadata(
            $url
        )['preview_url'];
    }

    /**
     * Membersihkan nilai foto dari spreadsheet.
     *
     * Format yang didukung antara lain URL biasa, formula HYPERLINK, URL yang
     * ter-encode, tautan tanpa skema, serta Google Drive file ID langsung.
     */
    private function normalizePhotoSource(
        mixed $value
    ): ?string {
        $text = html_entity_decode(
            trim((string) $value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        if ($text === '' || $text === '-') {
            return null;
        }

        if (
            preg_match(
                '/^=HYPERLINK\(\s*["\']([^"\']+)["\']/i',
                $text,
                $formulaMatch
            ) === 1
        ) {
            $text = trim(
                (string) ($formulaMatch[1] ?? '')
            );
        }

        if (
            !str_contains($text, '://') &&
            preg_match(
                '~https?%3A%2F%2F~i',
                $text
            ) === 1
        ) {
            $decoded = rawurldecode($text);

            if ($decoded !== '') {
                $text = $decoded;
            }
        }

        if (
            preg_match(
                '~https?://[^\s"\'<>]+~i',
                $text,
                $urlMatch
            ) === 1
        ) {
            $text = (string) ($urlMatch[0] ?? $text);
        }

        $text = trim(
            $text,
            " \t\n\r\0\x0B\"'<>"
        );

        if (str_starts_with($text, '//')) {
            $text = 'https:' . $text;
        }

        if (
            str_starts_with(
                strtolower($text),
                'www.'
            ) ||
            str_starts_with(
                strtolower($text),
                'drive.google.com/'
            ) ||
            str_starts_with(
                strtolower($text),
                'docs.google.com/'
            )
        ) {
            $text = 'https://' . $text;
        }

        if ($this->looksLikeGoogleDriveFileId($text)) {
            return $text;
        }

        return $this->isSafeHttpUrl($text)
            ? $text
            : null;
    }

    /**
     * Mendukung format:
     * - drive.google.com/open?id=...
     * - drive.google.com/file/d/.../view
     * - drive.google.com/uc?id=...
     * - drive.google.com/thumbnail?id=...
     * - drive.usercontent.google.com/...id=...
     * - lh3.googleusercontent.com/d/...
     * - file ID Google Drive langsung
     */
    private function googleDriveFileId(
        string $url
    ): ?string {
        $candidate = trim($url);

        if ($this->looksLikeGoogleDriveFileId($candidate)) {
            return $candidate;
        }

        $query = parse_url(
            $candidate,
            PHP_URL_QUERY
        );

        if (is_string($query)) {
            parse_str($query, $parameters);

            foreach (['id', 'file_id'] as $key) {
                $id = trim(
                    (string) ($parameters[$key] ?? '')
                );

                if ($this->looksLikeGoogleDriveFileId($id)) {
                    return $id;
                }
            }
        }

        $patterns = [
            '~drive\.google\.com/file/d/([^/?#]+)~i',
            '~drive\.google\.com/d/([^/?#]+)~i',
            '~googleusercontent\.com/d/([^/?#=]+)~i',
        ];

        foreach ($patterns as $pattern) {
            if (
                preg_match(
                    $pattern,
                    $candidate,
                    $matches
                ) !== 1
            ) {
                continue;
            }

            $id = trim(
                rawurldecode(
                    (string) ($matches[1] ?? '')
                )
            );

            if ($this->looksLikeGoogleDriveFileId($id)) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Google Drive file ID umumnya berisi huruf, angka, garis bawah, dan
     * tanda minus. Batas minimal mencegah teks biasa dianggap sebagai ID.
     */
    private function looksLikeGoogleDriveFileId(
        string $value
    ): bool {
        return preg_match(
            '/^[A-Za-z0-9_-]{20,}$/',
            trim($value)
        ) === 1;
    }

    /**
     * Hanya URL HTTP/HTTPS yang boleh diteruskan ke atribut href/src.
     */
    private function isSafeHttpUrl(
        string $url
    ): bool {
        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            return false;
        }

        $scheme = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_SCHEME
            )
        );

        return in_array(
            $scheme,
            ['http', 'https'],
            true
        );
    }

    /**
     * Menyiapkan nomor untuk tautan wa.me.
     */
    private function normalizeWhatsappNumber(
        string $value
    ): string {
        $digits = preg_replace(
            '/\D+/',
            '',
            $value
        ) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr(
                $digits,
                1
            );
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return $digits;
    }

    private function mergeNonEmpty(
        array $existing,
        array $incoming
    ): array {
        foreach ($incoming as $key => $value) {
            if (
                $key === 'source_row' ||
                (
                    is_string($value) &&
                    trim($value) !== '' &&
                    trim($value) !== '-'
                )
            ) {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }
}
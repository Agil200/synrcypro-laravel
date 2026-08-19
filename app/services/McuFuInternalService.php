<?php

namespace App\Services;

use App\Models\McuFuSimperOverride;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class McuFuInternalService
{
    private const SPREADSHEET_ID =
        '11uKwAXRyLCo0XjQ3_sWP7FwpsT0-Vg1Kp7oUGIP_yww';

    private const SHEET_ID = 1456862017;

    private const COLUMNS = 'A:N';

    /*
    |--------------------------------------------------------------------------
    | Resilient read cache
    |--------------------------------------------------------------------------
    */
    private const FRESH_CACHE_SECONDS = 45;

    private const LAST_SUCCESS_CACHE_SECONDS = 86400;

    private const REFRESH_LOCK_SECONDS = 60;

    public function __construct(
        private readonly GoogleSheetsService $googleSheets,
        private readonly EmployeeLifecycleService $employeeLifecycle
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
        $freshKey = $this->rowsFreshCacheKey();
        $lastSuccessKey = $this->rowsLastSuccessCacheKey();

        $fresh = Cache::get(
            $freshKey
        );

        if (is_array($fresh)) {
            return $this->filterActiveMasterEmployees(
                $fresh
            );
        }

        $lastSuccess = Cache::get(
            $lastSuccessKey
        );

        if (is_array($lastSuccess)) {
            $this->scheduleRowsRefresh();

            return $this->filterActiveMasterEmployees(
                $lastSuccess
            );
        }

        try {
            $rows = $this->loadRowsFromSheet();

            $this->storeRowsCache(
                $rows
            );

            return $this->filterActiveMasterEmployees(
                $rows
            );
        } catch (Throwable $exception) {
            $fallback = Cache::get(
                $lastSuccessKey
            );

            if (is_array($fallback)) {
                report($exception);

                return $this->filterActiveMasterEmployees(
                    $fallback
                );
            }

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Filter karyawan aktif berdasarkan MASTER_DATABASE
    |--------------------------------------------------------------------------
    |
    | MCU & FU Internal = Produksi site Bukit Asam.
    | Status RESIGN / MUTASI / TERMINATED di-hide.
    | Data history dan row Google MCU&FU TIDAK dihapus.
    */

    private function filterActiveMasterEmployees(
        array $rows
    ): array {
        return collect($rows)
            ->filter(
                fn (array $row): bool =>
                    $this->employeeLifecycle
                        ->activeForMcuFu(
                            (string) (
                                $row['nrp'] ?? ''
                            )
                        )
            )
            ->values()
            ->all();
    }

    public function invalidateReadCache(): void
    {
        Cache::forget(
            $this->rowsFreshCacheKey()
        );

        /*
         * Last-success tetap dipertahankan untuk fallback Google timeout.
         * Karena filter master dilakukan setiap rows(), perubahan status/site
         * tetap langsung memengaruhi visibility walau snapshot MCU berasal
         * dari cache last-success.
         */
    }

    private function loadRowsFromSheet(): array
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

        /*
        |--------------------------------------------------------------------------
        | Manual fallback EXP SIM/SIB DLT
        |--------------------------------------------------------------------------
        |
        | Kolom E MCU&FU tetap dibaca sebagai sumber utama.
        | Manual override hanya dipakai jika nilai dari Spreadsheet kosong /
        | BELUM ADA DATA. Jadi formula kolom E tidak pernah ditimpa website.
        |
        */
        $nrps = collect($result)
            ->pluck('nrp')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $overrides = $nrps === []
            ? collect()
            : McuFuSimperOverride::query()
                ->whereIn('nrp', $nrps)
                ->get()
                ->keyBy(
                    fn (McuFuSimperOverride $item): string =>
                        trim((string) $item->nrp)
                );

        foreach ($result as &$item) {
            $sheetValue = trim(
                (string) ($item['expired_sim_dlt'] ?? '')
            );

            $override = $overrides->get(
                trim((string) ($item['nrp'] ?? ''))
            );

            $manualValue = $override?->expired_sim_dlt
                ? $override->expired_sim_dlt->format('d-M-y')
                : '';

            $item['expired_sim_dlt_sheet'] = $sheetValue;
            $item['expired_sim_dlt_manual'] = $manualValue;

            if ($this->isMissingSimperValue($sheetValue)) {
                $item['expired_sim_dlt'] = $manualValue;
                $item['expired_sim_dlt_source'] =
                    $manualValue !== ''
                        ? 'MANUAL'
                        : 'BELUM ADA DATA';
            } else {
                $item['expired_sim_dlt'] = $sheetValue;
                $item['expired_sim_dlt_source'] = 'SPREADSHEET';
            }
        }

        unset($item);

        return $result;
    }

    private function rowsFreshCacheKey(): string
    {
        return implode(
            ':',
            [
                'mcu-fu-internal',
                'rows',
                sha1(self::SPREADSHEET_ID),
                self::SHEET_ID,
                strtolower(self::COLUMNS),
                'v1-final',
            ]
        );
    }

    private function rowsLastSuccessCacheKey(): string
    {
        return $this->rowsFreshCacheKey()
            .':last-success';
    }

    private function rowsRefreshLockKey(): string
    {
        return $this->rowsFreshCacheKey()
            .':refresh-lock';
    }

    private function rowsSyncMetaKey(): string
    {
        return $this->rowsFreshCacheKey()
            .':sync-meta';
    }

    private function storeRowsCache(
        array $rows
    ): void {
        Cache::put(
            $this->rowsFreshCacheKey(),
            $rows,
            now()->addSeconds(
                self::FRESH_CACHE_SECONDS
            )
        );

        Cache::put(
            $this->rowsLastSuccessCacheKey(),
            $rows,
            now()->addSeconds(
                self::LAST_SUCCESS_CACHE_SECONDS
            )
        );

        Cache::put(
            $this->rowsSyncMetaKey(),
            [
                'last_success_at' => now()->toIso8601String(),
                'total' => count($rows),
            ],
            now()->addSeconds(
                self::LAST_SUCCESS_CACHE_SECONDS
            )
        );
    }

    private function scheduleRowsRefresh(): void
    {
        $lockKey = $this->rowsRefreshLockKey();

        $acquired = Cache::add(
            $lockKey,
            now()->timestamp,
            now()->addSeconds(
                self::REFRESH_LOCK_SECONDS
            )
        );

        if (!$acquired) {
            return;
        }

        app()->terminating(
            function () use ($lockKey): void {
                try {
                    $rows = $this->loadRowsFromSheet();

                    $this->storeRowsCache(
                        $rows
                    );
                } catch (Throwable $exception) {
                    report($exception);
                } finally {
                    Cache::forget(
                        $lockKey
                    );
                }
            }
        );
    }

    private function patchCachedRow(
        array $row
    ): void {
        $sheetRow = (int) (
            $row['sheet_row'] ?? 0
        );

        if ($sheetRow <= 0) {
            return;
        }

        foreach (
            [
                $this->rowsFreshCacheKey(),
                $this->rowsLastSuccessCacheKey(),
            ] as $key
        ) {
            $cached = Cache::get(
                $key
            );

            if (!is_array($cached)) {
                continue;
            }

            $updated = false;

            foreach ($cached as $index => $cachedRow) {
                if (
                    (int) ($cachedRow['sheet_row'] ?? 0)
                    !== $sheetRow
                ) {
                    continue;
                }

                $cached[$index] = array_merge(
                    $cachedRow,
                    $row
                );

                $updated = true;
                break;
            }

            if (!$updated) {
                continue;
            }

            $ttl = $key === $this->rowsFreshCacheKey()
                ? self::FRESH_CACHE_SECONDS
                : self::LAST_SUCCESS_CACHE_SECONDS;

            Cache::put(
                $key,
                $cached,
                now()->addSeconds($ttl)
            );
        }
    }

    public function syncMeta(): array
    {
        $meta = Cache::get(
            $this->rowsSyncMetaKey(),
            []
        );

        return is_array($meta)
            ? $meta
            : [];
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

        $this->patchCachedRow(
            $after
        );

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

        $this->patchCachedRow(
            $after
        );

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Unified Update MCU & Follow Up
    |--------------------------------------------------------------------------
    |
    | Spreadsheet MCU&FU:
    | D = EXP MCU
    | F = JADWAL MCU
    | H = HASIL MCU
    | I = FOLLOW UP 1
    | J = FOLLOW UP 2
    | K = FOLLOW UP 3
    | L = JADWAL FU
    | M = STATUS FU
    |
    | E = EXPIRED SIM DLT TIDAK DITULIS.
    | Jika E kosong, fallback manual disimpan di database Laravel.
    */

    public function updateUnified(
        int $sheetRow,
        array $data,
        ?string $userName = null,
        ?string $userEmail = null
    ): array {
        $before = $this->findRow(
            $sheetRow
        );

        $requested = [
            'exp_mcu' => $this->sheetDate(
                $data['exp_mcu'] ?? null
            ),

            'jadwal_mcu' => $this->sheetDate(
                $data['jadwal_mcu'] ?? null
            ),

            'hasil_mcu' => trim(
                (string) ($data['hasil_mcu'] ?? '')
            ),

            'follow_up_1' => trim(
                (string) ($data['follow_up_1'] ?? '')
            ),

            'follow_up_2' => trim(
                (string) ($data['follow_up_2'] ?? '')
            ),

            'follow_up_3' => trim(
                (string) ($data['follow_up_3'] ?? '')
            ),

            'jadwal_fu' => $this->sheetDate(
                $data['jadwal_fu'] ?? null
            ),

            'status_fu' => trim(
                (string) ($data['status_fu'] ?? '')
            ),
        ];

        $labels = [
            'exp_mcu' => 'EXP MCU',
            'jadwal_mcu' => 'JADWAL MCU',
            'hasil_mcu' => 'HASIL MCU',
            'follow_up_1' => 'FOLLOW UP 1',
            'follow_up_2' => 'FOLLOW UP 2',
            'follow_up_3' => 'FOLLOW UP 3',
            'jadwal_fu' => 'JADWAL FU',
            'status_fu' => 'STATUS FU',
            'expired_sim_dlt' => 'EXP SIM/SIB DLT',
        ];

        /*
        |--------------------------------------------------------------------------
        | SAVE ONLY CHANGED FIELDS
        |--------------------------------------------------------------------------
        |
        | Kolom yang tidak berubah TIDAK dikirim ke Google Sheets.
        | Ini menjaga formula/format/data existing agar tidak disentuh sia-sia.
        |
        */
        $changes = [];

        $dateFields = [
            'exp_mcu',
            'jadwal_mcu',
            'jadwal_fu',
        ];

        foreach ($requested as $field => $newValue) {
            $oldValue = trim(
                (string) ($before[$field] ?? '')
            );

            /*
             * Tanggal dibandingkan secara semantik, bukan string.
             * Contoh:
             * 3-Oct-26 dan 03-Oct-26 = tanggal yang sama,
             * jadi TIDAK dihitung sebagai perubahan.
             */
            if (in_array($field, $dateFields, true)) {
                $oldComparable = $this->parseDate($oldValue)
                    ?->format('Y-m-d') ?? '';

                $newComparable = $this->parseDate($newValue)
                    ?->format('Y-m-d') ?? '';

                $isChanged =
                    $oldComparable !== $newComparable;
            } else {
                $isChanged =
                    $oldValue !== $newValue;
            }

            if ($isChanged) {
                $changes[$field] = [
                    'label' => $labels[$field] ?? strtoupper($field),
                    'before' => $oldValue,
                    'after' => $newValue,
                ];
            }
        }

        /*
         * Mapping write tetap terkunci.
         */
        $cellMap = [
            'exp_mcu' => 'D',
            'jadwal_mcu' => 'F',
            'hasil_mcu' => 'H',
        ];

        foreach ($cellMap as $field => $column) {
            if (!array_key_exists($field, $changes)) {
                continue;
            }

            $this->googleSheets->updateValues(
                self::SPREADSHEET_ID,
                "'MCU&FU'!{$column}{$sheetRow}",
                [$requested[$field]]
            );
        }

        /*
         * Follow Up I:M.
         * Jika ada perubahan di salah satu field FU, tulis range I:M sekali.
         * Nilai lain di range memakai nilai existing agar tetap konsisten.
         */
        $fuFields = [
            'follow_up_1',
            'follow_up_2',
            'follow_up_3',
            'jadwal_fu',
            'status_fu',
        ];

        $hasFuChange = collect($fuFields)
            ->contains(
                fn (string $field): bool =>
                    array_key_exists(
                        $field,
                        $changes
                    )
            );

        if ($hasFuChange) {
            $fuValues = array_map(
                fn (string $field): string =>
                    $requested[$field],
                $fuFields
            );

            $this->googleSheets->updateValues(
                self::SPREADSHEET_ID,
                "'MCU&FU'!I{$sheetRow}:M{$sheetRow}",
                $fuValues
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Manual EXP SIM/SIB DLT
        |--------------------------------------------------------------------------
        |
        | Hanya jika sumber Spreadsheet masih kosong / BELUM ADA DATA.
        | Kolom E MCU&FU tetap tidak pernah ditulis website.
        */
        $manualSimper = trim(
            (string) ($data['manual_expired_sim_dlt'] ?? '')
        );

        $sheetSimper = trim(
            (string) ($before['expired_sim_dlt_sheet'] ?? '')
        );

        if (
            $manualSimper !== '' &&
            $this->isMissingSimperValue($sheetSimper)
        ) {
            $manualDate = Carbon::parse(
                $manualSimper
            )->format('Y-m-d');

            $newDisplay = Carbon::parse(
                $manualSimper
            )->format('d-M-y');

            $oldDisplay = trim(
                (string) ($before['expired_sim_dlt'] ?? '')
            );

            if ($oldDisplay !== $newDisplay) {
                McuFuSimperOverride::query()
                    ->updateOrCreate(
                        [
                            'nrp' => trim(
                                (string) ($before['nrp'] ?? '')
                            ),
                        ],
                        [
                            'nama' => trim(
                                (string) ($before['nama'] ?? '')
                            ),
                            'expired_sim_dlt' => $manualDate,
                            'note' => trim(
                                (string) ($data['manual_simper_note'] ?? '')
                            ) ?: null,
                            'updated_by_name' => $userName,
                            'updated_by_email' => $userEmail,
                        ]
                    );

                $changes['expired_sim_dlt'] = [
                    'label' => $labels['expired_sim_dlt'],
                    'before' => $oldDisplay,
                    'after' => $newDisplay,
                ];
            }
        }

        $after = $before;

        foreach ($requested as $field => $value) {
            $after[$field] = $value;
        }

        if (
            isset($changes['expired_sim_dlt']) &&
            $manualSimper !== ''
        ) {
            $after['expired_sim_dlt'] =
                $changes['expired_sim_dlt']['after'];

            $after['expired_sim_dlt_manual'] =
                $after['expired_sim_dlt'];

            $after['expired_sim_dlt_source'] =
                'MANUAL';
        }

        $this->patchCachedRow(
            $after
        );

        return [
            'before' => $before,
            'after' => $after,
            'changes' => $changes,
            'change_count' => count($changes),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filter halaman Unified Update
    |--------------------------------------------------------------------------
    */

    public function updatePageData(
        array $rows,
        array $filters = []
    ): array {
        $dateType = trim(
            (string) ($filters['date_type'] ?? 'jadwal_mcu')
        );

        $allowedDateTypes = [
            'jadwal_mcu',
            'exp_mcu',
            'follow_up',
            'simper',
        ];

        if (!in_array($dateType, $allowedDateTypes, true)) {
            $dateType = 'jadwal_mcu';
        }

        $year = isset($filters['year'])
            && trim((string) $filters['year']) !== ''
                ? (int) $filters['year']
                : null;

        $month = isset($filters['month'])
            && trim((string) $filters['month']) !== ''
                ? (int) $filters['month']
                : null;

        $search = mb_strtoupper(
            trim(
                (string) ($filters['search'] ?? '')
            )
        );

        $simperExp = mb_strtoupper(
            trim(
                (string) ($filters['simper_exp'] ?? '')
            )
        );

        $hasilMcu = mb_strtoupper(
            trim(
                (string) ($filters['hasil_mcu'] ?? '')
            )
        );

        $statusMcu = mb_strtoupper(
            trim(
                (string) ($filters['status_mcu'] ?? '')
            )
        );

        $statusFu = mb_strtoupper(
            trim(
                (string) ($filters['status_fu'] ?? '')
            )
        );

        $jabatan = mb_strtoupper(
            trim(
                (string) ($filters['jabatan'] ?? '')
            )
        );

        $fuStage = (int) (
            $filters['fu_stage'] ?? 0
        );

        $followUpValue = mb_strtoupper(
            trim(
                (string) ($filters['follow_up_value'] ?? '')
            )
        );

        $dateField = match ($dateType) {
            'exp_mcu' => 'exp_mcu',
            'follow_up' => 'jadwal_fu',
            'simper' => 'expired_sim_dlt',
            default => 'jadwal_mcu',
        };

        $filtered = collect($rows)
            ->filter(
                function (array $row) use (
                    $dateField,
                    $year,
                    $month,
                    $search,
                    $simperExp,
                    $hasilMcu,
                    $statusMcu,
                    $statusFu,
                    $jabatan,
                    $fuStage,
                    $followUpValue
                ): bool {
                    if (
                        $year !== null ||
                        $month !== null
                    ) {
                        $parts = $this->dateParts(
                            $row[$dateField] ?? ''
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
                    }

                    /*
                     * Filter masa berlaku SIM/SIB DLT.
                     *
                     * Bucket dibuat tidak overlap:
                     * H-7  = 1 s.d. 7 hari
                     * H-14 = 8 s.d. 14 hari
                     * H-30 = 15 s.d. 30 hari
                     * H-40 = 31 s.d. 40 hari
                     * EXPIRED = hari ini / sudah lewat
                     */
                    if ($simperExp !== '') {
                        $simperMeta = $this->expiryMeta(
                            $row['expired_sim_dlt'] ?? ''
                        );

                        $days = $simperMeta['days'];

                        if ($days === null) {
                            return false;
                        }

                        $matchesSimper = match ($simperExp) {
                            'H-7' =>
                                $days >= 1 &&
                                $days <= 7,

                            'H-14' =>
                                $days >= 8 &&
                                $days <= 14,

                            'H-30' =>
                                $days >= 15 &&
                                $days <= 30,

                            'H-40' =>
                                $days >= 31 &&
                                $days <= 40,

                            'EXPIRED' =>
                                $days <= 0,

                            default => true,
                        };

                        if (!$matchesSimper) {
                            return false;
                        }
                    }

                    if ($search !== '') {
                        $identity = mb_strtoupper(
                            implode(
                                ' ',
                                [
                                    (string) ($row['nrp'] ?? ''),
                                    (string) ($row['nama'] ?? ''),
                                ]
                            )
                        );

                        if (
                            !str_contains(
                                $identity,
                                $search
                            )
                        ) {
                            return false;
                        }
                    }

                    if (
                        $hasilMcu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['hasil_mcu'] ?? '')
                            )
                        ) !== $hasilMcu
                    ) {
                        return false;
                    }

                    if (
                        $statusMcu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['status_mcu'] ?? '')
                            )
                        ) !== $statusMcu
                    ) {
                        return false;
                    }

                    if (
                        $statusFu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['status_fu'] ?? '')
                            )
                        ) !== $statusFu
                    ) {
                        return false;
                    }

                    if (
                        $jabatan !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['jabatan'] ?? '')
                            )
                        ) !== $jabatan
                    ) {
                        return false;
                    }

                    if (
                        in_array(
                            $fuStage,
                            [1, 2, 3],
                            true
                        )
                    ) {
                        $field =
                            'follow_up_' .
                            $fuStage;

                        $value = mb_strtoupper(
                            trim(
                                (string) ($row[$field] ?? '')
                            )
                        );

                        if ($value === '') {
                            return false;
                        }

                        if (
                            $followUpValue !== '' &&
                            $value !== $followUpValue
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            )
            ->values()
            ->all();

        $years = collect($rows)
            ->map(function (array $row) use ($dateField): ?int {
                $parts = $this->dateParts(
                    $row[$dateField] ?? ''
                );

                return $parts['year'] ?? null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (
            !in_array(
                (int) now()->year,
                $years,
                true
            )
        ) {
            $years[] = (int) now()->year;
            rsort($years, SORT_NUMERIC);
        }

        return [
            'rows' => $filtered,
            'years' => $years,
            'filters' => [
                'date_type' => $dateType,
                'year' => $year,
                'month' => $month,
                'search' => $filters['search'] ?? null,
                'simper_exp' => $filters['simper_exp'] ?? null,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prioritas & Reminder
    |--------------------------------------------------------------------------
    |
    | Worklist operasional:
    | - MCU akan expired / expired
    | - SIM/SIB DLT akan expired / expired
    | - Follow Up belum completed / overdue
    |
    | Bucket masa berlaku dibuat tidak overlap:
    | H-7  = 1-7 hari
    | H-14 = 8-14 hari
    | H-30 = 15-30 hari
    | H-40 = 31-40 hari
    */

    public function priorityData(
        array $filters = []
    ): array {
        $rows = $this->rows();

        $type = strtolower(
            trim(
                (string) ($filters['type'] ?? 'all')
            )
        );

        if (
            !in_array(
                $type,
                ['all', 'mcu', 'simper', 'follow_up'],
                true
            )
        ) {
            $type = 'all';
        }

        $bucket = strtoupper(
            trim(
                (string) ($filters['bucket'] ?? '')
            )
        );

        $search = mb_strtoupper(
            trim(
                (string) ($filters['search'] ?? '')
            )
        );

        $tasks = [];

        foreach ($rows as $row) {
            $identity = mb_strtoupper(
                implode(
                    ' ',
                    [
                        (string) ($row['nrp'] ?? ''),
                        (string) ($row['nama'] ?? ''),
                        (string) ($row['jabatan'] ?? ''),
                    ]
                )
            );

            if (
                $search !== '' &&
                !str_contains($identity, $search)
            ) {
                continue;
            }

            /*
             * MCU — hanya masuk worklist jika <= H-40
             * atau sudah expired.
             */
            if (
                $type === 'all' ||
                $type === 'mcu'
            ) {
                $meta = $this->expiryMeta(
                    $row['exp_mcu'] ?? ''
                );

                $taskBucket = $this->priorityExpiryBucket(
                    $meta['days']
                );

                if ($taskBucket !== null) {
                    $tasks[] = $this->priorityTaskRow(
                        $row,
                        'MCU',
                        $taskBucket,
                        $meta,
                        'EXP MCU',
                        'exp_mcu'
                    );
                }
            }

            /*
             * SIM/SIB DLT.
             */
            if (
                $type === 'all' ||
                $type === 'simper'
            ) {
                $meta = $this->expiryMeta(
                    $row['expired_sim_dlt'] ?? ''
                );

                $taskBucket = $this->priorityExpiryBucket(
                    $meta['days']
                );

                if ($taskBucket !== null) {
                    $task = $this->priorityTaskRow(
                        $row,
                        'SIM/SIB DLT',
                        $taskBucket,
                        $meta,
                        'EXP SIM/SIB DLT',
                        'simper'
                    );

                    $task['source'] =
                        $row['expired_sim_dlt_source']
                        ?? null;

                    $tasks[] = $task;
                }
            }

            /*
             * Follow Up.
             * Masuk worklist jika hasil MCU perlu Follow Up
             * atau FU1/FU2/FU3 sudah mulai terisi,
             * selama STATUS FU belum COMPLETED.
             */
            if (
                $type === 'all' ||
                $type === 'follow_up'
            ) {
                $hasilMcu = mb_strtoupper(
                    trim(
                        (string) ($row['hasil_mcu'] ?? '')
                    )
                );

                $statusFu = mb_strtoupper(
                    trim(
                        (string) ($row['status_fu'] ?? '')
                    )
                );

                $hasFu = (
                    str_contains(
                        $hasilMcu,
                        'FOLLOW UP'
                    )
                    ||
                    $this->filled(
                        $row['follow_up_1'] ?? ''
                    )
                    ||
                    $this->filled(
                        $row['follow_up_2'] ?? ''
                    )
                    ||
                    $this->filled(
                        $row['follow_up_3'] ?? ''
                    )
                );

                $completed = str_contains(
                    $statusFu,
                    'COMPLETED'
                );

                if ($hasFu && !$completed) {
                    $jadwal = $this->parseDate(
                        $row['jadwal_fu'] ?? ''
                    );

                    if ($jadwal !== null) {
                        $days = (int) now()
                            ->startOfDay()
                            ->diffInDays(
                                $jadwal->copy()->startOfDay(),
                                false
                            );

                        $taskBucket =
                            $days < 0
                                ? 'OVERDUE'
                                : 'PENDING';

                        $deadline =
                            $jadwal->format('d-M-Y');

                        $daysLabel =
                            $days < 0
                                ? abs($days).' HARI TERLAMBAT'
                                : (
                                    $days === 0
                                        ? 'HARI INI'
                                        : $days.' HARI LAGI'
                                );
                    } else {
                        $taskBucket = 'PENDING';
                        $deadline = null;
                        $days = null;
                        $daysLabel = 'BELUM DIJADWALKAN';
                    }

                    $tasks[] = [
                        'sheet_row' => $row['sheet_row'] ?? null,
                        'nrp' => $row['nrp'] ?? '',
                        'nama' => $row['nama'] ?? '',
                        'jabatan' => $row['jabatan'] ?? '',
                        'type' => 'FOLLOW UP',
                        'bucket' => $taskBucket,
                        'deadline_label' => $deadline,
                        'days' => $days,
                        'days_label' => $daysLabel,
                        'detail' => collect(
                            [
                                $row['follow_up_1'] ?? '',
                                $row['follow_up_2'] ?? '',
                                $row['follow_up_3'] ?? '',
                            ]
                        )
                            ->filter(
                                fn ($value) =>
                                    $this->filled($value)
                            )
                            ->implode(' / '),
                        'status_fu' => $row['status_fu'] ?? '',
                        'source' => null,
                        'update_date_type' => 'follow_up',
                    ];
                }
            }
        }

        if ($bucket !== '') {
            $tasks = collect($tasks)
                ->filter(
                    fn (array $task): bool =>
                        strtoupper(
                            (string) ($task['bucket'] ?? '')
                        ) === $bucket
                )
                ->values()
                ->all();
        }

        /*
         * Sort paling urgent:
         * EXPIRED -> OVERDUE -> H-7 -> H-14 -> H-30 -> H-40 -> PENDING
         */
        $weight = [
            'EXPIRED' => 1,
            'OVERDUE' => 2,
            'H-7' => 3,
            'H-14' => 4,
            'H-30' => 5,
            'H-40' => 6,
            'PENDING' => 7,
        ];

        usort(
            $tasks,
            function (
                array $a,
                array $b
            ) use ($weight): int {
                $aw = $weight[
                    strtoupper(
                        (string) ($a['bucket'] ?? '')
                    )
                ] ?? 99;

                $bw = $weight[
                    strtoupper(
                        (string) ($b['bucket'] ?? '')
                    )
                ] ?? 99;

                if ($aw !== $bw) {
                    return $aw <=> $bw;
                }

                $ad = $a['days'];
                $bd = $b['days'];

                if (
                    is_numeric($ad) &&
                    is_numeric($bd) &&
                    (int) $ad !== (int) $bd
                ) {
                    return (int) $ad <=> (int) $bd;
                }

                return strcmp(
                    mb_strtoupper(
                        (string) ($a['nama'] ?? '')
                    ),
                    mb_strtoupper(
                        (string) ($b['nama'] ?? '')
                    )
                );
            }
        );

        $summary = [
            'total' => count($tasks),
            'urgent' => collect($tasks)
                ->whereIn(
                    'bucket',
                    ['EXPIRED', 'OVERDUE', 'H-7']
                )
                ->count(),
            'expired' => collect($tasks)
                ->where('bucket', 'EXPIRED')
                ->count(),
            'overdue_fu' => collect($tasks)
                ->where('bucket', 'OVERDUE')
                ->count(),
            'h7' => collect($tasks)
                ->where('bucket', 'H-7')
                ->count(),
            'h14' => collect($tasks)
                ->where('bucket', 'H-14')
                ->count(),
            'h30' => collect($tasks)
                ->where('bucket', 'H-30')
                ->count(),
            'h40' => collect($tasks)
                ->where('bucket', 'H-40')
                ->count(),
            'pending_fu' => collect($tasks)
                ->where('bucket', 'PENDING')
                ->count(),
        ];

        return [
            'tasks' => $tasks,
            'summary' => $summary,
            'filters' => [
                'type' => $type,
                'bucket' => $bucket,
                'search' => $filters['search'] ?? null,
            ],
        ];
    }

    private function priorityExpiryBucket(
        ?int $days
    ): ?string {
        if ($days === null) {
            return null;
        }

        if ($days <= 0) {
            return 'EXPIRED';
        }

        if ($days <= 7) {
            return 'H-7';
        }

        if ($days <= 14) {
            return 'H-14';
        }

        if ($days <= 30) {
            return 'H-30';
        }

        if ($days <= 40) {
            return 'H-40';
        }

        return null;
    }

    private function priorityTaskRow(
        array $row,
        string $type,
        string $bucket,
        array $meta,
        string $detail,
        string $dateType
    ): array {
        return [
            'sheet_row' => $row['sheet_row'] ?? null,
            'nrp' => $row['nrp'] ?? '',
            'nama' => $row['nama'] ?? '',
            'jabatan' => $row['jabatan'] ?? '',
            'type' => $type,
            'bucket' => $bucket,
            'deadline_label' => $meta['date'] ?? null,
            'days' => $meta['days'] ?? null,
            'days_label' => $meta['label'] ?? '',
            'detail' => $detail,
            'status_fu' => $row['status_fu'] ?? '',
            'source' => null,
            'update_date_type' => $dateType,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Expiry indicator
    |--------------------------------------------------------------------------
    |
    | >30 hari = AMAN
    | 1-30 hari = AKAN EXPIRED
    | <=0 hari = EXPIRED / HARI INI
    */

    public function expiryMeta(
        mixed $value
    ): array {
        $date = $this->parseDate(
            $value
        );

        if ($date === null) {
            return [
                'date' => null,
                'days' => null,
                'status' => 'NO_DATA',
                'label' => 'BELUM ADA DATA',
            ];
        }

        $days = (int) now()
            ->startOfDay()
            ->diffInDays(
                $date->copy()->startOfDay(),
                false
            );

        if ($days <= 0) {
            return [
                'date' => $date->format('d-M-Y'),
                'days' => $days,
                'status' => 'EXPIRED',
                'label' => $days === 0
                    ? 'EXPIRED HARI INI'
                    : 'EXPIRED',
            ];
        }

        if ($days <= 30) {
            return [
                'date' => $date->format('d-M-Y'),
                'days' => $days,
                'status' => 'WARNING',
                'label' => $days.' HARI LAGI',
            ];
        }

        return [
            'date' => $date->format('d-M-Y'),
            'days' => $days,
            'status' => 'SAFE',
            'label' => $days.' HARI LAGI',
        ];
    }

    public function isMissingSimper(
        mixed $value
    ): bool {
        return $this->isMissingSimperValue(
            (string) $value
        );
    }

    private function isMissingSimperValue(
        string $value
    ): bool {
        $value = mb_strtoupper(
            trim($value)
        );

        if ($value === '') {
            return true;
        }

        return in_array(
            $value,
            [
                'BELUM ADA DATA',
                'BELUM ADA',
                'N/A',
                'NA',
                '#N/A',
                '#REF!',
                '-',
            ],
            true
        );
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
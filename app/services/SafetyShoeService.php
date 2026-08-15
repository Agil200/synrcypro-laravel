<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SafetyShoeService
{
    private const CACHE_KEY =
        'safety-shoes.monitoring.fresh.v1';

    private const BACKUP_KEY =
        'safety-shoes.monitoring.backup.v1';

    private const META_KEY =
        'safety-shoes.monitoring.meta.v1';

    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /**
     * Snapshot seluruh riwayat Sepatu Safety berdasarkan NRP.
     */
    public function snapshot(): array
    {
        $fresh = Cache::get(self::CACHE_KEY);

        if (is_array($fresh)) {
            return [
                'records' => $fresh,
                'meta' => array_merge(
                    $this->storedMeta(),
                    ['is_stale' => false]
                ),
            ];
        }

        try {
            return $this->refresh();
        } catch (Throwable $exception) {
            $backup = Cache::get(self::BACKUP_KEY);

            if (is_array($backup)) {
                report($exception);

                return [
                    'records' => $backup,
                    'meta' => array_merge(
                        $this->storedMeta(),
                        [
                            'status' => 'stale',
                            'is_stale' => true,
                            'error' => $exception->getMessage(),
                        ]
                    ),
                ];
            }

            throw $exception;
        }
    }

    /**
     * Status satu karyawan. NRP yang belum memiliki tanggal pengambilan
     * dianggap dapat mengajukan Sepatu Safety.
     */
    public function eligibilityFor(string $nrp): array
    {
        $normalizedNrp = $this->normalizeNrp($nrp);
        $snapshot = $this->snapshot();
        $record = $snapshot['records'][$normalizedNrp] ?? null;
        $isStale = (bool) data_get(
            $snapshot,
            'meta.is_stale',
            false
        );

        if (! is_array($record)) {
            return $this->emptyEligibility(
                $normalizedNrp,
                false,
                $isStale
            );
        }

        $lastTaken = $record['last_taken_date'] ?? null;

        if (! $lastTaken) {
            return array_merge(
                $this->emptyEligibility(
                    $normalizedNrp,
                    true,
                    $isStale
                ),
                [
                    'nama' => (string) ($record['nama'] ?? ''),
                ]
            );
        }

        return array_merge(
            $this->eligibilityFromDate(
                (string) $lastTaken,
                (string) ($record['nama'] ?? '')
            ),
            [
                'nrp' => $normalizedNrp,
                'found' => true,
                'is_stale' => $isStale,
            ]
        );
    }

    /**
     * Map status seluruh NRP untuk notifikasi langsung pada form APD.
     */
    public function eligibilityMap(): array
    {
        $snapshot = $this->snapshot();
        $result = [];

        foreach ($snapshot['records'] ?? [] as $nrp => $record) {
            if (! is_array($record)) {
                continue;
            }

            $result[$nrp] = $this->eligibilityForRecord(
                (string) $nrp,
                $record,
                (bool) data_get(
                    $snapshot,
                    'meta.is_stale',
                    false
                )
            );
        }

        return $result;
    }

    /**
     * Menghitung tanggal dapat mengajukan kembali tepat satu tahun setelah
     * tanggal pengambilan terakhir.
     */
    public function eligibilityFromDate(
        CarbonInterface|string $lastTaken,
        string $name = ''
    ): array {
        $lastTakenDate = $lastTaken instanceof CarbonInterface
            ? Carbon::instance($lastTaken)->startOfDay()
            : Carbon::parse(
                $lastTaken,
                'Asia/Jakarta'
            )->startOfDay();

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $eligibleAt = $lastTakenDate
            ->copy()
            ->addYearNoOverflow();

        $daysRemaining = $today->lt($eligibleAt)
            ? (int) ceil($today->diffInDays($eligibleAt))
            : 0;

        return [
            'available' => true,
            'found' => true,
            'has_history' => true,
            'eligible' => $daysRemaining === 0,
            'nama' => trim($name),
            'last_taken_date' => $lastTakenDate->format('Y-m-d'),
            'tanggal' => $lastTakenDate->format('d/m/Y'),
            'eligible_at' => $eligibleAt->format('Y-m-d'),
            'tanggal_bisa_ajukan' => $eligibleAt->format('d/m/Y'),
            'days_remaining' => $daysRemaining,
            'source' => 'google_sheets',
            'is_stale' => false,
        ];
    }

    private function refresh(): array
    {
        $spreadsheetId = trim((string) config(
            'services.google_sheets.safety_shoe_spreadsheet_id'
        ));
        $sheetGid = (int) config(
            'services.google_sheets.safety_shoe_sheet_gid',
            65848559
        );
        $columns = trim((string) config(
            'services.google_sheets.safety_shoe_columns',
            'A:K'
        ));

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID Monitoring Sepatu Safety belum diatur.'
            );
        }

        $values = $this->googleSheets->getValuesBySheetId(
            $spreadsheetId,
            $sheetGid,
            $columns
        );

        $records = $this->normalizeValues($values);
        $ttlSeconds = max(
            60,
            (int) config(
                'services.google_sheets.safety_shoe_cache_ttl_seconds',
                60
            )
        );
        $syncedAt = now();

        $meta = [
            'status' => 'synced',
            'is_stale' => false,
            'synced_at' => $syncedAt->toIso8601String(),
            'expires_at' => $syncedAt
                ->copy()
                ->addSeconds($ttlSeconds)
                ->toIso8601String(),
            'record_count' => count($records),
            'error' => null,
        ];

        Cache::put(
            self::CACHE_KEY,
            $records,
            now()->addSeconds($ttlSeconds)
        );
        Cache::forever(self::BACKUP_KEY, $records);
        Cache::forever(self::META_KEY, $meta);

        return [
            'records' => $records,
            'meta' => $meta,
        ];
    }

    /**
     * Mencari kolom NRP, NAMA KARYAWAN, dan semua kolom DATE OF <tahun>.
     * Penambahan DATE OF 2027/2028 tidak membutuhkan perubahan kode.
     */
    private function normalizeValues(array $values): array
    {
        $headerRowIndex = $this->detectHeaderRow($values);

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header NRP dan DATE OF <tahun> tidak ditemukan pada sheet Sepatu Safety.'
            );
        }

        $headers = array_map(
            fn (mixed $value): string =>
                $this->normalizeHeader((string) $value),
            is_array($values[$headerRowIndex] ?? null)
                ? $values[$headerRowIndex]
                : []
        );

        $nrpIndex = array_search('nrp', $headers, true);
        $nameIndex = array_search('nama_karyawan', $headers, true);

        if ($nameIndex === false) {
            $nameIndex = array_search('nama', $headers, true);
        }

        $dateIndexes = [];

        foreach ($headers as $index => $header) {
            if (preg_match('/^date_of_\d{4}$/', $header)) {
                $dateIndexes[] = (int) $index;
            }
        }

        if ($nrpIndex === false || $dateIndexes === []) {
            throw new RuntimeException(
                'Kolom wajib NRP atau DATE OF <tahun> belum tersedia.'
            );
        }

        $records = [];

        foreach (array_slice($values, $headerRowIndex + 1) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nrp = $this->normalizeNrp(
                (string) ($row[$nrpIndex] ?? '')
            );

            if ($nrp === '') {
                continue;
            }

            $name = $nameIndex !== false
                ? trim((string) ($row[$nameIndex] ?? ''))
                : '';
            $lastTaken = null;

            foreach ($dateIndexes as $dateIndex) {
                $date = $this->parseSheetDate(
                    $row[$dateIndex] ?? null
                );

                if ($date && (! $lastTaken || $date->gt($lastTaken))) {
                    $lastTaken = $date;
                }
            }

            $candidate = [
                'nrp' => $nrp,
                'nama' => $name,
                'last_taken_date' => $lastTaken?->format('Y-m-d'),
            ];

            if (! isset($records[$nrp])) {
                $records[$nrp] = $candidate;
                continue;
            }

            $existingDate = $records[$nrp]['last_taken_date'] ?? null;

            if (
                $lastTaken
                && (
                    ! $existingDate
                    || $lastTaken->gt(Carbon::parse($existingDate))
                )
            ) {
                $records[$nrp] = $candidate;
            } elseif (
                ($records[$nrp]['nama'] ?? '') === ''
                && $name !== ''
            ) {
                $records[$nrp]['nama'] = $name;
            }
        }

        return $records;
    }

    private function detectHeaderRow(array $values): ?int
    {
        foreach (array_slice($values, 0, 10, true) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $headers = array_map(
                fn (mixed $value): string =>
                    $this->normalizeHeader((string) $value),
                $row
            );

            $hasNrp = in_array('nrp', $headers, true);
            $hasDate = collect($headers)->contains(
                fn (string $header): bool =>
                    (bool) preg_match('/^date_of_\d{4}$/', $header)
            );

            if ($hasNrp && $hasDate) {
                return (int) $index;
            }
        }

        return null;
    }

    private function parseSheetDate(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        $monthMap = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];
        $normalized = str_ireplace(
            array_keys($monthMap),
            array_values($monthMap),
            $value
        );

        foreach (
            [
                'j-M-y',
                'j-M-Y',
                'd/m/Y',
                'd/m/y',
                'Y-m-d',
                'd-m-Y',
                'd-m-y',
                'j M Y',
                'd F Y',
            ] as $format
        ) {
            $date = DateTimeImmutable::createFromFormat(
                '!'.$format,
                $normalized
            );
            $errors = DateTimeImmutable::getLastErrors();
            $hasErrors = is_array($errors)
                && (
                    ($errors['warning_count'] ?? 0) > 0
                    || ($errors['error_count'] ?? 0) > 0
                );

            if ($date !== false && ! $hasErrors) {
                return Carbon::instance($date)->startOfDay();
            }
        }

        try {
            return Carbon::parse(
                $normalized,
                'Asia/Jakarta'
            )->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    private function eligibilityForRecord(
        string $nrp,
        array $record,
        bool $isStale
    ): array {
        if (! ($record['last_taken_date'] ?? null)) {
            return array_merge(
                $this->emptyEligibility($nrp, true, $isStale),
                ['nama' => (string) ($record['nama'] ?? '')]
            );
        }

        return array_merge(
            $this->eligibilityFromDate(
                (string) $record['last_taken_date'],
                (string) ($record['nama'] ?? '')
            ),
            [
                'nrp' => $nrp,
                'found' => true,
                'is_stale' => $isStale,
            ]
        );
    }

    private function emptyEligibility(
        string $nrp,
        bool $found,
        bool $isStale
    ): array {
        return [
            'available' => true,
            'nrp' => $nrp,
            'found' => $found,
            'has_history' => false,
            'eligible' => true,
            'nama' => '',
            'last_taken_date' => null,
            'tanggal' => null,
            'eligible_at' => null,
            'tanggal_bisa_ajukan' => null,
            'days_remaining' => 0,
            'source' => 'google_sheets',
            'is_stale' => $isStale,
        ];
    }

    private function storedMeta(): array
    {
        $meta = Cache::get(self::META_KEY, []);

        return is_array($meta) ? $meta : [];
    }

    private function normalizeHeader(string $value): string
    {
        $value = Str::ascii(Str::lower(trim($value)));

        return trim(
            preg_replace('/[^a-z0-9]+/', '_', $value) ?? '',
            '_'
        );
    }

    private function normalizeNrp(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace('/\.0+$/', '', $value) ?? $value;
        }

        return strtoupper(
            preg_replace('/\s+/', '', $value) ?? $value
        );
    }
}

<?php

namespace App\Services;

use App\Models\AtrImport;
use App\Models\AtrRecord;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;
use Throwable;

class AtrImportService
{
    /**
     * Menyimpan file sementara, membaca workbook, lalu menghasilkan preview.
     */
    public function createPreview(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = array_map(
            'strtolower',
            (array) config('atr.upload.allowed_extensions', ['xlsx'])
        );

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException(
                'Format file ATR tidak didukung. Gunakan file XLSX.'
            );
        }

        $token = (string) Str::uuid();
        $storedPath = $file->storeAs(
            'atr/previews',
            $token . '.' . $extension,
            'local'
        );

        if (! $storedPath) {
            throw new RuntimeException('File preview ATR gagal disimpan.');
        }

        $absolutePath = Storage::disk('local')->path($storedPath);

        try {
            $parsed = $this->parseWorkbook($absolutePath);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPath);

            throw new RuntimeException(
                'File ATR tidak dapat dibaca: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        $hash = hash_file('sha256', $absolutePath);

        if ($hash === false) {
            Storage::disk('local')->delete($storedPath);

            throw new RuntimeException('Hash file ATR gagal dibuat.');
        }

        $parsed['preview_token'] = $token;
        $parsed['original_name'] = $file->getClientOriginalName();
        $parsed['stored_path'] = $storedPath;
        $parsed['file_hash'] = $hash;

        return $parsed;
    }

    /**
     * Memvalidasi ulang file preview lalu menyimpan import dan atr_records.
     */
    public function commit(array $previewSession, ?int $userId): AtrImport
    {
        $storedPath = trim((string) ($previewSession['stored_path'] ?? ''));

        if (
            $storedPath === ''
            || ! Storage::disk('local')->exists($storedPath)
        ) {
            throw new RuntimeException(
                'File preview tidak ditemukan atau sesi sudah kedaluwarsa.'
            );
        }

        $absolutePath = Storage::disk('local')->path($storedPath);
        $hash = hash_file('sha256', $absolutePath);

        if ($hash === false) {
            throw new RuntimeException('Hash file preview ATR gagal dibuat.');
        }

        $previewHash = trim(
            (string) ($previewSession['file_hash'] ?? '')
        );

        if ($previewHash !== '' && ! hash_equals($previewHash, $hash)) {
            throw new RuntimeException(
                'Isi file ATR berubah setelah preview. Silakan preview ulang.'
            );
        }

        $parsed = $this->parseWorkbook($absolutePath);

        if (($parsed['errors'] ?? []) !== []) {
            throw new RuntimeException(
                'Struktur workbook tidak valid: '
                . implode(' ', (array) $parsed['errors'])
            );
        }

        if ((int) ($parsed['invalid_rows'] ?? 0) > 0) {
            throw new RuntimeException(
                'Import dibatalkan karena masih terdapat baris tidak valid. '
                . 'Perbaiki file lalu preview ulang.'
            );
        }

        if ((int) ($parsed['valid_rows'] ?? 0) === 0) {
            throw new RuntimeException(
                'Tidak ada data ATR valid untuk diimpor.'
            );
        }

        $duplicate = AtrImport::query()
            ->where('file_hash', $hash)
            ->where('status', 'COMPLETED')
            ->exists();

        if ($duplicate) {
            throw new RuntimeException(
                'File yang sama sudah pernah berhasil diimpor.'
            );
        }

        $finalName = $this->finalArchiveName(
            (string) ($previewSession['original_name'] ?? 'atr-import.xlsx')
        );
        $finalPath = 'atr/imports/' . $finalName;

        Storage::disk('local')->makeDirectory('atr/imports');

        if (! Storage::disk('local')->copy($storedPath, $finalPath)) {
            throw new RuntimeException(
                'File ATR gagal disalin ke arsip import.'
            );
        }

        try {
            $import = DB::transaction(function () use (
                $previewSession,
                $parsed,
                $hash,
                $userId,
                $finalName,
                $finalPath
            ): AtrImport {
                $periods = collect($parsed['records'])
                    ->pluck('period')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                $import = AtrImport::query()->create([
                    'file_name' => (string) (
                        $previewSession['original_name'] ?? $finalName
                    ),
                    'stored_path' => $finalPath,
                    'file_hash' => $hash,
                    'status' => 'PROCESSING',
                    'total_rows' => (int) $parsed['total_rows'],
                    'valid_rows' => (int) $parsed['valid_rows'],
                    'invalid_rows' => 0,
                    'imported_rows' => 0,
                    'period_min' => $periods->first(),
                    'period_max' => $periods->last(),
                    'periods' => $periods->all(),
                    'errors' => [],
                    'uploaded_by' => $userId,
                ]);

                $now = now();
                $rows = collect($parsed['records'])
                    ->map(function (array $row) use ($import, $now): array {
                        return [
                            'atr_import_id' => $import->id,
                            'period' => $row['period'],
                            'nrp' => $row['nrp'],
                            'employee_name' => $row['employee_name'],
                            'job_title' => $row['job_title'],
                            'site' => $row['site'],
                            'atr' => $row['atr'],
                            'sick' => $row['sick'],
                            'permission' => $row['permission'],
                            'alpha' => $row['alpha'],
                            'status' => $row['status'],
                            'source_row' => $row['source_row'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    });

                $rows->chunk(500)->each(function ($chunk): void {
                    AtrRecord::query()->insert($chunk->all());
                });

                $import->forceFill([
                    'status' => 'COMPLETED',
                    'imported_rows' => $rows->count(),
                    'imported_at' => now(),
                ])->save();

                return $import->fresh();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($finalPath);

            throw $exception;
        }

        Storage::disk('local')->delete($storedPath);

        return $import;
    }

    /**
     * Membaca DATABASE_KARYAWAN dan ATR_SOURCE dari workbook.
     */
    public function parseWorkbook(string $absolutePath): array
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('File workbook ATR tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($absolutePath);

        try {
            $databaseSheetName = (string) config(
                'atr.workbook.database_sheet',
                'DATABASE_KARYAWAN'
            );
            $sourceSheetName = (string) config(
                'atr.workbook.source_sheet',
                'ATR_SOURCE'
            );

            $databaseSheet = $spreadsheet->getSheetByName(
                $databaseSheetName
            );
            $sourceSheet = $spreadsheet->getSheetByName(
                $sourceSheetName
            );

            $errors = [];

            if (! $databaseSheet instanceof Worksheet) {
                $errors[] = "Sheet {$databaseSheetName} tidak ditemukan.";
            }

            if (! $sourceSheet instanceof Worksheet) {
                $errors[] = "Sheet {$sourceSheetName} tidak ditemukan.";
            }

            if ($errors !== []) {
                return $this->emptyResult($errors);
            }

            $databaseMap = $this->headerMap($databaseSheet);
            $sourceMap = $this->headerMap($sourceSheet);

            $requiredDatabaseHeaders = (array) config(
                'atr.workbook.database_headers',
                ['NRP', 'NAMA', 'JABATAN', 'SITE']
            );
            $requiredSourceHeaders = (array) config(
                'atr.workbook.source_headers',
                ['PERIODE', 'NRP', 'ATR', 'S', 'I', 'A']
            );

            $errors = array_merge(
                $errors,
                $this->missingHeaders(
                    $databaseMap,
                    $requiredDatabaseHeaders,
                    $databaseSheetName
                ),
                $this->missingHeaders(
                    $sourceMap,
                    $requiredSourceHeaders,
                    $sourceSheetName
                )
            );

            if ($errors !== []) {
                return $this->emptyResult($errors);
            }

            $databaseResult = $this->readEmployeeDatabase(
                $databaseSheet,
                $databaseMap
            );
            $employees = $databaseResult['employees'];
            $errors = array_merge(
                $errors,
                $databaseResult['errors']
            );

            if ($employees === []) {
                $errors[] =
                    'Sheet DATABASE_KARYAWAN tidak memiliki data karyawan valid.';
            }

            if ($errors !== []) {
                return $this->emptyResult(
                    $errors,
                    count($employees)
                );
            }

            $rows = [];
            $rowErrors = [];
            $seen = [];
            $highestRow = $sourceSheet->getHighestDataRow();
            $blankStreak = 0;
            $blankStop = max(
                20,
                (int) config('atr.upload.blank_row_stop', 100)
            );

            for (
                $rowNumber = 2;
                $rowNumber <= $highestRow;
                $rowNumber++
            ) {
                $rawNrp = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['NRP'],
                    $rowNumber
                );
                $rawPeriod = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['PERIODE'],
                    $rowNumber
                );
                $rawAtr = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['ATR'],
                    $rowNumber
                );
                $rawSick = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['S'],
                    $rowNumber
                );
                $rawPermission = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['I'],
                    $rowNumber
                );
                $rawAlpha = $this->cellValue(
                    $sourceSheet,
                    $sourceMap['A'],
                    $rowNumber
                );

                /*
                 * Template lama memiliki satu baris formula yang memantulkan
                 * header 00.MASTER_UPLOAD. Baris tersebut bukan data ATR.
                 */
                if ($this->isHeaderEcho(
                    $rawNrp,
                    $rawAtr,
                    $rawSick,
                    $rawPermission,
                    $rawAlpha
                )) {
                    continue;
                }

                $isBlank = $this->isBlank($rawNrp)
                    && $this->isBlank($rawPeriod)
                    && $this->isBlank($rawAtr)
                    && $this->isBlank($rawSick)
                    && $this->isBlank($rawPermission)
                    && $this->isBlank($rawAlpha);

                if ($isBlank) {
                    $blankStreak++;

                    if ($blankStreak >= $blankStop) {
                        break;
                    }

                    continue;
                }

                $blankStreak = 0;

                $nrp = $this->normalizeNrp($rawNrp);
                $period = $this->normalizePeriod($rawPeriod);
                $atr = $this->normalizeAtr($rawAtr);
                $sick = $this->normalizeCount($rawSick);
                $permission = $this->normalizeCount($rawPermission);
                $alpha = $this->normalizeCount($rawAlpha);
                $messages = [];

                if ($nrp === '') {
                    $messages[] = 'NRP kosong.';
                } elseif (! isset($employees[$nrp])) {
                    $messages[] =
                        'NRP tidak ditemukan pada sheet DATABASE_KARYAWAN.';
                }

                if ($period === null) {
                    $messages[] =
                        'PERIODE tidak valid. Gunakan format YYYY-MM.';
                }

                if ($atr === false) {
                    $messages[] =
                        'ATR tidak valid. Gunakan angka 0 sampai 100 '
                        . 'atau tanda - untuk NO DATA.';
                }

                if (
                    $sick === null
                    || $permission === null
                    || $alpha === null
                ) {
                    $messages[] =
                        'Kolom S, I, dan A wajib berupa bilangan bulat >= 0.';
                }

                $duplicateKey = $period !== null && $nrp !== ''
                    ? $period . '|' . $nrp
                    : null;

                if (
                    $duplicateKey !== null
                    && isset($seen[$duplicateKey])
                ) {
                    $messages[] =
                        'Duplikat NRP dan PERIODE di dalam file.';
                }

                if ($messages !== []) {
                    $rowErrors[] = [
                        'row' => $rowNumber,
                        'nrp' => $nrp,
                        'messages' => $messages,
                    ];

                    continue;
                }

                $seen[$duplicateKey] = true;
                $employee = $employees[$nrp];
                $status = $this->statusForAtr(
                    $atr === null ? null : (float) $atr
                );

                $rows[] = [
                    'period' => $period,
                    'nrp' => $nrp,
                    'employee_name' => $employee['name'],
                    'job_title' => $employee['job_title'],
                    'site' => $employee['site'],
                    'atr' => $atr,
                    'sick' => $sick,
                    'permission' => $permission,
                    'alpha' => $alpha,
                    'status' => $status,
                    'source_row' => $rowNumber,
                ];
            }

            $previewLimit = max(
                1,
                (int) config('atr.upload.preview_rows', 20)
            );
            $periods = collect($rows)
                ->pluck('period')
                ->unique()
                ->sort()
                ->values()
                ->all();
            $statusCounts = collect($rows)
                ->countBy('status')
                ->all();

            return [
                'total_rows' => count($rows) + count($rowErrors),
                'valid_rows' => count($rows),
                'invalid_rows' => count($rowErrors),
                'records' => $rows,
                'preview_rows' => array_slice(
                    $rows,
                    0,
                    $previewLimit
                ),
                'errors' => [],
                'row_errors' => $rowErrors,
                'periods' => $periods,
                'employee_count' => count($employees),
                'status_counts' => [
                    'AMAN' => (int) ($statusCounts['AMAN'] ?? 0),
                    'MONITORING' => (int) (
                        $statusCounts['MONITORING'] ?? 0
                    ),
                    'PEMANGGILAN' => (int) (
                        $statusCounts['PEMANGGILAN'] ?? 0
                    ),
                    'NO_DATA' => (int) (
                        $statusCounts['NO_DATA'] ?? 0
                    ),
                ],
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    /**
     * Membaca master karyawan dan mendeteksi NRP duplikat/kosong.
     *
     * @return array{employees: array<string,array<string,string>>, errors: array<int,string>}
     */
    private function readEmployeeDatabase(
        Worksheet $sheet,
        array $map
    ): array {
        $employees = [];
        $errors = [];
        $duplicates = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $nrp = $this->normalizeNrp(
                $this->cellValue($sheet, $map['NRP'], $rowNumber)
            );

            if ($nrp === '') {
                continue;
            }

            $name = trim((string) $this->cellValue(
                $sheet,
                $map['NAMA'],
                $rowNumber
            ));
            $jobTitle = trim((string) $this->cellValue(
                $sheet,
                $map['JABATAN'],
                $rowNumber
            ));
            $site = trim((string) $this->cellValue(
                $sheet,
                $map['SITE'],
                $rowNumber
            ));

            if ($name === '') {
                $errors[] =
                    "DATABASE_KARYAWAN baris {$rowNumber}: NAMA kosong.";
                continue;
            }

            if (isset($employees[$nrp])) {
                $duplicates[$nrp] = true;
                continue;
            }

            $employees[$nrp] = [
                'name' => $name,
                'job_title' => $jobTitle !== '' ? $jobTitle : '-',
                'site' => $site !== '' ? $site : '-',
            ];
        }

        if ($duplicates !== []) {
            $example = implode(
                ', ',
                array_slice(array_keys($duplicates), 0, 10)
            );
            $errors[] =
                'NRP duplikat pada DATABASE_KARYAWAN: ' . $example . '.';
        }

        return [
            'employees' => $employees,
            'errors' => $errors,
        ];
    }

    private function headerMap(Worksheet $sheet): array
    {
        $highestColumn = Coordinate::columnIndexFromString(
            $sheet->getHighestDataColumn()
        );
        $map = [];

        for ($column = 1; $column <= $highestColumn; $column++) {
            $header = $this->normalizeHeader(
                $this->cellValue($sheet, $column, 1)
            );

            if ($header !== '') {
                $map[$header] = $column;
            }
        }

        return $map;
    }

    private function missingHeaders(
        array $map,
        array $required,
        string $sheetName
    ): array {
        $errors = [];

        foreach ($required as $header) {
            $normalized = $this->normalizeHeader($header);

            if (! array_key_exists($normalized, $map)) {
                $errors[] =
                    "Header {$header} tidak ditemukan pada sheet {$sheetName}.";
            }
        }

        return $errors;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = strtoupper(trim((string) $value));
        $header = preg_replace('/[^A-Z0-9]+/', '_', $header) ?? $header;

        return trim($header, '_');
    }

    private function cellValue(
        Worksheet $sheet,
        int $column,
        int $row
    ): mixed {
        $cell = $sheet->getCell([$column, $row]);

        try {
            return $cell->getCalculatedValue();
        } catch (Throwable) {
            $oldCalculated = $cell->getOldCalculatedValue();

            if ($oldCalculated !== null) {
                return $oldCalculated;
            }

            return $cell->getValue();
        }
    }

    private function normalizeNrp(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (
            is_float($value)
            && is_finite($value)
            && floor($value) === $value
        ) {
            return number_format($value, 0, '', '');
        }

        $raw = str_replace("\u{00A0}", ' ', trim((string) $value));
        $raw = ltrim($raw, "'\"");
        $raw = preg_replace('/\.0$/', '', $raw) ?? $raw;
        $raw = preg_replace('/\s+/', '', $raw) ?? $raw;

        if (
            preg_match('/^[+-]?\d+(?:\.\d+)?[Ee][+-]?\d+$/', $raw)
            && is_numeric($raw)
        ) {
            return number_format((float) $raw, 0, '', '');
        }

        return $raw;
    }

    private function normalizePeriod(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)
                ->startOfMonth()
                ->format('Y-m-d');
        }

        if (is_numeric($value) && (float) $value > 20000) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject((float) $value)
                )->startOfMonth()->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        $raw = trim((string) $value);

        if ($raw === '' || str_starts_with($raw, '#')) {
            return null;
        }

        if (preg_match('/^(\d{4})[-\/]?(\d{2})$/', $raw, $match)) {
            $month = (int) $match[2];

            if ($month >= 1 && $month <= 12) {
                return sprintf(
                    '%04d-%02d-01',
                    (int) $match[1],
                    $month
                );
            }
        }

        try {
            return Carbon::parse($raw)
                ->startOfMonth()
                ->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeAtr(mixed $value): float|false|null
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '' || $raw === '-') {
            return null;
        }

        if (str_starts_with($raw, '#')) {
            return false;
        }

        if (is_numeric($value)) {
            $number = (float) $value;
        } else {
            $normalized = str_replace(['%', ' '], '', $raw);
            $normalized = str_replace(',', '.', $normalized);

            if (! is_numeric($normalized)) {
                return false;
            }

            $number = (float) $normalized;
        }

        if ($number >= 0 && $number <= 1) {
            $number *= 100;
        }

        if ($number < 0 || $number > 100) {
            return false;
        }

        return round($number, 2);
    }

    private function normalizeCount(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        $raw = trim((string) $value);

        if (str_starts_with($raw, '#') || ! is_numeric($raw)) {
            return null;
        }

        $number = (float) $raw;

        if (
            $number < 0
            || floor($number) !== $number
            || $number > 4294967295
        ) {
            return null;
        }

        return (int) $number;
    }

    private function statusForAtr(?float $atr): string
    {
        if ($atr === null) {
            return 'NO_DATA';
        }

        $aman = (float) config('atr.aman_minimum', 98.5);
        $monitoring = (float) config(
            'atr.monitoring_minimum',
            95.0
        );

        if ($atr >= $aman) {
            return 'AMAN';
        }

        if ($atr >= $monitoring) {
            return 'MONITORING';
        }

        return 'PEMANGGILAN';
    }

    private function isHeaderEcho(
        mixed $nrp,
        mixed $atr,
        mixed $sick,
        mixed $permission,
        mixed $alpha
    ): bool {
        return $this->normalizeHeader($nrp) === 'NRP'
            && $this->normalizeHeader($atr) === 'ATR'
            && $this->normalizeHeader($sick) === 'S'
            && $this->normalizeHeader($permission) === 'I'
            && $this->normalizeHeader($alpha) === 'A';
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function finalArchiveName(string $originalName): string
    {
        $baseName = Str::slug(
            pathinfo($originalName, PATHINFO_FILENAME)
        );

        if ($baseName === '') {
            $baseName = 'atr-import';
        }

        return now()->format('Ymd_His')
            . '_'
            . Str::lower(Str::random(6))
            . '_'
            . $baseName
            . '.xlsx';
    }

    private function emptyResult(
        array $errors,
        int $employeeCount = 0
    ): array {
        return [
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'records' => [],
            'preview_rows' => [],
            'errors' => $errors,
            'row_errors' => [],
            'periods' => [],
            'employee_count' => $employeeCount,
            'status_counts' => [
                'AMAN' => 0,
                'MONITORING' => 0,
                'PEMANGGILAN' => 0,
                'NO_DATA' => 0,
            ],
        ];
    }
}
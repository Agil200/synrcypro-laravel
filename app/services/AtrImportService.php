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
use RuntimeException;

class AtrImportService
{
    public function createPreview(UploadedFile $file): array
    {
        $token = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            'atr/previews',
            $token . '.' . $extension,
            'local'
        );

        if (! $storedPath) {
            throw new RuntimeException('File preview ATR gagal disimpan.');
        }

        $absolutePath = Storage::disk('local')->path($storedPath);
        $parsed = $this->parseWorkbook($absolutePath);
        $parsed['preview_token'] = $token;
        $parsed['original_name'] = $file->getClientOriginalName();
        $parsed['stored_path'] = $storedPath;
        $parsed['file_hash'] = hash_file('sha256', $absolutePath);

        return $parsed;
    }

    public function commit(array $previewSession, ?int $userId): AtrImport
    {
        $storedPath = (string) ($previewSession['stored_path'] ?? '');

        if ($storedPath === '' || ! Storage::disk('local')->exists($storedPath)) {
            throw new RuntimeException('File preview tidak ditemukan atau sudah kedaluwarsa.');
        }

        $absolutePath = Storage::disk('local')->path($storedPath);
        $parsed = $this->parseWorkbook($absolutePath);

        if ($parsed['invalid_rows'] > 0) {
            throw new RuntimeException(
                'Import dibatalkan karena masih terdapat baris tidak valid. Perbaiki file lalu preview ulang.'
            );
        }

        if ($parsed['valid_rows'] === 0) {
            throw new RuntimeException('Tidak ada data ATR valid untuk diimpor.');
        }

        $hash = hash_file('sha256', $absolutePath);
        $duplicate = AtrImport::query()
            ->where('file_hash', $hash)
            ->where('status', 'COMPLETED')
            ->exists();

        if ($duplicate) {
            throw new RuntimeException('File yang sama sudah pernah berhasil diimpor.');
        }

        return DB::transaction(function () use (
            $previewSession,
            $storedPath,
            $absolutePath,
            $parsed,
            $hash,
            $userId
        ): AtrImport {
            $finalName = now()->format('Ymd_His')
                . '_'
                . Str::slug(
                    pathinfo(
                        (string) ($previewSession['original_name'] ?? 'atr-import'),
                        PATHINFO_FILENAME
                    )
                )
                . '.xlsx';

            $finalPath = 'atr/imports/' . $finalName;
            Storage::disk('local')->makeDirectory('atr/imports');

            if (! Storage::disk('local')->move($storedPath, $finalPath)) {
                throw new RuntimeException('File ATR gagal dipindahkan ke arsip import.');
            }

            $periods = collect($parsed['records'])
                ->pluck('period')
                ->unique()
                ->sort()
                ->values();

            $import = AtrImport::query()->create([
                'file_name' => (string) ($previewSession['original_name'] ?? $finalName),
                'stored_path' => $finalPath,
                'file_hash' => $hash,
                'status' => 'PROCESSING',
                'total_rows' => $parsed['total_rows'],
                'valid_rows' => $parsed['valid_rows'],
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
    }

    public function parseWorkbook(string $absolutePath): array
    {
        $spreadsheet = IOFactory::load($absolutePath);

        $databaseSheetName = (string) config(
            'atr.workbook.database_sheet',
            'DATABASE_KARYAWAN'
        );
        $sourceSheetName = (string) config(
            'atr.workbook.source_sheet',
            'ATR_SOURCE'
        );

        $databaseSheet = $spreadsheet->getSheetByName($databaseSheetName);
        $sourceSheet = $spreadsheet->getSheetByName($sourceSheetName);

        $errors = [];

        if (! $databaseSheet) {
            $errors[] = "Sheet {$databaseSheetName} tidak ditemukan.";
        }

        if (! $sourceSheet) {
            $errors[] = "Sheet {$sourceSheetName} tidak ditemukan.";
        }

        if ($errors !== []) {
            return $this->emptyResult($errors);
        }

        $databaseMap = $this->headerMap($databaseSheet);
        $sourceMap = $this->headerMap($sourceSheet);

        $requiredDatabaseHeaders = config('atr.workbook.database_headers', []);
        $requiredSourceHeaders = config('atr.workbook.source_headers', []);

        $errors = array_merge(
            $errors,
            $this->missingHeaders($databaseMap, $requiredDatabaseHeaders, $databaseSheetName),
            $this->missingHeaders($sourceMap, $requiredSourceHeaders, $sourceSheetName)
        );

        if ($errors !== []) {
            return $this->emptyResult($errors);
        }

        $employees = $this->readEmployeeDatabase($databaseSheet, $databaseMap);
        $rows = [];
        $rowErrors = [];
        $seen = [];
        $highestRow = $sourceSheet->getHighestDataRow();

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $rawNrp = $this->cellFormatted($sourceSheet, $sourceMap['NRP'], $rowNumber);
            $rawPeriod = $this->cellValue($sourceSheet, $sourceMap['PERIODE'], $rowNumber);
            $rawAtr = $this->cellValue($sourceSheet, $sourceMap['ATR'], $rowNumber);
            $rawSick = $this->cellValue($sourceSheet, $sourceMap['S'], $rowNumber);
            $rawPermission = $this->cellValue($sourceSheet, $sourceMap['I'], $rowNumber);
            $rawAlpha = $this->cellValue($sourceSheet, $sourceMap['A'], $rowNumber);

            $isBlank = trim((string) $rawNrp) === ''
                && trim((string) $rawPeriod) === ''
                && trim((string) $rawAtr) === '';

            if ($isBlank) {
                continue;
            }

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
                $messages[] = 'NRP tidak ditemukan pada sheet DATABASE_KARYAWAN.';
            }

            if ($period === null) {
                $messages[] = 'PERIODE tidak valid. Gunakan format YYYY-MM.';
            }

            if ($atr === false) {
                $messages[] = 'ATR tidak valid. Gunakan angka 0 sampai 100 atau tanda - untuk no data.';
            }

            if ($sick === null || $permission === null || $alpha === null) {
                $messages[] = 'Kolom S, I, dan A wajib berupa bilangan bulat >= 0.';
            }

            $duplicateKey = $period && $nrp !== ''
                ? $period . '|' . $nrp
                : null;

            if ($duplicateKey && isset($seen[$duplicateKey])) {
                $messages[] = 'Duplikat NRP dan PERIODE di dalam file.';
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
            $status = $this->statusForAtr($atr === null ? null : (float) $atr);

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

        $previewLimit = (int) config('atr.upload.preview_rows', 20);
        $periods = collect($rows)->pluck('period')->unique()->sort()->values()->all();

        return [
            'total_rows' => count($rows) + count($rowErrors),
            'valid_rows' => count($rows),
            'invalid_rows' => count($rowErrors),
            'records' => $rows,
            'preview_rows' => array_slice($rows, 0, $previewLimit),
            'errors' => $errors,
            'row_errors' => $rowErrors,
            'periods' => $periods,
            'employee_count' => count($employees),
        ];
    }

    private function readEmployeeDatabase($sheet, array $map): array
    {
        $employees = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $nrp = $this->normalizeNrp(
                $this->cellFormatted($sheet, $map['NRP'], $rowNumber)
            );

            if ($nrp === '') {
                continue;
            }

            $name = trim((string) $this->cellFormatted($sheet, $map['NAMA'], $rowNumber));
            $jobTitle = trim((string) $this->cellFormatted($sheet, $map['JABATAN'], $rowNumber));
            $site = trim((string) $this->cellFormatted($sheet, $map['SITE'], $rowNumber));

            if ($name === '') {
                continue;
            }

            $employees[$nrp] = [
                'name' => $name,
                'job_title' => $jobTitle !== '' ? $jobTitle : '-',
                'site' => $site !== '' ? $site : '-',
            ];
        }

        return $employees;
    }

    private function headerMap($sheet): array
    {
        $highestColumn = Coordinate::columnIndexFromString(
            $sheet->getHighestDataColumn()
        );
        $map = [];

        for ($column = 1; $column <= $highestColumn; $column++) {
            $header = strtoupper(trim((string) $this->cellFormatted($sheet, $column, 1)));
            $header = preg_replace('/\s+/', '_', $header) ?? $header;

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
            $normalized = strtoupper(str_replace(' ', '_', trim((string) $header)));

            if (! array_key_exists($normalized, $map)) {
                $errors[] = "Header {$header} tidak ditemukan pada sheet {$sheetName}.";
            }
        }

        return $errors;
    }

    private function cellValue($sheet, int $column, int $row): mixed
    {
        return $sheet->getCell([$column, $row])->getCalculatedValue();
    }

    private function cellFormatted($sheet, int $column, int $row): string
    {
        return trim((string) $sheet->getCell([$column, $row])->getFormattedValue());
    }

    private function normalizeNrp(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\.0$/', '', $value) ?? $value;

        return preg_replace('/\s+/', '', $value) ?? $value;
    }

    private function normalizePeriod(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->startOfMonth()->format('Y-m-d');
        }

        if (is_numeric($value) && (float) $value > 20000) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject((float) $value)
                )->startOfMonth()->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{4})[-\/]?(\d{2})$/', $raw, $match)) {
            $month = (int) $match[2];

            if ($month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d-01', (int) $match[1], $month);
            }
        }

        try {
            return Carbon::parse($raw)->startOfMonth()->format('Y-m-d');
        } catch (\Throwable) {
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
        $raw = trim((string) $value);

        if ($raw === '') {
            return 0;
        }

        if (! is_numeric($raw)) {
            return null;
        }

        $number = (float) $raw;

        if ($number < 0 || floor($number) !== $number) {
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
        $monitoring = (float) config('atr.monitoring_minimum', 95.0);

        if ($atr >= $aman) {
            return 'AMAN';
        }

        if ($atr >= $monitoring) {
            return 'MONITORING';
        }

        return 'PEMANGGILAN';
    }

    private function emptyResult(array $errors): array
    {
        return [
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'records' => [],
            'preview_rows' => [],
            'errors' => $errors,
            'row_errors' => [],
            'periods' => [],
            'employee_count' => 0,
        ];
    }
}

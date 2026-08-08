<?php

namespace App\Services;

use App\Models\AtrCoachingCounseling;
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
    private const SOURCE_SHEET = '00.MASTER_UPLOAD';

    /**
     * Header final template satu sheet.
     */
    private const REQUIRED_HEADERS = [
        'NRP',
        'NAMA',
        'DEPT',
        'JABATAN',
        'POSISI',
        'SITE',
        'ATR',
        'S',
        'I',
        'A',
        'PERIODE',
    ];

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
     * Informasi konflik periode untuk ditampilkan pada halaman preview.
     */
    public function conflictForPreview(array $preview): ?array
    {
        $periods = collect($preview['periods'] ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($periods->count() !== 1) {
            return null;
        }

        $period = (string) $periods->first();
        $existing = $this->findActiveImportForPeriod($period);

        if (! $existing) {
            return null;
        }

        $existingRecords = $existing->records()
            ->whereDate('period', $period)
            ->get();

        $existingByKey = $existingRecords->mapWithKeys(
            fn (AtrRecord $row): array => [
                $this->recordKey($row->period->format('Y-m-d'), (string) $row->nrp)
                    => $this->comparableExistingRecord($row),
            ]
        );

        $incomingRecords = collect($preview['records'] ?? []);
        $incomingByKey = $incomingRecords->mapWithKeys(
            fn (array $row): array => [
                $this->recordKey((string) $row['period'], (string) $row['nrp'])
                    => $this->comparableIncomingRecord($row),
            ]
        );

        $newRows = 0;
        $duplicateRows = 0;
        $changedRows = 0;
        $unchangedRows = 0;

        foreach ($incomingByKey as $key => $incomingRow) {
            if (! $existingByKey->has($key)) {
                $newRows++;
                continue;
            }

            $duplicateRows++;

            if ($existingByKey->get($key) === $incomingRow) {
                $unchangedRows++;
            } else {
                $changedRows++;
            }
        }

        $removedRows = $existingByKey->keys()
            ->reject(fn (string $key): bool => $incomingByKey->has($key))
            ->count();

        $isIdenticalData = $newRows === 0
            && $changedRows === 0
            && $removedRows === 0
            && $existingByKey->count() === $incomingByKey->count();

        $previewHash = trim((string) ($preview['file_hash'] ?? ''));
        $existingHash = trim((string) ($existing->file_hash ?? ''));
        $isIdenticalFile = $previewHash !== ''
            && $existingHash !== ''
            && hash_equals($existingHash, $previewHash);

        return [
            'id' => $existing->id,
            'file_name' => $existing->file_name,
            'period' => $period,
            'record_count' => $existingRecords->count(),
            'imported_at' => $existing->imported_at?->toDateTimeString(),
            'active_coaching_count' => $this->activeCoachingCount($existing),
            'append_new_rows' => $newRows,
            'append_duplicate_rows' => $duplicateRows,
            'changed_rows' => $changedRows,
            'unchanged_rows' => $unchangedRows,
            'removed_rows_on_replace' => $removedRows,
            'is_identical_file' => $isIdenticalFile,
            'is_identical_data' => $isIdenticalData,
            'has_meaningful_change' => ! $isIdenticalData,
        ];
    }

    private function recordKey(string $period, string $nrp): string
    {
        return trim($period) . '|' . trim($nrp);
    }

    /**
     * Bentuk record yang dipakai khusus untuk membandingkan snapshot.
     * source_row tidak dibandingkan karena nomor baris Excel bukan data bisnis.
     */
    private function comparableIncomingRecord(array $row): array
    {
        return [
            'employee_name' => $this->comparisonText($row['employee_name'] ?? ''),
            'dept' => $this->comparisonText($row['dept'] ?? ''),
            'job_title' => $this->comparisonText($row['job_title'] ?? ''),
            'position' => $this->comparisonText($row['position'] ?? ''),
            'site' => $this->comparisonText($row['site'] ?? ''),
            'atr' => $this->comparisonAtr($row['atr'] ?? null),
            'sick' => (int) ($row['sick'] ?? 0),
            'permission' => (int) ($row['permission'] ?? 0),
            'alpha' => (int) ($row['alpha'] ?? 0),
            'status' => $this->comparisonText($row['status'] ?? ''),
        ];
    }

    private function comparableExistingRecord(AtrRecord $row): array
    {
        return [
            'employee_name' => $this->comparisonText($row->employee_name),
            'dept' => $this->comparisonText($row->dept ?? ''),
            'job_title' => $this->comparisonText($row->job_title ?? ''),
            'position' => $this->comparisonText($row->position ?? ''),
            'site' => $this->comparisonText($row->site ?? ''),
            'atr' => $this->comparisonAtr($row->atr),
            'sick' => (int) $row->sick,
            'permission' => (int) $row->permission,
            'alpha' => (int) $row->alpha,
            'status' => $this->comparisonText($row->status),
        ];
    }

    private function comparisonText(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }

    private function comparisonAtr(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Snapshot aktif untuk sebuah periode.
     */
    public function findActiveImportForPeriod(string $period): ?AtrImport
    {
        return AtrImport::query()
            ->where('status', 'COMPLETED')
            ->whereHas(
                'records',
                fn ($query) => $query->whereDate('period', $period)
            )
            ->latest('id')
            ->first();
    }

    /**
     * Coaching COMPLETED membuat snapshot tidak boleh direvisi langsung.
     */
    public function activeCoachingCount(AtrImport $import): int
    {
        $recordIds = $import->records()->pluck('id');

        if ($recordIds->isEmpty()) {
            return 0;
        }

        return AtrCoachingCounseling::query()
            ->whereIn('atr_record_id', $recordIds)
            ->where('status', 'COMPLETED')
            ->count();
    }

    /**
     * Commit final mendukung:
     * NEW     = periode baru;
     * REPLACE = ganti seluruh snapshot periode;
     * APPEND  = pertahankan data lama dan tambahkan NRP yang belum ada.
     *
     * Snapshot lama tidak dihapus. Statusnya menjadi REPLACED sehingga
     * audit tetap utuh, sedangkan dashboard hanya membaca COMPLETED terbaru.
     */
    public function commit(
        array $previewSession,
        ?int $userId,
        string $action = 'NEW',
        ?int $expectedExistingImportId = null
    ): AtrImport {
        $action = strtoupper(trim($action));

        if (! in_array($action, ['NEW', 'REPLACE', 'APPEND'], true)) {
            throw new RuntimeException('Tindakan import ATR tidak valid.');
        }

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

        $previewHash = trim((string) ($previewSession['file_hash'] ?? ''));

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
            throw new RuntimeException('Tidak ada data ATR valid untuk diimpor.');
        }

        $periods = collect($parsed['records'])
            ->pluck('period')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($periods->count() !== 1) {
            throw new RuntimeException(
                'Satu file ATR hanya boleh berisi satu periode. '
                . 'Pisahkan file per bulan lalu preview ulang.'
            );
        }

        $period = (string) $periods->first();
        $existing = $this->findActiveImportForPeriod($period);

        if ($action === 'NEW' && $existing) {
            throw new RuntimeException(
                'Periode ini sudah memiliki data aktif. Pilih GANTI DATA '
                . 'PERIODE INI atau TAMBAHKAN DATA.'
            );
        }

        if (in_array($action, ['REPLACE', 'APPEND'], true)) {
            if (! $existing) {
                throw new RuntimeException(
                    'Snapshot periode lama tidak ditemukan. Silakan preview ulang.'
                );
            }

            if (
                $expectedExistingImportId === null
                || $existing->id !== $expectedExistingImportId
            ) {
                throw new RuntimeException(
                    'Snapshot periode berubah setelah preview. Silakan preview ulang.'
                );
            }

            $activeCoachingCount = $this->activeCoachingCount($existing);

            if ($activeCoachingCount > 0) {
                throw new RuntimeException(
                    'Periode ini memiliki ' . $activeCoachingCount
                    . ' dokumentasi pemanggilan aktif. Batalkan dokumentasi '
                    . 'tersebut terlebih dahulu sebelum merevisi data ATR.'
                );
            }
        }

        if ($existing && hash_equals((string) $existing->file_hash, $hash)) {
            throw new RuntimeException(
                'File ini identik dengan snapshot aktif periode tersebut. '
                . 'Tidak ada perubahan yang perlu diimpor.'
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

        $snapshotRows = collect($parsed['records']);

        if ($action === 'APPEND' && $existing) {
            $oldRows = $existing->records()
                ->whereDate('period', $period)
                ->get()
                ->map(fn (AtrRecord $row): array => [
                    'period' => $row->period->format('Y-m-d'),
                    'nrp' => (string) $row->nrp,
                    'employee_name' => (string) $row->employee_name,
                    'dept' => (string) ($row->dept ?? 'PRODUKSI'),
                    'job_title' => (string) ($row->job_title ?? '-'),
                    'position' => (string) ($row->position ?? '-'),
                    'site' => (string) ($row->site ?? '-'),
                    'atr' => $row->atr === null ? null : (float) $row->atr,
                    'sick' => (int) $row->sick,
                    'permission' => (int) $row->permission,
                    'alpha' => (int) $row->alpha,
                    'status' => (string) $row->status,
                    'source_row' => $row->source_row,
                ]);

            $existingKeys = $oldRows
                ->mapWithKeys(
                    fn (array $row): array => [
                        $row['period'] . '|' . $row['nrp'] => true,
                    ]
                );

            $newRows = $snapshotRows->reject(
                fn (array $row): bool => $existingKeys->has(
                    $row['period'] . '|' . $row['nrp']
                )
            )->values();

            if ($newRows->isEmpty()) {
                throw new RuntimeException(
                    'Tidak ada NRP baru untuk ditambahkan. Jika ingin '
                    . 'memperbarui data NRP yang sudah ada, pilih GANTI DATA '
                    . 'PERIODE INI.'
                );
            }

            $snapshotRows = $oldRows->concat($newRows)->values();
        }

        $finalName = $this->finalArchiveName(
            (string) ($previewSession['original_name'] ?? 'atr-import.xlsx')
        );
        $finalPath = 'atr/imports/' . $finalName;

        Storage::disk('local')->makeDirectory('atr/imports');

        if (! Storage::disk('local')->copy($storedPath, $finalPath)) {
            throw new RuntimeException('File ATR gagal disalin ke arsip import.');
        }

        try {
            $import = DB::transaction(function () use (
                $previewSession,
                $parsed,
                $hash,
                $userId,
                $finalName,
                $finalPath,
                $periods,
                $snapshotRows,
                $action,
                $existing
            ): AtrImport {
                $import = AtrImport::query()->create([
                    'file_name' => (string) (
                        $previewSession['original_name'] ?? $finalName
                    ),
                    'stored_path' => $finalPath,
                    'file_hash' => $hash,
                    'status' => 'PROCESSING',
                    'import_mode' => $action,
                    'replaces_import_id' => $existing?->id,
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
                $rows = $snapshotRows
                    ->map(function (array $row) use ($import, $now): array {
                        return [
                            'atr_import_id' => $import->id,
                            'period' => $row['period'],
                            'nrp' => $row['nrp'],
                            'employee_name' => $row['employee_name'],
                            'dept' => $row['dept'],
                            'job_title' => $row['job_title'],
                            'position' => $row['position'],
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

                if ($existing) {
                    $existing->forceFill([
                        'status' => 'REPLACED',
                    ])->save();
                }

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
     * Membaca satu sheet 00.MASTER_UPLOAD.
     * Header boleh berada di baris 1 s.d. 10 agar template tetap fleksibel.
     */
    public function parseWorkbook(string $absolutePath): array
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('File workbook ATR tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($absolutePath);

        try {
            $sheet = $spreadsheet->getSheetByName(self::SOURCE_SHEET);

            if (! $sheet instanceof Worksheet) {
                return $this->emptyResult([
                    'Sheet 00.MASTER_UPLOAD tidak ditemukan.',
                ]);
            }

            $headerResult = $this->detectHeaderMap($sheet);

            if ($headerResult === null) {
                return $this->emptyResult([
                    'Header 00.MASTER_UPLOAD tidak ditemukan. '
                    . 'Header wajib: ' . implode(', ', self::REQUIRED_HEADERS) . '.',
                ]);
            }

            $headerRow = $headerResult['row'];
            $map = $headerResult['map'];

            $missing = array_values(array_filter(
                self::REQUIRED_HEADERS,
                fn (string $header): bool => ! array_key_exists($header, $map)
            ));

            if ($missing !== []) {
                return $this->emptyResult([
                    'Header berikut belum ada pada 00.MASTER_UPLOAD: '
                    . implode(', ', $missing) . '.',
                ]);
            }

            $rows = [];
            $rowErrors = [];
            $seen = [];
            $uniqueNrps = [];
            $highestRow = $sheet->getHighestDataRow();
            $blankStreak = 0;
            $blankStop = max(
                20,
                (int) config('atr.upload.blank_row_stop', 100)
            );

            for ($rowNumber = $headerRow + 1; $rowNumber <= $highestRow; $rowNumber++) {
                $rawNrp = $this->cellValue($sheet, $map['NRP'], $rowNumber);
                $rawName = $this->cellValue($sheet, $map['NAMA'], $rowNumber);
                $rawDept = $this->cellValue($sheet, $map['DEPT'], $rowNumber);
                $rawJobTitle = $this->cellValue($sheet, $map['JABATAN'], $rowNumber);
                $rawPosition = $this->cellValue($sheet, $map['POSISI'], $rowNumber);
                $rawSite = $this->cellValue($sheet, $map['SITE'], $rowNumber);
                $rawAtr = $this->cellValue($sheet, $map['ATR'], $rowNumber);
                $rawSick = $this->cellValue($sheet, $map['S'], $rowNumber);
                $rawPermission = $this->cellValue($sheet, $map['I'], $rowNumber);
                $rawAlpha = $this->cellValue($sheet, $map['A'], $rowNumber);
                $rawPeriod = $this->cellValue($sheet, $map['PERIODE'], $rowNumber);

                $isBlank = $this->isBlank($rawNrp)
                    && $this->isBlank($rawName)
                    && $this->isBlank($rawAtr)
                    && $this->isBlank($rawPeriod);

                if ($isBlank) {
                    $blankStreak++;

                    if ($blankStreak >= $blankStop) {
                        break;
                    }

                    continue;
                }

                $blankStreak = 0;

                $nrp = $this->normalizeNrp($rawNrp);
                $name = $this->cleanText($rawName);
                $dept = $this->cleanText($rawDept);
                $jobTitle = $this->cleanText($rawJobTitle);
                $position = $this->cleanText($rawPosition);
                $site = $this->cleanText($rawSite);
                $period = $this->normalizePeriod($rawPeriod);
                $atr = $this->normalizeAtr($rawAtr);
                $sick = $this->normalizeCount($rawSick);
                $permission = $this->normalizeCount($rawPermission);
                $alpha = $this->normalizeCount($rawAlpha);
                $messages = [];

                if ($nrp === '') {
                    $messages[] = 'NRP kosong.';
                }

                if ($name === '') {
                    $messages[] = 'NAMA kosong.';
                }

                if ($position === '') {
                    $messages[] = 'POSISI kosong.';
                }

                if ($period === null) {
                    $messages[] = 'PERIODE tidak valid. Gunakan format YYYY-MM.';
                }

                if ($atr === false) {
                    $messages[] =
                        'ATR tidak valid. Gunakan angka 0 sampai 100 '
                        . 'atau tanda - untuk NO DATA.';
                }

                if ($sick === null || $permission === null || $alpha === null) {
                    $messages[] =
                        'Kolom S, I, dan A wajib berupa bilangan bulat >= 0.';
                }

                $duplicateKey = $period !== null && $nrp !== ''
                    ? $period . '|' . $nrp
                    : null;

                if ($duplicateKey !== null && isset($seen[$duplicateKey])) {
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
                $uniqueNrps[$nrp] = true;

                $rows[] = [
                    'period' => $period,
                    'nrp' => $nrp,
                    'employee_name' => $name,
                    'dept' => $dept !== '' ? $dept : 'PRODUKSI',
                    'job_title' => $jobTitle !== '' ? $jobTitle : '-',
                    'position' => $position,
                    'site' => $site !== '' ? $site : '-',
                    'atr' => $atr,
                    'sick' => $sick,
                    'permission' => $permission,
                    'alpha' => $alpha,
                    'status' => $this->statusForAtr(
                        $atr === null ? null : (float) $atr
                    ),
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

            $statusCounts = collect($rows)->countBy('status')->all();

            return [
                'total_rows' => count($rows) + count($rowErrors),
                'valid_rows' => count($rows),
                'invalid_rows' => count($rowErrors),
                'records' => $rows,
                'preview_rows' => array_slice($rows, 0, $previewLimit),
                'errors' => [],
                'row_errors' => $rowErrors,
                'periods' => $periods,
                'employee_count' => count($uniqueNrps),
                'status_counts' => [
                    'AMAN' => (int) ($statusCounts['AMAN'] ?? 0),
                    'MONITORING' => (int) ($statusCounts['MONITORING'] ?? 0),
                    'PEMANGGILAN' => (int) ($statusCounts['PEMANGGILAN'] ?? 0),
                    'NO_DATA' => (int) ($statusCounts['NO_DATA'] ?? 0),
                ],
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    /**
     * Cari baris header 1-10.
     * Mendukung NAMA maupun NAME.
     */
    private function detectHeaderMap(Worksheet $sheet): ?array
    {
        $scanRows = min(10, $sheet->getHighestDataRow());
        $highestColumn = Coordinate::columnIndexFromString(
            $sheet->getHighestDataColumn()
        );

        for ($row = 1; $row <= $scanRows; $row++) {
            $map = [];

            for ($column = 1; $column <= $highestColumn; $column++) {
                $header = $this->canonicalHeader(
                    $this->cellValue($sheet, $column, $row)
                );

                if ($header !== null) {
                    $map[$header] = $column;
                }
            }

            if (isset($map['NRP'], $map['ATR'])) {
                return [
                    'row' => $row,
                    'map' => $map,
                ];
            }
        }

        return null;
    }

    private function canonicalHeader(mixed $value): ?string
    {
        $header = $this->normalizeHeader($value);

        return match ($header) {
            'NRP', 'NIK', 'NIK_KARYAWAN' => 'NRP',
            'NAMA', 'NAME', 'NAMA_KARYAWAN' => 'NAMA',
            'DEPT', 'DEPARTEMEN', 'DEPARTMENT' => 'DEPT',
            'JABATAN', 'JOB_TITLE', 'JOBTITLE' => 'JABATAN',
            'POSISI', 'POSITION' => 'POSISI',
            'SITE', 'LOKASI' => 'SITE',
            'ATR' => 'ATR',
            'S', 'SAKIT' => 'S',
            'I', 'IZIN' => 'I',
            'A', 'ALPA' => 'A',
            'PERIODE', 'PERIOD' => 'PERIODE',
            default => null,
        };
    }

    private function cellValue(Worksheet $sheet, int $column, int $row): mixed
    {
        return $sheet->getCell([$column, $row])->getCalculatedValue();
    }

    private function normalizeHeader(mixed $value): string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9]+/', '_', $value) ?? $value;

        return trim($value, '_');
    }

    private function normalizeNrp(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (floor($value) === $value) {
                return number_format($value, 0, '', '');
            }

            return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        if (preg_match('/^[0-9]+(?:\.0+)?$/', $raw)) {
            return preg_replace('/\.0+$/', '', $raw) ?? $raw;
        }

        if (is_numeric($raw) && str_contains(strtolower($raw), 'e')) {
            return number_format((float) $raw, 0, '', '');
        }

        return preg_replace('/\s+/', '', $raw) ?? $raw;
    }

    private function cleanText(mixed $value): string
    {
        $text = trim((string) $value);
        return preg_replace('/\s+/', ' ', $text) ?? $text;
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

        $raw = str_replace(',', '.', $raw);

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

    private function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function finalArchiveName(string $originalName): string
    {
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));

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
            'status_counts' => [
                'AMAN' => 0,
                'MONITORING' => 0,
                'PEMANGGILAN' => 0,
                'NO_DATA' => 0,
            ],
        ];
    }
}
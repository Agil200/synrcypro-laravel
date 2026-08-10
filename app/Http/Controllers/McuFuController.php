<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class McuFuController extends Controller
{
    private const CACHE_KEY = 'mcu_fu.rows.fresh.v1';
    private const BACKUP_KEY = 'mcu_fu.rows.backup.v1';
    private const META_KEY = 'mcu_fu.rows.meta.v1';

    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }


            public function generateMcuNotification()
                {


                $snapshot=$this->snapshot();


                foreach($snapshot['rows'] as $row){


                Notification::create([

                'title'=>'🏥 Jadwal MCU',

                'message'=>
                ($row['nama'] ?? '').
                ' memiliki jadwal MCU',

                'type'=>'mcu',

                'target_role'=>'all',

                'notification_date'=>today()

                ]);


                }


                }



    public function index(Request $request): View
    {
        $snapshot = $this->snapshot();
        $allRows = collect($snapshot['rows']);

        $search = trim((string) $request->query('search', ''));
        $kehadiran = trim((string) $request->query('kehadiran', ''));
        $keterangan = trim((string) $request->query('keterangan', ''));
        $jenisMcu = trim((string) $request->query('jenis_mcu', ''));
        $tahun = trim((string) $request->query('tahun', ''));

        $filteredRows = $this->filterRows(
            $allRows,
            $search,
            $kehadiran,
            $keterangan,
            $jenisMcu,
            $tahun
        );

        $perPage = max(10, min(100, (int) $request->query('per_page', 25)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = new LengthAwarePaginator(
            $filteredRows->forPage($page, $perPage)->values(),
            $filteredRows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $statistics = [
            'total' => $allRows->count(),
            'filtered' => $filteredRows->count(),
            'hadir' => $filteredRows->filter(
                fn (array $row): bool =>
                    $this->normalize($row['kehadiran'] ?? '') === 'HADIR'
            )->count(),
            'tidak_hadir' => $filteredRows->filter(
                fn (array $row): bool =>
                    str_contains(
                        $this->normalize($row['kehadiran'] ?? ''),
                        'TIDAK HADIR'
                    )
            )->count(),
            'done_review' => $filteredRows->filter(
                fn (array $row): bool =>
                    str_contains(
                        $this->normalize($row['keterangan'] ?? ''),
                        'DONE REVIEW'
                    )
            )->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.mcu-fu.index',
            'mcuRows' => $paginator,
            'statistics' => $statistics,
            'search' => $search,
            'selectedKehadiran' => $kehadiran,
            'selectedKeterangan' => $keterangan,
            'selectedJenisMcu' => $jenisMcu,
            'selectedTahun' => $tahun,
            'kehadiranOptions' => $this->options($allRows, 'kehadiran'),
            'keteranganOptions' => $this->options($allRows, 'keterangan'),
            'jenisMcuOptions' => $this->options($allRows, 'jenis_mcu'),
            'tahunOptions' => $this->yearOptions($allRows),
            'sheetError' => $snapshot['error'],
            'isStale' => $snapshot['is_stale'],
            'lastSyncedAt' => $snapshot['synced_at'],
            'sourceUrl' => $this->sourceUrl(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $snapshot = $this->snapshot();
        $rows = $this->filterRows(
            collect($snapshot['rows']),
            trim((string) $request->query('search', '')),
            trim((string) $request->query('kehadiran', '')),
            trim((string) $request->query('keterangan', '')),
            trim((string) $request->query('jenis_mcu', '')),
            trim((string) $request->query('tahun', ''))
        )->values();

        return response()->json([
            'data' => $rows,
            'total' => $rows->count(),
            'synced_at' => $snapshot['synced_at'],
            'is_stale' => $snapshot['is_stale'],
            'error' => $snapshot['error'],
        ]);
    }

    public function refresh(): RedirectResponse
    {
        Cache::forget(self::CACHE_KEY);

        try {
            $snapshot = $this->fetchAndCache();

            return redirect()
                ->route('mcu-fu.index')
                ->with(
                    'success',
                    'Data MCU berhasil disinkronkan: ' .
                    count($snapshot['rows']) . ' baris.'
                );
        } catch (Throwable $exception) {
            return redirect()
                ->route('mcu-fu.index')
                ->with('error', $exception->getMessage());
        }
    }

    private function snapshot(): array
    {
        $cachedRows = Cache::get(self::CACHE_KEY);
        $meta = Cache::get(self::META_KEY, []);

        if (is_array($cachedRows)) {
            return [
                'rows' => $cachedRows,
                'synced_at' => $meta['synced_at'] ?? null,
                'is_stale' => false,
                'error' => null,
            ];
        }

        try {
            return $this->fetchAndCache();
        } catch (Throwable $exception) {
            $backupRows = Cache::get(self::BACKUP_KEY, []);

            return [
                'rows' => is_array($backupRows) ? $backupRows : [],
                'synced_at' => $meta['synced_at'] ?? null,
                'is_stale' => is_array($backupRows) && $backupRows !== [],
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function fetchAndCache(): array
    {
        $spreadsheetId = trim((string) config(
            'services.google_sheets.mcu_spreadsheet_id'
        ));

        $range = trim((string) config(
            'services.google_sheets.mcu_range',
            "'PRO'!A:I"
        ));

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'GOOGLE_SHEETS_MCU_SPREADSHEET_ID belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'GOOGLE_SHEETS_MCU_RANGE belum diatur.'
            );
        }

        $values = $this->googleSheets->getValues(
            $spreadsheetId,
            $range
        );

        $rows = $this->normalizeValues($values);

        if ($rows === []) {
            throw new RuntimeException(
                'Spreadsheet MCU tidak menghasilkan data. Pastikan range adalah ' .
                "'PRO'!A:I dan akun Google memiliki akses."
            );
        }

        $ttl = max(60, (int) config(
            'services.google_sheets.mcu_cache_ttl_seconds',
            300
        ));

        $syncedAt = now()->toDateTimeString();

        Cache::put(self::CACHE_KEY, $rows, $ttl);
        Cache::forever(self::BACKUP_KEY, $rows);
        Cache::forever(self::META_KEY, [
            'synced_at' => $syncedAt,
            'range' => $range,
        ]);

        return [
            'rows' => $rows,
            'synced_at' => $syncedAt,
            'is_stale' => false,
            'error' => null,
        ];
    }

    private function normalizeValues(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $headerRowIndex = $this->findHeaderRowIndex($values);

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header NRP dan NAMA tidak ditemukan pada tab PRO.'
            );
        }

        $headers = array_map(
            fn (mixed $header): string => $this->normalize((string) $header),
            $values[$headerRowIndex]
        );

        $columns = [
            'no' => $this->findColumn($headers, ['NO', 'NOMOR']),
            'nrp' => $this->findColumn($headers, ['NRP']),
            'nama' => $this->findColumn($headers, ['NAMA', 'NAMA KARYAWAN']),
            'dept' => $this->findColumn($headers, ['DEPT', 'DEPARTEMEN']),
            'jabatan' => $this->findColumn($headers, ['JABATAN', 'POSISI']),
            'tanggal_mcu' => $this->findColumn(
                $headers,
                ['TANGGAL MCU', 'TANGGAL']
            ),
            'kehadiran' => $this->findColumn($headers, ['KEHADIRAN']),
            'keterangan' => $this->findColumn($headers, ['KETERANGAN']),
            'jenis_mcu' => $this->findColumn($headers, ['JENIS MCU']),
        ];

        if ($columns['nrp'] < 0 || $columns['nama'] < 0) {
            throw new RuntimeException(
                'Kolom NRP atau NAMA tidak ditemukan pada tab PRO.'
            );
        }

        $result = [];

        foreach (array_slice($values, $headerRowIndex + 1) as $offset => $row) {
            if (! is_array($row)) {
                continue;
            }

            $nrp = $this->cell($row, $columns['nrp']);
            $nama = $this->cell($row, $columns['nama']);

            if ($nrp === '' && $nama === '') {
                continue;
            }

            $result[] = [
                'sheet_row' => $headerRowIndex + $offset + 2,
                'no' => $this->cell($row, $columns['no']),
                'nrp' => $nrp,
                'nama' => $nama,
                'dept' => $this->cell($row, $columns['dept']),
                'jabatan' => $this->cell($row, $columns['jabatan']),
                'tanggal_mcu' => $this->cell($row, $columns['tanggal_mcu']),
                'kehadiran' => $this->cell($row, $columns['kehadiran']),
                'keterangan' => $this->cell($row, $columns['keterangan']),
                'jenis_mcu' => $this->cell($row, $columns['jenis_mcu']),
            ];
        }

        return $result;
    }

    private function findHeaderRowIndex(array $values): ?int
    {
        foreach (array_slice($values, 0, 25, true) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $headers = array_map(
                fn (mixed $value): string => $this->normalize((string) $value),
                $row
            );

            if (in_array('NRP', $headers, true) && in_array('NAMA', $headers, true)) {
                return (int) $index;
            }
        }

        return null;
    }

    private function findColumn(array $headers, array $aliases): int
    {
        $normalizedAliases = array_map(
            fn (string $alias): string => $this->normalize($alias),
            $aliases
        );

        foreach ($headers as $index => $header) {
            if (in_array($header, $normalizedAliases, true)) {
                return (int) $index;
            }
        }

        return -1;
    }

    private function cell(array $row, int $index): string
    {
        if ($index < 0) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    private function normalize(mixed $value): string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function filterRows(
        Collection $rows,
        string $search,
        string $kehadiran,
        string $keterangan,
        string $jenisMcu,
        string $tahun
    ): Collection {
        $searchNormal = mb_strtolower($search);

        return $rows
            ->filter(function (array $row) use ($searchNormal): bool {
                if ($searchNormal === '') {
                    return true;
                }

                $haystack = mb_strtolower(implode(' ', [
                    $row['nrp'] ?? '',
                    $row['nama'] ?? '',
                    $row['dept'] ?? '',
                    $row['jabatan'] ?? '',
                    $row['tanggal_mcu'] ?? '',
                    $row['kehadiran'] ?? '',
                    $row['keterangan'] ?? '',
                    $row['jenis_mcu'] ?? '',
                ]));

                return str_contains($haystack, $searchNormal);
            })
            ->filter(
                fn (array $row): bool =>
                    $kehadiran === '' ||
                    $this->normalize($row['kehadiran'] ?? '') ===
                    $this->normalize($kehadiran)
            )
            ->filter(
                fn (array $row): bool =>
                    $keterangan === '' ||
                    $this->normalize($row['keterangan'] ?? '') ===
                    $this->normalize($keterangan)
            )
            ->filter(
                fn (array $row): bool =>
                    $jenisMcu === '' ||
                    $this->normalize($row['jenis_mcu'] ?? '') ===
                    $this->normalize($jenisMcu)
            )
            ->filter(
                fn (array $row): bool =>
                    $tahun === '' ||
                    $this->extractYear($row['tanggal_mcu'] ?? '') === $tahun
            )
            ->values();
    }

    private function yearOptions(Collection $rows): array
    {
        return $rows
            ->pluck('tanggal_mcu')
            ->map(fn (mixed $value): ?string => $this->extractYear($value))
            ->filter(fn (?string $year): bool => $year !== null)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    private function extractYear(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/(?<!\d)((?:19|20)\d{2})(?!\d)/', $value, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function options(Collection $rows, string $key): array
    {
        return $rows
            ->pluck($key)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique(fn (string $value): string => $this->normalize($value))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function sourceUrl(): string
    {
        $spreadsheetId = trim((string) config(
            'services.google_sheets.mcu_spreadsheet_id'
        ));

        $gid = (int) config(
            'services.google_sheets.mcu_sheet_gid',
            1692836561
        );

        return 'https://docs.google.com/spreadsheets/d/' .
            rawurlencode($spreadsheetId) .
            '/edit?gid=' . $gid . '#gid=' . $gid;
    }
}
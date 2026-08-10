<?php

namespace App\Http\Controllers;

use App\Models\Bnn;
use App\Models\Employee;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class BNNController extends Controller
{
    private const MONITORING_CACHE_KEY = 'bnn.monitoring.rows.fresh.v1';
    private const MONITORING_BACKUP_KEY = 'bnn.monitoring.rows.backup.v1';
    private const MONITORING_META_KEY = 'bnn.monitoring.rows.meta.v1';

    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /**
     * Halaman form input BNN yang sudah ada.
     */
    public function index(): View
    {
        return view('manpower.bnn.form');
    }


        /** Notifikasi BNN
         *  */

                        public function generateNotification()
                {

                $data = Bnn::whereDate(
                'tanggal_pemeriksaan',
                today()
                )
                ->get();


                foreach($data as $row){


                Notification::firstOrCreate([

                'title'=>'🧪 Jadwal Pemeriksaan BNN',

                'reference_id'=>$row->id,

                'type'=>'bnn'

                ],[

                'message'=>
                $row->nama.
                ' jadwal pemeriksaan BNN hari ini',

                'target_role'=>'all',

                'notification_date'=>today()

                ]);


                }

                }


    /**
     * Menyimpan data dari form lokal yang sudah ada.
     * Monitoring pada file ini tetap membaca sumber Google Spreadsheet.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nrp' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'string', 'max:50'],
            'perusahaan' => ['nullable', 'string', 'max:255'],
            'dept' => ['nullable', 'string', 'max:255'],
            'posisi' => ['nullable', 'string', 'max:255'],
            'usia' => ['nullable', 'string', 'max:50'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:100'],
            'tanggal_pemeriksaan' => ['required', 'date'],
            'akomodasi' => [
                'required',
                Rule::in([
                    'DIANTAR DI MESS',
                    'BERANGKAT SENDIRI',
                    'BANGKO',
                ]),
            ],
        ]);

        Bnn::create($validated);

        return redirect()
            ->route('bnn.monitoring')
            ->with('success', 'Data BNN berhasil disimpan.');
    }

    /**
     * Dashboard BNN lama berbasis tabel bnn tetap dipertahankan.
     */
    public function dashboard(Request $request): View
    {
        $bulan = trim((string) $request->query('bulan', date('Y-m')));

        try {
            $periode = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        } catch (Throwable) {
            $periode = now()->startOfMonth();
            $bulan = $periode->format('Y-m');
        }

        $summary = [
            'total' => Bnn::count(),
            'month' => Bnn::query()
                ->whereYear('tanggal_pemeriksaan', $periode->year)
                ->whereMonth('tanggal_pemeriksaan', $periode->month)
                ->count(),
            'done' => Bnn::count(),
            'pending' => Employee::query()
                ->whereNotIn('nrp', Bnn::query()->select('nrp'))
                ->count(),
        ];

        $akomodasi = [
            'mess' => Bnn::where('akomodasi', 'DIANTAR DI MESS')->count(),
            'sendiri' => Bnn::where('akomodasi', 'BERANGKAT SENDIRI')->count(),
            'bangko' => Bnn::where('akomodasi', 'BANGKO')->count(),
        ];

        $trend = [];

        for ($month = 1; $month <= 12; $month++) {
            $trend[] = [
                'bulan' => Carbon::create($periode->year, $month, 1)
                    ->locale('id')
                    ->translatedFormat('M'),
                'total' => Bnn::query()
                    ->whereYear('tanggal_pemeriksaan', $periode->year)
                    ->whereMonth('tanggal_pemeriksaan', $month)
                    ->count(),
            ];
        }

        $maxTrend = max(1, (int) collect($trend)->max('total'));
        $recent = Bnn::query()->latest()->limit(10)->get();

        return view('bnn.dashboard', compact(
            'summary',
            'akomodasi',
            'trend',
            'maxTrend',
            'recent',
            'bulan'
        ));
    }

    /**
     * Monitoring BNN seperti Monitoring MCU, tetapi sumber datanya
     * Google Spreadsheet Daftar Test BNN.
     */
    public function monitoring(Request $request): View
    {
        $snapshot = $this->monitoringSnapshot();
        $allRows = collect($snapshot['rows']);

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $akomodasi = trim((string) $request->query('akomodasi', ''));
        $perusahaan = trim((string) $request->query('perusahaan', ''));
        $tahun = trim((string) $request->query('tahun', ''));

        $filteredRows = $this->filterMonitoringRows(
            $allRows,
            $search,
            $status,
            $akomodasi,
            $perusahaan,
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
            'sudah_test' => $filteredRows->filter(
                fn (array $row): bool =>
                    $this->normalize($row['status_test'] ?? '') === 'SUDAH TEST'
            )->count(),
            'belum_test' => $filteredRows->filter(
                fn (array $row): bool =>
                    $this->normalize($row['status_test'] ?? '') === 'BELUM TEST'
            )->count(),
            'diantar_mess' => $filteredRows->filter(
                fn (array $row): bool =>
                    $this->normalize($row['akomodasi'] ?? '') === 'DIANTAR DI MESS'
            )->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.bnn.monitoring',
            'bnnRows' => $paginator,
            'statistics' => $statistics,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedAkomodasi' => $akomodasi,
            'selectedPerusahaan' => $perusahaan,
            'selectedTahun' => $tahun,
            'statusOptions' => ['SUDAH TEST', 'BELUM TEST'],
            'akomodasiOptions' => $this->monitoringOptions($allRows, 'akomodasi'),
            'perusahaanOptions' => $this->monitoringOptions($allRows, 'perusahaan'),
            'tahunOptions' => $this->monitoringYearOptions($allRows),
            'sheetError' => $snapshot['error'],
            'isStale' => $snapshot['is_stale'],
            'lastSyncedAt' => $snapshot['synced_at'],
            'sourceUrl' => $this->monitoringSourceUrl(),
        ]);
    }

    /**
     * Endpoint JSON untuk kebutuhan AJAX atau integrasi lain.
     */
    public function data(Request $request): JsonResponse
    {
        $snapshot = $this->monitoringSnapshot();

        $rows = $this->filterMonitoringRows(
            collect($snapshot['rows']),
            trim((string) $request->query('search', '')),
            trim((string) $request->query('status', '')),
            trim((string) $request->query('akomodasi', '')),
            trim((string) $request->query('perusahaan', '')),
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

    /**
     * Menghapus cache lalu membaca ulang Google Spreadsheet.
     */
    public function refresh(): RedirectResponse
    {
        Cache::forget(self::MONITORING_CACHE_KEY);

        try {
            $snapshot = $this->fetchAndCacheMonitoring();

            return redirect()
                ->route('bnn.monitoring')
                ->with(
                    'success',
                    'Data Monitoring BNN berhasil disinkronkan: ' .
                    count($snapshot['rows']) . ' baris.'
                );
        } catch (Throwable $exception) {
            return redirect()
                ->route('bnn.monitoring')
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * Auto lookup data karyawan berdasarkan NRP.
     */
    public function cariNRP(string $nrp): JsonResponse
    {
        $data = Employee::query()->where('nrp', trim($nrp))->first();

        if (! $data) {
            return response()->json([
                'status' => false,
                'message' => 'NRP tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'nrp' => $data->nrp,
            'nama' => $data->nama,
            'jenis_kelamin' => $data->jenis_kelamin,
            'perusahaan' => $data->perusahaan,
            'dept' => $data->dept,
            'posisi' => $data->posisi,
            'usia' => $data->usia,
            'kontak' => $data->kontak,
            'nik' => $data->nik,
        ]);
    }

    private function monitoringSnapshot(): array
    {
        $cachedRows = Cache::get(self::MONITORING_CACHE_KEY);
        $meta = Cache::get(self::MONITORING_META_KEY, []);

        if (is_array($cachedRows)) {
            return [
                'rows' => $cachedRows,
                'synced_at' => $meta['synced_at'] ?? null,
                'is_stale' => false,
                'error' => null,
            ];
        }

        try {
            return $this->fetchAndCacheMonitoring();
        } catch (Throwable $exception) {
            $backupRows = Cache::get(self::MONITORING_BACKUP_KEY, []);

            return [
                'rows' => is_array($backupRows) ? $backupRows : [],
                'synced_at' => $meta['synced_at'] ?? null,
                'is_stale' => is_array($backupRows) && $backupRows !== [],
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function fetchAndCacheMonitoring(): array
    {
        $spreadsheetId = trim((string) config(
            'services.google_sheets.test_bnn_spreadsheet_id'
        ));

        $range = trim((string) config(
            'services.google_sheets.test_bnn_range',
            "'DAFTAR TEST BNN'!A:AZ"
        ));

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'GOOGLE_SHEETS_TEST_BNN_SPREADSHEET_ID belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'GOOGLE_SHEETS_TEST_BNN_RANGE belum diatur.'
            );
        }

        $values = $this->googleSheets->getValues($spreadsheetId, $range);
        $rows = $this->normalizeMonitoringValues($values);

        if ($rows === []) {
            throw new RuntimeException(
                'Spreadsheet BNN tidak menghasilkan data. Periksa nama tab, range, header NRP/NAMA, dan akses akun Google.'
            );
        }

        $ttl = max(60, (int) config(
            'services.google_sheets.test_bnn_cache_ttl_seconds',
            300
        ));

        $syncedAt = now()->toDateTimeString();

        Cache::put(self::MONITORING_CACHE_KEY, $rows, $ttl);
        Cache::forever(self::MONITORING_BACKUP_KEY, $rows);
        Cache::forever(self::MONITORING_META_KEY, [
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

    /**
     * Header dicari otomatis pada 30 baris pertama agar tetap bekerja
     * walaupun spreadsheet memiliki judul atau baris kosong di bagian atas.
     */
    private function normalizeMonitoringValues(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $headerRowIndex = $this->findMonitoringHeaderRowIndex($values);

        if ($headerRowIndex === null) {
            throw new RuntimeException(
                'Header NRP dan NAMA tidak ditemukan pada Spreadsheet BNN.'
            );
        }

        $headers = array_map(
            fn (mixed $header): string => $this->normalize($header),
            $values[$headerRowIndex]
        );

        $columns = [
            'no' => $this->findColumn($headers, ['NO', 'NOMOR']),
            'nrp' => $this->findColumn($headers, ['NRP', 'NRP KARYAWAN']),
            'nama' => $this->findColumn($headers, ['NAMA', 'NAMA KARYAWAN']),
            'jenis_kelamin' => $this->findColumn(
                $headers,
                ['JENIS KELAMIN', 'JK', 'GENDER']
            ),
            'perusahaan' => $this->findColumn(
                $headers,
                ['PERUSAHAAN', 'COMPANY', 'PT']
            ),
            'dept' => $this->findColumn(
                $headers,
                ['DEPT', 'DEPARTEMEN', 'DEPARTMENT']
            ),
            'posisi' => $this->findColumn(
                $headers,
                ['POSISI', 'JABATAN']
            ),
            'usia' => $this->findColumn($headers, ['USIA', 'UMUR']),
            'kontak' => $this->findColumn(
                $headers,
                ['KONTAK', 'NO HP', 'NOMOR HP', 'HP', 'WHATSAPP', 'WA']
            ),
            'nik' => $this->findColumn(
                $headers,
                ['NIK', 'NIK KTP', 'NO KTP', 'NOMOR KTP']
            ),
            'tanggal_pemeriksaan' => $this->findColumn(
                $headers,
                [
                    'TANGGAL PEMERIKSAAN',
                    'TANGGAL TEST',
                    'TANGGAL TES',
                    'TANGGAL BNN',
                    'TANGGAL',
                ]
            ),
            'status_test' => $this->findColumn(
                $headers,
                [
                    'STATUS TEST',
                    'STATUS TES',
                    'STATUS PEMERIKSAAN',
                    'KEHADIRAN',
                    'STATUS KEHADIRAN',
                    'STATUS',
                ]
            ),
            'akomodasi' => $this->findColumn(
                $headers,
                ['AKOMODASI', 'TRANSPORTASI']
            ),
            'keterangan' => $this->findColumn(
                $headers,
                ['KETERANGAN', 'CATATAN', 'REMARK', 'REMARKS']
            ),
        ];

        if ($columns['nrp'] < 0 || $columns['nama'] < 0) {
            throw new RuntimeException(
                'Kolom NRP atau NAMA tidak ditemukan pada Spreadsheet BNN.'
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

            $tanggal = $this->cell($row, $columns['tanggal_pemeriksaan']);
            $statusRaw = $this->cell($row, $columns['status_test']);

            $result[] = [
                'sheet_row' => $headerRowIndex + $offset + 2,
                'no' => $this->cell($row, $columns['no']),
                'nrp' => $nrp,
                'nama' => $nama,
                'jenis_kelamin' => $this->cell($row, $columns['jenis_kelamin']),
                'perusahaan' => $this->cell($row, $columns['perusahaan']),
                'dept' => $this->cell($row, $columns['dept']),
                'posisi' => $this->cell($row, $columns['posisi']),
                'usia' => $this->cell($row, $columns['usia']),
                'kontak' => $this->cell($row, $columns['kontak']),
                'nik' => $this->cell($row, $columns['nik']),
                'tanggal_pemeriksaan' => $tanggal,
                'status_test' => $this->deriveStatusTest($statusRaw, $tanggal),
                'akomodasi' => $this->cell($row, $columns['akomodasi']),
                'keterangan' => $this->cell($row, $columns['keterangan']),
            ];
        }

        return $result;
    }

    private function findMonitoringHeaderRowIndex(array $values): ?int
    {
        foreach (array_slice($values, 0, 30, true) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $headers = array_map(
                fn (mixed $value): string => $this->normalize($value),
                $row
            );

            $nrpColumn = $this->findColumn($headers, ['NRP', 'NRP KARYAWAN']);
            $namaColumn = $this->findColumn($headers, ['NAMA', 'NAMA KARYAWAN']);

            if ($nrpColumn >= 0 && $namaColumn >= 0) {
                return (int) $index;
            }
        }

        return null;
    }

    private function deriveStatusTest(string $status, string $tanggal): string
    {
        $statusNormal = $this->normalize($status);

        foreach (['TIDAK HADIR', 'BELUM', 'PENDING', 'TIDAK TEST', 'TIDAK TES'] as $negative) {
            if ($statusNormal !== '' && str_contains($statusNormal, $negative)) {
                return 'BELUM TEST';
            }
        }

        foreach (['SUDAH', 'HADIR', 'DONE', 'SELESAI', 'TELAH'] as $positive) {
            if ($statusNormal !== '' && str_contains($statusNormal, $positive)) {
                return 'SUDAH TEST';
            }
        }

        return trim($tanggal) !== '' ? 'SUDAH TEST' : 'BELUM TEST';
    }

    private function filterMonitoringRows(
        Collection $rows,
        string $search,
        string $status,
        string $akomodasi,
        string $perusahaan,
        string $tahun
    ): Collection {
        $searchNormal = mb_strtolower(trim($search));

        return $rows
            ->filter(function (array $row) use ($searchNormal): bool {
                if ($searchNormal === '') {
                    return true;
                }

                $haystack = mb_strtolower(implode(' ', [
                    $row['no'] ?? '',
                    $row['nrp'] ?? '',
                    $row['nama'] ?? '',
                    $row['jenis_kelamin'] ?? '',
                    $row['perusahaan'] ?? '',
                    $row['dept'] ?? '',
                    $row['posisi'] ?? '',
                    $row['usia'] ?? '',
                    $row['kontak'] ?? '',
                    $row['nik'] ?? '',
                    $row['tanggal_pemeriksaan'] ?? '',
                    $row['status_test'] ?? '',
                    $row['akomodasi'] ?? '',
                    $row['keterangan'] ?? '',
                ]));

                return str_contains($haystack, $searchNormal);
            })
            ->filter(
                fn (array $row): bool =>
                    $status === '' ||
                    $this->normalize($row['status_test'] ?? '') ===
                    $this->normalize($status)
            )
            ->filter(
                fn (array $row): bool =>
                    $akomodasi === '' ||
                    $this->normalize($row['akomodasi'] ?? '') ===
                    $this->normalize($akomodasi)
            )
            ->filter(
                fn (array $row): bool =>
                    $perusahaan === '' ||
                    $this->normalize($row['perusahaan'] ?? '') ===
                    $this->normalize($perusahaan)
            )
            ->filter(
                fn (array $row): bool =>
                    $tahun === '' ||
                    $this->extractYear($row['tanggal_pemeriksaan'] ?? '') === $tahun
            )
            ->values();
    }

    private function monitoringOptions(Collection $rows, string $key): array
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

    private function monitoringYearOptions(Collection $rows): array
    {
        return $rows
            ->pluck('tanggal_pemeriksaan')
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

    private function monitoringSourceUrl(): string
    {
        $spreadsheetId = trim((string) config(
            'services.google_sheets.test_bnn_spreadsheet_id'
        ));

        $gid = (int) config(
            'services.google_sheets.test_bnn_sheet_gid',
            615934612
        );

        return 'https://docs.google.com/spreadsheets/d/' .
            rawurlencode($spreadsheetId) .
            '/edit?gid=' . $gid . '#gid=' . $gid;
    }
}
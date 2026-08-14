<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class DriveFileController extends Controller
{
    public function __construct(
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    public function index(Request $request): View
    {
        $catalog = $this->loadCatalog();
        $allFiles = collect($catalog['files']);

        $categories = $allFiles
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', 'ALL'));

        $filtered = $allFiles
            ->when(
                $category !== '' && strtoupper($category) !== 'ALL',
                fn (Collection $items): Collection => $items->filter(
                    fn (array $file): bool => strcasecmp(
                        $file['category'],
                        $category
                    ) === 0
                )
            )
            ->when(
                $search !== '',
                fn (Collection $items): Collection => $items->filter(
                    function (array $file) use ($search): bool {
                        $haystack = implode(
                            ' ',
                            [
                                $file['title'],
                                $file['category'],
                                $file['description'],
                                $file['type'],
                                $file['access'],
                            ]
                        );

                        return str_contains(
                            mb_strtolower($haystack),
                            mb_strtolower($search)
                        );
                    }
                )
            )
            ->values();

        $perPage = 24;
        $currentPage = max(1, (int) $request->query('page', 1));

        $files = new LengthAwarePaginator(
            $filtered
                ->forPage($currentPage, $perPage)
                ->values(),
            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view(
            'database',
            [
                'contentView' => 'database.files.index',
                'activePage' => 'files',
                'files' => $files,
                'categories' => $categories,
                'selectedCategory' => $category,
                'search' => $search,
                'catalogMeta' => $catalog['meta'],
            ]
        );
    }

    /**
     * @return array{
     *     files: array<int, array<string, mixed>>,
     *     meta: array<string, mixed>
     * }
     */
    private function loadCatalog(): array
    {
        $sourceUrl = trim((string) config(
            'services.google_sheets.drive_files_source_url',
            ''
        ));

        try {
            if (! $this->googleSheets->hasStoredToken()) {
                throw new \RuntimeException(
                    'OAuth Google Sheets belum terhubung.'
                );
            }

            $values = $this->googleSheets->getDriveFileValues();
            $files = $this->parseRows($values);

            return [
                'files' => $files,
                'meta' => [
                    'connected' => true,
                    'message' => 'Spreadsheet Pusat File terhubung.',
                    'source_url' => $sourceUrl,
                    'range' => (string) config(
                        'services.google_sheets.drive_files_range',
                        'A:Z'
                    ),
                    'total' => count($files),
                    'synced_at' => now()->format('d M Y, H:i'),
                ],
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'files' => [],
                'meta' => [
                    'connected' => false,
                    'message' => 'Data Pusat File belum dapat dibaca.',
                    'source_url' => $sourceUrl,
                    'range' => (string) config(
                        'services.google_sheets.drive_files_range',
                        'A:Z'
                    ),
                    'total' => 0,
                    'synced_at' => null,
                ],
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseRows(array $values): array
    {
        $rows = array_values(array_filter(
            $values,
            fn ($row): bool => is_array($row)
                && $this->rowHasValue($row)
        ));

        if ($rows === []) {
            return [];
        }

        $headerIndex = $this->findHeaderRow($rows);
        $headers = array_map(
            fn ($value): string => $this->normalizeHeader($value),
            $rows[$headerIndex]
        );

        $columns = [
            'id' => $this->findColumn($headers, ['ID', 'NO', 'NOMOR']),
            'title' => $this->findColumn(
                $headers,
                ['JUDUL', 'JUDUL FILE', 'NAMA FILE', 'NAMA DOKUMEN']
            ),
            'category' => $this->findColumn(
                $headers,
                ['KATEGORI', 'KATEGORI FILE', 'JENIS DOKUMEN']
            ),
            'description' => $this->findColumn(
                $headers,
                ['DESKRIPSI', 'KETERANGAN', 'CATATAN']
            ),
            'type' => $this->findColumn(
                $headers,
                ['TIPE', 'TIPE FILE', 'JENIS FILE']
            ),
            'url' => $this->findColumn(
                $headers,
                ['LINK DRIVE', 'LINK FILE', 'LINK', 'URL', 'TAUTAN']
            ),
            'date' => $this->findColumn(
                $headers,
                ['TANGGAL', 'TANGGAL UPLOAD', 'TANGGAL UPDATE', 'UPDATED AT']
            ),
            'access' => $this->findColumn(
                $headers,
                ['AKSES', 'HAK AKSES', 'DILIHAT OLEH']
            ),
            'status' => $this->findColumn(
                $headers,
                ['STATUS', 'STATUS FILE', 'TAMPIL']
            ),
            'order' => $this->findColumn(
                $headers,
                ['URUTAN', 'NO URUT', 'ORDER', 'SORT']
            ),
        ];

        $files = [];

        foreach (array_slice($rows, $headerIndex + 1) as $rowIndex => $row) {
            $url = $this->cell($row, $columns['url']);

            if ($url === '') {
                $url = $this->firstUrl($row);
            }

            if (! $this->isAllowedDriveUrl($url)) {
                continue;
            }

            $status = strtoupper($this->cell($row, $columns['status']));

            if (
                $status !== ''
                && ! in_array(
                    $status,
                    ['AKTIF', 'ACTIVE', 'YA', 'YES', 'TAMPIL'],
                    true
                )
            ) {
                continue;
            }

            $title = $this->cell($row, $columns['title']);

            if ($title === '') {
                $title = $this->fallbackTitle($row, $url);
            }

            if ($title === '') {
                $title = 'File ' . ($rowIndex + 1);
            }

            $type = strtoupper($this->cell($row, $columns['type']));

            if ($type === '') {
                $type = $this->inferType($url);
            }

            $dateRaw = $this->cell($row, $columns['date']);

            $files[] = [
                'id' => $this->cell($row, $columns['id'])
                    ?: (string) ($rowIndex + 1),
                'title' => $title,
                'category' => $this->cell($row, $columns['category'])
                    ?: 'Lainnya',
                'description' => $this->cell(
                    $row,
                    $columns['description']
                ),
                'type' => $type,
                'url' => $url,
                'date' => $this->formatDate($dateRaw),
                'access' => strtoupper(
                    $this->cell($row, $columns['access']) ?: 'SEMUA'
                ),
                'order' => (int) (
                    $this->cell($row, $columns['order']) ?: 999999
                ),
            ];
        }

        usort(
            $files,
            function (array $left, array $right): int {
                $orderComparison = $left['order'] <=> $right['order'];

                return $orderComparison !== 0
                    ? $orderComparison
                    : strnatcasecmp($left['title'], $right['title']);
            }
        );

        return $files;
    }

    private function findHeaderRow(array $rows): int
    {
        $keywords = [
            'ID',
            'NO',
            'JUDUL',
            'NAMA FILE',
            'KATEGORI',
            'DESKRIPSI',
            'TIPE',
            'LINK DRIVE',
            'LINK',
            'URL',
            'TANGGAL',
            'AKSES',
            'STATUS',
            'URUTAN',
        ];

        $bestIndex = 0;
        $bestScore = -1;

        foreach (array_slice($rows, 0, 15, true) as $index => $row) {
            $headers = array_map(
                fn ($value): string => $this->normalizeHeader($value),
                $row
            );

            $score = 0;

            foreach ($keywords as $keyword) {
                if (in_array($keyword, $headers, true)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = (int) $index;
            }
        }

        return $bestIndex;
    }

    private function findColumn(
        array $headers,
        array $aliases
    ): ?int {
        foreach ($aliases as $alias) {
            $index = array_search(
                $this->normalizeHeader($alias),
                $headers,
                true
            );

            if ($index !== false) {
                return (int) $index;
            }
        }

        return null;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = mb_strtoupper(trim((string) $value));
        $header = preg_replace('/[^A-Z0-9]+/u', ' ', $header) ?? '';

        return trim(preg_replace('/\s+/', ' ', $header) ?? '');
    }

    private function cell(array $row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    private function firstUrl(array $row): string
    {
        foreach ($row as $value) {
            $value = trim((string) $value);

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
        }

        return '';
    }

    private function fallbackTitle(array $row, string $url): string
    {
        foreach ($row as $value) {
            $value = trim((string) $value);

            if (
                $value === ''
                || $value === $url
                || filter_var($value, FILTER_VALIDATE_URL)
                || ctype_digit($value)
            ) {
                continue;
            }

            return $value;
        }

        return '';
    }

    private function isAllowedDriveUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array(
            $host,
            ['drive.google.com', 'docs.google.com'],
            true
        );
    }

    private function inferType(string $url): string
    {
        if (str_contains($url, '/folders/')) {
            return 'FOLDER';
        }

        if (str_contains($url, 'docs.google.com/document/')) {
            return 'DOKUMEN';
        }

        if (str_contains($url, 'docs.google.com/spreadsheets/')) {
            return 'SPREADSHEET';
        }

        if (str_contains($url, 'docs.google.com/presentation/')) {
            return 'PRESENTASI';
        }

        return 'FILE';
    }

    private function formatDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->translatedFormat('d M Y');
        } catch (Throwable) {
            return $value;
        }
    }

    private function rowHasValue(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}
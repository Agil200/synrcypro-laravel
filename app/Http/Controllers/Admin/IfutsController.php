<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IfutsTicketInputRequest;
use App\Services\IfutsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class IfutsController extends Controller
{
    private const PER_PAGE_OPTIONS = [20, 30, 50, 100];

    public function index(
        Request $request,
        IfutsService $ifutsService
    ): View {
        $currentYear = (int) now('Asia/Jakarta')->year;
        $selectedYear = $this->resolveYear($request, $currentYear);

        [$ifutsDashboard, $ifutsIntegration, $ifutsSource] =
            $this->loadIfuts(
                $request,
                $ifutsService,
                $selectedYear,
                false
            );

        /*
        |--------------------------------------------------------------------------
        | Dashboard = PURE ANALYTIC
        |--------------------------------------------------------------------------
        | Dashboard tidak membawa rows ke Blade. Semua rows hanya digunakan
        | service untuk menghitung summary + chart.
        */
        $ifutsDashboard['rows'] = [];

        return view(
            'admin-all.ifuts.index',
            compact(
                'ifutsDashboard',
                'ifutsIntegration',
                'ifutsSource'
            )
        );
    }

    public function monitoring(
        Request $request,
        IfutsService $ifutsService
    ): View {
        $currentYear = (int) now('Asia/Jakarta')->year;
        $selectedYear = $this->resolveYear($request, $currentYear);

        [$ifutsDashboard, $ifutsIntegration, $ifutsSource] =
            $this->loadIfuts(
                $request,
                $ifutsService,
                $selectedYear,
                true
            );

        /*
        |--------------------------------------------------------------------------
        | Pagination Monitoring Ticket
        |--------------------------------------------------------------------------
        | Google Sheet tetap READ ONLY. Pagination dilakukan pada hasil filter
        | yang sudah ada di Laravel sehingga view tidak merender 1.000+ rows.
        */
        $perPage = (int) $request->query('per_page', 20);

        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 20;
        }

        $page = max(1, (int) $request->query('page', 1));

        $filteredRows = is_array($ifutsDashboard['rows'] ?? null)
            ? $ifutsDashboard['rows']
            : [];

        $total = count($filteredRows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $offset = ($page - 1) * $perPage;
        $pagedRows = array_slice($filteredRows, $offset, $perPage);

        $ifutsDashboard['rows'] = $pagedRows;
        $ifutsDashboard['pagination'] = [
            'page' => $page,
            'per_page' => $perPage,
            'per_page_options' => self::PER_PAGE_OPTIONS,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => $total > 0
                ? min($offset + $perPage, $total)
                : 0,
        ];

        return view(
            'admin-all.ifuts.monitoring',
            compact(
                'ifutsDashboard',
                'ifutsIntegration',
                'ifutsSource'
            )
        );
    }


public function detail(
    int $sheetRow,
    IfutsService $ifutsService
): View {
    abort_if($sheetRow < 1, 404);

    $ifutsIntegration = [
        'connected' => false,
        'message' => null,
    ];

    $ifutsSource = [
        'spreadsheet_url' => $ifutsService->spreadsheetUrl(),
        'header_row' => null,
        'column_map' => [],
    ];

    $ticket = null;

    try {
        $ifutsData = $ifutsService->getData();

        foreach (($ifutsData['rows'] ?? []) as $row) {
            if (
                (int) ($row['_SHEET_ROW'] ?? 0)
                === $sheetRow
            ) {
                $ticket = $row;
                break;
            }
        }

        abort_if($ticket === null, 404);

        $ifutsSource = array_merge(
            $ifutsSource,
            $ifutsData['source'] ?? []
        );

        $ifutsIntegration = [
            'connected' => true,
            'message' => null,
        ];
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
        throw $e;
    } catch (Throwable $e) {
        report($e);

        $ifutsIntegration = [
            'connected' => false,
            'message' => app()->isLocal()
                ? $e->getMessage()
                : 'Integrasi Google Sheets IFUTS sedang tidak tersedia.',
        ];
    }

    return view(
        'admin-all.ifuts.detail',
        compact(
            'ticket',
            'sheetRow',
            'ifutsIntegration',
            'ifutsSource'
        )
    );
}


public function input(): View
{
    return view(
        'admin-all.ifuts.input',
        [
            'ifutsFormOptions' => [
                'categories' => ['TIKET', 'TRAVEL', 'NON TIKET'],
                'locality_statuses' => ['LOKAL', 'NON LOKAL'],
                'ticket_types' => ['REGULER', 'TAMBAHAN'],
                'locations_in' => ['PALEMBANG', 'SITE'],
                'department' => 'PRODUKSI',
            ],
        ]
    );
}

public function validateInput(
    IfutsTicketInputRequest $request
): RedirectResponse {
    $validated = $request->validated();
    $validated['department'] = 'PRODUKSI';

    // STEP 3A: validasi saja. Belum ada write ke Google Sheet.
    return redirect()
        ->route('admin-all.ifuts.input')
        ->with('ifuts_input_validated', true);
}

    private function loadIfuts(
        Request $request,
        IfutsService $ifutsService,
        int $selectedYear,
        bool $withSearch
    ): array {
        $ifutsIntegration = [
            'connected' => false,
            'message' => null,
        ];

        $ifutsDashboard = $ifutsService->emptyDashboard();

        $ifutsSource = [
            'spreadsheet_url' => $ifutsService->spreadsheetUrl(),
            'header_row' => null,
            'column_map' => [],
        ];

        try {
            $ifutsData = $ifutsService->getData();

            $ifutsDashboard = $ifutsService->buildDashboard(
                $ifutsData['rows'] ?? [],
                [
                    'month' => $request->query('month'),
                    'year' => $selectedYear,
                    'category' => $request->query('category'),
                    'position' => $request->query('position'),
                    'poh' => $request->query('poh'),
                    'search' => $withSearch
                        ? $request->query('search')
                        : null,
                ]
            );

            $ifutsSource = array_merge(
                $ifutsSource,
                $ifutsData['source'] ?? []
            );

            $ifutsIntegration = [
                'connected' => true,
                'message' => null,
            ];
        } catch (Throwable $e) {
            report($e);

            $ifutsDashboard['filters']['year'] = $selectedYear;

            $ifutsIntegration = [
                'connected' => false,
                'message' => app()->isLocal()
                    ? $e->getMessage()
                    : 'Integrasi Google Sheets IFUTS sedang tidak tersedia.',
            ];
        }

        return [
            $ifutsDashboard,
            $ifutsIntegration,
            $ifutsSource,
        ];
    }

    private function resolveYear(
        Request $request,
        int $currentYear
    ): int {
        $requestedYear = trim(
            (string) $request->query('year', '')
        );

        return preg_match('/^\d{4}$/', $requestedYear) === 1
            ? (int) $requestedYear
            : $currentYear;
    }
}
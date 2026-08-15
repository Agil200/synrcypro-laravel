<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SuggestionSystemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class AdminAllController extends Controller
{
    public function index(
        SuggestionSystemService $suggestionService
    ): View {
        $modules = collect(
            config('admin_all.modules', [])
        );

        $archiveFolders = collect(
            config('admin_all.e_archive', [])
        );

        $summary = [
            'modules' => $modules->count(),

            'sheet_integrations' => $modules
                ->whereIn(
                    'source',
                    ['Google Sheets', 'Sheet PRODUKSI']
                )
                ->count(),

            'archive_folders' => $archiveFolders->count(),

            'active_modules' => $modules
                ->where('status', 'aktif')
                ->count(),
        ];

        /*
         * Control Center default.
         * Dashboard Admin All tetap hidup walaupun Google Sheets bermasalah.
         */
        $controlCenter = [
            'suggestion' => [
                'connected' => false,
                'total' => 0,
                'submitted' => 0,
                'verified_gl_qcc' => 0,
                'pending_gl_qcc' => 0,
                'pending_sh' => 0,
                'latest' => [],
                'message' => 'Menunggu koneksi Suggestion System.',
            ],

            'integrations' => [
                'google_sheets' => false,
                'laravel' => true,
                'google_drive' => $archiveFolders->count() > 0,
            ],
        ];

        try {
            $suggestionData =
                $suggestionService->getData();

            $rows =
                $suggestionData['database']['rows'] ?? [];

            $analytics =
                $suggestionService->buildDashboard($rows);

            $submitted = 0;
            $verifiedGlQcc = 0;
            $pendingGlQcc = 0;
            $pendingSh = 0;

            foreach ($rows as $row) {
                $status = strtoupper(
                    trim((string) ($row['STATUS'] ?? ''))
                );

                $statusGlQcc = strtoupper(
                    trim(
                        (string) ($row['STATUS_GL_QCC'] ?? '')
                    )
                );

                $statusSh = strtoupper(
                    trim(
                        (string) ($row['STATUS_SH'] ?? '')
                    )
                );

                if ($status === 'SUBMITTED') {
                    $submitted++;
                }

                if ($status === 'VERIFIED_GL_QCC') {
                    $verifiedGlQcc++;
                }

                if (
                    in_array(
                        $statusGlQcc,
                        ['PENDING', 'WAITING'],
                        true
                    )
                ) {
                    $pendingGlQcc++;
                }

                if (
                    in_array(
                        $statusSh,
                        ['PENDING'],
                        true
                    )
                ) {
                    $pendingSh++;
                }
            }

            $latest = array_slice(
                $analytics['rows'] ?? [],
                0,
                5
            );

            $controlCenter['suggestion'] = [
                'connected' => true,
                'total' => count($rows),
                'submitted' => $submitted,
                'verified_gl_qcc' => $verifiedGlQcc,
                'pending_gl_qcc' => $pendingGlQcc,
                'pending_sh' => $pendingSh,
                'latest' => $latest,
                'message' => null,
            ];

            $controlCenter['integrations']['google_sheets'] =
                true;
        } catch (Throwable $e) {
            report($e);

            $controlCenter['suggestion']['message'] =
                app()->isLocal()
                    ? $e->getMessage()
                    : 'Suggestion System sedang tidak tersedia.';
        }

        return view(
            'admin-all.dashboard',
            compact(
                'modules',
                'archiveFolders',
                'summary',
                'controlCenter'
            )
        );
    }


    public function suggestionMonitoring(
        Request $request,
        SuggestionSystemService $suggestionService
    ): View {
        $suggestion = config(
            'admin_all.suggestion_system',
            []
        );

        $suggestionData = [
            'database' => [
                'headers' => [],
                'rows' => [],
                'total' => 0,
            ],

            'access_atasan' => [
                'headers' => [],
                'rows' => [],
                'total' => 0,
                'active_rows' => [],
                'active_total' => 0,
                'status_column_found' => false,
            ],
        ];

        $suggestionIntegration = [
            'connected' => false,
            'message' => null,
        ];

        $suggestionAccess = [
            'allowed' => false,
            'access' => null,
            'name' => null,
            'nrp' => null,
            'position' => null,
            'department' => null,
            'email' => auth()->user()?->email,
            'status' => null,
            'source' => 'ACCESS_ATASAN',
            'message' => 'Akses belum diverifikasi.',
        ];

        $monitoring = [
            'filters' => [
                'month' => null,
                'year' => null,
                'status' => null,
                'nrp' => null,
                'q' => null,
            ],
            'available_years' => [],
            'status_options' => [],
            'period_total' => 0,
            'filtered_total' => 0,
            'rows' => [],
        ];

        $monitoringRows = new LengthAwarePaginator(
            [],
            0,
            20,
            1,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        try {
            $suggestionData =
                $suggestionService->getData();

            $currentUser = auth()->user();

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'],
                    $currentUser?->email
                );

            $month = $request->filled('month')
                ? (int) $request->query('month')
                : null;

            $year = $request->filled('year')
                ? (int) $request->query('year')
                : null;

            $status = $request->filled('status')
                ? (string) $request->query('status')
                : null;

            $nrp = $request->filled('nrp')
                ? (string) $request->query('nrp')
                : null;

            $search = $request->filled('q')
                ? (string) $request->query('q')
                : null;

            $monitoring =
                $suggestionService->buildMonitoring(
                    $suggestionData['database']['rows'],
                    $month,
                    $year,
                    $status,
                    $nrp,
                    $search
                );

            $page = max(
                1,
                (int) $request->query('page', 1)
            );

            $perPage = 20;

            $allRows = collect(
                $monitoring['rows'] ?? []
            );

            $monitoringRows = new LengthAwarePaginator(
                $allRows
                    ->forPage($page, $perPage)
                    ->values()
                    ->all(),

                $allRows->count(),
                $perPage,
                $page,

                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $suggestionIntegration = [
                'connected' => true,
                'message' => null,
            ];
        } catch (Throwable $e) {
            report($e);

            $suggestionIntegration = [
                'connected' => false,

                'message' => app()->isLocal()
                    ? $e->getMessage()
                    : 'Integrasi Google Sheets sedang tidak tersedia.',
            ];
        }

        return view(
            'admin-all.suggestion.monitoring',
            compact(
                'suggestion',
                'suggestionData',
                'suggestionIntegration',
                'suggestionAccess',
                'monitoring',
                'monitoringRows'
            )
        );
    }

    public function suggestion(
        Request $request,
        SuggestionSystemService $suggestionService
    ): View {
        $suggestion = config(
            'admin_all.suggestion_system',
            []
        );

        /*
         * Default aman.
         * Kalau Google API error, halaman tetap terbuka.
         */
        $suggestionData = [
            'database' => [
                'headers' => [],
                'rows' => [],
                'total' => 0,
            ],

            'access_atasan' => [
                'headers' => [],
                'rows' => [],
                'total' => 0,
                'active_rows' => [],
                'active_total' => 0,
                'status_column_found' => false,
            ],
        ];

        $suggestionIntegration = [
            'connected' => false,
            'message' => null,
        ];

        $suggestionAccess = [
            'allowed' => false,
            'access' => null,
            'name' => null,
            'nrp' => null,
            'position' => null,
            'department' => null,
            'email' => auth()->user()?->email,
            'status' => null,
            'source' => 'ACCESS_ATASAN',
            'message' => 'Akses belum diverifikasi.',
        ];

        $suggestionDashboard = [
            'filters' => [
                'month' => null,
                'year' => null,
                'status' => null,
                'nrp' => null,
            ],
            'available_years' => [],
            'total' => 0,
            'status_chart' => [],
            'top_names' => [],
            'rows' => [],
            'data_total' => 0,
        ];

        try {
            $suggestionData =
                $suggestionService->getData();

            $currentUser = auth()->user();

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'],
                    $currentUser?->email
                );

            $month = $request->filled('month')
                ? (int) $request->query('month')
                : null;

            $year = $request->filled('year')
                ? (int) $request->query('year')
                : null;

            $status = $request->filled('status')
                ? (string) $request->query('status')
                : null;

            $nrp = $request->filled('nrp')
                ? (string) $request->query('nrp')
                : null;

            $suggestionDashboard =
                $suggestionService->buildDashboard(
                    $suggestionData['database']['rows'],
                    $month,
                    $year,
                    $status,
                    $nrp
                );

            $suggestionIntegration = [
                'connected' => true,
                'message' => null,
            ];
        } catch (Throwable $e) {
            report($e);

            $suggestionIntegration = [
                'connected' => false,

                'message' => app()->isLocal()
                    ? $e->getMessage()
                    : 'Integrasi Google Sheets sedang tidak tersedia.',
            ];
        }

        return view(
            'admin-all.suggestion.index',
            compact(
                'suggestion',
                'suggestionData',
                'suggestionIntegration',
                'suggestionAccess',
                'suggestionDashboard'
            )
        );
    }
}

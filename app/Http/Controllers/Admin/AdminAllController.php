<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SuggestionSystemService;
use App\Services\SuggestionWorkflowBridgeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
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


    public function suggestionVerificationGl(
        SuggestionSystemService $suggestionService
    ): View {
        $suggestion = config(
            'admin_all.suggestion_system',
            []
        );

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
            'message' => 'View only.',
        ];

        $glQueue = [
            'summary' => [
                'pending' => 0,
                'revision' => 0,
                'verified' => 0,
                'rejected' => 0,
                'total' => 0,
            ],
            'rows' => [],
        ];

        $canReviewGl = false;

        try {
            $suggestionData =
                $suggestionService->getData();

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    auth()->user()?->email
                );

            $canReviewGl =
                $suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'GL_QCC'
                );

            $glQueue =
                $suggestionService->buildGlVerificationQueue(
                    $suggestionData['database']['rows'] ?? []
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
                    : 'Integrasi Suggestion System sedang tidak tersedia.',
            ];
        }

        return view(
            'admin-all.suggestion.verification-gl',
            compact(
                'suggestion',
                'suggestionIntegration',
                'suggestionAccess',
                'canReviewGl',
                'glQueue'
            )
        );
    }


    public function suggestionVerificationGlBridgeCheck(
        SuggestionSystemService $suggestionService,
        SuggestionWorkflowBridgeService $bridgeService
    ): RedirectResponse {
        try {
            $suggestionData =
                $suggestionService->getData();

            $email = (string) (
                auth()->user()?->email
                ?? ''
            );

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    $email
                );

            if (
                !$suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'GL_QCC'
                )
            ) {
                abort(
                    403,
                    'Akun tidak memiliki akses Verifikasi GL / QCC.'
                );
            }

            $result =
                $bridgeService->ping($email);

            if (($result['success'] ?? false) !== true) {
                return back()->with(
                    'error',
                    (string) (
                        $result['message']
                        ?? 'Bridge Apps Script belum siap.'
                    )
                );
            }

            return back()->with(
                'success',
                'Bridge Apps Script terhubung. '
                .'Akses reviewer: '
                .(string) (
                    $result['access']['akses']
                    ?? $suggestionAccess['access']
                    ?? '-'
                )
                .'.'
            );
        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                app()->isLocal()
                    ? $e->getMessage()
                    : 'Bridge Apps Script tidak dapat dihubungi.'
            );
        }
    }


    public function suggestionVerificationGlAction(
        Request $request,
        string $noSs,
        SuggestionSystemService $suggestionService,
        SuggestionWorkflowBridgeService $bridgeService
    ): RedirectResponse {
        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in([
                    'VERIFIED',
                    'REVISION',
                    'REJECTED',
                ]),
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $decision = strtoupper(
            trim(
                (string) $validated['decision']
            )
        );

        $note = trim(
            (string) (
                $validated['note']
                ?? ''
            )
        );

        if (
            in_array(
                $decision,
                ['REVISION', 'REJECTED'],
                true
            )
            && mb_strlen($note) < 5
        ) {
            return back()
                ->withErrors([
                    'note' =>
                        'Catatan / alasan minimal 5 karakter '
                        .'untuk REVISI atau TOLAK.',
                ])
                ->withInput();
        }

        try {
            /*
             * SECURITY CHECK #1:
             * Laravel mengecek email login terhadap ACCESS_ATASAN.
             */
            $suggestionData =
                $suggestionService->getData();

            $email = (string) (
                auth()->user()?->email
                ?? ''
            );

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    $email
                );

            if (
                !$suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'GL_QCC'
                )
            ) {
                abort(
                    403,
                    'Akun tidak memiliki akses Verifikasi GL / QCC.'
                );
            }

            $row =
                $suggestionService->findByNoSs(
                    $suggestionData['database']['rows'] ?? [],
                    $noSs
                );

            if ($row === null) {
                abort(
                    404,
                    'Suggestion System tidak ditemukan.'
                );
            }

            $currentStatus =
                $this->normalizeWorkflowStatus(
                    $row['STATUS'] ?? ''
                );

            if (
                !in_array(
                    $currentStatus,
                    [
                        'SUBMITTED',
                        'REVISION_GL_QCC',
                    ],
                    true
                )
            ) {
                return back()->with(
                    'error',
                    'Suggestion ini tidak lagi berada '
                    .'pada tahap GL / QCC. Refresh data.'
                );
            }

            /*
             * SECURITY CHECK #2 + WRITE:
             * Request ditandatangani HMAC dan dikirim ke Apps Script.
             * Apps Script kembali mengecek ACCESS_ATASAN,
             * role, status workflow, transition, dan audit trail.
             */
            $result =
                $bridgeService->updateGl(
                    $email,
                    $noSs,
                    $decision,
                    $note
                );

            if (($result['success'] ?? false) !== true) {
                return back()->with(
                    'error',
                    (string) (
                        $result['message']
                        ?? 'Workflow Apps Script menolak request.'
                    )
                );
            }

            return redirect()
                ->route(
                    'admin-all.suggestion.detail',
                    [
                        'noSs' => $noSs,
                    ]
                )
                ->with(
                    'success',
                    (string) (
                        $result['message']
                        ?? 'Status berhasil diperbarui.'
                    )
                );
        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with(
                'error',
                app()->isLocal()
                    ? $e->getMessage()
                    : 'Proses workflow gagal dijalankan.'
            );
        }
    }


    public function suggestionApprovalSh(
        SuggestionSystemService $suggestionService
    ): View {
        $suggestion = config(
            'admin_all.suggestion_system',
            []
        );

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
            'message' => 'View only.',
        ];

        $shQueue = [
            'summary' => [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0,
            ],
            'rows' => [],
        ];

        $canReviewSh = false;

        try {
            $suggestionData =
                $suggestionService->getData();

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    auth()->user()?->email
                );

            $canReviewSh =
                $suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'SH'
                );

            $rows =
                $suggestionData['database']['rows'] ?? [];

            /*
             * SH non-ADMIN hanya melihat Suggestion departemen yang sama.
             * ADMIN tetap dapat melihat seluruh queue.
             */
            $accessRole = strtoupper(
                trim((string) ($suggestionAccess['access'] ?? ''))
            );

            $reviewerDepartment = strtoupper(
                trim((string) ($suggestionAccess['department'] ?? ''))
            );

            if (
                $accessRole === 'SH'
                && $reviewerDepartment !== ''
            ) {
                $rows = array_values(
                    array_filter(
                        $rows,
                        static function (mixed $row) use (
                            $reviewerDepartment
                        ): bool {
                            if (!is_array($row)) {
                                return false;
                            }

                            return strtoupper(
                                trim(
                                    (string) ($row['DEPARTEMEN'] ?? '')
                                )
                            ) === $reviewerDepartment;
                        }
                    )
                );
            }

            $shQueue =
                $suggestionService->buildShApprovalQueue(
                    $rows
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
                    : 'Integrasi Suggestion System sedang tidak tersedia.',
            ];
        }

        return view(
            'admin-all.suggestion.approval-sh',
            compact(
                'suggestion',
                'suggestionIntegration',
                'suggestionAccess',
                'canReviewSh',
                'shQueue'
            )
        );
    }


    public function suggestionApprovalShAction(
        Request $request,
        string $noSs,
        SuggestionSystemService $suggestionService,
        SuggestionWorkflowBridgeService $bridgeService
    ): RedirectResponse {
        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in([
                    'APPROVED',
                    'REJECTED',
                ]),
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $decision = strtoupper(
            trim(
                (string) $validated['decision']
            )
        );

        $note = trim(
            (string) (
                $validated['note']
                ?? ''
            )
        );

        /*
         * Penolakan SH wajib memiliki alasan yang dapat diaudit.
         * Approval boleh tanpa catatan.
         */
        if (
            $decision === 'REJECTED'
            && mb_strlen($note) < 5
        ) {
            return back()
                ->withErrors([
                    'note' =>
                        'Catatan / alasan minimal 5 karakter '
                        .'untuk TOLAK.',
                ])
                ->withInput();
        }

        try {
            /*
             * SECURITY CHECK #1 — LARAVEL
             * Email login harus aktif di ACCESS_ATASAN dan berhak tahap SH.
             */
            $suggestionData =
                $suggestionService->getData();

            $email = (string) (
                auth()->user()?->email
                ?? ''
            );

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    $email
                );

            if (
                !$suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'SH'
                )
            ) {
                abort(
                    403,
                    'Akun tidak memiliki akses Persetujuan SH.'
                );
            }

            $row =
                $suggestionService->findByNoSs(
                    $suggestionData['database']['rows'] ?? [],
                    $noSs
                );

            if ($row === null) {
                abort(
                    404,
                    'Suggestion System tidak ditemukan.'
                );
            }

            /*
             * SH hanya boleh memproses data setelah GL/QCC VERIFIED.
             */
            $currentStatus =
                $this->normalizeWorkflowStatus(
                    $row['STATUS'] ?? ''
                );

            if ($currentStatus !== 'VERIFIED_GL_QCC') {
                return back()->with(
                    'error',
                    'Suggestion ini tidak lagi berada '
                    .'pada tahap Persetujuan SH. Refresh data.'
                );
            }

            /*
             * SECURITY CHECK #2 + WRITE — APPS SCRIPT
             * Request HMAC -> Apps Script -> ACCESS_ATASAN ->
             * reviewerCanStage_(SH) -> validateWorkflowTransition_() ->
             * updateWorkflowStatus() existing.
             */
            $result =
                $bridgeService->updateSh(
                    $email,
                    $noSs,
                    $decision,
                    $note
                );

            if (($result['success'] ?? false) !== true) {
                return back()->withInput()->with(
                    'error',
                    (string) (
                        $result['message']
                        ?? 'Workflow Apps Script menolak request SH.'
                    )
                );
            }

            return redirect()
                ->route(
                    'admin-all.suggestion.detail',
                    [
                        'noSs' => $noSs,
                        'from' => 'sh',
                    ]
                )
                ->with(
                    'success',
                    (string) (
                        $result['message']
                        ?? 'Persetujuan SH berhasil diperbarui.'
                    )
                );
        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with(
                'error',
                app()->isLocal()
                    ? $e->getMessage()
                    : 'Proses Persetujuan SH gagal dijalankan.'
            );
        }
    }


    public function suggestionDetail(
        string $noSs,
        SuggestionSystemService $suggestionService
    ): View {
        $suggestion = config(
            'admin_all.suggestion_system',
            []
        );

        /*
         * Default akses = VIEW ONLY.
         * Pada STEP 5A belum ada write/approval.
         */
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
            'message' => 'View only.',
        ];

        $suggestionIntegration = [
            'connected' => false,
            'message' => null,
        ];

        try {
            $suggestionData =
                $suggestionService->getData();

            $suggestionRow =
                $suggestionService->findByNoSs(
                    $suggestionData['database']['rows'] ?? [],
                    $noSs
                );

            if ($suggestionRow === null) {
                abort(
                    404,
                    'Data Suggestion System tidak ditemukan.'
                );
            }

            $currentUser = auth()->user();

            $suggestionAccess =
                $suggestionService->resolveAccess(
                    $suggestionData['access_atasan']['active_rows'] ?? [],
                    $currentUser?->email
                );

            $canReviewGl =
                $suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'GL_QCC'
                );

            $canReviewSh =
                $suggestionService->canAccessWorkflowStage(
                    $suggestionAccess,
                    'SH'
                );

            $detailStatus =
                $this->normalizeWorkflowStatus(
                    $suggestionRow['STATUS'] ?? ''
                );

            $canActGl =
                $canReviewGl
                && in_array(
                    $detailStatus,
                    [
                        'SUBMITTED',
                        'REVISION_GL_QCC',
                    ],
                    true
                );

            /*
             * STEP 7C: SH/ADMIN hanya dapat melakukan aksi ketika
             * status utama tepat berada pada VERIFIED_GL_QCC.
             */
            $canActSh =
                $canReviewSh
                && $detailStatus === 'VERIFIED_GL_QCC';

            $suggestionIntegration = [
                'connected' => true,
                'message' => null,
            ];

            return view(
                'admin-all.suggestion.detail',
                compact(
                    'suggestion',
                    'suggestionRow',
                    'suggestionIntegration',
                    'suggestionAccess',
                    'canReviewGl',
                    'canActGl',
                    'canReviewSh',
                    'canActSh'
                )
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            abort(
                500,
                app()->isLocal()
                    ? $e->getMessage()
                    : 'Detail Suggestion System sedang tidak tersedia.'
            );
        }
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

    /*
    |--------------------------------------------------------------------------
    | Normalisasi status workflow Google Sheets
    |--------------------------------------------------------------------------
    |
    | Google Sheets dapat membawa NBSP / zero-width character / simbol
    | tersembunyi walaupun di UI terlihat "Submitted".
    |
    | Gate workflow memakai nilai canonical:
    | SUBMITTED, REVISION_GL_QCC, VERIFIED_GL_QCC, dst.
    |
    */

    private function normalizeWorkflowStatus(
        mixed $status
    ): string {
        $value = strtoupper(
            trim((string) $status)
        );

        /*
         * Bersihkan whitespace Unicode yang tidak selalu dibuang trim().
         */
        $value = preg_replace(
            '/[\x{00A0}\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u',
            ' ',
            $value
        ) ?? $value;

        /*
         * Semua separator menjadi underscore agar:
         * "REVISION GL QCC" -> "REVISION_GL_QCC"
         * "SUBMITTED "      -> "SUBMITTED"
         */
        $value = preg_replace(
            '/[^A-Z0-9]+/u',
            '_',
            $value
        ) ?? $value;

        return trim(
            $value,
            '_'
        );
    }

}
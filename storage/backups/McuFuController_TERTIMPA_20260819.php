<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McuFuInternalHistory;
use App\Services\McuFuInternalService;
use App\Services\EmployeeLifecycleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Throwable;

class McuFuInternalController extends Controller
{
    public function __construct(
        private readonly McuFuInternalService $mcuFu,
        private readonly EmployeeLifecycleService $employeeLifecycle
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $year = $this->validatedYear(
            $request->query(
                'year',
                now()->year
            )
        );

        $month = $this->validatedMonth(
            $request->query('month')
        );

        $jabatan = trim(
            (string) $request->query(
                'jabatan',
                ''
            )
        );

        $jabatan = $jabatan !== ''
            ? $jabatan
            : null;

        try {
            $dashboard = $this->mcuFu->dashboard(
                $year,
                $month,
                $jabatan
            );

            $error = null;
        } catch (Throwable $e) {
            report($e);

            $dashboard = [
                'summary' => [
                    'total_data' => 0,
                    'mcu_done' => 0,
                    'fit_to_work' => 0,
                    'hasil_follow_up' => 0,
                    'fu_active' => 0,
                    'fu_completed' => 0,
                ],
                'hasil_mcu' => [],
                'status_mcu' => [],
                'status_fu' => [],
                'jabatan' => [],
                'follow_up' => [],
                'follow_up_1_detail' => [],
                'follow_up_2_detail' => [],
                'follow_up_3_detail' => [],
                'filters' => [
                    'year' => $year,
                    'month' => $month,
                    'jabatan' => $jabatan,
                    'years' => [now()->year],
                    'jabatan_options' => [],
                    'date_field' => 'JADWAL MCU',
                    'total_all' => 0,
                ],
            ];

            $error = $e->getMessage();
        }

        return view(
            'admin-all.mcu-fu.index',
            compact(
                'dashboard',
                'error'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prioritas & Reminder
    |--------------------------------------------------------------------------
    */

    public function priority(
        Request $request
    ): View {
        $error = null;

        try {
            $priority = $this->mcuFu->priorityData(
                [
                    'type' => $request->query(
                        'type',
                        'all'
                    ),
                    'bucket' => $request->query(
                        'bucket'
                    ),
                    'search' => $request->query(
                        'q'
                    ),
                ]
            );
        } catch (Throwable $e) {
            report($e);

            $priority = [
                'tasks' => [],
                'summary' => [
                    'total' => 0,
                    'urgent' => 0,
                    'expired' => 0,
                    'overdue_fu' => 0,
                    'h7' => 0,
                    'h14' => 0,
                    'h30' => 0,
                    'h40' => 0,
                    'pending_fu' => 0,
                ],
                'filters' => [
                    'type' => 'all',
                    'bucket' => '',
                    'search' => null,
                ],
            ];

            $error = $e->getMessage();
        }

        $requestedPerPage = (int) $request->query(
            'per_page',
            20
        );

        $perPage = in_array(
            $requestedPerPage,
            [20, 50, 100],
            true
        )
            ? $requestedPerPage
            : 20;

        $data = $this->paginateArray(
            $priority['tasks'],
            $request,
            $perPage
        );

        return view(
            'admin-all.mcu-fu.priority',
            [
                'data' => $data,
                'summary' => $priority['summary'],
                'filters' => $priority['filters'],
                'perPage' => $perPage,
                'error' => $error,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Lifecycle — MASTER_DATABASE pipeline
    |--------------------------------------------------------------------------
    */

    public function employeeLookup(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'nrp' => [
                'required',
                'string',
                'max:40',
            ],
        ]);

        try {
            $employee =
                $this->employeeLifecycle
                    ->findByNrp(
                        $validated['nrp']
                    );

            return response()->json([
                'ok' => true,
                'found' => $employee !== null,
                'employee' => $employee,
                'status_options' =>
                    $this->employeeLifecycle
                        ->statusOptions(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(
                [
                    'ok' => false,
                    'found' => false,
                    'message' => $e->getMessage(),
                ],
                422
            );
        }
    }

    public function storeEmployee(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'nrp' => [
                'required',
                'string',
                'max:40',
            ],
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:150',
            ],
            'departemen' => [
                'required',
                'string',
                'max:100',
            ],
            'site' => [
                'required',
                'string',
                'max:100',
            ],
            'status_karyawan' => [
                'required',
                'in:NEW HIRE,EXISTING DATA',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        try {
            $user = Auth::user();

            $result =
                $this->employeeLifecycle
                    ->submitNewEmployee(
                        $validated,
                        $user?->name,
                        $user?->email
                    );

            $this->mcuFu
                ->invalidateReadCache();

            return redirect()
                ->route(
                    'admin-all.mcu-fu.update',
                    [
                        'q' => $result['nrp'],
                        'per_page' => $request->input(
                            '_return_per_page',
                            20
                        ),
                    ]
                )
                ->with(
                    'success',
                    'Karyawan berhasil dikirim melalui pipeline UPDATE_DATA_KARYAWAN. MASTER_DATABASE akan menjadi sumber final setelah sinkronisasi.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function updateEmployeeLifecycle(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'nrp' => [
                'required',
                'string',
                'max:40',
            ],
            'status_baru' => [
                'required',
                'in:NEW HIRE,EXISTING DATA,RESIGN,MUTASI,TERMINATED',
            ],
            'site_baru' => [
                'nullable',
                'string',
                'max:100',
                'required_if:status_baru,MUTASI',
            ],
            'tanggal_efektif' => [
                'required',
                'date',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        try {
            $user = Auth::user();

            $result =
                $this->employeeLifecycle
                    ->submitLifecycleUpdate(
                        $validated,
                        $user?->name,
                        $user?->email
                    );

            $this->mcuFu
                ->invalidateReadCache();

            $hidden = in_array(
                $result['status_baru'],
                [
                    'RESIGN',
                    'MUTASI',
                    'TERMINATED',
                ],
                true
            );

            $message = $hidden
                ? (
                    $result['nama'] .
                    ' berhasil dikirim ke UPDATE_STATUS_KARYAWAN dan langsung dieliminasi dari data operasional MCU & FU aktif.'
                )
                : (
                    $result['nama'] .
                    ' berhasil dikirim ke UPDATE_STATUS_KARYAWAN.'
                );

            return redirect()
                ->route(
                    'admin-all.mcu-fu.update'
                )
                ->with(
                    'success',
                    $message
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Unified Update MCU & Follow Up
    |--------------------------------------------------------------------------
    */

    public function update(Request $request): View
    {
        $error = null;
        $rows = [];
        $options = [
            'hasil_mcu' => [],
            'follow_up' => [],
            'status_fu' => [],
        ];

        try {
            $rows = $this->mcuFu->rows();

            $options = $this->mcuFu->options(
                $rows
            );
        } catch (Throwable $e) {
            report($e);

            $error = $e->getMessage();
        }

        $filterData = $this->mcuFu->updatePageData(
            $rows,
            [
                'date_type' => $request->query(
                    'date_type',
                    'jadwal_mcu'
                ),

                'year' => $request->query(
                    'year',
                    now()->year
                ),

                'month' => $request->query(
                    'month'
                ),

                'search' => $request->query(
                    'q'
                ),

                'simper_exp' => $request->query(
                    'simper_exp'
                ),

                /*
                 * Drill-down Dashboard.
                 */
                'hasil_mcu' => $request->query(
                    'hasil_mcu'
                ),
                'status_mcu' => $request->query(
                    'status_mcu'
                ),
                'status_fu' => $request->query(
                    'status_fu'
                ),
                'jabatan' => $request->query(
                    'jabatan'
                ),
                'fu_stage' => $request->query(
                    'fu_stage'
                ),
                'follow_up_value' => $request->query(
                    'follow_up_value'
                ),
            ]
        );

        $requestedPerPage = (int) $request->query(
            'per_page',
            20
        );

        $perPage = in_array(
            $requestedPerPage,
            [20, 50, 100],
            true
        )
            ? $requestedPerPage
            : 20;

        $data = $this->paginateArray(
            $filterData['rows'],
            $request,
            $perPage
        );

        return view(
            'admin-all.mcu-fu.update',
            [
                'data' => $data,
                'options' => $options,
                'years' => $filterData['years'],
                'filters' => $filterData['filters'],
                'perPage' => $perPage,
                'error' => $error,
            ]
        );
    }

    public function saveUpdate(
        Request $request,
        int $sheetRow
    ): RedirectResponse {
        $validated = $request->validate([
            'exp_mcu' => [
                'nullable',
                'date',
            ],
            'jadwal_mcu' => [
                'nullable',
                'date',
            ],
            'hasil_mcu' => [
                'nullable',
                'string',
                'max:100',
            ],
            'follow_up_1' => [
                'nullable',
                'string',
                'max:100',
            ],
            'follow_up_2' => [
                'nullable',
                'string',
                'max:100',
            ],
            'follow_up_3' => [
                'nullable',
                'string',
                'max:100',
            ],
            'jadwal_fu' => [
                'nullable',
                'date',
            ],
            'status_fu' => [
                'nullable',
                'string',
                'max:100',
            ],
            'manual_expired_sim_dlt' => [
                'nullable',
                'date',
            ],
            'manual_simper_note' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /*
         * Preserve filter/search/page setelah save.
         */
        $returnQuery = collect(
            [
                'date_type' => $request->input('_return_date_type'),
                'year' => $request->input('_return_year'),
                'month' => $request->input('_return_month'),
                'simper_exp' => $request->input('_return_simper_exp'),
                'q' => $request->input('_return_q'),
                'hasil_mcu' => $request->input('_return_hasil_mcu'),
                'status_mcu' => $request->input('_return_status_mcu'),
                'status_fu' => $request->input('_return_status_fu'),
                'jabatan' => $request->input('_return_jabatan'),
                'fu_stage' => $request->input('_return_fu_stage'),
                'follow_up_value' => $request->input('_return_follow_up_value'),
                'page' => $request->input('_return_page'),
                'per_page' => $request->input('_return_per_page'),
            ]
        )
            ->reject(
                fn ($value) =>
                    $value === null ||
                    $value === ''
            )
            ->all();

        try {
            $user = Auth::user();

            $change = $this->mcuFu->updateUnified(
                $sheetRow,
                $validated,
                $user?->name,
                $user?->email
            );

            if (
                (int) ($change['change_count'] ?? 0) <= 0
            ) {
                return redirect()
                    ->route(
                        'admin-all.mcu-fu.update',
                        $returnQuery
                    )
                    ->with(
                        'success',
                        'Tidak ada perubahan data untuk disimpan.'
                    );
            }

            $this->storeHistory(
                'MCU_FU_UPDATE',
                $change
            );

            return redirect()
                ->route(
                    'admin-all.mcu-fu.update',
                    $returnQuery
                )
                ->with(
                    'success',
                    sprintf(
                        '%d perubahan berhasil disimpan.',
                        (int) $change['change_count']
                    )
                );
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'admin-all.mcu-fu.update',
                    $returnQuery
                )
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Input / Update MCU
    |--------------------------------------------------------------------------
    */

    public function mcu(Request $request): RedirectResponse
    {
        return redirect()->route(
            'admin-all.mcu-fu.update',
            $request->query()
        );
    }

    public function updateMcu(
        Request $request,
        int $sheetRow
    ): RedirectResponse {
        $validated = $request->validate([
            'exp_mcu' => [
                'nullable',
                'date',
            ],
            'jadwal_mcu' => [
                'nullable',
                'date',
            ],
            'hasil_mcu' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        try {
            $change = $this->mcuFu->updateMcu(
                $sheetRow,
                $validated
            );

            $this->storeHistory(
                'MCU_UPDATE',
                $change
            );

            return back()->with(
                'success',
                'Data MCU berhasil diperbarui.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Input Follow Up
    |--------------------------------------------------------------------------
    */

    public function followUp(Request $request): RedirectResponse
    {
        $query = $request->query();

        $query['date_type'] =
            $query['date_type']
            ?? 'follow_up';

        return redirect()->route(
            'admin-all.mcu-fu.update',
            $query
        );
    }

    public function updateFollowUp(
        Request $request,
        int $sheetRow
    ): RedirectResponse {
        $validated = $request->validate([
            'follow_up_1' => [
                'nullable',
                'string',
                'max:100',
            ],
            'follow_up_2' => [
                'nullable',
                'string',
                'max:100',
            ],
            'follow_up_3' => [
                'nullable',
                'string',
                'max:100',
            ],
            'jadwal_fu' => [
                'nullable',
                'date',
            ],
            'status_fu' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        try {
            $change = $this->mcuFu->updateFollowUp(
                $sheetRow,
                $validated
            );

            $this->storeHistory(
                'FOLLOW_UP_UPDATE',
                $change
            );

            return back()->with(
                'success',
                'Data Follow Up berhasil diperbarui.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Update
    |--------------------------------------------------------------------------
    */

    public function history(Request $request): View
    {
        $query = McuFuInternalHistory::query()
            ->latest();

        $search = trim(
            (string) $request->query(
                'q',
                ''
            )
        );

        if ($search !== '') {
            $query->where(
                function ($builder) use ($search): void {
                    $builder
                        ->where('nrp', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('user_email', 'like', "%{$search}%");
                }
            );
        }

        $action = strtoupper(
            trim(
                (string) $request->query(
                    'action',
                    ''
                )
            )
        );

        if (
            in_array(
                $action,
                [
                    'MCU_UPDATE',
                    'FOLLOW_UP_UPDATE',
                    'MCU_FU_UPDATE',
                ],
                true
            )
        ) {
            $query->where(
                'action',
                $action
            );
        }

        $histories = $query
            ->paginate(30)
            ->withQueryString();

        return view(
            'admin-all.mcu-fu.history',
            compact(
                'histories'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Filter data list
    |--------------------------------------------------------------------------
    |
    | Filter chart dashboard dikirim melalui query string:
    | hasil_mcu / status_mcu / status_fu / jabatan / fu_stage.
    |
    | year + month mengikuti acuan dashboard yaitu JADWAL MCU.
    */

    private function filterRows(
        array $rows,
        Request $request
    ): array {
        $search = mb_strtoupper(
            trim(
                (string) $request->query(
                    'q',
                    ''
                )
            )
        );

        $hasilMcu = mb_strtoupper(
            trim(
                (string) $request->query(
                    'hasil_mcu',
                    ''
                )
            )
        );

        $statusMcu = mb_strtoupper(
            trim(
                (string) $request->query(
                    'status_mcu',
                    ''
                )
            )
        );

        $statusFu = mb_strtoupper(
            trim(
                (string) $request->query(
                    'status_fu',
                    ''
                )
            )
        );

        $jabatan = mb_strtoupper(
            trim(
                (string) $request->query(
                    'jabatan',
                    ''
                )
            )
        );

        $fuStage = (int) $request->query(
            'fu_stage',
            0
        );

        $followUpValue = mb_strtoupper(
            trim(
                (string) $request->query(
                    'follow_up_value',
                    ''
                )
            )
        );

        $year = $this->validatedYear(
            $request->query('year'),
            false
        );

        $month = $this->validatedMonth(
            $request->query('month')
        );

        return collect($rows)
            ->filter(
                function (array $row) use (
                    $search,
                    $hasilMcu,
                    $statusMcu,
                    $statusFu,
                    $jabatan,
                    $fuStage,
                    $followUpValue,
                    $year,
                    $month
                ): bool {
                    if ($search !== '') {
                        $haystack = mb_strtoupper(
                            implode(
                                ' ',
                                [
                                    (string) ($row['nrp'] ?? ''),
                                    (string) ($row['nama'] ?? ''),
                                    (string) ($row['jabatan'] ?? ''),
                                    (string) ($row['hasil_mcu'] ?? ''),
                                    (string) ($row['status_mcu'] ?? ''),
                                    (string) ($row['follow_up_1'] ?? ''),
                                    (string) ($row['follow_up_2'] ?? ''),
                                    (string) ($row['follow_up_3'] ?? ''),
                                    (string) ($row['status_fu'] ?? ''),
                                ]
                            )
                        );

                        if (
                            !str_contains(
                                $haystack,
                                $search
                            )
                        ) {
                            return false;
                        }
                    }

                    if (
                        $hasilMcu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['hasil_mcu'] ?? '')
                            )
                        ) !== $hasilMcu
                    ) {
                        return false;
                    }

                    if (
                        $statusMcu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['status_mcu'] ?? '')
                            )
                        ) !== $statusMcu
                    ) {
                        return false;
                    }

                    if (
                        $statusFu !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['status_fu'] ?? '')
                            )
                        ) !== $statusFu
                    ) {
                        return false;
                    }

                    if (
                        $jabatan !== '' &&
                        mb_strtoupper(
                            trim(
                                (string) ($row['jabatan'] ?? '')
                            )
                        ) !== $jabatan
                    ) {
                        return false;
                    }

                    if (
                        in_array(
                            $fuStage,
                            [1, 2, 3],
                            true
                        )
                    ) {
                        $field =
                            'follow_up_' .
                            $fuStage;

                        $rowFollowUpValue = mb_strtoupper(
                            trim(
                                (string) ($row[$field] ?? '')
                            )
                        );

                        if ($rowFollowUpValue === '') {
                            return false;
                        }

                        /*
                         * Jika chart mengirim follow_up_value,
                         * tampilkan spesialis/tujuan yang sama persis.
                         */
                        if (
                            $followUpValue !== '' &&
                            $rowFollowUpValue !== $followUpValue
                        ) {
                            return false;
                        }
                    }

                    if (
                        $year !== null ||
                        $month !== null
                    ) {
                        $parts = $this->mcuFu->dateParts(
                            $row['jadwal_mcu'] ?? ''
                        );

                        if ($parts === null) {
                            return false;
                        }

                        if (
                            $year !== null &&
                            $parts['year'] !== $year
                        ) {
                            return false;
                        }

                        if (
                            $month !== null &&
                            $parts['month'] !== $month
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            )
            ->values()
            ->all();
    }

    private function paginateArray(
        array $rows,
        Request $request,
        int $perPage
    ): LengthAwarePaginator {
        $page = max(
            1,
            (int) $request->query(
                'page',
                1
            )
        );

        $items = array_slice(
            $rows,
            ($page - 1) * $perPage,
            $perPage
        );

        return new LengthAwarePaginator(
            $items,
            count($rows),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function validatedYear(
        mixed $value,
        bool $defaultCurrent = true
    ): ?int {
        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            return $defaultCurrent
                ? (int) now()->year
                : null;
        }

        $year = (int) $value;

        if (
            $year < 2000 ||
            $year > 2100
        ) {
            return $defaultCurrent
                ? (int) now()->year
                : null;
        }

        return $year;
    }

    private function validatedMonth(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            return null;
        }

        $month = (int) $value;

        return (
            $month >= 1 &&
            $month <= 12
        )
            ? $month
            : null;
    }

    private function storeHistory(
        string $action,
        array $change
    ): void {
        $before = is_array(
            $change['before'] ?? null
        )
            ? $change['before']
            : [];

        $after = is_array(
            $change['after'] ?? null
        )
            ? $change['after']
            : [];

        $user = Auth::user();

        if (isset($change['changes']) && is_array($change['changes'])) {
            $after['_changes'] = $change['changes'];
        }

        McuFuInternalHistory::create([
            'sheet_row' => (int) (
                $after['sheet_row']
                ?? $before['sheet_row']
                ?? 0
            ),

            'nrp' => (string) (
                $after['nrp']
                ?? $before['nrp']
                ?? ''
            ),

            'nama' => (string) (
                $after['nama']
                ?? $before['nama']
                ?? ''
            ),

            'action' => $action,

            'before_data' => $before,

            'after_data' => $after,

            'user_name' => $user?->name,

            'user_email' => $user?->email,
        ]);
    }
}
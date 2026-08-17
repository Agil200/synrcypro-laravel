<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McuFuInternalHistory;
use App\Services\McuFuInternalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Throwable;

class McuFuInternalController extends Controller
{
    public function __construct(
        private readonly McuFuInternalService $mcuFu
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
    | Input / Update MCU
    |--------------------------------------------------------------------------
    */

    public function mcu(Request $request): View
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

        $filtered = $this->filterRows(
            $rows,
            $request
        );

        $data = $this->paginateArray(
            $filtered,
            $request,
            30
        );

        return view(
            'admin-all.mcu-fu.mcu',
            compact(
                'data',
                'options',
                'error'
            )
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

    public function followUp(Request $request): View
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

        /*
         * Halaman Follow Up hanya berisi orang yang memang terkait FU.
         */
        $rows = collect($rows)
            ->filter(function (array $row): bool {
                $hasilMcu = strtoupper(
                    trim(
                        (string) ($row['hasil_mcu'] ?? '')
                    )
                );

                return
                    str_contains($hasilMcu, 'FOLLOW UP') ||
                    trim((string) ($row['follow_up_1'] ?? '')) !== '' ||
                    trim((string) ($row['follow_up_2'] ?? '')) !== '' ||
                    trim((string) ($row['follow_up_3'] ?? '')) !== '' ||
                    trim((string) ($row['jadwal_fu'] ?? '')) !== '' ||
                    trim((string) ($row['status_fu'] ?? '')) !== '';
            })
            ->values()
            ->all();

        $filtered = $this->filterRows(
            $rows,
            $request
        );

        $data = $this->paginateArray(
            $filtered,
            $request,
            30
        );

        return view(
            'admin-all.mcu-fu.follow-up',
            compact(
                'data',
                'options',
                'error'
            )
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
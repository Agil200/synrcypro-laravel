<?php

namespace App\Http\Controllers;

use App\Services\EmployeeMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Throwable;

class DatabaseUiController extends Controller
{
    public function __construct(
        private readonly EmployeeMasterService $employeeMaster
    ) {
    }

    public function dashboard(): View
    {
        $dashboardSummary =
            $this->employeeMaster
                ->dashboardSummary();

        return $this->render(
            'database.dashboard',
            'dashboard',
            [
                'dashboardSummary' =>
                    $dashboardSummary,
                'sourceUrl' =>
                    $this->employeeMaster
                        ->sourceUrl(),
                'googleConnected' =>
                    $this->employeeMaster
                        ->isGoogleConnected(),
            ]
        );
    }

    public function employees(Request $request): View
    {
        $snapshot =
            $this->employeeMaster->snapshot();

        $allEmployees = collect(
            $snapshot['employees'] ?? []
        );

        $search = strtolower(
            trim(
                (string) $request->query(
                    'search',
                    ''
                )
            )
        );

        $residence = strtolower(
            trim(
                (string) $request->query(
                    'residence',
                    'all'
                )
            )
        );

        $perPage = (int) $request->query(
            'per_page',
            25
        );

        if (!in_array(
            $perPage,
            [25, 50, 100],
            true
        )) {
            $perPage = 25;
        }

        $filtered = $allEmployees
            ->filter(function (array $employee) use (
                $search,
                $residence
            ): bool {
                $haystack = strtolower(
                    implode(
                        ' ',
                        [
                            (string) (
                                $employee['nrp'] ?? ''
                            ),
                            (string) (
                                $employee['nama'] ?? ''
                            ),
                            (string) (
                                $employee['jabatan'] ?? ''
                            ),
                            (string) (
                                $employee['departemen'] ?? ''
                            ),
                        ]
                    )
                );

                $matchesSearch =
                    $search === '' ||
                    str_contains(
                        $haystack,
                        $search
                    );

                $residenceValue = strtolower(
                    str_replace(
                        ' ',
                        '-',
                        trim(
                            (string) (
                                $employee['status_tinggal'] ??
                                ''
                            )
                        )
                    )
                );

                $matchesResidence =
                    $residence === 'all' ||
                    $residenceValue === $residence;

                return
                    $matchesSearch &&
                    $matchesResidence;
            })
            ->sortBy(
                fn (array $employee): string =>
                    strtolower(
                        (string) (
                            $employee['nama'] ??
                            ''
                        )
                    )
            )
            ->values();

        $currentPage = max(
            1,
            (int) $request->query(
                'page',
                1
            )
        );

        $employees = new LengthAwarePaginator(
            $filtered
                ->forPage(
                    $currentPage,
                    $perPage
                )
                ->values(),

            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $employeeStats = [
            'total' =>
                $allEmployees->count(),

            'mess' =>
                $allEmployees
                    ->where(
                        'status_tinggal',
                        'MESS'
                    )
                    ->count(),

            'non_mess' =>
                $allEmployees
                    ->where(
                        'status_tinggal',
                        'NON MESS'
                    )
                    ->count(),

            'unknown' =>
                $allEmployees
                    ->filter(
                        fn (array $employee): bool =>
                            !in_array(
                                (string) (
                                    $employee['status_tinggal'] ??
                                    ''
                                ),
                                [
                                    'MESS',
                                    'NON MESS',
                                ],
                                true
                            )
                    )
                    ->count(),
        ];

        return $this->render(
            'database.employees.index',
            'employees',
            [
                'employees' => $employees,
                'employeeStats' => $employeeStats,
                'search' =>
                    $request->query(
                        'search',
                        ''
                    ),
                'residence' => $residence,
                'perPage' => $perPage,
                'syncMeta' =>
                    $snapshot['meta'] ?? [],
                'sourceUrl' =>
                    $this->employeeMaster
                        ->sourceUrl(),
                'googleConnected' =>
                    $this->employeeMaster
                        ->isGoogleConnected(),
            ]
        );
    }


    public function employeeMappingDiagnostics(): View
    {
        try {
            $diagnostics =
                $this->employeeMaster
                    ->mappingDiagnostics();

            $diagnosticError = null;
        } catch (Throwable $exception) {
            $diagnostics = [];
            $diagnosticError =
                $exception->getMessage();
        }

        return $this->render(
            'database.employees.mapping-diagnostics',
            'employees',
            [
                'diagnostics' => $diagnostics,
                'diagnosticError' =>
                    $diagnosticError,
                'sourceUrl' =>
                    $this->employeeMaster
                        ->sourceUrl(),
            ]
        );
    }


    public function testEmployeeFallback(): RedirectResponse
    {
        try {
            $result =
                $this->employeeMaster
                    ->testFallbackCache();

            return redirect()
                ->route(
                    'database.employees.mapping-diagnostics'
                )
                ->with(
                    'success',
                    'Uji fallback berhasil. Backup cache dapat dibaca: ' .
                    number_format(
                        (int) (
                            $result['employees_count'] ??
                            0
                        )
                    ) .
                    ' karyawan.'
                );
        } catch (Throwable $exception) {
            return redirect()
                ->route(
                    'database.employees.mapping-diagnostics'
                )
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function syncEmployees(): RedirectResponse
    {
        $snapshot =
            $this->employeeMaster
                ->synchronize();

        $meta =
            $snapshot['meta'] ?? [];

        $status = (string) (
            $meta['status'] ?? 'error'
        );

        $mappedRows = (int) (
            $meta['mapped_rows'] ??
            count(
                $snapshot['employees'] ?? []
            )
        );

        if ($status === 'synced') {
            return redirect()
                ->route('database.employees')
                ->with(
                    'success',
                    'MASTER_DATABASE berhasil disinkronkan. ' .
                    number_format($mappedRows) .
                    ' karyawan dimuat ke cache dan backup lokal.'
                );
        }

        if ($status === 'stale') {
            return redirect()
                ->route('database.employees')
                ->with(
                    'warning',
                    'Google Sheets tidak dapat diakses. Sistem tetap menampilkan ' .
                    number_format($mappedRows) .
                    ' karyawan dari backup terakhir.'
                );
        }

        return redirect()
            ->route('database.employees')
            ->with(
                'error',
                (string) (
                    $meta['error'] ??
                    'MASTER_DATABASE belum dapat dimuat dan backup belum tersedia.'
                )
            );
    }

    public function atrSummary(): View
    {
        return $this->render(
            'database.atr.summary',
            'atr-summary',
            [
                'atrStats' => [
                    'aman' => 643,
                    'di_bawah' => 210,
                    'no_data' => 16,
                    'sakit' => 200,
                    'izin' => 108,
                    'alpa' => 2,
                ],
                'atrProgress' => [
                    'belum' => 162,
                    'sudah' => 48,
                    'total' => 210,
                    'percentage' => 23,
                ],
                'atrRanking' => collect([
                    [
                        'nrp' => '22000992',
                        'nama' => 'ANGGI OKTA FIANSYA',
                        'jabatan' => 'OPERATOR HD 785',
                        's' => 3,
                        'i' => 2,
                        'a' => 0,
                        'atr' => '80.0%',
                    ],
                    [
                        'nrp' => '21002009',
                        'nama' => 'RIDUAN KUSUMA',
                        'jabatan' => 'OPERATOR GD 825',
                        's' => 4,
                        'i' => 0,
                        'a' => 0,
                        'atr' => '85.2%',
                    ],
                    [
                        'nrp' => '22002089',
                        'nama' => 'WIDA ARDIYANTO',
                        'jabatan' => 'OPERATOR PC 500',
                        's' => 0,
                        'i' => 3,
                        'a' => 1,
                        'atr' => '85.7%',
                    ],
                ]),
            ]
        );
    }

    public function atrUpload(): View
    {
        return $this->render(
            'database.atr.upload',
            'atr-upload'
        );
    }

    public function atrHistory(): View
    {
        return $this->render(
            'database.atr.import-history',
            'atr-history',
            [
                'imports' => collect([
                    [
                        'file' =>
                            'SS6-BA-ATR_INDIVIDUAL-PRO-20260802_20260802.xlsx',
                        'period' => '02 Agu 2026 – 02 Agu 2026',
                        'rows' => 863,
                        'valid' => 863,
                        'invalid' => 0,
                        'status' => 'Preview UI',
                    ],
                ]),
            ]
        );
    }

    public function atrCalls(): View
    {
        return $this->render(
            'database.atr.call-documentation',
            'atr-calls',
            [
                'monthLabel' => 'Juni 2026',

                /*
                 * Endpoint POST belum diaktifkan pada Fase 1.
                 * Nanti diarahkan ke route penyimpanan dokumentasi.
                 */
                'atrDocumentationEndpoint' => '',

                /*
                 * Data ini masih dummy untuk finalisasi UI/UX.
                 * PIC roster disimpan terpisah dari data operator.
                 */
                'employees' => collect([
                    [
                        'id' => '1707255',
                        'nrp' => '1707255',
                        'nama' => 'NANANG SAHRANI',
                        'jabatan' => 'OPERATOR',
                        'roster_group' => 'DOZER',
                        'pic_primary' => 'IRFAN WIBAWA',
                        'pic_backup' =>
                            'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                        'pic_effective_from' => '01 Agu 2026',
                        'pic_effective_to' => null,
                        'period' => 'Juni 2026',
                        's' => 1,
                        'i' => 0,
                        'a' => 0,
                        'atr' => '96.4%',
                        'called' => false,
                    ],
                    [
                        'id' => '1683312',
                        'nrp' => '1683312',
                        'nama' => 'JONI',
                        'jabatan' => 'OPERATOR PC 500',
                        'roster_group' => 'EXCAVATOR',
                        'pic_primary' =>
                            'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                        'pic_backup' => 'IRFAN WIBAWA',
                        'pic_effective_from' => '01 Agu 2026',
                        'pic_effective_to' => null,
                        'period' => 'Juni 2026',
                        's' => 0,
                        'i' => 0,
                        'a' => 0,
                        'atr' => '85.7%',
                        'called' => false,
                    ],
                    [
                        'id' => '1707256',
                        'nrp' => '1707256',
                        'nama' => 'DEDZA AUDIO BAYU PRADAMA',
                        'jabatan' => 'OPERATOR HD 785',
                        'roster_group' => 'DUMPMAN',
                        'pic_primary' => 'IRFAN WIBAWA',
                        'pic_backup' =>
                            'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                        'pic_effective_from' => '01 Agu 2026',
                        'pic_effective_to' => null,
                        'period' => 'Juni 2026',
                        's' => 0,
                        'i' => 0,
                        'a' => 0,
                        'atr' => '90.5%',
                        'called' => true,
                    ],
                ]),
            ]
        );
    }

    public function atrPicRoster(): View
    {
        $rosterGroups = collect([
            [
                'code' => 'EXCAVATOR',
                'label' => 'Excavator',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'DOZER',
                'label' => 'Dozer',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'WHEEL_LOADER',
                'label' => 'Wheel Loader',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'VIBRO',
                'label' => 'Vibro',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'HD_CAT',
                'label' => 'HD/CAT',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'DT_TRINTIN',
                'label' => 'DT Trintin',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'DT_TRONTON',
                'label' => 'DT Tronton',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'WTHD',
                'label' => 'WTHD',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'WTDT',
                'label' => 'WTDT',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
            [
                'code' => 'GRADER',
                'label' => 'Grader',
                'pic_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
                'effective_from' => '01 Agu 2026',
                'effective_to' => null,
                'status' => 'AKTIF',
            ],
        ]);

        $positionMappings = collect([
            ['raw_position' => 'OPERATOR EXCAVATOR', 'roster_group' => 'EXCAVATOR', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR PC 500', 'roster_group' => 'EXCAVATOR', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR DOZER', 'roster_group' => 'DOZER', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR BULLDOZER', 'roster_group' => 'DOZER', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR WHEEL LOADER', 'roster_group' => 'WHEEL_LOADER', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR VIBRO', 'roster_group' => 'VIBRO', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR HD 785', 'roster_group' => 'HD_CAT', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR CAT 777', 'roster_group' => 'HD_CAT', 'status' => 'AKTIF'],
            ['raw_position' => 'DRIVER DT TRINTIN', 'roster_group' => 'DT_TRINTIN', 'status' => 'AKTIF'],
            ['raw_position' => 'DRIVER DT TRONTON', 'roster_group' => 'DT_TRONTON', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR WATER TRUCK HD', 'roster_group' => 'WTHD', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR WATER TRUCK DT', 'roster_group' => 'WTDT', 'status' => 'AKTIF'],
            ['raw_position' => 'OPERATOR GRADER', 'roster_group' => 'GRADER', 'status' => 'AKTIF'],
        ]);

        $rotationHistory = collect([
            [
                'roster_group' => 'DOZER',
                'old_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'new_primary' => 'IRFAN WIBAWA',
                'effective_date' => '01 Agu 2026',
                'reason' => 'Rotasi pembagian roster',
            ],
            [
                'roster_group' => 'EXCAVATOR',
                'old_primary' => 'IRFAN WIBAWA',
                'new_primary' =>
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'effective_date' => '01 Agu 2026',
                'reason' => 'Penyesuaian unit kerja',
            ],
        ]);

        $activeAssignments = $rosterGroups
            ->where('status', 'AKTIF')
            ->count();

        $unassignedGroups = $rosterGroups
            ->filter(function (array $group): bool {
                return trim(
                    (string) $group['pic_primary']
                ) === '';
            })
            ->count();

        return $this->render(
            'database.atr.pic-roster',
            'atr-pic-roster',
            [
                'rosterGroups' => $rosterGroups,
                'positionMappings' => $positionMappings,
                'rotationHistory' => $rotationHistory,
                'picOptions' => collect([
                    'IRFAN WIBAWA',
                    'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                ]),
                'picRosterStats' => [
                    'total_groups' =>
                        $rosterGroups->count(),

                    'active_assignments' =>
                        $activeAssignments,

                    'active_pics' =>
                        2,

                    'unassigned_groups' =>
                        $unassignedGroups,
                ],
            ]
        );
    }

    private function render(
        string $contentView,
        string $activePage,
        array $data = []
    ): View {
        return view(
            'database',
            array_merge(
                [
                    'contentView' => $contentView,
                    'activePage' => $activePage,
                ],
                $data
            )
        );
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\AtrPicRosterGroup;
use App\Models\AtrPicRosterHistory;
use App\Models\AtrPicMonthlyRoster;
use App\Models\AtrPicRosterRule;
use App\Models\AtrRecord;
use App\Services\AtrPicRosterService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AtrPicRosterController extends Controller
{
    public function __construct(
        private readonly AtrPicRosterService $picRoster
    ) {
    }

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | PERIODE
        |--------------------------------------------------------------------------
        |
        | Roster sekarang benar-benar bulanan.
        | Upload ulang periode yang sama tidak mengubah assignment karena data PIC
        | tersimpan terpisah dari snapshot ATR.
        |
        */
        $periodOptions = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) =>
                    $query->where('status', 'COMPLETED')
            )
            ->whereNotNull('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period')
            ->map(
                fn ($period) =>
                    Carbon::parse($period)->startOfMonth()
            )
            ->unique(
                fn (Carbon $period) =>
                    $period->format('Y-m')
            )
            ->values();

        $requestedPeriod = trim(
            $request->string('period')->toString()
        );

        try {
            $period = $requestedPeriod !== ''
                ? Carbon::createFromFormat(
                    'Y-m',
                    $requestedPeriod
                )->startOfMonth()
                : ($periodOptions->first()
                    ? Carbon::parse(
                        $periodOptions->first()
                    )->startOfMonth()
                    : now()->startOfMonth());
        } catch (\Throwable) {
            $period = $periodOptions->first()
                ? Carbon::parse(
                    $periodOptions->first()
                )->startOfMonth()
                : now()->startOfMonth();
        }

        $periodValue = $period->format('Y-m');

        /*
        |--------------------------------------------------------------------------
        | POSISI AKTIF PADA SNAPSHOT BULAN TERPILIH
        |--------------------------------------------------------------------------
        */
        $positionCounts = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) =>
                    $query->where('status', 'COMPLETED')
            )
            ->whereDate(
                'period',
                $period->format('Y-m-d')
            )
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->select(
                'position',
                DB::raw('COUNT(*) as total_records')
            )
            ->groupBy('position')
            ->orderBy('position')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | POSITION -> CATEGORY
        |--------------------------------------------------------------------------
        */
        $buckets = [];
        $unmappedPositions = collect();
        $positionDiagnostics = collect();

        foreach ($positionCounts as $positionRow) {
            $rawPosition = trim(
                (string) $positionRow->position
            );

            $category = $this->picRoster
                ->categoryFor($rawPosition);

            $positionDiagnostics->push([
                'position' => $rawPosition,
                'total_records' =>
                    (int) $positionRow->total_records,
                'matched' =>
                    (bool) $category['matched'],
                'category' =>
                    $category['group_label'] ?? null,
                'group_code' =>
                    $category['group_code'] ?? null,
                'rule_type' =>
                    $category['rule_type'] ?? null,
                'rule_pattern' =>
                    $category['rule_pattern'] ?? null,
                'reason' =>
                    $category['reason'] ?? null,
            ]);

            if (! $category['matched']) {
                $unmappedPositions->push([
                    'position' => $rawPosition,
                    'total_records' =>
                        (int) $positionRow->total_records,
                ]);

                continue;
            }

            $groupId = (int) $category['group_id'];

            if (! isset($buckets[$groupId])) {
                $buckets[$groupId] = [
                    'group_id' => $groupId,
                    'group_code' =>
                        (string) $category['group_code'],
                    'category' =>
                        (string) $category['group_label'],
                    'positions' => collect(),
                    'employee_count' => 0,
                ];
            }

            $buckets[$groupId]['positions']->push(
                $rawPosition
            );

            $buckets[$groupId]['employee_count'] +=
                (int) $positionRow->total_records;
        }

        $groupIds = collect(
            array_keys($buckets)
        );

        $monthlyRosters = AtrPicMonthlyRoster::query()
            ->whereDate(
                'period',
                $period->format('Y-m-d')
            )
            ->when(
                $groupIds->isNotEmpty(),
                fn ($query) =>
                    $query->whereIn(
                        'atr_pic_roster_group_id',
                        $groupIds->all()
                    )
            )
            ->get()
            ->keyBy('atr_pic_roster_group_id');

        $categoryRows = collect($buckets)
            ->map(function (
                array $bucket
            ) use ($monthlyRosters): array {
                $roster = $monthlyRosters->get(
                    $bucket['group_id']
                );

                $primary = trim(
                    (string) ($roster?->pic_primary ?? '')
                );

                return [
                    ...$bucket,
                    'positions' =>
                        $bucket['positions']
                            ->unique()
                            ->sort()
                            ->values(),
                    'roster_id' => $roster?->id,
                    'pic_primary' =>
                        $primary !== ''
                            ? $primary
                            : null,
                    'pic_backup' =>
                        trim(
                            (string) (
                                $roster?->pic_backup ?? ''
                            )
                        ) ?: null,
                    'is_filled' => $primary !== '',
                ];
            })
            ->sortBy('category')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | DAFTAR PIC TERKONTROL
        |--------------------------------------------------------------------------
        |
        | Dropdown mengambil nama yang sudah pernah terdaftar pada roster bulanan
        | + assignment legacy. Jadi user tidak mengetik nama bebas / typo.
        |
        */
        $picOptions = AtrPicMonthlyRoster::query()
            ->select([
                'pic_primary',
                'pic_backup',
            ])
            ->get()
            ->flatMap(
                fn (AtrPicMonthlyRoster $roster) => [
                    $roster->pic_primary,
                    $roster->pic_backup,
                ]
            )
            ->merge(
                AtrPicRosterGroup::query()
                    ->select([
                        'pic_primary',
                        'pic_backup',
                    ])
                    ->get()
                    ->flatMap(
                        fn (AtrPicRosterGroup $group) => [
                            $group->pic_primary,
                            $group->pic_backup,
                        ]
                    )
            )
            ->filter()
            ->map(
                fn ($name) =>
                    trim((string) $name)
            )
            ->filter()
            ->unique(
                fn ($name) =>
                    Str::upper($name)
            )
            ->sort()
            ->values();

        $filled = $categoryRows
            ->where('is_filled', true)
            ->count();

        $activePics = $categoryRows
            ->pluck('pic_primary')
            ->merge(
                $categoryRows->pluck('pic_backup')
            )
            ->filter()
            ->unique()
            ->count();

        return view('database', [
            'contentView' =>
                'database.atr.pic-roster',
            'activePage' =>
                'atr-pic-roster',

            'periodOptions' =>
                $periodOptions,
            'selectedPeriod' =>
                $periodValue,
            'selectedPeriodLabel' =>
                $period
                    ->locale('id')
                    ->translatedFormat('F Y'),

            'categoryRows' =>
                $categoryRows,
            'unmappedPositions' =>
                $unmappedPositions,
            'positionDiagnostics' =>
                $positionDiagnostics
                    ->sortBy('position')
                    ->values(),
            'picOptions' =>
                $picOptions,

            'picRosterStats' => [
                'total_categories' =>
                    $categoryRows->count(),
                'filled_categories' =>
                    $filled,
                'unfilled_categories' =>
                    max(
                        0,
                        $categoryRows->count()
                        - $filled
                    ),
                'active_pics' =>
                    $activePics,
                'unmapped_positions' =>
                    $unmappedPositions->count(),
            ],
        ]);
    }

    /**
     * Satu endpoint saja untuk CREATE/EDIT roster bulanan.
     * updateOrCreate menjamin upload ulang bulan yang sama tidak membuat duplikat.
     */
    public function saveMonthlyRoster(
        Request $request
    ): RedirectResponse {
        $knownPicOptions = AtrPicMonthlyRoster::query()
            ->select([
                'pic_primary',
                'pic_backup',
            ])
            ->get()
            ->flatMap(
                fn (AtrPicMonthlyRoster $roster) => [
                    $roster->pic_primary,
                    $roster->pic_backup,
                ]
            )
            ->merge(
                AtrPicRosterGroup::query()
                    ->select([
                        'pic_primary',
                        'pic_backup',
                    ])
                    ->get()
                    ->flatMap(
                        fn (AtrPicRosterGroup $group) => [
                            $group->pic_primary,
                            $group->pic_backup,
                        ]
                    )
            )
            ->filter()
            ->map(
                fn ($name) =>
                    trim((string) $name)
            )
            ->filter()
            ->unique(
                fn ($name) =>
                    Str::upper($name)
            )
            ->values()
            ->all();

        $validated = $request->validate([
            'period' => [
                'required',
                'date_format:Y-m',
            ],
            'atr_pic_roster_group_id' => [
                'required',
                'integer',
                'exists:atr_pic_roster_groups,id',
            ],
            'pic_primary' => [
                'required',
                'string',
                'max:150',
                Rule::in($knownPicOptions),
            ],
            'pic_backup' => [
                'nullable',
                'string',
                'max:150',
                Rule::in($knownPicOptions),
            ],
        ], [
            'period.required' =>
                'Periode wajib tersedia.',
            'period.date_format' =>
                'Format periode tidak valid.',
            'pic_primary.required' =>
                'PIC Roster 1 wajib diisi.',
            'pic_primary.in' =>
                'PIC Roster 1 harus dipilih dari daftar PIC yang tersedia.',
            'pic_backup.in' =>
                'PIC Roster 2 harus dipilih dari daftar PIC yang tersedia.',
        ]);

        $primary = trim(
            (string) $validated['pic_primary']
        );

        $backup = trim(
            (string) (
                $validated['pic_backup'] ?? ''
            )
        );

        if (
            $backup !== ''
            && Str::upper($primary)
                === Str::upper($backup)
        ) {
            throw ValidationException::withMessages([
                'pic_backup' =>
                    'PIC Roster 2 harus berbeda '
                    . 'dengan PIC Roster 1.',
            ]);
        }

        $period = Carbon::createFromFormat(
            'Y-m',
            $validated['period']
        )->startOfMonth();

        $group = AtrPicRosterGroup::query()
            ->findOrFail(
                (int) $validated[
                    'atr_pic_roster_group_id'
                ]
            );

        DB::transaction(
            function () use (
                $period,
                $group,
                $primary,
                $backup
            ): void {
                $existing = AtrPicMonthlyRoster::query()
                    ->where(
                        'atr_pic_roster_group_id',
                        $group->id
                    )
                    ->whereDate(
                        'period',
                        $period->format('Y-m-d')
                    )
                    ->first();

                $before = $existing?->toArray();

                $roster = AtrPicMonthlyRoster::query()
                    ->updateOrCreate(
                        [
                            'atr_pic_roster_group_id' =>
                                $group->id,
                            'period' =>
                                $period->format('Y-m-d'),
                        ],
                        [
                            'pic_primary' =>
                                $primary,
                            'pic_backup' =>
                                $backup !== ''
                                    ? $backup
                                    : null,
                            'is_active' =>
                                true,
                            'created_by' =>
                                $existing?->created_by
                                ?? auth()->id(),
                            'updated_by' =>
                                auth()->id(),
                        ]
                    );

                $this->history(
                    $existing
                        ? 'MONTHLY_ROSTER_UPDATED'
                        : 'MONTHLY_ROSTER_CREATED',
                    $group,
                    null,
                    $before,
                    $roster->fresh()->toArray(),
                    'PIC Roster '
                    . $period
                        ->locale('id')
                        ->translatedFormat('F Y')
                    . ' disimpan.'
                );
            }
        );

        $this->picRoster->flushCache();

        return redirect()
            ->route(
                'database.atr.pic-roster',
                [
                    'period' =>
                        $period->format('Y-m'),
                ]
            )
            ->with(
                'success',
                'PIC Roster '
                . $group->label
                . ' untuk '
                . $period
                    ->locale('id')
                    ->translatedFormat('F Y')
                . ' berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UI SEDERHANA — DAFTAR MAPPING PIC ROSTER
    |--------------------------------------------------------------------------
    |
    | User tidak perlu lagi membuat GROUP lalu RULE secara terpisah.
    | Satu form "Tambah Mapping" akan membuat keduanya sekaligus.
    | Struktur group + rule tetap dipertahankan di backend karena engine
    | AtrPicRosterService tetap membutuhkannya.
    |
    */

    public function storeMapping(Request $request): RedirectResponse
    {
        $validated = $this->validateMapping($request);

        $positions = $this->normalizeSelectedPositions(
            $validated['positions'] ?? []
        );

        if ($positions->isEmpty()) {
            throw ValidationException::withMessages([
                'positions' =>
                    'Pilih minimal 1 posisi yang akan ditangani PIC Roster.',
            ]);
        }

        $this->ensurePositionsCanBeMapped($positions);

        [$group, $firstRule] = DB::transaction(
            function () use (
                $validated,
                $positions
            ): array {
                $label = $this->mappingGroupLabelFromPositions(
                    $validated['mapping_label'] ?? null,
                    $positions
                );

                $group = AtrPicRosterGroup::query()->create([
                    'code' => $this->nextMappingGroupCode($label),
                    'label' => $label,
                    'pic_primary' => trim($validated['pic_primary']),
                    'pic_backup' => $this->nullableTrim(
                        $validated['pic_backup'] ?? null
                    ),
                    'effective_from' =>
                        $validated['effective_from'] ?? null,
                    'effective_to' =>
                        $validated['effective_to'] ?? null,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $createdRules = collect();

                foreach ($positions as $position) {
                    $createdRules->push(
                        AtrPicRosterRule::query()->create([
                            'atr_pic_roster_group_id' => $group->id,
                            'match_type' => 'EXACT',
                            'pattern' => $position,
                            'priority' => 100,
                            'is_active' => true,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ])
                    );
                }

                /** @var AtrPicRosterRule $firstRule */
                $firstRule = $createdRules->first();

                $group->load('rules');

                $this->history(
                    'MAPPING_CREATED',
                    $group,
                    $firstRule,
                    null,
                    $this->mappingSetSnapshot($group),
                    'Mapping PIC Roster dibuat untuk '
                    . $positions->count()
                    . ' posisi.'
                );

                return [$group, $firstRule];
            }
        );

        $this->picRoster->flushCache();

        return redirect()
            ->route('database.atr.pic-roster')
            ->with(
                'success',
                "Mapping {$group->label} berhasil dibuat untuk "
                . $positions->count()
                . ' posisi.'
            );
    }

    public function updateMapping(
        Request $request,
        AtrPicRosterRule $rule
    ): RedirectResponse {
        $validated = $this->validateMapping($request);

        $rule->load('group.rules');

        $group = $rule->group;

        if (! $group) {
            throw ValidationException::withMessages([
                'mapping' =>
                    'Mapping tidak memiliki data PIC Roster.',
            ]);
        }

        $positions = $this->normalizeSelectedPositions(
            $validated['positions'] ?? []
        );

        $legacyKeywordRules = $group->rules
            ->where('match_type', 'KEYWORD')
            ->where('is_active', true);

        if (
            $positions->isEmpty()
            && $legacyKeywordRules->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'positions' =>
                    'Pilih minimal 1 posisi yang akan ditangani PIC Roster.',
            ]);
        }

        $this->ensurePositionsCanBeMapped(
            $positions,
            $group
        );

        DB::transaction(function () use (
            $validated,
            $positions,
            $group
        ): void {
            $group->load('rules');

            $before = $this->mappingSetSnapshot($group);

            $label = $this->mappingGroupLabelFromPositions(
                $validated['mapping_label'] ?? null,
                $positions,
                $group->label
            );

            $group->fill([
                'label' => $label,
                'pic_primary' => trim($validated['pic_primary']),
                'pic_backup' => $this->nullableTrim(
                    $validated['pic_backup'] ?? null
                ),
                'effective_from' =>
                    $validated['effective_from'] ?? null,
                'effective_to' =>
                    $validated['effective_to'] ?? null,
                'updated_by' => auth()->id(),
            ])->save();

            $selectedLookup = $positions
                ->mapWithKeys(
                    fn (string $position) => [$position => true]
                );

            /*
             * Rule EXACT adalah posisi yang dipilih user pada STEP PIC.2.
             * Rule KEYWORD lama tidak dihapus agar kompatibilitas mapping
             * yang sudah ada tetap aman.
             */
            $exactRules = $group->rules
                ->where('match_type', 'EXACT');

            foreach ($exactRules as $exactRule) {
                $shouldBeActive = $selectedLookup->has(
                    Str::upper(trim((string) $exactRule->pattern))
                );

                if ((bool) $exactRule->is_active !== $shouldBeActive) {
                    $exactRule->forceFill([
                        'is_active' => $shouldBeActive,
                        'updated_by' => auth()->id(),
                    ])->save();
                }
            }

            $existingExactLookup = $exactRules
                ->mapWithKeys(
                    fn (AtrPicRosterRule $existingRule) => [
                        Str::upper(
                            trim((string) $existingRule->pattern)
                        ) => $existingRule,
                    ]
                );

            foreach ($positions as $position) {
                if ($existingExactLookup->has($position)) {
                    continue;
                }

                AtrPicRosterRule::query()->create([
                    'atr_pic_roster_group_id' => $group->id,
                    'match_type' => 'EXACT',
                    'pattern' => $position,
                    'priority' => 100,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            $group->load('rules');

            $representativeRule = $group->rules
                ->firstWhere('is_active', true)
                ?? $group->rules->first();

            $this->history(
                'MAPPING_UPDATED',
                $group,
                $representativeRule,
                $before,
                $this->mappingSetSnapshot($group),
                'Mapping PIC Roster diperbarui. '
                . $positions->count()
                . ' posisi dipilih.'
            );
        });

        $this->picRoster->flushCache();

        return redirect()
            ->route('database.atr.pic-roster')
            ->with(
                'success',
                'Mapping PIC Roster berhasil diperbarui.'
            );
    }

    public function toggleMapping(
        AtrPicRosterRule $rule
    ): RedirectResponse {
        $rule->load('group.rules');

        $group = $rule->group;

        if (! $group) {
            return back()->withErrors([
                'mapping' =>
                    'Mapping tidak memiliki data PIC Roster.',
            ]);
        }

        DB::transaction(function () use ($group): void {
            $before = $this->mappingSetSnapshot($group);

            $group->forceFill([
                'is_active' => ! $group->is_active,
                'updated_by' => auth()->id(),
            ])->save();

            $group->load('rules');

            $representativeRule = $group->rules
                ->firstWhere('is_active', true)
                ?? $group->rules->first();

            $this->history(
                $group->is_active
                    ? 'MAPPING_ACTIVATED'
                    : 'MAPPING_DEACTIVATED',
                $group,
                $representativeRule,
                $before,
                $this->mappingSetSnapshot($group),
                $group->is_active
                    ? 'Mapping PIC Roster diaktifkan.'
                    : 'Mapping PIC Roster dinonaktifkan.'
            );
        });

        $this->picRoster->flushCache();

        return back()->with(
            'success',
            'Status mapping berhasil diperbarui.'
        );
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $this->validateGroup($request);

        $group = DB::transaction(function () use ($validated): AtrPicRosterGroup {
            $group = AtrPicRosterGroup::query()->create([
                ...$validated,
                'code' => Str::upper(
                    preg_replace(
                        '/[^A-Z0-9]+/',
                        '_',
                        Str::upper($validated['code'])
                    ) ?: $validated['code']
                ),
                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->history(
                'GROUP_CREATED',
                $group,
                null,
                null,
                $group->toArray(),
                'Kelompok PIC Roster dibuat.'
            );

            return $group;
        });

        $this->picRoster->flushCache();

        return back()->with(
            'success',
            "Kelompok {$group->label} berhasil dibuat."
        );
    }

    public function updateGroup(
        Request $request,
        AtrPicRosterGroup $group
    ): RedirectResponse {
        $validated = $this->validateGroup(
            $request,
            $group
        );

        DB::transaction(function () use (
            $validated,
            $group
        ): void {
            $before = $group->toArray();

            $group->fill([
                ...$validated,
                'code' => Str::upper(
                    preg_replace(
                        '/[^A-Z0-9]+/',
                        '_',
                        Str::upper($validated['code'])
                    ) ?: $validated['code']
                ),
                'updated_by' => auth()->id(),
            ])->save();

            $this->history(
                'GROUP_UPDATED',
                $group,
                null,
                $before,
                $group->fresh()->toArray(),
                'Pengaturan kelompok/PIC diperbarui.'
            );
        });

        $this->picRoster->flushCache();

        return back()->with(
            'success',
            'Pengaturan PIC Roster berhasil diperbarui.'
        );
    }

    public function toggleGroup(
        AtrPicRosterGroup $group
    ): RedirectResponse {
        DB::transaction(function () use ($group): void {
            $before = $group->toArray();

            $group->forceFill([
                'is_active' => ! $group->is_active,
                'updated_by' => auth()->id(),
            ])->save();

            $this->history(
                $group->is_active
                    ? 'GROUP_ACTIVATED'
                    : 'GROUP_DEACTIVATED',
                $group,
                null,
                $before,
                $group->fresh()->toArray(),
                $group->is_active
                    ? 'Kelompok diaktifkan.'
                    : 'Kelompok dinonaktifkan.'
            );
        });

        $this->picRoster->flushCache();

        return back()->with('success', 'Status kelompok diperbarui.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $this->validateRule($request);

        $rule = DB::transaction(function () use ($validated): AtrPicRosterRule {
            $rule = AtrPicRosterRule::query()->create([
                ...$validated,
                'pattern' => Str::upper(
                    trim($validated['pattern'])
                ),
                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->history(
                'RULE_CREATED',
                $rule->group,
                $rule,
                null,
                $rule->toArray(),
                'Rule Auto PIC dibuat.'
            );

            return $rule;
        });

        $this->picRoster->flushCache();

        return back()->with(
            'success',
            "Rule {$rule->pattern} berhasil ditambahkan."
        );
    }

    public function updateRule(
        Request $request,
        AtrPicRosterRule $rule
    ): RedirectResponse {
        $validated = $this->validateRule(
            $request,
            $rule
        );

        DB::transaction(function () use (
            $validated,
            $rule
        ): void {
            $before = $rule->toArray();

            $rule->fill([
                ...$validated,
                'pattern' => Str::upper(
                    trim($validated['pattern'])
                ),
                'updated_by' => auth()->id(),
            ])->save();

            $this->history(
                'RULE_UPDATED',
                $rule->group,
                $rule,
                $before,
                $rule->fresh()->toArray(),
                'Rule Auto PIC diperbarui.'
            );
        });

        $this->picRoster->flushCache();

        return back()->with('success', 'Rule Auto PIC diperbarui.');
    }

    public function toggleRule(
        AtrPicRosterRule $rule
    ): RedirectResponse {
        DB::transaction(function () use ($rule): void {
            $before = $rule->toArray();

            $rule->forceFill([
                'is_active' => ! $rule->is_active,
                'updated_by' => auth()->id(),
            ])->save();

            $this->history(
                $rule->is_active
                    ? 'RULE_ACTIVATED'
                    : 'RULE_DEACTIVATED',
                $rule->group,
                $rule,
                $before,
                $rule->fresh()->toArray(),
                $rule->is_active
                    ? 'Rule diaktifkan.'
                    : 'Rule dinonaktifkan.'
            );
        });

        $this->picRoster->flushCache();

        return back()->with('success', 'Status rule diperbarui.');
    }

    private function validateMapping(Request $request): array
    {
        $validated = $request->validate([
            'mapping_label' => [
                'nullable',
                'string',
                'max:120',
            ],
            'positions' => [
                'nullable',
                'array',
                'max:100',
            ],
            'positions.*' => [
                'string',
                'max:150',
                'distinct',
            ],
            'pic_primary' => [
                'required',
                'string',
                'max:150',
            ],
            'pic_backup' => [
                'nullable',
                'string',
                'max:150',
            ],
            'effective_from' => [
                'nullable',
                'date',
            ],
            'effective_to' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
        ], [
            'pic_primary.required' =>
                'PIC Roster 1 wajib diisi.',
            'pic_primary.max' =>
                'Nama PIC Roster 1 maksimal 150 karakter.',
            'pic_backup.max' =>
                'Nama PIC Roster 2 maksimal 150 karakter.',
            'positions.array' =>
                'Daftar posisi tidak valid.',
            'positions.max' =>
                'Maksimal 100 posisi dalam satu mapping.',
            'positions.*.distinct' =>
                'Posisi yang sama tidak boleh dipilih dua kali.',
            'effective_from.date' =>
                'Tanggal berlaku dari tidak valid.',
            'effective_to.date' =>
                'Tanggal berlaku sampai tidak valid.',
            'effective_to.after_or_equal' =>
                'Berlaku sampai tidak boleh sebelum berlaku dari.',
        ]);

        $picPrimary = Str::upper(
            trim((string) ($validated['pic_primary'] ?? ''))
        );

        $picBackup = Str::upper(
            trim((string) ($validated['pic_backup'] ?? ''))
        );

        if (
            $picBackup !== ''
            && $picPrimary === $picBackup
        ) {
            throw ValidationException::withMessages([
                'pic_backup' =>
                    'PIC Roster 2 harus berbeda dengan PIC Roster 1. '
                    . 'Kosongkan PIC Roster 2 jika hanya ada satu PIC.',
            ]);
        }

        $validated['mapping_label'] = $this->nullableTrim(
            $validated['mapping_label'] ?? null
        );

        $validated['pic_primary'] = trim(
            (string) $validated['pic_primary']
        );

        $validated['pic_backup'] = $this->nullableTrim(
            $validated['pic_backup'] ?? null
        );

        return $validated;
    }

    private function normalizeSelectedPositions(
        array $positions
    ) {
        return collect($positions)
            ->map(
                fn ($position) =>
                    Str::upper(trim((string) $position))
            )
            ->filter()
            ->unique()
            ->values();
    }

    private function ensurePositionsCanBeMapped(
        $positions,
        ?AtrPicRosterGroup $currentGroup = null
    ): void {
        if ($positions->isEmpty()) {
            return;
        }

        $availablePositions = $this->activePositionOptions()
            ->map(
                fn ($position) =>
                    Str::upper(trim((string) $position))
            )
            ->unique();

        if ($currentGroup) {
            $currentExactPatterns = $currentGroup->rules()
                ->where('match_type', 'EXACT')
                ->pluck('pattern')
                ->map(
                    fn ($position) =>
                        Str::upper(trim((string) $position))
                );

            $availablePositions = $availablePositions
                ->merge($currentExactPatterns)
                ->unique();
        }

        $unknown = $positions
            ->diff($availablePositions)
            ->values();

        if ($unknown->isNotEmpty()) {
            throw ValidationException::withMessages([
                'positions' =>
                    'Posisi berikut tidak ditemukan pada data ATR: '
                    . $unknown->implode(', '),
            ]);
        }

        $duplicates = AtrPicRosterRule::query()
            ->where('match_type', 'EXACT')
            ->whereIn('pattern', $positions->all())
            ->when(
                $currentGroup,
                fn ($query) =>
                    $query->where(
                        'atr_pic_roster_group_id',
                        '!=',
                        $currentGroup->id
                    )
            )
            ->with('group')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $details = $duplicates
                ->map(
                    fn (AtrPicRosterRule $duplicate) =>
                        $duplicate->pattern
                        . ' → '
                        . ($duplicate->group?->label ?? 'mapping lain')
                )
                ->implode('; ');

            throw ValidationException::withMessages([
                'positions' =>
                    'Sebagian posisi sudah memiliki mapping EXACT: '
                    . $details
                    . '. Edit mapping lama terlebih dahulu.',
            ]);
        }
    }

    private function activePositionOptions()
    {
        return AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) =>
                    $query->where('status', 'COMPLETED')
            )
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');
    }

    private function nextMappingGroupCode(string $source): string
    {
        $slug = Str::upper(
            preg_replace(
                '/[^A-Z0-9]+/',
                '_',
                Str::upper($source)
            ) ?: 'MAPPING'
        );

        $slug = trim($slug, '_');

        if ($slug === '') {
            $slug = 'MAPPING';
        }

        $base = Str::limit(
            'MAP_' . $slug,
            52,
            ''
        );

        $code = $base;
        $counter = 2;

        while (
            AtrPicRosterGroup::query()
                ->where('code', $code)
                ->exists()
        ) {
            $suffix = '_' . $counter;

            $code = Str::limit(
                $base,
                60 - strlen($suffix),
                ''
            ) . $suffix;

            $counter++;
        }

        return $code;
    }

    private function mappingGroupLabelFromPositions(
        mixed $requestedLabel,
        $positions,
        ?string $fallbackLabel = null
    ): string {
        $requestedLabel = trim(
            (string) ($requestedLabel ?? '')
        );

        if ($requestedLabel !== '') {
            return Str::limit(
                $requestedLabel,
                120,
                ''
            );
        }

        if ($fallbackLabel) {
            return Str::limit(
                trim($fallbackLabel),
                120,
                ''
            );
        }

        $first = (string) ($positions->first() ?? 'PIC ROSTER');

        if ($positions->count() <= 1) {
            return Str::limit($first, 120, '');
        }

        return Str::limit(
            $first . ' +' . ($positions->count() - 1) . ' posisi',
            120,
            ''
        );
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function mappingSetSnapshot(
        AtrPicRosterGroup $group
    ): array {
        $group->loadMissing('rules');

        return [
            'group_id' => $group->id,
            'code' => $group->code,
            'label' => $group->label,
            'group_active' => (bool) $group->is_active,
            'pic_primary' => $group->pic_primary,
            'pic_backup' => $group->pic_backup,
            'effective_from' =>
                $group->effective_from?->format('Y-m-d'),
            'effective_to' =>
                $group->effective_to?->format('Y-m-d'),
            'positions_exact' => $group->rules
                ->where('match_type', 'EXACT')
                ->where('is_active', true)
                ->pluck('pattern')
                ->values()
                ->all(),
            'legacy_keywords' => $group->rules
                ->where('match_type', 'KEYWORD')
                ->where('is_active', true)
                ->pluck('pattern')
                ->values()
                ->all(),
        ];
    }

    private function validateGroup(
        Request $request,
        ?AtrPicRosterGroup $group = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('atr_pic_roster_groups', 'code')
                    ->ignore($group?->id),
            ],
            'label' => [
                'required',
                'string',
                'max:120',
            ],
            'pic_primary' => [
                'required',
                'string',
                'max:150',
            ],
            'pic_backup' => [
                'nullable',
                'string',
                'max:150',
            ],
            'effective_from' => [
                'nullable',
                'date',
            ],
            'effective_to' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
        ]);
    }

    private function validateRule(
        Request $request,
        ?AtrPicRosterRule $rule = null
    ): array {
        return $request->validate([
            'atr_pic_roster_group_id' => [
                'required',
                'integer',
                'exists:atr_pic_roster_groups,id',
            ],
            'match_type' => [
                'required',
                Rule::in(['EXACT', 'KEYWORD']),
            ],
            'pattern' => [
                'required',
                'string',
                'max:150',
            ],
            'priority' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
        ]);
    }

    private function history(
        string $action,
        ?AtrPicRosterGroup $group,
        ?AtrPicRosterRule $rule,
        ?array $before,
        ?array $after,
        ?string $notes = null
    ): void {
        $user = auth()->user();

        AtrPicRosterHistory::query()->create([
            'atr_pic_roster_group_id' => $group?->id,
            'atr_pic_roster_rule_id' => $rule?->id,
            'action' => $action,
            'actor_user_id' => auth()->id(),
            'actor_name' =>
                trim((string) ($user?->name ?? ''))
                ?: trim((string) ($user?->email ?? ''))
                ?: 'SYSTEM',
            'notes' => $notes,
            'before_data' => $before,
            'after_data' => $after,
        ]);
    }
}
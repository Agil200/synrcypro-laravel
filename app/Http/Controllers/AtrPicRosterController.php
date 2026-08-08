<?php

namespace App\Http\Controllers;

use App\Models\AtrPicRosterGroup;
use App\Models\AtrPicRosterHistory;
use App\Models\AtrPicRosterRule;
use App\Models\AtrRecord;
use App\Services\AtrPicRosterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AtrPicRosterController extends Controller
{
    public function __construct(
        private readonly AtrPicRosterService $picRoster
    ) {
    }

    public function index(Request $request): View
    {
        $groups = AtrPicRosterGroup::query()
            ->with([
                'rules' => fn ($query) =>
                    $query->orderBy('priority')->orderBy('id'),
            ])
            ->orderBy('label')
            ->get();

        $rules = AtrPicRosterRule::query()
            ->with('group')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Diagnosis posisi aktual dari ATR aktif
        |--------------------------------------------------------------------------
        */
        $positions = AtrRecord::query()
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

        $diagnostics = $positions
            ->map(function (string $position): array {
                return $this->picRoster->resolve($position);
            })
            ->values();

        $unmapped = $diagnostics
            ->where('matched', false)
            ->values();

        $testPosition = trim(
            $request->string('test_position')->toString()
        );

        $testResult = $testPosition !== ''
            ? $this->picRoster->resolve($testPosition)
            : null;

        $histories = AtrPicRosterHistory::query()
            ->with(['group', 'rule', 'actor'])
            ->latest('id')
            ->limit(30)
            ->get();

        $activeGroups = $groups->where('is_active', true);

        $activePics = $activeGroups
            ->pluck('pic_primary')
            ->merge($activeGroups->pluck('pic_backup'))
            ->filter()
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        return view('database', [
            'contentView' => 'database.atr.pic-roster',
            'activePage' => 'atr-pic-roster',
            'rosterGroups' => $groups,
            'rosterRules' => $rules,
            'rosterHistories' => $histories,
            'rosterDiagnostics' => $diagnostics,
            'unmappedPositions' => $unmapped,
            'testPosition' => $testPosition,
            'testResult' => $testResult,
            'picRosterStats' => [
                'total_groups' => $groups->count(),
                'active_assignments' => $activeGroups->count(),
                'active_pics' => $activePics->count(),
                'unmapped_positions' => $unmapped->count(),
            ],
        ]);
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
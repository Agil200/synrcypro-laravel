<?php

namespace App\Services;

use App\Models\AtrPicMonthlyRoster;
use App\Models\AtrPicRosterRule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AtrPicRosterService
{
    private const CATEGORY_CACHE_KEY = 'atr_pic_roster_categories:v3';

    /**
     * Resolve posisi ATR menjadi:
     * POSISI -> KATEGORI -> PIC BULANAN.
     */
    public function resolve(
        ?string $position,
        CarbonInterface|string|null $at = null
    ): array {
        $raw = trim((string) $position);

        if ($raw === '') {
            return $this->unresolved(
                $raw,
                'Posisi kosong.'
            );
        }

        /*
         * Sebelum migration monthly dijalankan, sistem lama tetap bisa hidup.
         */
        if (
            ! Schema::hasTable('atr_pic_roster_groups')
            || ! Schema::hasTable('atr_pic_roster_rules')
        ) {
            return $this->unresolved(
                $raw,
                'Master kategori PIC Roster belum dimigrasikan.'
            );
        }

        $category = $this->categoryFor($raw);

        if (! $category['matched']) {
            return $this->unresolved(
                $raw,
                'Posisi belum memiliki kategori PIC Roster.'
            );
        }

        $period = $this->resolveDate($at)->startOfMonth();

        /*
         * Monthly table adalah sumber resmi PIC setelah migration.
         */
        if (Schema::hasTable('atr_pic_monthly_rosters')) {
            $monthly = AtrPicMonthlyRoster::query()
                ->where(
                    'atr_pic_roster_group_id',
                    $category['group_id']
                )
                ->whereDate(
                    'period',
                    $period->format('Y-m-d')
                )
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if (! $monthly) {
                return $this->unresolvedMonthly(
                    $raw,
                    $category,
                    $period,
                    'PIC periode ini belum diisi.'
                );
            }

            $primary = trim(
                (string) $monthly->pic_primary
            );

            if ($primary === '') {
                return $this->unresolvedMonthly(
                    $raw,
                    $category,
                    $period,
                    'PIC Roster 1 periode ini belum diisi.'
                );
            }

            return [
                'matched' => true,
                'position' => $raw,
                'normalized_position' =>
                    $this->normalize($raw),

                'period' => $period->format('Y-m-d'),

                'group_id' => $category['group_id'],
                'group_code' => $category['group_code'],
                'group_label' => $category['group_label'],
                'category_label' => $category['group_label'],

                'pic_primary' => $primary,
                'pic_backup' => trim(
                    (string) $monthly->pic_backup
                ) ?: null,

                'rule_id' => $category['rule_id'],
                'rule_type' => $category['rule_type'],
                'rule_pattern' => $category['rule_pattern'],
                'priority' => $category['priority'],

                'monthly_roster_id' => $monthly->id,
                'reason' => null,
            ];
        }

        /*
         * Fallback kompatibilitas sebelum monthly migration tersedia.
         */
        $rule = $category['rule'];

        return [
            'matched' => true,
            'position' => $raw,
            'normalized_position' =>
                $this->normalize($raw),

            'period' => $period->format('Y-m-d'),

            'group_id' => $rule->group->id,
            'group_code' => $rule->group->code,
            'group_label' => $rule->group->label,
            'category_label' => $rule->group->label,

            'pic_primary' => $rule->group->pic_primary,
            'pic_backup' => $rule->group->pic_backup,

            'rule_id' => $rule->id,
            'rule_type' => $rule->match_type,
            'rule_pattern' => $rule->pattern,
            'priority' => $rule->priority,

            'monthly_roster_id' => null,
            'reason' => null,
        ];
    }

    /**
     * Hanya menentukan kategori, tanpa melihat PIC bulanannya.
     * Dipakai halaman Pengaturan PIC Roster.
     */
    public function categoryFor(?string $position): array
    {
        $raw = trim((string) $position);
        $normalized = $this->normalize($raw);

        if ($normalized === '') {
            return $this->unmatchedCategory(
                $raw,
                'Posisi kosong.'
            );
        }

        $rules = $this->categoryRules();

        $exact = $rules
            ->where('match_type', 'EXACT')
            ->first(
                fn (AtrPicRosterRule $rule): bool =>
                    $this->normalize($rule->pattern)
                    === $normalized
            );

        if ($exact) {
            return $this->matchedCategory(
                $raw,
                $exact
            );
        }

        $keyword = $rules
            ->where('match_type', 'KEYWORD')
            ->sort(function (
                AtrPicRosterRule $a,
                AtrPicRosterRule $b
            ): int {
                $priority = $a->priority <=> $b->priority;

                if ($priority !== 0) {
                    return $priority;
                }

                return mb_strlen($b->pattern)
                    <=> mb_strlen($a->pattern);
            })
            ->first(
                fn (AtrPicRosterRule $rule): bool =>
                    $this->keywordMatches(
                        $normalized,
                        $this->normalize($rule->pattern)
                    )
            );

        if ($keyword) {
            return $this->matchedCategory(
                $raw,
                $keyword
            );
        }

        return $this->unmatchedCategory(
            $raw,
            'Belum ada kategori untuk posisi ini.'
        );
    }

    public function resolveName(
        ?string $position,
        CarbonInterface|string|null $at = null
    ): string {
        $result = $this->resolve(
            $position,
            $at
        );

        return $result['matched']
            ? $result['pic_primary']
            : 'PIC BELUM TERDAFTAR';
    }

    public function flushCache(): void
    {
        Cache::forget(self::CATEGORY_CACHE_KEY);
    }

    private function categoryRules(): Collection
    {
        return Cache::remember(
            self::CATEGORY_CACHE_KEY,
            now()->addMinutes(5),
            fn () => AtrPicRosterRule::query()
                ->with('group')
                ->where('is_active', true)
                ->whereHas(
                    'group',
                    fn ($query) =>
                        $query->where('is_active', true)
                )
                ->orderBy('priority')
                ->orderByDesc('id')
                ->get()
        );
    }

    private function matchedCategory(
        string $raw,
        AtrPicRosterRule $rule
    ): array {
        return [
            'matched' => true,
            'position' => $raw,
            'normalized_position' =>
                $this->normalize($raw),

            'group_id' => $rule->group->id,
            'group_code' => $rule->group->code,
            'group_label' => $rule->group->label,

            'rule_id' => $rule->id,
            'rule_type' => $rule->match_type,
            'rule_pattern' => $rule->pattern,
            'priority' => $rule->priority,

            /*
             * Hanya untuk fallback internal sebelum monthly migration.
             * Jangan dipakai langsung oleh view.
             */
            'rule' => $rule,

            'reason' => null,
        ];
    }

    private function unmatchedCategory(
        string $raw,
        string $reason
    ): array {
        return [
            'matched' => false,
            'position' => $raw,
            'normalized_position' =>
                $this->normalize($raw),

            'group_id' => null,
            'group_code' => null,
            'group_label' => null,

            'rule_id' => null,
            'rule_type' => null,
            'rule_pattern' => null,
            'priority' => null,

            'rule' => null,
            'reason' => $reason,
        ];
    }

    private function unresolvedMonthly(
        string $raw,
        array $category,
        CarbonInterface $period,
        string $reason
    ): array {
        return [
            'matched' => false,
            'position' => $raw,
            'normalized_position' =>
                $this->normalize($raw),

            'period' => $period->format('Y-m-d'),

            'group_id' => $category['group_id'],
            'group_code' => $category['group_code'],
            'group_label' => $category['group_label'],
            'category_label' => $category['group_label'],

            'pic_primary' => 'PIC BELUM DIISI',
            'pic_backup' => null,

            'rule_id' => $category['rule_id'],
            'rule_type' => $category['rule_type'],
            'rule_pattern' => $category['rule_pattern'],
            'priority' => $category['priority'],

            'monthly_roster_id' => null,
            'reason' => $reason,
        ];
    }

    private function unresolved(
        string $raw,
        string $reason
    ): array {
        return [
            'matched' => false,
            'position' => $raw,
            'normalized_position' =>
                $this->normalize($raw),

            'period' => null,

            'group_id' => null,
            'group_code' => null,
            'group_label' => null,
            'category_label' => null,

            'pic_primary' => 'PIC BELUM TERDAFTAR',
            'pic_backup' => null,

            'rule_id' => null,
            'rule_type' => null,
            'rule_pattern' => null,
            'priority' => null,

            'monthly_roster_id' => null,
            'reason' => $reason,
        ];
    }

    private function keywordMatches(
        string $position,
        string $pattern
    ): bool {
        if ($pattern === '') {
            return false;
        }

        $positionTokens = preg_split(
            '/\s+/',
            $position,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $patternTokens = preg_split(
            '/\s+/',
            $pattern,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        /*
         * Satu token keluarga unit:
         *
         * PC -> PC 1250 / PC1250 / PC-1250
         * DZ -> DZ 999  / DZ999
         * HD -> HD 785  / HD785
         *
         * normalize() sudah mengubah PC-1250 menjadi PC 1250.
         * Matching tetap berbasis token/batas kata agar "PC" tidak cocok
         * sembarangan di tengah kata lain.
         */
        if (count($patternTokens) === 1) {
            $keyword = $patternTokens[0];

            /*
             * Bentuk terpisah:
             * OPERATOR PC 1250 -> token "PC" ada.
             */
            if (in_array(
                $keyword,
                $positionTokens,
                true
            )) {
                return true;
            }

            if (
                preg_match(
                    '/^[A-Z]+$/',
                    $keyword
                ) === 1
            ) {
                /*
                 * Bentuk menempel:
                 * OPERATOR PC1250 -> token "PC1250".
                 */
                $attachedRegex = '/^'
                    . preg_quote($keyword, '/')
                    . '[0-9]+[A-Z0-9]*$/';

                foreach ($positionTokens as $token) {
                    if (
                        preg_match(
                            $attachedRegex,
                            $token
                        ) === 1
                    ) {
                        return true;
                    }
                }

                /*
                 * Bentuk keluarga + nomor setelah normalisasi:
                 * PC 1250 / PC-1250.
                 */
                $familyRegex = '/(?:^|\s)'
                    . preg_quote($keyword, '/')
                    . '\s*[0-9]+[A-Z0-9]*(?:\s|$)/';

                if (
                    preg_match(
                        $familyRegex,
                        $position
                    ) === 1
                ) {
                    return true;
                }
            }

            return false;
        }

        return str_contains(
            ' ' . $position . ' ',
            ' ' . $pattern . ' '
        );
    }

    private function normalize(string $value): string
    {
        $value = mb_strtoupper(
            trim($value)
        );

        $value = preg_replace(
            '/[^A-Z0-9]+/u',
            ' ',
            $value
        ) ?: '';

        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                $value
            ) ?: ''
        );
    }

    private function resolveDate(
        CarbonInterface|string|null $at
    ): CarbonInterface {
        if ($at instanceof CarbonInterface) {
            return Carbon::instance($at)
                ->startOfDay();
        }

        if (
            is_string($at)
            && trim($at) !== ''
        ) {
            try {
                return Carbon::parse($at)
                    ->startOfDay();
            } catch (\Throwable) {
                // fallback ke hari ini
            }
        }

        return now()->startOfDay();
    }
}
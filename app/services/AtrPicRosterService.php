<?php

namespace App\Services;

use App\Models\AtrPicRosterRule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AtrPicRosterService
{
    private const CACHE_KEY = 'atr_pic_roster_rules:v1';

    public function resolve(
        ?string $position,
        CarbonInterface|string|null $at = null
    ): array {
        $raw = trim((string) $position);

        if ($raw === '') {
            return $this->unresolved($raw, 'Posisi kosong.');
        }

        if (
            ! Schema::hasTable('atr_pic_roster_groups')
            || ! Schema::hasTable('atr_pic_roster_rules')
        ) {
            return $this->unresolved(
                $raw,
                'Master PIC Roster belum dimigrasikan.'
            );
        }

        $date = $this->resolveDate($at);
        $normalized = $this->normalize($raw);

        $rules = $this->rules();

        /*
        |--------------------------------------------------------------------------
        | EXACT selalu menang
        |--------------------------------------------------------------------------
        */
        $exact = $rules
            ->where('match_type', 'EXACT')
            ->first(function (AtrPicRosterRule $rule) use ($normalized, $date): bool {
                return $this->groupIsEffective($rule, $date)
                    && $this->normalize($rule->pattern) === $normalized;
            });

        if ($exact) {
            return $this->resolved($raw, $normalized, $exact, 'EXACT');
        }

        /*
        |--------------------------------------------------------------------------
        | KEYWORD
        |--------------------------------------------------------------------------
        |
        | Diurutkan dari priority terkecil, lalu pola terpanjang.
        | Contoh:
        | "WATER TRUCK HD" harus menang sebelum keyword umum "HD".
        |
        */
        $keyword = $rules
            ->where('match_type', 'KEYWORD')
            ->filter(
                fn (AtrPicRosterRule $rule): bool =>
                    $this->groupIsEffective($rule, $date)
            )
            ->sort(function (AtrPicRosterRule $a, AtrPicRosterRule $b): int {
                $priority = $a->priority <=> $b->priority;

                if ($priority !== 0) {
                    return $priority;
                }

                return mb_strlen($b->pattern) <=> mb_strlen($a->pattern);
            })
            ->first(
                fn (AtrPicRosterRule $rule): bool =>
                    $this->keywordMatches(
                        $normalized,
                        $this->normalize($rule->pattern)
                    )
            );

        if ($keyword) {
            return $this->resolved(
                $raw,
                $normalized,
                $keyword,
                'KEYWORD'
            );
        }

        return $this->unresolved(
            $raw,
            'Tidak ada rule PIC Roster yang cocok.'
        );
    }

    public function resolveName(
        ?string $position,
        CarbonInterface|string|null $at = null
    ): string {
        $result = $this->resolve($position, $at);

        return $result['matched']
            ? $result['pic_primary']
            : 'PIC BELUM TERDAFTAR';
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function rules(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(5),
            fn () => AtrPicRosterRule::query()
                ->with('group')
                ->where('is_active', true)
                ->orderBy('priority')
                ->orderByDesc('id')
                ->get()
        );
    }

    private function groupIsEffective(
        AtrPicRosterRule $rule,
        CarbonInterface $date
    ): bool {
        $group = $rule->group;

        if (! $group || ! $group->is_active) {
            return false;
        }

        if (
            $group->effective_from
            && $date->lt($group->effective_from->startOfDay())
        ) {
            return false;
        }

        if (
            $group->effective_to
            && $date->gt($group->effective_to->endOfDay())
        ) {
            return false;
        }

        return trim((string) $group->pic_primary) !== '';
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
         * Keyword satu token seperti PC, DZ, HD harus menjadi token utuh.
         * Jadi PC tidak salah menembak teks lain yang kebetulan mengandung PC.
         */
        if (count($patternTokens) === 1) {
            return in_array(
                $patternTokens[0],
                $positionTokens,
                true
            );
        }

        return str_contains(
            ' ' . $position . ' ',
            ' ' . $pattern . ' '
        );
    }

    private function resolved(
        string $raw,
        string $normalized,
        AtrPicRosterRule $rule,
        string $matchType
    ): array {
        return [
            'matched' => true,
            'position' => $raw,
            'normalized_position' => $normalized,
            'group_id' => $rule->group->id,
            'group_code' => $rule->group->code,
            'group_label' => $rule->group->label,
            'pic_primary' => $rule->group->pic_primary,
            'pic_backup' => $rule->group->pic_backup,
            'rule_id' => $rule->id,
            'rule_type' => $matchType,
            'rule_pattern' => $rule->pattern,
            'priority' => $rule->priority,
            'reason' => null,
        ];
    }

    private function unresolved(
        string $raw,
        string $reason
    ): array {
        return [
            'matched' => false,
            'position' => $raw,
            'normalized_position' => $this->normalize($raw),
            'group_id' => null,
            'group_code' => null,
            'group_label' => null,
            'pic_primary' => 'PIC BELUM TERDAFTAR',
            'pic_backup' => null,
            'rule_id' => null,
            'rule_type' => null,
            'rule_pattern' => null,
            'priority' => null,
            'reason' => $reason,
        ];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtoupper(trim($value));
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value) ?: '';

        return trim(
            preg_replace('/\s+/', ' ', $value) ?: ''
        );
    }

    private function resolveDate(
        CarbonInterface|string|null $at
    ): CarbonInterface {
        if ($at instanceof CarbonInterface) {
            return Carbon::instance($at)->startOfDay();
        }

        if (is_string($at) && trim($at) !== '') {
            try {
                return Carbon::parse($at)->startOfDay();
            } catch (\Throwable) {
                // fallback ke hari ini
            }
        }

        return now()->startOfDay();
    }
}
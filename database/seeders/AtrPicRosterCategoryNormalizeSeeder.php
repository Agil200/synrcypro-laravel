<?php

namespace Database\Seeders;

use App\Models\AtrPicMonthlyRoster;
use App\Models\AtrPicRosterGroup;
use App\Models\AtrPicRosterRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtrPicRosterCategoryNormalizeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            /*
            |--------------------------------------------------------------------------
            | MASTER KATEGORI CANONICAL
            |--------------------------------------------------------------------------
            |
            | Group + rule lama sekarang berfungsi sebagai MASTER KATEGORI POSISI.
            | Karena itu kategori canonical harus aktif walaupun dulu pernah
            | dinonaktifkan dari UI mapping lama.
            |
            */
            $categories = [
                'EXCAVATOR' => [
                    'label' => 'OPERATOR PC',
                    'rules' => [
                        ['KEYWORD', 'PC', 50],
                        ['KEYWORD', 'EXCAVATOR', 60],
                    ],
                ],
                'DOZER' => [
                    'label' => 'OPERATOR DOZER',
                    'rules' => [
                        ['KEYWORD', 'DOZER', 20],
                        ['KEYWORD', 'DZ', 50],
                        ['KEYWORD', 'BULLDOZER', 20],
                    ],
                ],
                'HD_CAT' => [
                    'label' => 'OPERATOR HD',
                    'rules' => [
                        ['KEYWORD', 'CAT 777', 20],
                        ['KEYWORD', 'HD', 50],
                    ],
                ],
                'WHEEL_LOADER' => [
                    'label' => 'OPERATOR WHEEL LOADER',
                    'rules' => [
                        ['KEYWORD', 'WHEEL LOADER', 20],
                    ],
                ],
                'VIBRO' => [
                    'label' => 'OPERATOR VIBRO',
                    'rules' => [
                        ['KEYWORD', 'VIBRO', 20],
                    ],
                ],
                'GRADER' => [
                    'label' => 'OPERATOR GRADER',
                    'rules' => [
                        ['KEYWORD', 'GRADER', 20],
                    ],
                ],
                'WTHD' => [
                    'label' => 'OPERATOR WATER TRUCK HD',
                    'rules' => [
                        ['KEYWORD', 'WATER TRUCK HD', 10],
                    ],
                ],
                'WTDT' => [
                    'label' => 'OPERATOR WATER TRUCK DT',
                    'rules' => [
                        ['KEYWORD', 'WATER TRUCK DT', 10],
                    ],
                ],
                'DT_TRINTIN' => [
                    'label' => 'OPERATOR DT TRINTIN',
                    'rules' => [
                        ['KEYWORD', 'DT TRINTIN', 10],
                        ['KEYWORD', 'TRINTIN', 20],
                    ],
                ],
                'DT_TRONTON' => [
                    'label' => 'OPERATOR DT TRONTON',
                    'rules' => [
                        ['KEYWORD', 'DT TRONTON', 10],
                        ['KEYWORD', 'TRONTON', 20],
                    ],
                ],
            ];

            foreach ($categories as $code => $config) {
                $group = AtrPicRosterGroup::query()
                    ->where('code', $code)
                    ->first();

                if (! $group) {
                    continue;
                }

                $group->forceFill([
                    'label' => $config['label'],
                    'is_active' => true,
                ])->save();

                foreach ($config['rules'] as [$type, $pattern, $priority]) {
                    $rule = AtrPicRosterRule::query()
                        ->where(
                            'atr_pic_roster_group_id',
                            $group->id
                        )
                        ->where('match_type', $type)
                        ->whereRaw(
                            'UPPER(TRIM(pattern)) = ?',
                            [mb_strtoupper(trim($pattern))]
                        )
                        ->first();

                    if ($rule) {
                        $rule->forceFill([
                            'priority' => $priority,
                            'is_active' => true,
                        ])->save();
                    } else {
                        AtrPicRosterRule::query()->create([
                            'atr_pic_roster_group_id' => $group->id,
                            'match_type' => $type,
                            'pattern' => $pattern,
                            'priority' => $priority,
                            'is_active' => true,
                        ]);
                    }
                }

                $this->backfillMonthlyRoster($group);
            }

            /*
            |--------------------------------------------------------------------------
            | CAT 777 DIGABUNG KE OPERATOR HD
            |--------------------------------------------------------------------------
            |
            | Mapping CAT 777 standalone lama tidak dihapus. Rule CAT 777 dipindah
            | ke HD_CAT, lalu group standalone dinonaktifkan bila sudah kosong.
            |
            */
            $hdGroup = AtrPicRosterGroup::query()
                ->where('code', 'HD_CAT')
                ->first();

            if ($hdGroup) {
                $catRules = AtrPicRosterRule::query()
                    ->where(function ($query): void {
                        $query
                            ->whereRaw(
                                'UPPER(TRIM(pattern)) = ?',
                                ['CAT 777']
                            )
                            ->orWhereRaw(
                                'UPPER(TRIM(pattern)) = ?',
                                ['OPERATOR CAT 777']
                            );
                    })
                    ->get();

                foreach ($catRules as $catRule) {
                    $oldGroupId = $catRule->atr_pic_roster_group_id;

                    $catRule->forceFill([
                        'atr_pic_roster_group_id' => $hdGroup->id,
                        'match_type' => 'KEYWORD',
                        'pattern' => 'CAT 777',
                        'priority' => 20,
                        'is_active' => true,
                    ])->save();

                    if (
                        $oldGroupId
                        && $oldGroupId !== $hdGroup->id
                    ) {
                        $oldGroup = AtrPicRosterGroup::query()
                            ->find($oldGroupId);

                        if (
                            $oldGroup
                            && ! $oldGroup->rules()
                                ->where('is_active', true)
                                ->exists()
                        ) {
                            $oldGroup->forceFill([
                                'is_active' => false,
                            ])->save();
                        }
                    }
                }

                $this->backfillMonthlyRoster($hdGroup);
            }

            /*
             * CRANE dibuat sebelumnya melalui UI sehingga code bisa dinamis.
             */
            $craneRule = AtrPicRosterRule::query()
                ->with('group')
                ->where('is_active', true)
                ->whereRaw(
                    'UPPER(TRIM(pattern)) LIKE ?',
                    ['%CRANE%']
                )
                ->first();

            if ($craneRule?->group) {
                $craneRule->group->forceFill([
                    'label' => 'OPERATOR CRANE',
                    'is_active' => true,
                ])->save();

                $craneRule->forceFill([
                    'is_active' => true,
                ])->save();

                $this->backfillMonthlyRoster(
                    $craneRule->group
                );
            }
        });
    }

    /**
     * Jika monthly roster belum ada, salin assignment lama sebagai snapshot
     * pada bulan effective_from. Ini menjaga PIC Agustus yang sudah digunakan.
     */
    private function backfillMonthlyRoster(
        AtrPicRosterGroup $group
    ): void {
        $primary = trim(
            (string) $group->pic_primary
        );

        if ($primary === '') {
            return;
        }

        try {
            $period = $group->effective_from
                ? Carbon::parse(
                    $group->effective_from
                )->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $period = now()->startOfMonth();
        }

        /*
         * SQLite + date cast bisa membuat firstOrCreate tidak mengenali record
         * existing walaupun UNIQUE(group, period) sudah terisi. Karena itu
         * cek tanggal secara eksplisit dengan whereDate().
         *
         * PENTING: jika roster bulanan sudah ada, JANGAN overwrite PIC-nya.
         */
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

        if ($existing) {
            return;
        }

        AtrPicMonthlyRoster::query()->create([
            'atr_pic_roster_group_id' =>
                $group->id,
            'period' =>
                $period->format('Y-m-d'),
            'pic_primary' =>
                $primary,
            'pic_backup' =>
                trim(
                    (string) $group->pic_backup
                ) ?: null,
            'is_active' =>
                true,
            'created_by' =>
                $group->created_by,
            'updated_by' =>
                $group->updated_by,
        ]);
    }
}
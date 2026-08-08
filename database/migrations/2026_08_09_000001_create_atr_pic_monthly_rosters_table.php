<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atr_pic_monthly_rosters', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('atr_pic_roster_group_id')
                ->constrained('atr_pic_roster_groups')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Selalu disimpan tanggal pertama bulan.
             * Contoh Agustus 2026 = 2026-08-01.
             */
            $table->date('period');

            $table->string('pic_primary', 150)->nullable();
            $table->string('pic_backup', 150)->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['atr_pic_roster_group_id', 'period'],
                'atr_pic_monthly_group_period_unique'
            );

            $table->index(
                ['period', 'is_active'],
                'atr_pic_monthly_period_active_idx'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | BACKFILL AMAN
        |--------------------------------------------------------------------------
        |
        | Assignment lama tidak dibuang. Untuk menjaga kondisi Agustus/current
        | tetap hidup setelah migration, PIC lama disalin menjadi snapshot bulanan.
        |
        | Setelah ini, perubahan bulan berikutnya hanya terjadi di tabel monthly.
        */
        if (
            Schema::hasTable('atr_pic_roster_groups')
            && Schema::hasTable('atr_pic_monthly_rosters')
        ) {
            $groups = DB::table('atr_pic_roster_groups')
                ->where('is_active', true)
                ->get();

            foreach ($groups as $group) {
                $primary = trim((string) ($group->pic_primary ?? ''));

                if ($primary === '') {
                    continue;
                }

                try {
                    $period = !empty($group->effective_from)
                        ? Carbon::parse($group->effective_from)->startOfMonth()
                        : now()->startOfMonth();
                } catch (\Throwable) {
                    $period = now()->startOfMonth();
                }

                DB::table('atr_pic_monthly_rosters')->updateOrInsert(
                    [
                        'atr_pic_roster_group_id' => $group->id,
                        'period' => $period->format('Y-m-d'),
                    ],
                    [
                        'pic_primary' => $primary,
                        'pic_backup' => trim(
                            (string) ($group->pic_backup ?? '')
                        ) ?: null,
                        'is_active' => true,
                        'created_by' => $group->created_by ?? null,
                        'updated_by' => $group->updated_by ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_pic_monthly_rosters');
    }
};

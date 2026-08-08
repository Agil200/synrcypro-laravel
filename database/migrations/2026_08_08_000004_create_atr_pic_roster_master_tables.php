<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('atr_pic_roster_groups')) {
            Schema::create('atr_pic_roster_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('label', 120);
                $table->string('pic_primary', 150);
                $table->string('pic_backup', 150)->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['is_active', 'effective_from', 'effective_to']);
            });
        }

        if (! Schema::hasTable('atr_pic_roster_rules')) {
            Schema::create('atr_pic_roster_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('atr_pic_roster_group_id')
                    ->constrained('atr_pic_roster_groups')
                    ->cascadeOnDelete();

                /*
                | EXACT   : posisi harus sama setelah normalisasi.
                | KEYWORD : pola harus muncul sebagai token/frasa posisi.
                */
                $table->string('match_type', 20)->default('KEYWORD');
                $table->string('pattern', 150);
                $table->unsignedInteger('priority')->default(100);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['is_active', 'match_type', 'priority']);
                $table->unique(
                    ['atr_pic_roster_group_id', 'match_type', 'pattern'],
                    'atr_pic_roster_rules_unique_pattern'
                );
            });
        }

        if (! Schema::hasTable('atr_pic_roster_histories')) {
            Schema::create('atr_pic_roster_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('atr_pic_roster_group_id')
                    ->nullable()
                    ->constrained('atr_pic_roster_groups')
                    ->nullOnDelete();
                $table->foreignId('atr_pic_roster_rule_id')
                    ->nullable()
                    ->constrained('atr_pic_roster_rules')
                    ->nullOnDelete();
                $table->string('action', 50);
                $table->foreignId('actor_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->string('actor_name', 150)->nullable();
                $table->text('notes')->nullable();
                $table->json('before_data')->nullable();
                $table->json('after_data')->nullable();
                $table->timestamps();

                $table->index(['action', 'created_at']);
            });
        }

        $this->seedDefaults();
    }

    private function seedDefaults(): void
    {
        if (
            DB::table('atr_pic_roster_groups')->exists()
            || DB::table('atr_pic_roster_rules')->exists()
        ) {
            return;
        }

        $now = now();
        $effectiveFrom = '2026-08-01';

        $groups = [
            [
                'code' => 'EXCAVATOR',
                'label' => 'Excavator / PC',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
            [
                'code' => 'DOZER',
                'label' => 'Dozer / DZ',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
            ],
            [
                'code' => 'WHEEL_LOADER',
                'label' => 'Wheel Loader',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
            [
                'code' => 'VIBRO',
                'label' => 'Vibro',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
            [
                'code' => 'HD_CAT',
                'label' => 'HD / CAT',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
            ],
            [
                'code' => 'DT_TRINTIN',
                'label' => 'DT Trintin',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
            ],
            [
                'code' => 'DT_TRONTON',
                'label' => 'DT Tronton',
                'pic_primary' => 'IRFAN WIBAWA',
                'pic_backup' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
            ],
            [
                'code' => 'WTHD',
                'label' => 'Water Truck HD',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
            [
                'code' => 'WTDT',
                'label' => 'Water Truck DT',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
            [
                'code' => 'GRADER',
                'label' => 'Grader',
                'pic_primary' => 'SAMUEL LAURENT ALSIM SIMANJUNTAK',
                'pic_backup' => 'IRFAN WIBAWA',
            ],
        ];

        foreach ($groups as $group) {
            DB::table('atr_pic_roster_groups')->insert([
                ...$group,
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'is_active' => true,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $ids = DB::table('atr_pic_roster_groups')
            ->pluck('id', 'code');

        /*
        |--------------------------------------------------------------------------
        | Rule awal
        |--------------------------------------------------------------------------
        |
        | Karena engine bersifat rule-based, PC 200 / 300 / 400 / 500 /
        | 1250 / 2000 dan model PC berikutnya otomatis masuk EXCAVATOR
        | tanpa penambahan source code.
        |
        | Rule frasa spesifik diberi priority lebih kecil daripada keyword
        | umum agar WATER TRUCK HD tidak tertangkap lebih dahulu oleh "HD".
        |
        */
        $rules = [
            ['EXCAVATOR', 'KEYWORD', 'EXCAVATOR', 20],
            ['EXCAVATOR', 'KEYWORD', 'PC', 50],

            ['DOZER', 'KEYWORD', 'BULLDOZER', 10],
            ['DOZER', 'KEYWORD', 'DOZER', 20],
            ['DOZER', 'KEYWORD', 'DZ', 50],

            ['WHEEL_LOADER', 'KEYWORD', 'WHEEL LOADER', 20],
            ['VIBRO', 'KEYWORD', 'VIBRO', 20],

            ['WTHD', 'KEYWORD', 'WATER TRUCK HD', 10],
            ['WTDT', 'KEYWORD', 'WATER TRUCK DT', 10],

            ['DT_TRINTIN', 'KEYWORD', 'DT TRINTIN', 10],
            ['DT_TRINTIN', 'KEYWORD', 'TRINTIN', 20],

            ['DT_TRONTON', 'KEYWORD', 'DT TRONTON', 10],
            ['DT_TRONTON', 'KEYWORD', 'TRONTON', 20],

            ['GRADER', 'KEYWORD', 'GRADER', 20],

            ['HD_CAT', 'KEYWORD', 'CAT 777', 20],
            ['HD_CAT', 'KEYWORD', 'HD', 90],
        ];

        foreach ($rules as [$code, $type, $pattern, $priority]) {
            DB::table('atr_pic_roster_rules')->insert([
                'atr_pic_roster_group_id' => $ids[$code],
                'match_type' => $type,
                'pattern' => $pattern,
                'priority' => $priority,
                'is_active' => true,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_pic_roster_histories');
        Schema::dropIfExists('atr_pic_roster_rules');
        Schema::dropIfExists('atr_pic_roster_groups');
    }
};
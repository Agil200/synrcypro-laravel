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
        if (Schema::hasTable('atr_coaching_counselings')) {
            Schema::table('atr_coaching_counselings', function (Blueprint $table): void {
                if (! Schema::hasColumn('atr_coaching_counselings', 'leader_name')) {
                    $table->string('leader_name', 150)->nullable()->after('created_by_name');
                }

                if (! Schema::hasColumn('atr_coaching_counselings', 'system_document_number')) {
                    $table->string('system_document_number', 60)->nullable()->after('document_number');
                }
            });
        }

        if (! Schema::hasTable('atr_coaching_histories')) {
            Schema::create('atr_coaching_histories', function (Blueprint $table): void {
                $table->id();

                $table->foreignId('atr_coaching_counseling_id')
                    ->constrained('atr_coaching_counselings')
                    ->cascadeOnDelete();

                $table->string('action', 40);
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();

                $table->foreignId('actor_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('actor_name', 150)->nullable();
                $table->text('notes')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(
                    ['atr_coaching_counseling_id', 'created_at'],
                    'atr_coaching_histories_coaching_created_idx'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Backfill data coaching lama
        |--------------------------------------------------------------------------
        |
        | Dokumen lama tetap mendapatkan nomor dokumentasi sistem dan audit trail.
        | NO FORM resmi perusahaan tidak diubah.
        |
        */
        if (
            Schema::hasTable('atr_coaching_counselings')
            && Schema::hasTable('atr_coaching_histories')
        ) {
            DB::table('atr_coaching_counselings')
                ->orderBy('id')
                ->get()
                ->each(function ($row): void {
                    $reference = $row->completed_at
                        ?: $row->created_at
                        ?: now();

                    try {
                        $period = Carbon::parse($reference)->format('Ym');
                    } catch (\Throwable) {
                        $period = now()->format('Ym');
                    }

                    $systemNumber = trim(
                        (string) ($row->system_document_number ?? '')
                    );

                    if ($systemNumber === '') {
                        $systemNumber = 'PPA-ATR-CC-'
                            . $period
                            . '-'
                            . str_pad(
                                (string) $row->id,
                                6,
                                '0',
                                STR_PAD_LEFT
                            );

                        DB::table('atr_coaching_counselings')
                            ->where('id', $row->id)
                            ->update([
                                'system_document_number' => $systemNumber,
                            ]);
                    }

                    $hasHistory = DB::table('atr_coaching_histories')
                        ->where(
                            'atr_coaching_counseling_id',
                            $row->id
                        )
                        ->exists();

                    if ($hasHistory) {
                        return;
                    }

                    $createdAt = $row->created_at ?: now();
                    $actorName = trim(
                        (string) ($row->created_by_name ?? '')
                    );

                    DB::table('atr_coaching_histories')->insert([
                        'atr_coaching_counseling_id' => $row->id,
                        'action' => 'CREATED',
                        'from_status' => null,
                        'to_status' => 'DRAFT',
                        'actor_user_id' =>
                            $row->created_by_user_id ?? null,
                        'actor_name' =>
                            $actorName !== '' ? $actorName : null,
                        'notes' =>
                            'Riwayat awal dibuat otomatis saat aktivasi audit trail.',
                        'meta' => json_encode([
                            'system_document_number' => $systemNumber,
                            'migrated' => true,
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $status = strtoupper(
                        trim((string) ($row->status ?? ''))
                    );

                    if ($status === 'COMPLETED') {
                        $completedAt = $row->completed_at ?: $createdAt;

                        DB::table('atr_coaching_histories')->insert([
                            'atr_coaching_counseling_id' => $row->id,
                            'action' => 'COMPLETED',
                            'from_status' => 'DRAFT',
                            'to_status' => 'COMPLETED',
                            'actor_user_id' =>
                                $row->created_by_user_id ?? null,
                            'actor_name' =>
                                $actorName !== '' ? $actorName : null,
                            'notes' =>
                                'Dokumentasi Coaching & Counseling selesai.',
                            'meta' => json_encode([
                                'system_document_number' =>
                                    $systemNumber,
                                'migrated' => true,
                            ], JSON_UNESCAPED_UNICODE),
                            'created_at' => $completedAt,
                            'updated_at' => $completedAt,
                        ]);
                    }

                    if ($status === 'CANCELLED') {
                        $cancelledAt =
                            $row->cancelled_at ?: $createdAt;

                        $cancelActorName = null;

                        if (
                            ! empty($row->cancelled_by)
                            && Schema::hasTable('users')
                            && Schema::hasColumn('users', 'name')
                        ) {
                            $cancelActorName = DB::table('users')
                                ->where('id', $row->cancelled_by)
                                ->value('name');
                        }

                        DB::table('atr_coaching_histories')->insert([
                            'atr_coaching_counseling_id' => $row->id,
                            'action' => 'CANCELLED',
                            'from_status' => 'COMPLETED',
                            'to_status' => 'CANCELLED',
                            'actor_user_id' =>
                                $row->cancelled_by ?? null,
                            'actor_name' => $cancelActorName,
                            'notes' =>
                                $row->cancellation_reason
                                ?: 'Dokumentasi dibatalkan.',
                            'meta' => json_encode([
                                'system_document_number' =>
                                    $systemNumber,
                                'migrated' => true,
                            ], JSON_UNESCAPED_UNICODE),
                            'created_at' => $cancelledAt,
                            'updated_at' => $cancelledAt,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_coaching_histories');

        if (Schema::hasTable('atr_coaching_counselings')) {
            Schema::table(
                'atr_coaching_counselings',
                function (Blueprint $table): void {
                    if (
                        Schema::hasColumn(
                            'atr_coaching_counselings',
                            'system_document_number'
                        )
                    ) {
                        $table->dropColumn('system_document_number');
                    }

                    if (
                        Schema::hasColumn(
                            'atr_coaching_counselings',
                            'leader_name'
                        )
                    ) {
                        $table->dropColumn('leader_name');
                    }
                }
            );
        }
    }
};
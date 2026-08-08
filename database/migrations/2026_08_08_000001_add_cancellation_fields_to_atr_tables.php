<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atr_imports', function (Blueprint $table): void {
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('atr_coaching_counselings', function (Blueprint $table): void {
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('atr_coaching_counselings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancellation_reason', 'cancelled_at']);
        });

        Schema::table('atr_imports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancellation_reason', 'cancelled_at']);
        });
    }
};
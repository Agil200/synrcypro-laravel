<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atr_records', function (Blueprint $table): void {

            /*
            |--------------------------------------------------------------------------
            | Data organisasi dari 00.MASTER_UPLOAD
            |--------------------------------------------------------------------------
            */

            $table->string('dept', 100)
                ->nullable()
                ->after('employee_name')
                ->index();

            $table->string('position')
                ->nullable()
                ->after('job_title')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Index utama dashboard ATR
            |--------------------------------------------------------------------------
            |
            | Dashboard sekarang difilter berdasarkan PERIODE + POSISI,
            | bukan lagi hanya JABATAN.
            |
            */

            $table->index(
                ['period', 'position'],
                'atr_records_period_position_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('atr_records', function (Blueprint $table): void {

            $table->dropIndex(
                'atr_records_period_position_index'
            );

            $table->dropColumn([
                'dept',
                'position',
            ]);
        });
    }
};
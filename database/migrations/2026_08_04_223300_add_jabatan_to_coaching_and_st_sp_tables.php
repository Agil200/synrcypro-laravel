<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coaching_counsellings') && ! Schema::hasColumn('coaching_counsellings', 'jabatan')) {
            Schema::table('coaching_counsellings', function (Blueprint $table) {
                $table->string('jabatan', 150)->nullable();
            });
        }

        if (Schema::hasTable('st_sp_records') && ! Schema::hasColumn('st_sp_records', 'jabatan')) {
            Schema::table('st_sp_records', function (Blueprint $table) {
                $table->string('jabatan', 150)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('coaching_counsellings') && Schema::hasColumn('coaching_counsellings', 'jabatan')) {
            Schema::table('coaching_counsellings', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }

        if (Schema::hasTable('st_sp_records') && Schema::hasColumn('st_sp_records', 'jabatan')) {
            Schema::table('st_sp_records', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }
};

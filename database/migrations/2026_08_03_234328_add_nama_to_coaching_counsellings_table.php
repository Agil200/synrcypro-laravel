<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('coaching_counsellings', 'nama')) {
            Schema::table('coaching_counsellings', function (Blueprint $table) {
                $table->string('nama', 150)
                    ->nullable()
                    ->after('nrp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coaching_counsellings', 'nama')) {
            Schema::table('coaching_counsellings', function (Blueprint $table) {
                $table->dropColumn('nama');
            });
        }
    }
};
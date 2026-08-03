<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('st_sp_records', function (Blueprint $table) {
            $table->string('nama', 150)
                ->nullable()
                ->after('nrp');
        });
    }

    public function down(): void
    {
        Schema::table('st_sp_records', function (Blueprint $table) {
            $table->dropColumn('nama');
        });
    }
};

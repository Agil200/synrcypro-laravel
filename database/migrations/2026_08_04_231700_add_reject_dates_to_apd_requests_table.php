<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apd_requests', function (Blueprint $table) {
            $table->date('tanggal_reject_helm')->nullable();
            $table->date('tanggal_reject_sepatu')->nullable();
            $table->date('tanggal_reject_rompi')->nullable();
            $table->date('tanggal_reject_kacamata')->nullable();
            $table->date('tanggal_reject_ear_plug')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('apd_requests', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_reject_helm',
                'tanggal_reject_sepatu',
                'tanggal_reject_rompi',
                'tanggal_reject_kacamata',
                'tanggal_reject_ear_plug',
            ]);
        });
    }
};

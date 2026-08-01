<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('st_sp_records')) {
            return;
        }

        Schema::create('st_sp_records', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 50)->index();
            $table->string('jenis_pelanggaran', 150);
            $table->date('tanggal')->index();
            $table->date('expired_date')->index();
            $table->string('tempat_kejadian', 255)->nullable();
            $table->string('jenis', 50)->index();
            $table->text('deskripsi')->nullable();
            $table->string('atasan', 150)->nullable();
            $table->string('file_path', 255);
            $table->string('file_nama_asli', 255);
            $table->string('status', 20)
                ->default('AKTIF')
                ->index();
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('st_sp_records');
    }
};
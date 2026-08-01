<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_surat')->index();

            // Tidak semua dokumen mempunyai nomor surat.
            $table->string('nomor_surat', 150)
                ->nullable()
                ->unique();

            $table->string('tujuan_surat', 255);
            $table->string('nama', 150);
            $table->string('nrp', 50)->nullable()->index();
            $table->string('jenis_surat', 150);
            $table->string('file_path', 255);
            $table->string('file_nama_asli', 255);
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
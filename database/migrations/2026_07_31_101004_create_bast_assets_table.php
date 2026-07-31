<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_assets', function (Blueprint $table) {
            $table->id();
            $table->string('nrp');
            $table->string('nama');
            $table->string('jabatan');
            $table->string('jenis_asset'); // Senter P101X, Laser, Laptop, Radio HT, Lainnya
            $table->string('departemen')->default('PRODUKSI');
            $table->string('no_asset')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('tanggal_ambil');
            $table->string('file_pdf')->nullable(); // Untuk upload dokumen BAST (PDF)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_assets');
    }
};
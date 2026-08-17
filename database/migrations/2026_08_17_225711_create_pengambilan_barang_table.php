<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengambilan_barang', function (Blueprint $table) {
            $table->string('id', 100)->primary(); // Mendukung ID unik batch atau per item (contoh: BRG-20260817-XXXXXX)
            $table->timestamp('timestamp')->nullable();
            $table->string('nama', 100);
            $table->string('nrp', 30);
            $table->string('jabatan', 100)->nullable();
            $table->date('tanggal');
            $table->string('barang', 100);
            $table->string('jumlah', 50); // Menyimpan format angka + satuan (contoh: "2 Box" atau "5 Pcs")
            $table->string('lokasi', 100);
            $table->text('foto_url')->nullable();
            $table->string('foto_file_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengambilan_barang');
    }
};
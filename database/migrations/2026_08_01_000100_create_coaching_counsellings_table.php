<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'coaching_counsellings',
            function (Blueprint $table) {
                $table->id();

                $table->string('nrp', 50)->index();

                $table->string('materi', 255);

                $table->string('perihal', 255)
                    ->nullable();

                $table->date('tanggal')->index();

                $table->string('shift', 50);

                $table->text('keterangan')
                    ->nullable();

                $table->string('dibuat_oleh', 150)
                    ->nullable();

                $table->string('file_path', 255);

                $table->string(
                    'file_nama_asli',
                    255
                );

                $table->unsignedBigInteger('created_by')
                    ->nullable()
                    ->index();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'coaching_counsellings'
        );
    }
};
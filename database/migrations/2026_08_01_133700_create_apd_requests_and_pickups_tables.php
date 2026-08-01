<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('apd_requests')) {
            Schema::create('apd_requests', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal_pengajuan')->index();
                $table->string('nrp', 50)->index();
                $table->string('nama', 150);
                $table->string('jabatan', 150);
                $table->string('ukuran_sepatu', 20)->nullable();

                $table->boolean('item_helm')->default(false);
                $table->boolean('item_sepatu_safety')->default(false);
                $table->boolean('item_rompi')->default(false);
                $table->boolean('item_kacamata')->default(false);
                $table->boolean('item_ear_plug')->default(false);

                /*
                 * Status sepatu:
                 * SHE, WAREHOUSE, LOGISTIK, READY, DIAMBIL.
                 *
                 * Nullable jika pengajuan tidak memilih Sepatu Safety.
                 */
                $table->string('status_sepatu', 30)
                    ->nullable()
                    ->index();

                $table->timestamp('picked_up_at')->nullable();
                $table->unsignedBigInteger('created_by')
                    ->nullable()
                    ->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('apd_pickups')) {
            Schema::create('apd_pickups', function (Blueprint $table) {
                $table->id();

                $table->foreignId('apd_request_id')
                    ->unique()
                    ->constrained('apd_requests')
                    ->cascadeOnDelete();

                $table->date('tanggal_pengambilan')->index();
                $table->string('diambil_oleh', 150);
                $table->string('petugas', 150)->nullable();
                $table->string('photo_path', 255);
                $table->string('photo_original_name', 255);
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by')
                    ->nullable()
                    ->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apd_pickups');
        Schema::dropIfExists('apd_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'mcu_fu_simper_overrides',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'nrp',
                    50
                )->unique();

                $table->string(
                    'nama',
                    150
                )->nullable();

                $table->date(
                    'expired_sim_dlt'
                );

                $table->string(
                    'note',
                    255
                )->nullable();

                $table->string(
                    'updated_by_name',
                    150
                )->nullable();

                $table->string(
                    'updated_by_email',
                    190
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'mcu_fu_simper_overrides'
        );
    }
};
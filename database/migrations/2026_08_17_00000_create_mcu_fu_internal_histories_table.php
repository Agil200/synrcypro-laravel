<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'mcu_fu_internal_histories',
            function (Blueprint $table): void {
                $table->id();

                $table->unsignedInteger(
                    'sheet_row'
                )->index();

                $table->string(
                    'nrp',
                    50
                )->nullable()->index();

                $table->string(
                    'nama',
                    150
                )->nullable();

                $table->string(
                    'action',
                    50
                )->index();

                $table->longText(
                    'before_data'
                )->nullable();

                $table->longText(
                    'after_data'
                )->nullable();

                $table->string(
                    'user_name',
                    150
                )->nullable();

                $table->string(
                    'user_email',
                    190
                )->nullable()->index();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'mcu_fu_internal_histories'
        );
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atr_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atr_import_id')->constrained('atr_imports')->cascadeOnDelete();
            $table->date('period')->index();
            $table->string('nrp', 50)->index();
            $table->string('employee_name');
            $table->string('job_title')->nullable()->index();
            $table->string('site')->nullable()->index();
            $table->decimal('atr', 5, 2)->nullable()->index();
            $table->unsignedInteger('sick')->default(0);
            $table->unsignedInteger('permission')->default(0);
            $table->unsignedInteger('alpha')->default(0);
            $table->string('status', 30)->index();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->unique(
                ['atr_import_id', 'period', 'nrp'],
                'atr_records_import_period_nrp_unique'
            );
            $table->index(['period', 'status']);
            $table->index(['period', 'job_title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_records');
    }
};

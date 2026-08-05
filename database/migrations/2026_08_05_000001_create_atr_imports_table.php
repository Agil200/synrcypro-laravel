<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atr_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('file_name');
            $table->string('stored_path');
            $table->string('file_hash', 64)->index();
            $table->string('status', 30)->default('PROCESSING')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->date('period_min')->nullable()->index();
            $table->date('period_max')->nullable()->index();
            $table->json('periods')->nullable();
            $table->json('errors')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'imported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_imports');
    }
};

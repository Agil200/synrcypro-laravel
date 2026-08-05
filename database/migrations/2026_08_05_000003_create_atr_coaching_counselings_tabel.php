<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atr_coaching_counselings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atr_record_id')->constrained('atr_records')->cascadeOnDelete();
            $table->string('document_number')->default('PPA-PTBA-F-SHE-14D');
            $table->date('coaching_date');
            $table->string('shift', 30);
            $table->string('location');
            $table->time('coaching_time');
            $table->boolean('material_personal')->default(false);
            $table->boolean('material_family')->default(false);
            $table->boolean('material_work')->default(false);
            $table->text('notes');
            $table->string('created_by_name');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('COMPLETED')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['atr_record_id', 'status']);
            $table->index(['coaching_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_coaching_counselings');
    }
};

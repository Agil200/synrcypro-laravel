<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atr_coaching_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atr_coaching_counseling_id')
                ->constrained('atr_coaching_counselings')
                ->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->index(['atr_coaching_counseling_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atr_coaching_attachments');
    }
};

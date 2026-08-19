<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_archive_links', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 150);
            $table->string('category', 80)->default('LAINNYA');

            /*
             * SYNRGYPRO hanya menyimpan registry/link.
             * File/folder fisik tetap berada di Google Drive.
             */
            $table->text('drive_url');

            $table->string('description', 500)->nullable();

            $table->unsignedInteger('sort_order')
                ->default(10);

            $table->boolean('is_active')
                ->default(true);

            $table->string('created_by', 191)
                ->nullable();

            $table->string('updated_by', 191)
                ->nullable();

            $table->timestamps();

            /*
             * Delete di E-Arsip = soft delete registry.
             * Google Drive tidak pernah dihapus.
             */
            $table->softDeletes();

            $table->index(
                ['is_active', 'sort_order'],
                'e_archive_links_active_sort_idx'
            );

            $table->index(
                'category',
                'e_archive_links_category_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_archive_links');
    }
};

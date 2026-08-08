<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atr_imports', function (Blueprint $table): void {
            $table->string('import_mode', 20)->default('NEW')->index();
            $table->unsignedBigInteger('replaces_import_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('atr_imports', function (Blueprint $table): void {
            $table->dropIndex(['replaces_import_id']);
            $table->dropIndex(['import_mode']);
            $table->dropColumn(['replaces_import_id', 'import_mode']);
        });
    }
};
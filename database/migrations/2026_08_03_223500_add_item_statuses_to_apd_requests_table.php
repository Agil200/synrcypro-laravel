<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('apd_requests')) {
            return;
        }

        $columns = [
            'status_helm',
            'status_rompi',
            'status_kacamata',
            'status_ear_plug',
        ];

        $missingColumns = array_values(array_filter(
            $columns,
            fn (string $column) =>
                ! Schema::hasColumn('apd_requests', $column)
        ));

        if ($missingColumns === []) {
            return;
        }

        Schema::table(
            'apd_requests',
            function (Blueprint $table) use ($missingColumns) {
                foreach ($missingColumns as $column) {
                    $table
                        ->string($column, 30)
                        ->nullable()
                        ->index();
                }
            }
        );

        /*
         * Data lama yang sudah memilih barang tetapi belum memiliki
         * kolom status baru dimulai dari posisi SHE.
         */
        $backfills = [
            'item_helm' => 'status_helm',
            'item_rompi' => 'status_rompi',
            'item_kacamata' => 'status_kacamata',
            'item_ear_plug' => 'status_ear_plug',
        ];

        foreach ($backfills as $itemColumn => $statusColumn) {
            DB::table('apd_requests')
                ->where($itemColumn, true)
                ->whereNull($statusColumn)
                ->update([$statusColumn => 'SHE']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('apd_requests')) {
            return;
        }

        $columns = [
            'status_helm',
            'status_rompi',
            'status_kacamata',
            'status_ear_plug',
        ];

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) =>
                Schema::hasColumn('apd_requests', $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table(
            'apd_requests',
            function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            }
        );
    }
};
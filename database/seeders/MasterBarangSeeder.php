<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterBarangSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        $items = [
            'Pulpen Gel',
            'Pulpen Pilot',
            'Spidol Putih Permanen',
            'Spidol Hitam Permanen',
            'Spidol Hitam Whiteboard',
            'Buku Saku',
            'Isolasi Bening Kecil',
            'Isolasi Bening Besar',
        ];

        $data = [];
        foreach ($items as $index => $name) {
            $data[] = [
                'kode' => 'BRG-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'nama_barang' => $name,
                'aktif' => true,
                'urutan' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Masukkan data ke tabel master_barangs (menggunakan updateOrInsert agar aman jika dijalankan ulang)
        foreach ($data as $row) {
            DB::table('master_barangs')->updateOrInsert(
                ['kode' => $row['kode']],
                $row
            );
        }
    }
}
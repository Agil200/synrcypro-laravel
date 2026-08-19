<?php

namespace Database\Seeders;

use App\Models\EArchiveLink;
use Illuminate\Database\Seeder;

class EArchiveSeeder extends Seeder
{
    public function run(): void
    {
        EArchiveLink::query()->updateOrCreate(
            [
                'drive_url' =>
                    'https://drive.google.com/drive/folders/1X01OjcwoWZkItRwpK8J8nZ1I5We2xECv?hl=ID',
            ],
            [
                'name' => 'Prosedur Departemen',
                'category' => 'PROSEDUR',
                'description' =>
                    'Folder Google Drive Prosedur Departemen Produksi.',
                'sort_order' => 10,
                'is_active' => true,
                'created_by' => 'SYSTEM INITIAL SEED',
                'updated_by' => 'SYSTEM INITIAL SEED',
            ]
        );

        EArchiveLink::query()->updateOrCreate(
            [
                'drive_url' =>
                    'https://drive.google.com/drive/folders/1umQAn1zufRo_D-ohTesPVfN12vl8R0tV',
            ],
            [
                'name' => 'Kumpulan Admin Form',
                'category' => 'FORM ADMIN',
                'description' =>
                    'Folder Google Drive kumpulan form administrasi.',
                'sort_order' => 20,
                'is_active' => true,
                'created_by' => 'SYSTEM INITIAL SEED',
                'updated_by' => 'SYSTEM INITIAL SEED',
            ]
        );
    }
}

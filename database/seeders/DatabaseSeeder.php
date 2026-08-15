<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Data lokal lama tetap dipertahankan untuk kompatibilitas.
         * Akses produksi menggunakan akun Google yang dipromosikan melalui
         * AdminAccessSeeder dan SYNRGYPRO_SUPER_ADMIN_EMAILS.
         */
        User::query()->firstOrCreate(
            ['email' => 'admin@synrcypro.local'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => 'Administrator',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            AdminAccessSeeder::class,
        ]);
    }
}

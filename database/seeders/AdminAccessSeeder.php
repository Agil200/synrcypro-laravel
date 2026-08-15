<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'Lihat Admin All', 'slug' => 'admin-all.view', 'module' => 'Admin All'],
            ['name' => 'Lihat User', 'slug' => 'users.view', 'module' => 'Manajemen Akses'],
            ['name' => 'Tambah User', 'slug' => 'users.create', 'module' => 'Manajemen Akses'],
            ['name' => 'Ubah User', 'slug' => 'users.update', 'module' => 'Manajemen Akses'],
            ['name' => 'Ubah Status User', 'slug' => 'users.change-status', 'module' => 'Manajemen Akses'],
            ['name' => 'Assign Role User', 'slug' => 'users.assign-role', 'module' => 'Manajemen Akses'],
            ['name' => 'Lihat Role', 'slug' => 'roles.view', 'module' => 'Manajemen Akses'],
            ['name' => 'Ubah Permission Role', 'slug' => 'roles.update', 'module' => 'Manajemen Akses'],
            ['name' => 'Lihat Audit Log', 'slug' => 'audit-logs.view', 'module' => 'Manajemen Akses'],
            ['name' => 'Lihat Monitoring Suggestion System', 'slug' => 'suggestion-system.view', 'module' => 'Monitoring'],
            ['name' => 'Lihat IFUTS', 'slug' => 'ifuts.view', 'module' => 'IFUTS'],
            ['name' => 'Kelola IFUTS', 'slug' => 'ifuts.update', 'module' => 'IFUTS'],
            ['name' => 'Diskusi IFUTS', 'slug' => 'ifuts.comment', 'module' => 'IFUTS'],
            ['name' => 'Lihat MCU & FU Internal', 'slug' => 'mcu-fu.view', 'module' => 'MCU & FU Internal'],
            ['name' => 'Input MCU & FU Internal', 'slug' => 'mcu-fu.update', 'module' => 'MCU & FU Internal'],
            ['name' => 'Lihat Riwayat MCU & FU', 'slug' => 'mcu-fu.audit', 'module' => 'MCU & FU Internal'],
            ['name' => 'Lihat Stock Opname', 'slug' => 'stock-opname.view', 'module' => 'Stock Opname Gudang'],
            ['name' => 'Input Stock Opname', 'slug' => 'stock-opname.create', 'module' => 'Stock Opname Gudang'],
            ['name' => 'Kelola Master Barang', 'slug' => 'stock-opname.manage-master', 'module' => 'Stock Opname Gudang'],
            ['name' => 'Lihat E-Arsip', 'slug' => 'e-arsip.view', 'module' => 'E-Arsip'],
            ['name' => 'Kelola E-Arsip', 'slug' => 'e-arsip.manage', 'module' => 'E-Arsip'],
        ])->mapWithKeys(function (array $permission): array {
            $model = Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );

            return [$model->slug => $model->id];
        });

        $roles = [
            'super-administrator' => [
                'name' => 'Super Administrator',
                'permissions' => $permissions->values()->all(),
            ],
            'administrator' => [
                'name' => 'Administrator',
                'permissions' => $permissions
                    ->only([
                        'admin-all.view',
                        'users.view',
                        'users.create',
                        'users.update',
                        'users.change-status',
                        'users.assign-role',
                        'roles.view',
                        'audit-logs.view',
                        'suggestion-system.view',
                        'ifuts.view',
                        'ifuts.update',
                        'ifuts.comment',
                    ])
                    ->values()
                    ->all(),
            ],
            'operator' => [
                'name' => 'Operator',
                'permissions' => [],
            ],
        ];

        $roleModels = collect();

        foreach ($roles as $slug => $definition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'is_system' => true,
                ]
            );

            $role->permissions()->sync($definition['permissions']);
            $roleModels->put($slug, $role);
        }

        User::query()
            ->orderBy('id')
            ->each(function (User $user) use ($roleModels): void {
                if ($user->role_id !== null) {
                    return;
                }

                $legacyRole = strtolower(trim((string) $user->role));
                $roleSlug = match ($legacyRole) {
                    'administrator', 'admin' => 'administrator',
                    'operator' => 'operator',
                    default => null,
                };

                if ($roleSlug !== null) {
                    $user->forceFill([
                        'role_id' => $roleModels->get($roleSlug)?->id,
                    ])->save();
                }
            });

        collect(config('access.super_admin_emails', []))
            ->filter()
            ->each(function (string $email) use ($roleModels): void {
                $normalizedEmail = strtolower(trim($email));

                $user = User::query()->firstOrNew([
                    'email' => $normalizedEmail,
                ]);

                if (! $user->exists) {
                    $user->forceFill([
                        'name' => 'Super Administrator',
                        'password' => Hash::make(Str::random(40)),
                    ]);
                }

       $user->forceFill([
    'role_id' => $roleModels->get('super-administrator')?->id,
    'is_active' => true,
])->save();
            });
    }
}

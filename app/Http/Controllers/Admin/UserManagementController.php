<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('users.view');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'role' => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        $users = User::query()
            ->with('accessRole')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(($filters['status'] ?? null) === 'active', function ($query): void {
                $query->where('is_active', true);
            })
            ->when(($filters['status'] ?? null) === 'inactive', function ($query): void {
                $query->where('is_active', false);
            })
            ->when($filters['role'] ?? null, function ($query, int $roleId): void {
                $query->where('role_id', $roleId);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::query()
            ->orderBy('id')
            ->get();

        return view('admin-all.users.index', compact(
            'users',
            'roles',
            'filters'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('users.create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email:rfc',
                'max:190',
                Rule::unique('users', 'email'),
            ],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        User::query()->create([
            'name' => trim($data['name']),
            'email' => Str::lower(trim($data['email'])),
            'password' => Str::random(48),
            'role' => $this->legacyRoleFor($role),
            'role_id' => $role->id,
            'is_active' => (bool) $data['is_active'],
        ]);

        return back()->with('success', 'Pengguna berhasil ditambahkan. Pengguna dapat login dengan email Google tersebut.');
    }

    public function updateRole(
        Request $request,
        User $user
    ): RedirectResponse {
        Gate::authorize('users.assign-role');

        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        if ($request->user()->is($user) && $role->slug !== 'super-administrator') {
            return back()->withErrors([
                'role_id' => 'Anda tidak dapat menurunkan role akun sendiri.',
            ]);
        }

        if (
            $user->isSuperAdministrator()
            && $role->slug !== 'super-administrator'
            && $this->isLastActiveSuperAdministrator($user)
        ) {
            return back()->withErrors([
                'role_id' => 'Super Administrator aktif terakhir tidak dapat diturunkan rolenya.',
            ]);
        }

        $user->forceFill([
            'role_id' => $role->id,
        ])->save();

        return back()->with('success', 'Role akses '.$user->name.' berhasil diperbarui.');
    }

    public function updateStatus(
        Request $request,
        User $user
    ): RedirectResponse {
        Gate::authorize('users.change-status');

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $data['is_active'];

        if ($request->user()->is($user) && ! $newStatus) {
            return back()->withErrors([
                'is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        if (
            ! $newStatus
            && $this->isLastActiveSuperAdministrator($user)
        ) {
            return back()->withErrors([
                'is_active' => 'Super Administrator aktif terakhir tidak dapat dinonaktifkan.',
            ]);
        }

        $user->forceFill([
            'is_active' => $newStatus,
        ])->save();

        return back()->with(
            'success',
            $newStatus
                ? 'Akun '.$user->name.' berhasil diaktifkan.'
                : 'Akun '.$user->name.' berhasil dinonaktifkan.'
        );
    }

    private function legacyRoleFor(Role $role): string
    {
        return in_array($role->slug, [
            'super-administrator',
            'administrator',
        ], true)
            ? 'Administrator'
            : 'Operator';
    }

    private function isLastActiveSuperAdministrator(User $user): bool
    {
        if (! $user->isActive() || ! $user->isSuperAdministrator()) {
            return false;
        }

        return User::query()
            ->where('is_active', true)
            ->whereHas('accessRole', function ($query): void {
                $query->where('slug', 'super-administrator');
            })
            ->count() <= 1;
    }
}

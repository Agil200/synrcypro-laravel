<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminAllController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('is_active', true)->count(),
            'inactive_users' => User::query()->where('is_active', false)->count(),
            'administrators' => User::query()
                ->whereHas('accessRole', function ($query): void {
                    $query->whereIn('slug', [
                        'super-administrator',
                        'administrator',
                    ]);
                })
                ->count(),
        ];

        $roleSummary = Role::query()
            ->withCount('users')
            ->orderBy('id')
            ->get();

        $recentUsers = User::query()
            ->with('accessRole')
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('admin-all.dashboard', compact(
            'stats',
            'roleSummary',
            'recentUsers'
        ));
    }
}

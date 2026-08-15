<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        foreach (config('access.permissions', []) as $permission) {
            Gate::define(
                $permission,
                fn (User $user): bool => $user->hasPermission($permission)
            );
        }

        View::composer('*', function ($view): void {
            $user = auth()->user();

            $isAuthenticated = auth()->check();

            $isGuest =
                $isAuthenticated
                && (
                    session('access_mode') === 'guest'
                    || strtolower((string) $user?->role) === 'guest'
                );

            $canManage =
                $isAuthenticated
                && ! $isGuest;

            $view->with([
                'isGuest' => $isGuest,
                'canManage' => $canManage,
            ]);
        });
    }
}

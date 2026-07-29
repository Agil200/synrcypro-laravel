<?php

namespace App\Providers;

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
        View::composer('*', function ($view) {
            $user = auth()->user();

            $isAuthenticated = auth()->check();

            $isGuest =
                $isAuthenticated &&
                (
                    session('access_mode') === 'guest'
                    || strtolower((string) $user?->role) === 'guest'
                );

            $canManage =
                $isAuthenticated &&
                ! $isGuest;

            $view->with([
                'isGuest' => $isGuest,
                'canManage' => $canManage,
            ]);
        });
    }
}
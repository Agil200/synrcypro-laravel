<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsNotGuest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |--------------------------------------------------------------------------
        | Middleware Web
        |--------------------------------------------------------------------------
        | User nonaktif dikeluarkan dari session pada request berikutnya. Portal
        | operator tetap aman karena session portal tidak memakai Auth::login().
        */
        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'not.guest' => EnsureUserIsNotGuest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

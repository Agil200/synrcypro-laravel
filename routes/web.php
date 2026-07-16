<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Login
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin'])
    ->name('login');

/*
|--------------------------------------------------------------------------
| Google Login
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Guest Login
|--------------------------------------------------------------------------
*/

Route::post('/auth/guest', [AuthController::class, 'loginAsGuest'])
    ->name('auth.guest');

/*
|--------------------------------------------------------------------------
| Halaman yang membutuhkan login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
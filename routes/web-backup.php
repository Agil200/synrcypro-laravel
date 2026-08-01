<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApdController;
use App\Http\Controllers\BastAssetController;
use App\Http\Controllers\CoachingCounsellingController;
use App\Http\Controllers\StSpController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\MinePermitController;
use App\Http\Controllers\ManpowerDashboardController;
use App\Http\Controllers\SuratKeluarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Login
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [AuthController::class, 'showLogin']
)->name('login');

/*
|--------------------------------------------------------------------------
| Google Login SYNRGYPRO
|--------------------------------------------------------------------------
| Digunakan untuk login pengguna ke aplikasi.
*/

Route::get(
    '/auth/google',
    [AuthController::class, 'redirectToGoogle']
)->name('auth.google');

Route::get(
    '/auth/google/callback',
    [AuthController::class, 'handleGoogleCallback']
)->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Guest Login
|--------------------------------------------------------------------------
*/

Route::post(
    '/auth/guest',
    [AuthController::class, 'loginAsGuest']
)->name('auth.guest');

/*
|--------------------------------------------------------------------------
| Halaman yang Membutuhkan Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Utama
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/dashboard',
        'dashboard'
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Menu Manpower
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/manpower',
        [ManpowerDashboardController::class, 'index']
    )->name('manpower');

    /*
    |--------------------------------------------------------------------------
    | Google OAuth untuk Google Sheets
    |--------------------------------------------------------------------------
    | Berbeda dengan Google Login di atas.
    | Digunakan untuk memberikan izin baca Spreadsheet.
    */

    Route::get(
        '/google/oauth/redirect',
        [GoogleOAuthController::class, 'redirect']
    )->name('google.oauth.redirect');

    Route::get(
        '/google/oauth/callback',
        [GoogleOAuthController::class, 'callback']
    )->name('google.oauth.callback');

    /*
    |--------------------------------------------------------------------------
    | Mine Permit
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/manpower/mine-permit/dashboard',
        [MinePermitController::class, 'dashboardMinePermit']
    )->name('mine-permit.dashboard');

    Route::get(
        '/manpower/mine-permit/monitoring-she',
        [MinePermitController::class, 'monitoringShe']
    )->name('mine-permit.monitoring-she');

    Route::get(
        '/manpower/mine-permit/monitoring-internal-upload',
        [MinePermitController::class, 'monitoringInternalUpload']
    )->name('mine-permit.monitoring-internal-upload');

    /*
     * URL lama tetap aman dan diarahkan ke Dashboard Mine Permit.
     */
    Route::redirect(
        '/manpower/mine-permit/monitoring-mine-permit',
        '/manpower/mine-permit/dashboard'
    );

    /*
    |--------------------------------------------------------------------------
    | Menu People Development
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/people-development',
        'people-development'
    )->name('people-development');

    /*
    |--------------------------------------------------------------------------
    | Menu Database
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/database',
        'database'
    )->name('database');

    /*
    |--------------------------------------------------------------------------
    | Menu Admin All
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/admin-all',
        'admin-all'
    )->name('admin-all');

    /*
    |--------------------------------------------------------------------------
    | Profil Pengguna
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/profil',
        'profile.index'
    )->name('profile.index');

    Route::view(
        '/pengaturan-akun',
        'profile.settings'
    )->name('profile.settings');

    Route::view(
        '/ubah-email',
        'profile.change-email'
    )->name('profile.change-email');

    /*
    |--------------------------------------------------------------------------
    | Berita Acara Asset
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bast-asset/{category?}',
        [BastAssetController::class, 'index']
    )->name('bast.index');

    Route::post(
        '/bast-asset/store',
        [BastAssetController::class, 'store']
    )->name('bast.store');



    /*
    |--------------------------------------------------------------------------
    | Monitoring APD
    |--------------------------------------------------------------------------
    */

    Route::prefix('manpower/apd')
        ->name('apd.')
        ->controller(ApdController::class)
        ->group(function () {
            Route::get(
                '/',
                'index'
            )->name('index');

            Route::post(
                '/',
                'store'
            )->name('store');

            Route::put(
                '/{apdRequest}',
                'update'
            )->name('update');

            Route::patch(
                '/{apdRequest}/status',
                'updateStatus'
            )->name('status');

            Route::delete(
                '/{apdRequest}',
                'destroy'
            )->name('destroy');

            Route::post(
                '/pickup/store',
                'pickup'
            )->name('pickup.store');

            Route::get(
                '/pickup/{apdPickup}/photo',
                'pickupPhoto'
            )->name('pickup.photo');
        });

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT OUT — Monitoring Surat Keluar
    |--------------------------------------------------------------------------
    */

    Route::prefix('manpower/document-out')
        ->name('document-out.')
        ->controller(SuratKeluarController::class)
        ->group(function () {
            Route::get(
                '/',
                'index'
            )->name('index');

            Route::post(
                '/',
                'store'
            )->name('store');

            Route::get(
                '/{suratKeluar}/file',
                'file'
            )->name('file');

            Route::put(
                '/{suratKeluar}',
                'update'
            )->name('update');

            Route::delete(
                '/{suratKeluar}',
                'destroy'
            )->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | CC, ST & SP
    |--------------------------------------------------------------------------
    */

    Route::prefix('manpower/cc-st-sp')
        ->name('cc-st-sp.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Coaching & Counselling
            |--------------------------------------------------------------------------
            */

            Route::prefix('coaching-counselling')
                ->name('coaching.')
                ->controller(CoachingCounsellingController::class)
                ->group(function () {
                    Route::get(
                        '/',
                        'index'
                    )->name('index');

                    Route::post(
                        '/',
                        'store'
                    )->name('store');

                    Route::get(
                        '/{coachingCounselling}/file',
                        'file'
                    )->name('file');

                    Route::put(
                        '/{coachingCounselling}',
                        'update'
                    )->name('update');

                    Route::delete(
                        '/{coachingCounselling}',
                        'destroy'
                    )->name('destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Surat Teguran dan Surat Peringatan
            |--------------------------------------------------------------------------
            */

            Route::controller(StSpController::class)
                ->group(function () {
                    Route::get(
                        '/surat-teguran',
                        'teguran'
                    )->name('teguran.index');

                    Route::get(
                        '/surat-peringatan',
                        'peringatan'
                    )->name('peringatan.index');

                    Route::post(
                        '/st-sp',
                        'store'
                    )->name('st-sp.store');

                    Route::get(
                        '/st-sp/{stSpRecord}/file',
                        'file'
                    )->name('st-sp.file');

                    Route::put(
                        '/st-sp/{stSpRecord}',
                        'update'
                    )->name('st-sp.update');

                    Route::delete(
                        '/st-sp/{stSpRecord}',
                        'destroy'
                    )->name('st-sp.destroy');
                });
        });

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');
});
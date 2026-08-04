<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApdController;
use App\Http\Controllers\BastAssetController;
use App\Http\Controllers\CoachingCounsellingController;
use App\Http\Controllers\StSpController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\MinePermitController;
use App\Http\Controllers\ManpowerDashboardController;
use App\Http\Controllers\McuFuController;
use App\Http\Controllers\DatabaseUiController;
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
| Route ini harus tersedia sebelum middleware auth.
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
| Google OAuth untuk Google Sheets
|--------------------------------------------------------------------------
| Berbeda dari login pengguna.
| Redirect memerlukan login, sedangkan callback diletakkan di luar
| middleware auth agar respons dari Google tidak terhalang.
*/

Route::get(
    '/google/oauth/redirect',
    [GoogleOAuthController::class, 'redirect']
)->middleware('auth')
    ->name('google.oauth.redirect');

Route::get(
    '/google/oauth/callback',
    [GoogleOAuthController::class, 'callback']
)->name('google.oauth.callback');


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
    | Menu Database & ATR — Fase 1 UI/UX
    |--------------------------------------------------------------------------
    */

    Route::prefix('database')
        ->name('database.')
        ->controller(DatabaseUiController::class)
        ->group(function () {
            Route::get(
                '/',
                'dashboard'
            )->name('dashboard');

            Route::get(
                '/employees',
                'employees'
            )->name('employees');


            Route::get(
                '/employees/mapping-diagnostics',
                'employeeMappingDiagnostics'
            )->name('employees.mapping-diagnostics');


            Route::post(
                '/employees/test-fallback',
                'testEmployeeFallback'
            )->name('employees.test-fallback');


            Route::post(
                '/employees/sync',
                'syncEmployees'
            )->name('employees.sync');

            Route::get(
                '/atr',
                'atrSummary'
            )->name('atr.summary');

            Route::get(
                '/atr/upload',
                'atrUpload'
            )->name('atr.upload');

            Route::get(
                '/atr/import-history',
                'atrHistory'
            )->name('atr.history');

            Route::get(
                '/atr/call-documentation',
                'atrCalls'
            )->name('atr.calls');


            Route::get(
                '/atr/pic-roster',
                'atrPicRoster'
            )->name('atr.pic-roster');
        });

/*
|--------------------------------------------------------------------------
| Admin Database Karyawan
|--------------------------------------------------------------------------
*/

Route::prefix('database/employees')
    ->name('database.employees.')
    ->controller(
    \App\Http\Controllers\EmployeeAdminController::class
)
    ->middleware('throttle:20,1')
    ->group(function () {
        Route::post(
            '/update-data',
            'storeDataUpdate'
        )->name('update-data');

        Route::post(
            '/update-status',
            'storeStatusUpdate'
        )->name('update-status');
    });

    /*
     * Kompatibilitas untuk link lama yang masih memakai:
     * route('database')
     */
    Route::redirect(
        '/database-home',
        '/database'
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

            /*
             * Lookup NRP ke MASTER_DATABASE untuk autofill Nama dan Jabatan.
             */
            Route::get(
                '/employee-lookup',
                'employeeLookup'
            )->middleware('throttle:60,1')
                ->name('employee.lookup');

            /*
             * Download Excel data Sepatu Safety berdasarkan bulan dan status.
             */
            Route::get(
                '/export-shoes',
                'exportShoes'
            )->name('export.shoes');

            /*
             * Pengambilan Sepatu Safety.
             * Route spesifik diletakkan sebelum route pengajuan dinamis.
             */
            Route::post(
                '/pickup/store',
                'pickup'
            )->name('pickup.store');

            Route::put(
                '/pickup/{apdPickup}',
                'updatePickup'
            )->whereNumber('apdPickup')
                ->name('pickup.update');

            Route::delete(
                '/pickup/{apdPickup}',
                'destroyPickup'
            )->whereNumber('apdPickup')
                ->name('pickup.destroy');

            Route::get(
                '/pickup/{apdPickup}/photo',
                'pickupPhoto'
            )->whereNumber('apdPickup')
                ->name('pickup.photo');

            /*
             * Pengajuan APD.
             */
            Route::post(
                '/',
                'store'
            )->name('store');

            Route::put(
                '/{apdRequest}',
                'update'
            )->whereNumber('apdRequest')
                ->name('update');

            Route::patch(
                '/{apdRequest}/status',
                'updateStatus'
            )->whereNumber('apdRequest')
                ->name('status');

            Route::delete(
                '/{apdRequest}',
                'destroy'
            )->whereNumber('apdRequest')
                ->name('destroy');
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
    | MCU & FU — Monitoring Medical Check Up dan Follow Up
    |--------------------------------------------------------------------------
    */

    Route::prefix('manpower/mcu-fu')
    ->name('mcu-fu.')
    ->controller(McuFuController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::post('/refresh', 'refresh')->name('refresh');
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
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApdController;
use App\Http\Controllers\AtrController;
use App\Http\Controllers\AtrPicRosterController;
use App\Http\Controllers\BastAssetController;
use App\Http\Controllers\BNNController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CoachingCounsellingController;
use App\Http\Controllers\DatabaseUiController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\ManpowerDashboardController;
use App\Http\Controllers\McuFuController;
use App\Http\Controllers\MinePermitController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperatorPortalController;
use App\Http\Controllers\StSpController;
use App\Http\Controllers\SuratKeluarController;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\DriveFileController;



Schedule::call(function(){

app(
\App\Http\Controllers\BNNController::class
)
->generateNotification();


})
->dailyAt('00:05');

/*
|--------------------------------------------------------------------------
| Notifikasi Harian — Ulang Tahun Operator/Karyawan
|--------------------------------------------------------------------------
| Membuat notifikasi ulang tahun dari data terbaru menggunakan zona waktu
| SITE BA (WIB). firstOrNew mencegah notifikasi ulang tahun ganda.
*/

Schedule::call(function (): void {
    app(NotificationService::class)->generateBirthday();
})
    ->name('notifications.generate-birthday')
    ->timezone('Asia/Jakarta')
    ->dailyAt('00:10')
    ->withoutOverlapping();


Route::middleware('auth')->group(function(){


    Route::get(
        '/notifications',
        [
            NotificationController::class,
            'index'
        ]
    );


    Route::get(
        '/notifications/count',
        [
            NotificationController::class,
            'unreadCount'
        ]
    );


    Route::get(
    '/database/files',
    [DriveFileController::class, 'index']
)->name('database.files');


    Route::post(
        '/notifications/read/{id}',
        [
            NotificationController::class,
            'read'
        ]
    );


});

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
    [OperatorPortalController::class, 'begin']
)->name('auth.guest');

/*
|--------------------------------------------------------------------------
| Portal Operator — Guest Read Only
|--------------------------------------------------------------------------
| Operator wajib memverifikasi NRP dan tanggal lahir. Portal ini tidak
| menggunakan Auth::login, sehingga tidak dapat membuka route admin yang
| berada di dalam middleware auth.
*/

Route::get(
    '/operator/access',
    [OperatorPortalController::class, 'accessForm']
)->name('operator.access');

Route::post(
    '/operator/access',
    [OperatorPortalController::class, 'verify']
)->middleware('throttle:10,1')
    ->name('operator.verify');

Route::get(
    '/operator/dashboard',
    [OperatorPortalController::class, 'dashboard']
)->name('operator.dashboard');

Route::post(
    '/operator/logout',
    [OperatorPortalController::class, 'logout']
)->name('operator.logout');

/*
|--------------------------------------------------------------------------
| SYNRGY Assistant - Gemini AI
|--------------------------------------------------------------------------
| Portal operator menggunakan session guest sendiri dan tidak memakai
| Auth::login(), sehingga route chatbot harus berada di luar middleware auth.
*/

Route::post(
    '/operator/chatbot',
    [ChatbotController::class, 'chat']
)->middleware('throttle:20,1')
  ->name('operator.chatbot');

Route::post(
    '/operator/chatbot/reset',
    [ChatbotController::class, 'reset']
)->middleware('throttle:20,1')
  ->name('operator.chatbot.reset');

/*
|--------------------------------------------------------------------------
| Halaman yang Membutuhkan Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    Route::prefix('manpower/bnn')
    ->name('bnn.')
    ->controller(\App\Http\Controllers\BNNController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');

        Route::get('/dashboard', 'dashboard')->name('dashboard');

        Route::get('/monitoring', 'monitoring')->name('monitoring');
        Route::post('/monitoring/refresh', 'refresh')->name('refresh');
        Route::get('/monitoring/data', 'data')->name('data');

        Route::get('/cari/{nrp}', 'cariNRP')->name('cari');
    });

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
| Test BNN
|--------------------------------------------------------------------------
*/

Route::prefix('manpower/bnn')
    ->name('bnn.')
    ->controller(BNNController::class)
    ->group(function () {


        // Form Input BNN
        Route::get(
            '/',
            'index'
        )->name('index');


        // Simpan Data BNN
        Route::post(
            '/',
            'store'
        )->name('store');


        // Dashboard BNN
        Route::get(
            '/dashboard',
            'dashboard'
        )->name('dashboard');


        // Monitoring BNN
        Route::get(
            '/monitoring',
            'monitoring'
        )->name('monitoring');


        // Cari NRP Master Database
        Route::get(
            '/cari/{nrp}',
            'cariNRP'
        )->name('cari');


    });




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

        });

    /*
    |--------------------------------------------------------------------------
    | ATR Karyawan — Pengaturan PIC Roster
    |--------------------------------------------------------------------------
    | Master Auto PIC ATR Produksi.
    | Semua mapping posisi ke PIC dikelola melalui AtrPicRosterController.
    | Tidak ada lagi hardcode PC/DZ/DOZER/EXCAVATOR di AtrController.
    */

    Route::prefix('database/atr')
        ->name('database.atr.')
        ->controller(AtrPicRosterController::class)
        ->group(function (): void {
            Route::get(
                '/pic-roster',
                'index'
            )->name('pic-roster');

            Route::post(
                '/pic-roster/groups',
                'storeGroup'
            )->name('pic-roster.groups.store');

            Route::post(
                '/pic-roster/groups/{group}',
                'updateGroup'
            )->name('pic-roster.groups.update');

            Route::post(
                '/pic-roster/groups/{group}/toggle',
                'toggleGroup'
            )->name('pic-roster.groups.toggle');

            Route::post(
                '/pic-roster/rules',
                'storeRule'
            )->name('pic-roster.rules.store');

            Route::post(
                '/pic-roster/rules/{rule}',
                'updateRule'
            )->name('pic-roster.rules.update');

            Route::post(
                '/pic-roster/rules/{rule}/toggle',
                'toggleRule'
            )->name('pic-roster.rules.toggle');

            /*
            |--------------------------------------------------------------------------
            | UI Sederhana PIC Roster — Daftar Mapping
            |--------------------------------------------------------------------------
            | Route lama groups/rules tetap dipertahankan untuk kompatibilitas.
            | UI baru cukup memakai Tambah Mapping / Edit / Aktif-Nonaktif.
            */

            Route::post(
                '/pic-roster/mappings',
                'storeMapping'
            )->name('pic-roster.mappings.store');

            Route::post(
                '/pic-roster/mappings/{rule}',
                'updateMapping'
            )->name('pic-roster.mappings.update');

            Route::post(
                '/pic-roster/mappings/{rule}/toggle',
                'toggleMapping'
            )->name('pic-roster.mappings.toggle');

            /*
            |--------------------------------------------------------------------------
            | PIC ROSTER BULANAN
            |--------------------------------------------------------------------------
            | Struktur route lama tetap dipertahankan.
            | Endpoint ini hanya menyimpan PIC per periode + kategori.
            */
            Route::post(
                '/pic-roster/monthly',
                'saveMonthlyRoster'
            )->name('pic-roster.monthly.save');
        });

    /*
    |--------------------------------------------------------------------------
    | ATR Karyawan — Backend Laravel
    |--------------------------------------------------------------------------
    | ATR_MTD/Apps Script hanya menjadi referensi prototype. Data ATR sekarang
    | diproses melalui upload Excel dan disimpan ke database Laravel.
    */

    Route::prefix('database/atr')
        ->name('database.atr.')
        ->controller(AtrController::class)
        ->group(function (): void {
            Route::get(
                '/',
                'summary'
            )->name('summary');

            Route::get(
                '/upload',
                'upload'
            )->name('upload');

            Route::post(
                '/upload/preview',
                'preview'
            )->middleware('throttle:10,1')
                ->name('upload.preview');

            Route::post(
                '/upload/preview/cancel',
                'discardPreview'
            )->middleware('throttle:20,1')
                ->name('upload.preview.cancel');

            Route::post(
                '/upload/commit',
                'commit'
            )->middleware('throttle:5,1')
                ->name('upload.commit');

            Route::get(
                '/template',
                'downloadTemplate'
            )->name('template');

            Route::get(
                '/import-history',
                'history'
            )->name('history');

            Route::post(
                '/imports/{import}/cancel',
                'cancelImport'
            )->middleware('throttle:10,1')
                ->name('imports.cancel');

            Route::get(
                '/call-documentation',
                'calls'
            )->name('calls');

            Route::post(
                '/call-documentation',
                'storeCoaching'
            )->middleware('throttle:20,1')
                ->name('calls.store');

            Route::post(
                '/coaching/{coaching}/cancel',
                'cancelCoaching'
            )->middleware('throttle:10,1')
                ->name('coaching.cancel');

            Route::get(
                '/coaching/{coaching}/print',
                'printCoaching'
            )->name('coaching.print');

            Route::get(
                '/coaching/{coaching}/attachment/{attachment}',
                'attachment'
            )->name('attachments.show');
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

    Route::get(
        '/admin-all',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'index']
    )->middleware('can:admin-all.view')
        ->name('admin-all');


    /*
    |--------------------------------------------------------------------------
    | Suggestion System
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin-all/suggestion-system',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestion']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.index');


    Route::get(
        '/admin-all/suggestion-system/monitoring',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionMonitoring']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.monitoring');


    Route::get(
        '/admin-all/suggestion-system/verifikasi-gl',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionVerificationGl']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.verification-gl');


    Route::post(
        '/admin-all/suggestion-system/verifikasi-gl/bridge-check',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionVerificationGlBridgeCheck']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.verification-gl.bridge-check');


    Route::post(
        '/admin-all/suggestion-system/verifikasi-gl/{noSs}/action',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionVerificationGlAction']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.verification-gl.action');

    Route::get(
        '/admin-all/suggestion-system/persetujuan-sh',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionApprovalSh']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.approval-sh');


    Route::post(
        '/admin-all/suggestion-system/persetujuan-sh/{noSs}/action',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionApprovalShAction']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.approval-sh.action');


    Route::get(
        '/admin-all/suggestion-system/detail/{noSs}',
        [\App\Http\Controllers\Admin\AdminAllController::class, 'suggestionDetail']
    )->middleware('can:admin-all.view')
        ->name('admin-all.suggestion.detail');


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
    | NOTIFIKASI — Menandai Notifikasi sebagai Dibaca
    |--------------------------------------------------------------------------
    */
        Route::get(
        '/notifications',
        [NotificationController::class,'index']
        )
        ->name('notifications');


        Route::post(
        '/notifications/read/{id}',
        [NotificationController::class,'read']
        )
        ->name('notifications.read');



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
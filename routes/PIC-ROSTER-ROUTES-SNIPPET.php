/*
|--------------------------------------------------------------------------
| STEP 2B FINAL PATCH + STEP 3 — Master PIC Roster
|--------------------------------------------------------------------------
|
| 1. Tambahkan import ini di bagian atas routes/web.php:
|
| use App\Http\Controllers\AtrPicRosterController;
|
| 2. HAPUS route PIC Roster lama yang menunjuk:
|    DatabaseUiController@atrPicRoster
|
| 3. Tambahkan route berikut DI DALAM middleware('auth').
|    Nama GET tetap database.atr.pic-roster agar sidebar tidak perlu diubah.
|
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
    });
<?php

/*
|--------------------------------------------------------------------------
| ATR Karyawan Produksi — Route Snippet
|--------------------------------------------------------------------------
|
| 1. Tambahkan import ini di bagian atas routes/web.php:
|
| use App\Http\Controllers\AtrController;
|
| 2. Di dalam Route::middleware('auth')->group(...), ganti route ATR lama
|    dengan blok berikut. Route Database Karyawan yang lain jangan diubah.
|
*/

Route::prefix('database/atr')
    ->name('database.atr.')
    ->controller(AtrController::class)
    ->group(function (): void {
        Route::get('/', 'summary')->name('summary');

        Route::get('/upload', 'upload')->name('upload');
        Route::post('/upload/preview', 'preview')
            ->middleware('throttle:10,1')
            ->name('upload.preview');
        Route::post('/upload/commit', 'commit')
            ->middleware('throttle:5,1')
            ->name('upload.commit');
        Route::get('/template', 'downloadTemplate')->name('template');

        Route::get('/import-history', 'history')->name('history');

        Route::get('/call-documentation', 'calls')->name('calls');
        Route::post('/call-documentation', 'storeCoaching')
            ->middleware('throttle:20,1')
            ->name('calls.store');

        Route::get('/coaching/{coaching}/print', 'printCoaching')
            ->name('coaching.print');
        Route::get('/coaching/{coaching}/attachment/{attachment}', 'attachment')
            ->name('attachments.show');
    });

/*
| Route Pengaturan PIC Roster yang lama tetap dipertahankan:
|
| Route::get(
|     '/database/atr/pic-roster',
|     [DatabaseUiController::class, 'atrPicRoster']
| )->name('database.atr.pic-roster');
|
| Pastikan tidak ada route lama dengan nama yang sama:
| database.atr.summary
| database.atr.upload
| database.atr.history
| database.atr.calls
*/

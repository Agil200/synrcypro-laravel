<?php

/*
|--------------------------------------------------------------------------
| ATR Karyawan Produksi — FINAL + Pembatalan
|--------------------------------------------------------------------------
|
| Tambahkan/use di bagian atas routes/web.php:
| use App\Http\Controllers\AtrController;
|
| Tempel blok ini di dalam middleware auth yang saat ini memuat route ATR.
| Route PIC Roster lama tetap dipertahankan terpisah.
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
        Route::post('/imports/{import}/cancel', 'cancelImport')
            ->middleware('throttle:10,1')
            ->name('imports.cancel');

        Route::get('/call-documentation', 'calls')->name('calls');
        Route::post('/call-documentation', 'storeCoaching')
            ->middleware('throttle:20,1')
            ->name('calls.store');

        Route::post('/coaching/{coaching}/cancel', 'cancelCoaching')
            ->middleware('throttle:10,1')
            ->name('coaching.cancel');

        Route::get('/coaching/{coaching}/print', 'printCoaching')
            ->name('coaching.print');
        Route::get('/coaching/{coaching}/attachment/{attachment}', 'attachment')
            ->name('attachments.show');
    });

/*
| Route PIC Roster yang lama JANGAN DIHAPUS, misalnya:
|
| Route::get(
|     '/database/atr/pic-roster',
|     [DatabaseUiController::class, 'atrPicRoster']
| )->name('database.atr.pic-roster');
*/
<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    EnsureInternalAccess::class.':koordinator_tu,kepala_tu,kepala_sekolah',
])->group(function () {
    Route::prefix('surat-keluar')->name('surat-keluar.')
        ->group(function () {
            Route::get('/', [SuratKeluarController::class, 'index'])
                ->name('index');

            Route::get('/create', [SuratKeluarController::class, 'create'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('create');

            Route::post('/', [SuratKeluarController::class, 'store'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('store');

            Route::get('/{suratKeluar}/edit', [SuratKeluarController::class, 'edit'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('edit');

            Route::put('/{suratKeluar}', [SuratKeluarController::class, 'update'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('update');

            Route::get('/{suratKeluar}/edit', [SuratKeluarController::class, 'edit'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('edit');

            Route::put('/{suratKeluar}', [SuratKeluarController::class, 'update'])
                ->middleware(EnsureInternalAccess::class.':koordinator_tu')
                ->name('update');

            Route::post('/{suratKeluar}/aksi', [
                SuratKeluarController::class, 'action',
            ])->name('aksi');

            Route::get('/{suratKeluar}/download', [
                SuratKeluarController::class, 'download',
            ])->name('download');

            Route::get('/{suratKeluar}/riwayat/{riwayat}', [
                SuratKeluarController::class, 'downloadHistory',
            ])->whereNumber('riwayat')->name('riwayat');

            Route::get('/{suratKeluar}', [
                SuratKeluarController::class, 'show',
            ])->name('show');
        });

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('laporan.index');

    Route::get('/laporan/export', [LaporanController::class, 'export'])
        ->name('laporan.export');
});

require __DIR__.'/mitra.php';

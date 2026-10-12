<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index'])->name('beranda');

// Pengiriman dan pelacakan surat hanya dilakukan melalui sistem internal.

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])
        ->middleware('throttle:20,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::middleware(['auth', EnsureInternalAccess::class])
    ->group(function () {
        Route::get('/dashboard', function (Request $request) {
            return redirect()->route(match ($request->user()->role) {
                'koordinator_tu' => 'tu.dashboard',
                'kepala_tu' => 'kepala-tu.dashboard',
                'kepala_sekolah' => 'kepala.dashboard',
                'waka_humas' => 'waka-humas.dashboard',
                'penerima_disposisi' => 'penerima.dashboard',
            });
        })->name('dashboard');

        Route::get('/tu/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':koordinator_tu')
            ->name('tu.dashboard');
        Route::get('/kepala/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')
            ->name('kepala.dashboard');
        Route::get('/kepala-tu/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':kepala_tu')
            ->name('kepala-tu.dashboard');
        Route::get('/penerima/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':penerima_disposisi')
            ->name('penerima.dashboard');
        Route::get('/waka-humas/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':waka_humas')
            ->name('waka-humas.dashboard');
    });

Route::middleware([
    'auth', EnsureInternalAccess::class.':koordinator_tu',
])->prefix('tu')->name('tu.')->group(function () {
    Route::get('/surat-masuk', [SuratMasukController::class, 'index'])
        ->name('surat-masuk.index');
    Route::get('/surat-masuk/create', [SuratMasukController::class, 'create'])
        ->name('surat-masuk.create');
    Route::post('/surat-masuk', [SuratMasukController::class, 'store'])
        ->name('surat-masuk.store');
    Route::get('/surat-masuk/{suratMasuk}/download', [
        SuratMasukController::class, 'download',
    ])->name('surat-masuk.download');
    Route::get('/surat-masuk/{suratMasuk}', [
        SuratMasukController::class, 'show',
    ])->name('surat-masuk.show');

});

Route::middleware(['auth', EnsureInternalAccess::class])
    ->prefix('disposisi')->name('disposisi.')
    ->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::get('/arsip', [DisposisiController::class, 'archive'])->name('archive');
        Route::get('/create', [DisposisiController::class, 'create'])
            ->middleware(EnsureInternalAccess::class.':koordinator_tu')
            ->name('create');
        Route::post('/', [DisposisiController::class, 'store'])
            ->middleware(EnsureInternalAccess::class.':koordinator_tu')
            ->name('store');
        Route::patch('/{disposisi}/setujui', [DisposisiController::class, 'approve'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')->name('approve');
        Route::patch('/{disposisi}/tolak', [DisposisiController::class, 'reject'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')->name('reject');
        Route::patch('/{disposisi}/teruskan', [DisposisiController::class, 'issue'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')->name('issue');
        Route::get('/{disposisi}/surat-masuk', [
            DisposisiController::class, 'downloadSuratMasuk',
        ])->name('surat-masuk');
        Route::get('/{disposisi}/lembar', [
            DisposisiController::class, 'downloadLembar',
        ])->name('lembar');
        Route::get('/{disposisi}/lampiran/{lampiran}', [
            DisposisiController::class, 'downloadLampiran',
        ])->whereNumber('lampiran')->name('lampiran');
        Route::post('/{disposisi}/tindak-lanjut', [
            DisposisiController::class, 'followUp',
        ])->middleware(EnsureInternalAccess::class.':kepala_sekolah,penerima_disposisi,waka_humas')
            ->name('tindak-lanjut');
        Route::get('/{disposisi}', [DisposisiController::class, 'show'])
            ->name('show');
    });

require __DIR__.'/persuratan.php';

Route::middleware(['auth', EnsureInternalAccess::class.':koordinator_tu'])->group(function () {
    Route::delete('/tu/surat-masuk/{suratMasuk}', [\App\Http\Controllers\HapusDataController::class, 'suratMasuk'])->name('tu.surat-masuk.destroy');
    Route::delete('/surat-keluar/{suratKeluar}', [\App\Http\Controllers\HapusDataController::class, 'suratKeluar'])->name('surat-keluar.destroy');
    Route::delete('/tu/kegiatan/{kegiatan}', [\App\Http\Controllers\HapusDataController::class, 'kegiatan'])->name('tu.kegiatan.destroy');
});

Route::delete('/disposisi/{disposisi}', [\App\Http\Controllers\HapusDataController::class, 'disposisi'])
    ->middleware(['auth', EnsureInternalAccess::class.':koordinator_tu,kepala_sekolah'])
    ->name('disposisi.destroy');

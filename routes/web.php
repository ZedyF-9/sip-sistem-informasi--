<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\PemeriksaanSuratController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratPublikController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index'])->name('beranda');

Route::get('/kirim-surat', [SuratPublikController::class, 'create'])
    ->name('publik.kirim');
Route::post('/kirim-surat', [SuratPublikController::class, 'store'])
    ->middleware('throttle:30,1')->name('publik.store');
Route::get('/surat-terkirim', [SuratPublikController::class, 'berhasil'])
    ->name('publik.berhasil');
Route::get('/lacak-surat', [SuratPublikController::class, 'lacakForm'])
    ->name('publik.lacak');
Route::post('/lacak-surat', [SuratPublikController::class, 'lacak'])
    ->middleware('throttle:20,1')->name('publik.lacak.submit');

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
                'kepala_sekolah' => 'kepala.dashboard',
                'penerima_disposisi' => 'penerima.dashboard',
            });
        })->name('dashboard');

        Route::get('/tu/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':koordinator_tu')
            ->name('tu.dashboard');
        Route::get('/kepala/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')
            ->name('kepala.dashboard');
        Route::get('/penerima/dashboard', [DashboardController::class, 'index'])
            ->middleware(EnsureInternalAccess::class.':penerima_disposisi')
            ->name('penerima.dashboard');
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

    Route::get('/pemeriksaan', [PemeriksaanSuratController::class, 'indexTu'])
        ->name('pemeriksaan.index');
    Route::get('/pemeriksaan/{suratMasuk}', [
        PemeriksaanSuratController::class, 'showTu',
    ])->name('pemeriksaan.show');
    Route::patch('/pemeriksaan/{suratMasuk}', [
        PemeriksaanSuratController::class, 'update',
    ])->name('pemeriksaan.update');
});

Route::middleware([
    'auth', EnsureInternalAccess::class.':kepala_sekolah',
])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/surat', [PemeriksaanSuratController::class, 'indexKepala'])
        ->name('surat.index');
    Route::get('/surat/{suratMasuk}/download', [
        PemeriksaanSuratController::class, 'downloadKepala',
    ])->name('surat.download');
    Route::get('/surat/{suratMasuk}', [
        PemeriksaanSuratController::class, 'showKepala',
    ])->name('surat.show');
});

Route::middleware(['auth', EnsureInternalAccess::class])
    ->prefix('disposisi')->name('disposisi.')
    ->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::get('/create', [DisposisiController::class, 'create'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')
            ->name('create');
        Route::post('/', [DisposisiController::class, 'store'])
            ->middleware(EnsureInternalAccess::class.':kepala_sekolah')
            ->name('store');
        Route::get('/{disposisi}/surat', [
            DisposisiController::class, 'downloadSurat',
        ])->name('surat');
        Route::get('/{disposisi}/lampiran/{lampiran}', [
            DisposisiController::class, 'downloadLampiran',
        ])->whereNumber('lampiran')->name('lampiran');
        Route::post('/{disposisi}/tindak-lanjut', [
            DisposisiController::class, 'followUp',
        ])->middleware(EnsureInternalAccess::class.':penerima_disposisi')
            ->name('tindak-lanjut');
        Route::get('/{disposisi}', [DisposisiController::class, 'show'])
            ->name('show');
    });

require __DIR__.'/persuratan.php';

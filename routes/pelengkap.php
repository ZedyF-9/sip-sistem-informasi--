<?php

use App\Http\Controllers\CetakLaporanController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\NomorResmiController;
use App\Http\Controllers\PerbaikanSuratController;
use App\Http\Controllers\SertifikatController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Support\Facades\Route;

Route::get('/verifikasi-sertifikat', [
    SertifikatController::class,
    'form',
])->name('publik.sertifikat.form');

Route::post('/verifikasi-sertifikat', [
    SertifikatController::class,
    'cari',
])
    ->middleware('throttle:20,1')
    ->name('publik.sertifikat.cari');

Route::get('/verifikasi-sertifikat/{kode}', [
    SertifikatController::class,
    'hasil',
])
    ->where('kode', '[a-f0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('publik.sertifikat.hasil');

Route::middleware([
    'auth',
    EnsureInternalAccess::class.':koordinator_tu,kepala_sekolah',
])->group(function () {
    Route::get('/laporan/cetak', [
        CetakLaporanController::class,
        'index',
    ])->name('laporan.cetak');
});

Route::middleware([
    'auth',
    EnsureInternalAccess::class.':koordinator_tu',
])
    ->prefix('tu')
    ->name('tu.')
    ->group(function () {
        Route::get('/kegiatan', [
            KegiatanController::class, 'index',
        ])->name('kegiatan.index');

        Route::get('/kegiatan/create', [
            KegiatanController::class, 'create',
        ])->name('kegiatan.create');

        Route::post('/kegiatan', [
            KegiatanController::class, 'store',
        ])->name('kegiatan.store');

        Route::get('/kegiatan/{kegiatan}/edit', [
            KegiatanController::class, 'edit',
        ])->whereNumber('kegiatan')->name('kegiatan.edit');

        Route::put('/kegiatan/{kegiatan}', [
            KegiatanController::class, 'update',
        ])->whereNumber('kegiatan')->name('kegiatan.update');

        Route::get('/kegiatan/{kegiatan}', [
            KegiatanController::class, 'show',
        ])->whereNumber('kegiatan')->name('kegiatan.show');

        Route::post('/kegiatan/{kegiatan}/sertifikat', [
            SertifikatController::class, 'store',
        ])
            ->whereNumber('kegiatan')
            ->middleware('throttle:10,1')
            ->name('sertifikat.store');

        Route::get('/sertifikat/{sertifikat}/download', [
            SertifikatController::class, 'download',
        ])->whereNumber('sertifikat')->name('sertifikat.download');

        Route::patch('/sertifikat/{sertifikat}/cabut', [
            SertifikatController::class, 'cabut',
        ])->whereNumber('sertifikat')->name('sertifikat.cabut');

        Route::get('/perbaikan-surat', [
            PerbaikanSuratController::class, 'index',
        ])->name('perbaikan.index');

        Route::get('/perbaikan-surat/{suratMasuk}', [
            PerbaikanSuratController::class, 'edit',
        ])->whereNumber('suratMasuk')->name('perbaikan.edit');

        Route::put('/perbaikan-surat/{suratMasuk}', [
            PerbaikanSuratController::class, 'update',
        ])->whereNumber('suratMasuk')->name('perbaikan.update');

        Route::get('/nomor-resmi', [
            NomorResmiController::class, 'index',
        ])->name('nomor-resmi.index');

        Route::post('/nomor-resmi/{suratKeluar}', [
            NomorResmiController::class, 'store',
        ])->whereNumber('suratKeluar')->name('nomor-resmi.store');
    });
<?php

use App\Http\Controllers\CetakLaporanController;
use App\Http\Controllers\KegiatanController;
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
    ->where('kode', '(?:[0-9]{8}|[a-f0-9]{64})')
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

        Route::post('/kegiatan/{kegiatan}/impor/pratinjau', [
            KegiatanController::class, 'previewImport',
        ])->whereNumber('kegiatan')->name('kegiatan.import.preview');

        Route::post('/kegiatan/{kegiatan}/impor', [
            KegiatanController::class, 'importStudents',
        ])->whereNumber('kegiatan')->name('kegiatan.import.store');

        Route::patch('/kegiatan/{kegiatan}/peserta/{pesertaPkl}/selesai', [
            KegiatanController::class, 'completeParticipant',
        ])->whereNumber('kegiatan')->whereNumber('pesertaPkl')->name('kegiatan.peserta.complete');

        Route::patch('/kegiatan/{kegiatan}/kelas/selesai', [
            KegiatanController::class, 'completeClass',
        ])->whereNumber('kegiatan')->name('kegiatan.kelas.complete');

        Route::delete('/kegiatan/{kegiatan}/peserta/{pesertaPkl}', [
            KegiatanController::class, 'removeParticipant',
        ])->whereNumber('kegiatan')->whereNumber('pesertaPkl')->name('kegiatan.peserta.destroy');

        Route::post('/kegiatan/{kegiatan}/sertifikat/terbitkan-semua', [
            SertifikatController::class, 'bulkStore',
        ])->whereNumber('kegiatan')->name('kegiatan.sertifikat.bulk');

        Route::get('/kegiatan/{kegiatan}/sertifikat/unduh-zip', [
            SertifikatController::class, 'downloadZip',
        ])->whereNumber('kegiatan')->name('kegiatan.sertifikat.zip');

        Route::get('/kegiatan/{kegiatan}/edit', [
            KegiatanController::class, 'edit',
        ])
            ->whereNumber('kegiatan')
            ->name('kegiatan.edit');

        Route::put('/kegiatan/{kegiatan}', [
            KegiatanController::class, 'update',
        ])
            ->whereNumber('kegiatan')
            ->name('kegiatan.update');

        Route::get('/kegiatan/{kegiatan}', [
            KegiatanController::class, 'show',
        ])
            ->whereNumber('kegiatan')
            ->name('kegiatan.show');

        Route::post('/kegiatan/{kegiatan}/peserta/{pesertaPkl}/sertifikat', [
            SertifikatController::class, 'store',
        ])
            ->whereNumber('kegiatan')->whereNumber('pesertaPkl')
            ->middleware('throttle:10,1')
            ->name('sertifikat.store');

        Route::get('/sertifikat/{sertifikat}/download', [
            SertifikatController::class, 'download',
        ])
            ->whereNumber('sertifikat')
            ->name('sertifikat.download');

        Route::patch('/sertifikat/{sertifikat}/cabut', [
            SertifikatController::class, 'cabut',
        ])
            ->whereNumber('sertifikat')
            ->name('sertifikat.cabut');

    });

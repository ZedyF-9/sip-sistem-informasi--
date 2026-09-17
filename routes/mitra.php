<?php

use App\Http\Controllers\KerjasamaController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MitraPublikController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Support\Facades\Route;

Route::get('/mitra', [
    MitraPublikController::class,
    'index',
])->name('publik.mitra.index');

Route::get('/mitra/{mitra}/logo', [
    MitraPublikController::class,
    'logo',
])
    ->whereNumber('mitra')
    ->name('publik.mitra.logo');

Route::get('/mitra/{mitra}', [
    MitraPublikController::class,
    'show',
])
    ->whereNumber('mitra')
    ->name('publik.mitra.show');

Route::middleware([
    'auth',
    EnsureInternalAccess::class.':koordinator_tu',
])
    ->prefix('tu')
    ->name('tu.')
    ->group(function () {
        Route::get('/mitra', [
            MitraController::class,
            'index',
        ])->name('mitra.index');

        Route::get('/mitra/create', [
            MitraController::class,
            'create',
        ])->name('mitra.create');

        Route::post('/mitra', [
            MitraController::class,
            'store',
        ])->name('mitra.store');

        Route::get('/mitra/{mitra}/edit', [
            MitraController::class,
            'edit',
        ])
            ->whereNumber('mitra')
            ->name('mitra.edit');

        Route::put('/mitra/{mitra}', [
            MitraController::class,
            'update',
        ])
            ->whereNumber('mitra')
            ->name('mitra.update');

        Route::get('/mitra/{mitra}/logo', [
            MitraController::class,
            'logo',
        ])
            ->whereNumber('mitra')
            ->name('mitra.logo');

        Route::get('/mitra/{mitra}/kerjasama/create', [
            KerjasamaController::class,
            'create',
        ])
            ->whereNumber('mitra')
            ->name('kerjasama.create');

        Route::post('/mitra/{mitra}/kerjasama', [
            KerjasamaController::class,
            'store',
        ])
            ->whereNumber('mitra')
            ->name('kerjasama.store');

        Route::get('/mitra/{mitra}', [
            MitraController::class,
            'show',
        ])
            ->whereNumber('mitra')
            ->name('mitra.show');

        Route::get('/kerjasama/{kerjasama}/edit', [
            KerjasamaController::class,
            'edit',
        ])
            ->whereNumber('kerjasama')
            ->name('kerjasama.edit');

        Route::put('/kerjasama/{kerjasama}', [
            KerjasamaController::class,
            'update',
        ])
            ->whereNumber('kerjasama')
            ->name('kerjasama.update');

        Route::get('/kerjasama/{kerjasama}/download', [
            KerjasamaController::class,
            'download',
        ])
            ->whereNumber('kerjasama')
            ->name('kerjasama.download');
    });

require __DIR__.'/akun.php';
<?php

use App\Http\Controllers\KerjasamaController;
use App\Http\Controllers\AgendaKerjasamaController;
use App\Http\Controllers\AgendaMitraController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MitraPublikController;
use App\Http\Controllers\PersetujuanKerjasamaController;
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

Route::middleware(['auth', EnsureInternalAccess::class.':waka_humas'])
    ->prefix('waka-humas/kerjasama')
    ->name('waka-humas.kerjasama.')
    ->group(function () {
        Route::get('/', [PersetujuanKerjasamaController::class, 'index'])->name('index');
        Route::get('/mitra/{mitra}/logo', [MitraController::class, 'logo'])
            ->whereNumber('mitra')->name('mitra.logo');
        Route::patch('/mitra/{mitra}/setujui', [PersetujuanKerjasamaController::class, 'approveMitra'])
            ->whereNumber('mitra')->name('mitra.approve');
        Route::patch('/mitra/{mitra}/tolak', [PersetujuanKerjasamaController::class, 'rejectMitra'])
            ->whereNumber('mitra')->name('mitra.reject');
        Route::patch('/{kerjasama}/setujui', [PersetujuanKerjasamaController::class, 'approve'])
            ->whereNumber('kerjasama')->name('approve');
        Route::patch('/{kerjasama}/tolak', [PersetujuanKerjasamaController::class, 'reject'])
            ->whereNumber('kerjasama')->name('reject');
        Route::get('/{kerjasama}/download', [PersetujuanKerjasamaController::class, 'download'])
            ->whereNumber('kerjasama')->name('download');
        Route::get('/{kerjasama}', [AgendaKerjasamaController::class, 'show'])
            ->whereNumber('kerjasama')->name('show');
        Route::post('/{kerjasama}/agenda', [AgendaKerjasamaController::class, 'store'])
            ->whereNumber('kerjasama')->name('agenda.store');
    });

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

        Route::delete('/mitra/{mitra}', [
            \App\Http\Controllers\HapusDataController::class, 'mitra',
        ])->whereNumber('mitra')->name('mitra.destroy');

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

        Route::post('/mitra/{mitra}/agenda', [
            AgendaMitraController::class,
            'store',
        ])->whereNumber('mitra')->name('mitra.agenda.store');

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

        Route::get('/kerjasama/{kerjasama}', [
            AgendaKerjasamaController::class,
            'show',
        ])->whereNumber('kerjasama')->name('kerjasama.show');

        Route::post('/kerjasama/{kerjasama}/agenda', [
            AgendaKerjasamaController::class,
            'store',
        ])->whereNumber('kerjasama')->name('kerjasama.agenda.store');
    });

require __DIR__.'/akun.php';

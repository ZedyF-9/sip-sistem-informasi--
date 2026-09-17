<?php

use App\Http\Controllers\KelolaAkunController;
use App\Http\Controllers\PasswordSayaController;
use App\Http\Middleware\EnsureInternalAccess;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    EnsureInternalAccess::class,
])->group(function () {
    Route::get('/profil/password', [
        PasswordSayaController::class,
        'edit',
    ])->name('profil.password.edit');

    Route::put('/profil/password', [
        PasswordSayaController::class,
        'update',
    ])
        ->middleware('throttle:6,1')
        ->name('profil.password.update');
});

Route::middleware([
    'auth',
    EnsureInternalAccess::class.':koordinator_tu',
])
    ->prefix('tu')
    ->name('tu.')
    ->group(function () {
        Route::get('/akun', [
            KelolaAkunController::class,
            'index',
        ])->name('akun.index');

        Route::get('/akun/create', [
            KelolaAkunController::class,
            'create',
        ])->name('akun.create');

        Route::post('/akun', [
            KelolaAkunController::class,
            'store',
        ])
            ->middleware('throttle:10,1')
            ->name('akun.store');

        Route::get('/akun/{akun}/edit', [
            KelolaAkunController::class,
            'edit',
        ])
            ->whereNumber('akun')
            ->name('akun.edit');

        Route::put('/akun/{akun}', [
            KelolaAkunController::class,
            'update',
        ])
            ->whereNumber('akun')
            ->middleware('throttle:10,1')
            ->name('akun.update');

        Route::get('/akun/{akun}/password', [
            KelolaAkunController::class,
            'resetForm',
        ])
            ->whereNumber('akun')
            ->name('akun.password.edit');

        Route::put('/akun/{akun}/password', [
            KelolaAkunController::class,
            'resetPassword',
        ])
            ->whereNumber('akun')
            ->middleware('throttle:6,1')
            ->name('akun.password.update');
    });

require __DIR__.'/pelengkap.php';
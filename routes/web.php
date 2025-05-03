<?php

use Illuminate\Support\Facades\Route;

// Halaman Login
Route::get('/login', function () {
    return view('login');
})->name('login');

// Register untuk Pembeli
Route::get('/register/pembeli', function () {
    return view('auth.register-pembeli');
})->name('register.pembeli');

// Register untuk Organisasi
Route::get('/register/organisasi', function () {
    return view('auth.register-organisasi');
})->name('register.organisasi');

// Halaman-halaman dashboard yang memerlukan autentikasi
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Dashboard Admin
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Route lain untuk admin
    });

    // Dashboard Customer Service
    Route::middleware(['role:customer service'])->prefix('cs')->group(function () {
        Route::get('/dashboard', function () {
            return view('cs.dashboard');
        })->name('cs.dashboard');

        // Route lain untuk CS
    });

    // Dashboard Pegawai Gudang
    Route::middleware(['role:pegawai gudang,gudang'])->prefix('gudang')->group(function () {
        Route::get('/dashboard', function () {
            return view('gudang.dashboard');
        })->name('gudang.dashboard');

        // Route lain untuk pegawai gudang
    });

    // Dashboard Hunter
    Route::middleware(['role:hunter'])->prefix('hunter')->group(function () {
        Route::get('/dashboard', function () {
            return view('hunter.dashboard');
        })->name('hunter.dashboard');

        // Route lain untuk hunter
    });

    // Dashboard Pembeli
    Route::middleware(['role:pembeli'])->prefix('pembeli')->group(function () {
        Route::get('/dashboard', function () {
            return view('pembeli.dashboard');
        })->name('pembeli.dashboard');

        // Route lain untuk pembeli
    });

    // Dashboard Penitip
    Route::middleware(['role:penitip'])->prefix('penitip')->group(function () {
        Route::get('/dashboard', function () {
            return view('penitip.dashboard');
        })->name('penitip.dashboard');

        // Route lain untuk penitip
    });

    // Dashboard Organisasi
    Route::middleware(['role:organisasi'])->prefix('organisasi')->group(function () {
        Route::get('/dashboard', function () {
            return view('organisasi.dashboard');
        })->name('organisasi.dashboard');

        // Route lain untuk organisasi
    });
});

// Halaman Unauthorized
Route::get('/unauthorized', function () {
    return view('errors.unauthorized');
})->name('unauthorized');

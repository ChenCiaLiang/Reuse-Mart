<?php

use App\Http\Controllers\Api\Auth\OrganisasiAuthController;
use App\Http\Controllers\Api\Auth\PegawaiAuthController;
use App\Http\Controllers\Api\Auth\PembeliAuthController;
use App\Http\Controllers\Api\Auth\PenitipAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Routes untuk Pegawai (Admin, CS, Pegawai Gudang, Hunter)
Route::prefix('pegawai')->group(function () {
    Route::post('/login', [PegawaiAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [PegawaiAuthController::class, 'me']);
        Route::post('/logout', [PegawaiAuthController::class, 'logout']);
        Route::post('/change-password', [PegawaiAuthController::class, 'changePassword']);
        // Register pegawai baru (hanya untuk Admin)
        // Route::middleware('role:admin')->post('/register', [PegawaiAuthController::class, 'register']);
        Route::post('/register', [PegawaiAuthController::class, 'register']);
    });
});

// Routes untuk Pembeli
Route::prefix('pembeli')->group(function () {
    Route::post('/login', [PembeliAuthController::class, 'login']);
    Route::post('/register', [PembeliAuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [PembeliAuthController::class, 'me']);
        Route::post('/logout', [PembeliAuthController::class, 'logout']);
        Route::post('/change-password', [PembeliAuthController::class, 'changePassword']);
    });
});

// Routes untuk Penitip
Route::prefix('penitip')->group(function () {
    Route::post('/login', [PenitipAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [PenitipAuthController::class, 'me']);
        Route::post('/logout', [PenitipAuthController::class, 'logout']);
        Route::post('/change-password', [PenitipAuthController::class, 'changePassword']);

        // Register penitip baru (hanya untuk CS)
        Route::middleware('role:customer service')->post('/register', [PenitipAuthController::class, 'register']);
    });
});

// Routes untuk Organisasi
Route::prefix('organisasi')->group(function () {
    Route::post('/login', [OrganisasiAuthController::class, 'login']);
    Route::post('/register', [OrganisasiAuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [OrganisasiAuthController::class, 'me']);
        Route::post('/logout', [OrganisasiAuthController::class, 'logout']);
        Route::post('/change-password', [OrganisasiAuthController::class, 'changePassword']);
    });
});

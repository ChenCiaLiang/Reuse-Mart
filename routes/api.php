<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OrganisasiAuthController;
use App\Http\Controllers\Auth\PegawaiAuthController;
use App\Http\Controllers\Auth\PembeliAuthController;
use App\Http\Controllers\Auth\PenitipAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Request;

// Routes untuk Pegawai (Admin, CS, Pegawai Gudang, Hunter)
Route::prefix('pegawai')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [PegawaiAuthController::class, 'me']);
        Route::post('/logout', [PegawaiAuthController::class, 'logout']);
        Route::post('/changePassword', [PegawaiAuthController::class, 'changePassword']);
        Route::middleware('role:admin')->post('/register', [PegawaiAuthController::class, 'register']);
    });
});

Route::prefix('pembeli')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [PembeliAuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [PembeliAuthController::class, 'me']);
        Route::post('/logout', [PembeliAuthController::class, 'logout']);
        Route::post('/change-password', [PembeliAuthController::class, 'changePassword']);
    });
});

Route::prefix('penitip')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [PenitipAuthController::class, 'me']);
        Route::post('/logout', [PenitipAuthController::class, 'logout']);
        Route::post('/change-password', [PenitipAuthController::class, 'changePassword']);

        // Register penitip baru (hanya untuk CS)
        Route::middleware('role:customer service')->post('/register', [PenitipAuthController::class, 'register']);
    });
});

Route::prefix('organisasi')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [OrganisasiAuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [OrganisasiAuthController::class, 'me']);
        Route::post('/logout', [OrganisasiAuthController::class, 'logout']);
        Route::post('/changePassword', [OrganisasiAuthController::class, 'changePassword']);
    });
});

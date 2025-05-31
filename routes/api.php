<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->middleware('Role:admin')->group(function () {
        Route::get('pegawai/{id}', [PegawaiController::class, 'show'])->name('show');
    });
    Route::prefix('cs')->middleware('Role:cs')->group(function () {});

    Route::prefix('gudang')->middleware('Role:gudang')->group(function () {});

    Route::prefix('hunter')->middleware('Role:hunter')->group(function () {});

    Route::prefix('owner')->middleware('Role:owner')->group(function () {});

    Route::prefix('pembeli')->middleware('Role:pembeli')->group(function () {});

    Route::prefix('penitip')->middleware('Role:penitip')->group(function () {});

    Route::prefix('organisasi')->middleware('Role:organisasi')->group(function () {});

    Route::get('/logout', [AuthController::class, 'logout']);
});

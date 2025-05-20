<?php

use App\Http\Controllers\PegawaiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('pegawai')->group(function () {
    Route::post('/login', [PegawaiController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::post('/create', [PegawaiController::class, 'create']);
        });
        Route::get('/logout', [PegawaiController::class, 'logout']);
    });
});

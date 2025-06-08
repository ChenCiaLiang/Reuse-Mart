<?php

use App\Http\Controllers\TopSellerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\PenitipController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

//umum
Route::prefix('produk')->group(function () {
    Route::get('/', [ProdukController::class, 'index']);
    Route::get('/{id}', [ProdukController::class, 'show']);
});
Route::get('/kategori', [ProdukController::class, 'kategori']);

Route::prefix('top-seller')->group(function () {
    Route::get('/current', [TopSellerController::class, 'getCurrentTopSeller']);
    Route::get('/history', [TopSellerController::class, 'getTopSellerHistory']);
    Route::get('/stats', [TopSellerController::class, 'getTopSellerStats']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('admin')->middleware('Role:admin')->group(function () {
        Route::get('pegawai/{id}', [PegawaiController::class, 'show'])->name('show');
    });
    Route::prefix('cs')->middleware('Role:cs')->group(function () {});

    Route::prefix('gudang')->middleware('Role:gudang')->group(function () {});

    Route::prefix('hunter')->middleware('Role:hunter')->group(function () {
        Route::get('/profile', [PegawaiController::class, 'getHunterProfile']);
        Route::get('/history-komisi', [PegawaiController::class, 'getHunterHistoryKomisi']);
        Route::get('/stats', [PegawaiController::class, 'getHunterStats']);
    });

    Route::prefix('owner')->middleware('Role:owner')->group(function () {});

    Route::prefix('pembeli')->middleware('Role:pembeli')->group(function () {
        Route::get('/profile', [PembeliController::class, 'getProfile']);
        Route::get('/history-transaksi', [PembeliController::class, 'getHistoryTransaksi']);
    });

    Route::prefix('penitip')->middleware('Role:penitip')->group(function () {
        Route::get('/profile', [PenitipController::class, 'getProfile']);
        Route::get('/history-transaksi', [PenitipController::class, 'getHistoryTransaksi']);
        Route::get('/detail-transaksi/{idTransaksiPenjualan}', [PenitipController::class, 'getDetailTransaksi']);
    });

    Route::prefix('organisasi')->middleware('Role:organisasi')->group(function () {});

    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});

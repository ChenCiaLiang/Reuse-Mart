<?php

use App\Http\Controllers\TopSellerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchandiseController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\PenitipController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

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

Route::get('/merchandise/katalog', [MerchandiseController::class, 'katalog'])->name('api.mobile.merchandise.katalog');

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

        Route::prefix('merchandise')->group(function () {
            // Klaim merchandise
            Route::post('/klaim', [MerchandiseController::class, 'klaimMerchandise'])->name('klaimMerchandise');

            // History klaim merchandise
            Route::get('/history', [MerchandiseController::class, 'historyKlaim'])->name('historyKlaim');

            // Detail klaim merchandise
            Route::get('/klaim/{idPenukaran}', [MerchandiseController::class, 'detailKlaim'])->name('detailKlaim');
        });

        // Get poin pembeli
        Route::get('/poin', [MerchandiseController::class, 'getPoin'])->name('getPoin');
    });

    Route::prefix('penitip')->middleware('Role:penitip')->group(function () {
        Route::get('/profile', [PenitipController::class, 'getProfile']);
        Route::get('/history-transaksi', [PenitipController::class, 'getHistoryTransaksi']);
        Route::get('/detail-transaksi/{idTransaksiPenjualan}', [PenitipController::class, 'getDetailTransaksi']);
    });

    Route::prefix('kurir')->middleware('Role:kurir')->group(function () {
        Route::get('/profile', [PegawaiController::class, 'getKurirProfile']);
        Route::get('/history-pengiriman', [PegawaiController::class, 'getKurirHistoryPengiriman']);
        Route::post('/update-status-selesai', [PegawaiController::class, 'updateStatusPengirimanSelesai']);
        Route::get('/stats', [PegawaiController::class, 'getKurirStats']);
        Route::get('/tugas-hari-ini', [PegawaiController::class, 'getTugasHariIni']);
    });

    Route::prefix('organisasi')->middleware('Role:organisasi')->group(function () {});
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});

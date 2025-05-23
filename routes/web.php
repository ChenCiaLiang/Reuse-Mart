<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiskusiProdukController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\PenitipController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\RequestDonasiController;
use App\Http\Controllers\TransaksiPengirimanController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('loginPage');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::prefix('register')->name('register.')->group(function () {
        Route::get('/pembeli', function () {
            return view('auth.register.pembeli');
        })->name('pembeliPage');
        Route::post('/pembeli', [AuthController::class, 'registerPembeli'])->name('pembeli');

        Route::get('/organisasi', function () {
            return view('auth.register.organisasi');
        })->name('organisasiPage');
        Route::post('/organisasi', [AuthController::class, 'registerOrganisasi'])->name('organisasi');
    });

    Route::prefix('produk')->group(function () {
        Route::get('/index', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/show/{id}', [ProdukController::class, 'show'])->name('produk.show');
        Route::get('/popup', [ProdukController::class, 'indexPopup'])->name('produk.showPopup');
    });
});

// Route::middleware('auth')->group(function () {

//untuk pegawai bro
Route::prefix('pegawai')->middleware('RolePegawai:pegawai')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [PegawaiController::class, 'adminDashboard'])->name('dashboard');

        //manajemen pegawai
        Route::prefix('pegawai')->name('pegawai.')->group(function () {
            Route::get('/', [PegawaiController::class, 'index'])->name('index');
            Route::get('/create', [PegawaiController::class, 'create'])->name('create');
            Route::post('/', [PegawaiController::class, 'store'])->name('store');
            Route::get('/{id}', [PegawaiController::class, 'show'])->name('show');
            Route::get('/edit/{id}', [PegawaiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PegawaiController::class, 'update'])->name('update');
            Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('destroy');
        });

        //manajemen organisasi
        Route::prefix('organisasi')->name('organisasi.')->group(function () {
            Route::get('/', [OrganisasiController::class, 'index'])->name('index');
            Route::get('/{id}', [OrganisasiController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [OrganisasiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [OrganisasiController::class, 'update'])->name('update');
            Route::delete('/{id}', [OrganisasiController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('cs')->name('cs.')->group(function () {
        //in case kalo cs punya database sendiri yah bro
        Route::get('/dashboard', function () {
            return redirect()->route('cs.penitip.index');
        })->name('dashboard');

        //manajemen penitip
        Route::prefix('penitip')->name('penitip.')->group(function () {
            Route::get('/', [PenitipController::class, 'index'])->name('index');
            Route::get('/create', [PenitipController::class, 'create'])->name('create');
            Route::post('/', [PenitipController::class, 'store'])->name('store');
            Route::get('/{id}', [PenitipController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PenitipController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PenitipController::class, 'update'])->name('update');
            Route::delete('/{id}', [PenitipController::class, 'destroy'])->name('destroy');
        });
    });

    //untuk gudang bro
    Route::prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('gudang.pengiriman.index');
        })->name('dashboard');

        Route::prefix('pengiriman')->name('pengiriman.')->group(function () {
            Route::get('/', [TransaksiPengirimanController::class, 'index'])->name('index');
            Route::get('/{id}', [TransaksiPengirimanController::class, 'show'])->name('show');
            Route::get('penjadwalanPage/{id}', [TransaksiPengirimanController::class, 'penjadwalanPage'])->name('penjadwalanPage');
            Route::post('penjadwalan/{id}', [TransaksiPengirimanController::class, 'penjadwalan'])->name('penjadwalan');
            Route::post('konfirmasiAmbil/{id}', [TransaksiPengirimanController::class, 'konfirmasiAmbil'])->name('konfirmasiAmbil');
        });
    });

    //owner
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [RequestDonasiController::class, 'dashboard'])->name('dashboard');
        // Manajemen donasi
        Route::prefix('donasi')->name('donasi.')->group(function () {
            Route::get('/request', [RequestDonasiController::class, 'index'])->name('request');
            Route::get('/history', [RequestDonasiController::class, 'historyDonasi'])->name('history');
            Route::get('/barang', [RequestDonasiController::class, 'barangDonasi'])->name('barang');
            Route::post('/alokasi', [RequestDonasiController::class, 'alokasikanBarang'])->name('alokasi');
            Route::get('/edit/{id}', [RequestDonasiController::class, 'editDonasi'])->name('edit');
            Route::put('/update/{id}', [RequestDonasiController::class, 'updateDonasi'])->name('update');
        });
    });
});

//untuk customer bro
Route::prefix('customer')->group(function () {
    Route::get('/homePage', function () {
        return view('customer.homePage');
    })->name('homePage');

    //pembeli
    Route::prefix('pembeli')->middleware('auth:pembeli')->name('pembeli.')->group(function () {
        Route::get('/profile', [PembeliController::class, 'profile'])->name('profile');
        Route::get('/history', [PembeliController::class, 'historyTransaksi'])->name('history');
        Route::get('/transaksi/{idTransaksi}', [PembeliController::class, 'detailTransaksi'])->name('transaksi.detail');

        //alamat
        Route::prefix('alamat')->name('alamat.')->group(function () {});
        Route::get('/', [AlamatController::class, 'index'])->name('index');
        Route::get('/create', [AlamatController::class, 'create'])->name('create');
        Route::post('/', [AlamatController::class, 'store'])->name('store');
        Route::get('/search', [AlamatController::class, 'search'])->name('search');
        Route::get('/{id}', [AlamatController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AlamatController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AlamatController::class, 'update'])->name('update');
        Route::delete('/{id}', [AlamatController::class, 'destroy'])->name('destroy');
    });

    //penitip
    Route::prefix('penitip')->middleware('auth:penitip')->name('penitip.')->group(function () {
        Route::get('/profile', [PenitipController::class, 'profile'])->name('profile');
        Route::get('/history', [PenitipController::class, 'historyTransaksi'])->name('history');
        Route::get('/transaksi/{id}', [PenitipController::class, 'detailTransaksi'])->name('transaksi.detail');
    });

    Route::prefix('organisasi')->middleware('auth:organisasi')->name('organisasi.')->group(function () {
        //CEKME
        Route::get('/profile', [PembeliController::class, 'profile'])->name('profile');

        //request donasi
        Route::prefix('requestDonasi')->name('requestDonasi.')->group(function () {
            Route::get('/', [RequestDonasiController::class, 'index'])->name('index');
            Route::get('/create', [RequestDonasiController::class, 'create'])->name('create');
            Route::post('/', [RequestDonasiController::class, 'store'])->name('store');
            Route::get('/{id}', [RequestDonasiController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RequestDonasiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RequestDonasiController::class, 'update'])->name('update');
            Route::delete('/{id}', [RequestDonasiController::class, 'destroy'])->name('destroy');
        });
    });

    //produk
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::get('/show/{id}', [ProdukController::class, 'show'])->name('show');
        Route::get('/{id}/diskusi', [DiskusiProdukController::class, 'index'])->name('diskusi');
        Route::post('/{id}/diskusi', [DiskusiProdukController::class, 'store'])->name('diskusi.store');
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// });

Route::get('/unAuthorized', function () {
    return view('auth.unAuthorized');
})->name('unAuthorized');

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
use App\Http\Controllers\TransaksiPenitipanController;
use App\Http\Controllers\TransaksiPenjualanController;
use App\Http\Controllers\MerchandiseController;
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

        // Route merchandise
        Route::prefix('merchandise')->name('merchandise.')->group(function () {
            Route::get('/', [MerchandiseController::class, 'index'])->name('index');
            Route::get('/{id}', [MerchandiseController::class, 'show'])->name('show');
            Route::get('/{id}/konfirmasi', [MerchandiseController::class, 'konfirmasiForm'])->name('konfirmasi.form');
            Route::post('/{id}/konfirmasi', [MerchandiseController::class, 'konfirmasiPengambilan'])->name('konfirmasi');
        });
        
        // TAMBAHAN BARU: Payment Verification Routes (Fungsionalitas 69-70)
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::get('/', [TransaksiPenjualanController::class, 'indexVerification'])->name('index');
            Route::get('/{idTransaksi}', [TransaksiPenjualanController::class, 'showVerification'])->name('show');
            Route::post('/{idTransaksi}/verify', [TransaksiPenjualanController::class, 'verifyPayment'])->name('verify');
        });
    });

    //untuk gudang bro
    Route::prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('gudang.penitipan.dashboard');
        })->name('dashboard');

        // Pengiriman Routes
        Route::prefix('pengiriman')->name('pengiriman.')->group(function () {
            Route::get('/', [TransaksiPengirimanController::class, 'index'])->name('index');
            Route::get('/{id}', [TransaksiPengirimanController::class, 'show'])->name('show');
            Route::get('penjadwalanKirimPage/{id}', [TransaksiPengirimanController::class, 'penjadwalanKirimPage'])->name('penjadwalanKirimPage');
            Route::post('penjadwalanKirim/{id}', [TransaksiPengirimanController::class, 'penjadwalanKirim'])->name('penjadwalanKirim');
            Route::get('penjadwalanAmbilPage/{id}', [TransaksiPengirimanController::class, 'penjadwalanAmbilPage'])->name('penjadwalanAmbilPage');
            Route::post('penjadwalanAmbil/{id}', [TransaksiPengirimanController::class, 'penjadwalanAmbil'])->name('penjadwalanAmbil');
            Route::get('konfirmasiAmbil/{id}', [TransaksiPengirimanController::class, 'konfirmasiAmbil'])->name('konfirmasiAmbil');
        });

        Route::prefix('penitipan')->name('penitipan.')->group(function () {
            Route::get('/dashboard', [TransaksiPenitipanController::class, 'dashboard'])->name('dashboard');

            Route::get('/', [TransaksiPenitipanController::class, 'indexGudang'])->name('index');
            Route::get('/create', [TransaksiPenitipanController::class, 'create'])->name('create');
            Route::post('/', [TransaksiPenitipanController::class, 'store'])->name('store');
            Route::get('/{id}', [TransaksiPenitipanController::class, 'showGudang'])->name('show');
            Route::get('/{id}/edit', [TransaksiPenitipanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TransaksiPenitipanController::class, 'update'])->name('update');
            Route::delete('/{id}', [TransaksiPenitipanController::class, 'destroy'])->name('destroy');

            Route::get('/{id}/print-nota', [TransaksiPenitipanController::class, 'printNota'])->name('print-nota');
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
        Route::post('/buy-direct', [TransaksiPenjualanController::class, 'buyDirect'])->name('buy.direct');

        // Rating routes - Tambahkan ini
        Route::prefix('rating')->name('rating.')->group(function () {
            Route::get('/', [PembeliController::class, 'indexRating'])->name('index');
            Route::post('/store', [PembeliController::class, 'storeRating'])->name('store');
        });

        //alamat
        Route::prefix('alamat')->name('alamat.')->group(function () {
            Route::get('/', [AlamatController::class, 'index'])->name('index');
            Route::get('/create', [AlamatController::class, 'create'])->name('create');
            Route::post('/', [AlamatController::class, 'store'])->name('store');
            Route::get('/search', [AlamatController::class, 'search'])->name('search');
            Route::get('/{id}', [AlamatController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AlamatController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AlamatController::class, 'update'])->name('update');
            Route::delete('/{id}', [AlamatController::class, 'destroy'])->name('destroy');
        });

        // TAMBAHAN BARU: Cart dan Checkout Routes (Fungsionalitas 57-62)
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [TransaksiPenjualanController::class, 'showCart'])->name('show'); // Lihat keranjang
            Route::post('/add', [TransaksiPenjualanController::class, 'addToCart'])->name('add'); // Fungsi 57: Add to cart
            Route::post('/remove', [TransaksiPenjualanController::class, 'removeFromCart'])->name('remove'); // Fungsi 57: Remove from cart
            Route::get('/count', [TransaksiPenjualanController::class, 'getCartCount'])->name('count'); // Get cart count
        });

        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::get('/', [TransaksiPenjualanController::class, 'showCheckout'])->name('show'); // Halaman checkout
            Route::post('/calculate-total', [TransaksiPenjualanController::class, 'calculateTotal'])->name('calculate-total'); // Fungsi 60: Hitung total
            Route::post('/update-shipping-method', [TransaksiPenjualanController::class, 'updateShippingMethod'])->name('update-shipping-method'); // Fungsi 58: Update metode pengiriman
            Route::post('/update-shipping-address', [TransaksiPenjualanController::class, 'updateShippingAddress'])->name('update-shipping-address'); // Fungsi 59: Update alamat pengiriman
            Route::post('/update-point-usage', [TransaksiPenjualanController::class, 'updatePointUsage'])->name('update-point-usage'); // Fungsi 61: Update penggunaan poin
            Route::get('/info', [TransaksiPenjualanController::class, 'getCheckoutInfo'])->name('info'); // Get checkout info lengkap

            Route::post('/proceed', [TransaksiPenjualanController::class, 'proceedCheckout'])->name('proceed');
        });
        
        // TAMBAHAN BARU: Payment Routes (Fungsionalitas 68)
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/{idTransaksi}', [TransaksiPenjualanController::class, 'showPayment'])->name('show');
            Route::post('/{idTransaksi}/upload', [TransaksiPenjualanController::class, 'uploadPaymentProof'])->name('upload');
            Route::get('/{idTransaksi}/status', [TransaksiPenjualanController::class, 'getTransactionStatus'])->name('status');
        });
    });

    //penitip
    Route::prefix('penitip')->middleware('auth:penitip')->name('penitip.')->group(function () {
        Route::get('/profile', [PenitipController::class, 'profile'])->name('profile');
        Route::get('/history', [PenitipController::class, 'historyTransaksi'])->name('history');
        Route::get('/transaksi/{id}', [PenitipController::class, 'detailTransaksi'])->name('transaksi.detail');

        Route::prefix('penitipan')->name('penitipan.')->group(function () {
            Route::get('/', [TransaksiPenitipanController::class, 'indexPenitip'])->name('index');
            Route::get('/{id}', [TransaksiPenitipanController::class, 'showPenitip'])->name('show');
            Route::get('perpanjangan/{id}', [TransaksiPenitipanController::class, 'perpanjangan'])->name('perpanjangan');
        });
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

Route::get('/system/check-expired-transactions', [TransaksiPenjualanController::class, 'checkExpiredTransactions'])
    ->name('system.check-expired-transactions');

Route::get('/unAuthorized', function () {
    return view('auth.unAuthorized');
})->name('unAuthorized');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Halaman dashboard
Route::get('/', function () {
    return view('welcome');
})->name('dashboard');

// Halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $loginController = new LoginController();
    $result = $loginController->login($request);

    // Jika ini adalah response JSON, ambil kontennya
    $data = json_decode($result->getContent(), true);

    if ($result->getStatusCode() === 200) {

        session([
            'access_token' => $data['access_token'],
            'user_id' => $data['user']['id'],
            'user_type' => $data['user']['userType'],
            'user_name' => $data['user']['nama'],
            'user_username' => $data['user']['username'] ?? null,
            'user_email' => $data['user']['email'] ?? null,
            'user_foto_profile' => $data['user']['foto_profile'] ?? null,
            'user_poin' => $data['user']['poin'] ?? null,
            'user_idJabatan' => $data['user']['idJabatan'] ?? null,
        ]);

        // Redirect berdasarkan tipe user
        switch ($data['user']['userType']) {
            case 'pegawai':
                return redirect()->route('admin.dashboard');
            case 'pembeli':
                return redirect()->route('customer.homePage');
            case 'penitip':
                return redirect()->route('customer.homePage');
            case 'organisasi':
                return redirect()->route('customer.homePage');
            default:
                return redirect('/');
        }
    }

    // Jika login gagal
    return back()->withErrors([
        'loginID' => $data['message'] ?? 'Login gagal',
    ])->withInput();
})->name('login.submit');

// Halaman Logout
Route::post('/logout', function () {
    return view('logout');
})->name('logout');

// Register untuk Pembeli
Route::get('/register/pembeli', function () {
    return view('auth.register-pembeli');
})->name('register.pembeli');

// Register untuk Organisasi
Route::get('/register/organisasi', function () {
    return view('auth.register-organisasi');
})->name('register.organisasi');

Route::prefix('produk')->group(function () {
    Route::get('/index', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/show/{id}', [ProdukController::class, 'show'])->name('produk.show');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');
Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
// Routes untuk mengelola pegawai
Route::prefix('pegawai')->group(function () {
    Route::get('/', [PegawaiController::class, 'index'])->name('admin.pegawai.index');
    Route::get('/create', [PegawaiController::class, 'create'])->name('admin.pegawai.create');
    Route::post('/', [PegawaiController::class, 'store'])->name('admin.pegawai.store');
    Route::get('/{id}', [PegawaiController::class, 'show'])->name('admin.pegawai.show');
    Route::get('/{id}/edit', [PegawaiController::class, 'edit'])->name('admin.pegawai.edit');
    Route::put('/{id}', [PegawaiController::class, 'update'])->name('admin.pegawai.update');
    Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('admin.pegawai.destroy');
});


// Halaman-halaman dashboard yang memerlukan autentikasi
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Dashboard Admin
    /*Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');*/
    // Routes untuk mengelola pegawai
    /*Route::prefix('pegawai')->group(function () {
        Route::get('/', [PegawaiController::class, 'index'])->name('admin.pegawai.index');
        Route::get('/create', [PegawaiController::class, 'create'])->name('admin.pegawai.create');
        Route::post('/', [PegawaiController::class, 'store'])->name('admin.pegawai.store');
        Route::get('/{id}', [PegawaiController::class, 'show'])->name('admin.pegawai.show');
        Route::get('/{id}/edit', [PegawaiController::class, 'edit'])->name('admin.pegawai.edit');
        Route::put('/{id}', [PegawaiController::class, 'update'])->name('admin.pegawai.update');
        Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('admin.pegawai.destroy');
    });
    });*/

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

});
Route::get('/homePage', function () {
    return view('customer.homePage');
})->name('customer.homePage');

Route::get('/profile', function () {
    return view('customer.profile');
})->name('customer.profile');

// Halaman Unauthorized
Route::get('/unauthorized', function () {
    return view('errors.unauthorized');
})->name('unauthorized');

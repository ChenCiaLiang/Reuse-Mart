<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use App\Models\Pembeli;
use App\Models\Alamat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransaksiPenjualanController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja
     */
    public function showCart()
    {
        // Check if user is logged in as pembeli
        if (!session('user') || session('role') !== 'pembeli') {
            return redirect()->route('loginPage')->with('error', 'Silakan login sebagai pembeli terlebih dahulu');
        }

        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);

        // Ambil data cart dari session
        $cart = session('cart', []);
        $cartItems = [];

        if (!empty($cart)) {
            $productIds = array_keys($cart);
            $products = Produk::whereIn('idProduk', $productIds)
                ->where('status', 'Tersedia')
                ->get();

            foreach ($products as $product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $cart[$product->idProduk] ?? 1,
                    'subtotal' => $product->hargaJual
                ];
            }
        }

        return view('customer.pembeli.cart.index', compact('cartItems', 'pembeli'));
    }

    /**
     * Menambahkan produk ke keranjang (Fungsionalitas 57)
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idProduk' => 'required|exists:produk,idProduk',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Cek apakah user sudah login sebagai pembeli
        if (!session('user') || session('role') !== 'pembeli') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('loginPage')->with('error', 'Silakan login sebagai pembeli terlebih dahulu');
        }

        $idProduk = $request->idProduk;

        // Cek apakah produk masih tersedia
        $produk = Produk::where('idProduk', $idProduk)
            ->where('status', 'Tersedia')
            ->first();

        if (!$produk) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Produk tidak tersedia'], 404);
            }
            return back()->with('error', 'Produk tidak tersedia');
        }

        // Ambil cart dari session
        $cart = session('cart', []);

        // Karena barang bekas stoknya selalu 1, cek apakah sudah ada di cart
        if (isset($cart[$idProduk])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Produk sudah ada dalam keranjang'], 400);
            }
            return back()->with('error', 'Produk sudah ada dalam keranjang');
        }

        // Tambahkan ke cart (quantity = 1 karena barang bekas)
        $cart[$idProduk] = 1;
        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang',
                'cartCount' => count($cart) // PERBAIKAN: Pastikan cartCount dikembalikan
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Menghapus produk dari keranjang (Fungsionalitas 57)
     */
    public function removeFromCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idProduk' => 'required|exists:produk,idProduk',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Data tidak valid',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator);
            }

            // Check authentication
            if (!session('user') || session('role') !== 'pembeli') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized'
                    ], 401);
                }
                return redirect()->route('loginPage');
            }

            $idProduk = $request->idProduk;
            $cart = session('cart', []);

            if (isset($cart[$idProduk])) {
                unset($cart[$idProduk]);
                session(['cart' => $cart]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Produk berhasil dihapus dari keranjang',
                        'cartCount' => count($cart),
                        'remainingItems' => count($cart)
                    ], 200);
                }
                return back()->with('success', 'Produk berhasil dihapus dari keranjang');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Produk tidak ditemukan dalam keranjang'
                ], 404);
            }
            return back()->with('error', 'Produk tidak ditemukan dalam keranjang');
        } catch (\Exception $e) {
            \Log::error('Error removing from cart: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Terjadi kesalahan server'
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menghapus produk');
        }
    }
    /**
     * Modifikasi method showCheckout untuk support beli langsung
     */
    public function showCheckout()
    {
        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return redirect()->route('loginPage')->with('error', 'Silakan login sebagai pembeli terlebih dahulu');
        }

        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);

        // Ambil alamat pembeli
        $alamatList = Alamat::where('idPembeli', $idPembeli)->get();
        $alamatDefault = $alamatList->where('statusDefault', true)->first();

        // PERBAIKAN: Cek apakah ini dari direct buy atau cart biasa
        $isDirectBuy = session()->has('direct_buy');
        $items = $isDirectBuy ? session('direct_buy', []) : session('cart', []);

        if (empty($items)) {
            return redirect()->route('pembeli.cart.show')->with('error', 'Tidak ada produk untuk checkout');
        }

        $cartItems = [];
        $subtotal = 0;

        $productIds = array_keys($items);
        $products = Produk::whereIn('idProduk', $productIds)
            ->where('status', 'Tersedia')
            ->get();

        // Validasi apakah semua produk masih tersedia
        if ($products->count() !== count($productIds)) {
            // Ada produk yang sudah tidak tersedia
            $availableIds = $products->pluck('idProduk')->toArray();
            $unavailableIds = array_diff($productIds, $availableIds);

            // Remove unavailable products
            foreach ($unavailableIds as $unavailableId) {
                unset($items[$unavailableId]);
            }

            if ($isDirectBuy) {
                session(['direct_buy' => $items]);
            } else {
                session(['cart' => $items]);
            }

            if (empty($items)) {
                return redirect()->route('pembeli.cart.show')->with('error', 'Semua produk sudah tidak tersedia');
            }

            return redirect()->route('pembeli.checkout.show')->with('error', 'Beberapa produk sudah tidak tersedia dan telah dihapus');
        }

        foreach ($products as $product) {
            $cartItems[] = [
                'product' => $product,
                'quantity' => 1, // Selalu 1 untuk barang bekas
                'subtotal' => $product->hargaJual
            ];
            $subtotal += $product->hargaJual;
        }

        return view('customer.pembeli.checkout.index', compact(
            'cartItems',
            'pembeli',
            'alamatList',
            'alamatDefault',
            'subtotal',
            'isDirectBuy'  // Tambahkan flag ini
        ));
    }
    /**
     * Menghitung total belanja dengan ongkir (Fungsionalitas 60)
     */
    public function calculateTotal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'metode_pengiriman' => 'required|in:kurir,ambil_sendiri',
            'idAlamat' => 'required_if:metode_pengiriman,kurir|exists:alamat,idAlamat',
            'poin_digunakan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);

        // Ambil data cart
        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Keranjang kosong'], 400);
        }

        // Hitung subtotal
        $productIds = array_keys($cart);
        $products = Produk::whereIn('idProduk', $productIds)
            ->where('status', 'Tersedia')
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json(['error' => 'Beberapa produk sudah tidak tersedia'], 400);
        }

        $subtotal = 0;
        foreach ($products as $product) {
            $subtotal += $product->hargaJual;
        }

        // Hitung ongkir (Fungsionalitas 60)
        $ongkir = 0;
        if ($request->metode_pengiriman === 'kurir') {
            // Validasi alamat jika kurir
            if ($request->idAlamat) {
                $alamat = Alamat::where('idAlamat', $request->idAlamat)
                    ->where('idPembeli', $idPembeli)
                    ->first();

                if (!$alamat) {
                    return response()->json(['error' => 'Alamat tidak valid'], 400);
                }
            }

            // Ongkir gratis jika total pembelian >= 1.5 juta
            // Ongkir = 100 ribu jika total pembelian < 1.5 juta
            if ($subtotal < 1500000) {
                $ongkir = 100000;
            }
        }

        // Validasi dan hitung penggunaan poin (Fungsionalitas 61)
        $poinDigunakan = min($request->poin_digunakan ?? 0, $pembeli->poin);
        $diskonPoin = $poinDigunakan * 10; // 1 poin = Rp 10 (sesuai dokumen reward: 100 poin = Rp 10.000)

        // Pastikan diskon tidak melebihi subtotal + ongkir
        $totalSebelumDiskon = $subtotal + $ongkir;
        $diskonPoin = min($diskonPoin, $totalSebelumDiskon);
        $poinDigunakan = floor($diskonPoin / 10); // Recalculate berdasarkan diskon yang valid

        // Total akhir
        $totalAkhir = $totalSebelumDiskon - $diskonPoin;

        // Hitung poin yang akan didapat (Fungsionalitas 62)
        // 1 poin = 10.000, bonus 20% jika > 500.000
        $poinDapat = floor($totalAkhir / 10000);
        if ($totalAkhir > 500000) {
            $bonusPoin = floor($poinDapat * 0.2);
            $poinDapat += $bonusPoin;
        }

        return response()->json([
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'poin_digunakan' => $poinDigunakan,
            'diskon_poin' => $diskonPoin,
            'total_akhir' => $totalAkhir,
            'poin_didapat' => $poinDapat,
            'sisa_poin' => $pembeli->poin - $poinDigunakan,
            'metode_pengiriman' => $request->metode_pengiriman
        ]);
    }

    /**
     * Update metode pengiriman (Fungsionalitas 58)
     */
    public function updateShippingMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'metode_pengiriman' => 'required|in:kurir,ambil_sendiri',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Simpan metode pengiriman ke session
        session([
            'checkout_shipping_method' => $request->metode_pengiriman
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metode pengiriman berhasil diperbarui',
            'metode_pengiriman' => $request->metode_pengiriman
        ]);
    }

    /**
     * Update alamat pengiriman (Fungsionalitas 59)
     */
    public function updateShippingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idAlamat' => 'required|exists:alamat,idAlamat',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];

        // Pastikan alamat milik pembeli yang login
        $alamat = Alamat::where('idAlamat', $request->idAlamat)
            ->where('idPembeli', $idPembeli)
            ->first();

        if (!$alamat) {
            return response()->json(['error' => 'Alamat tidak valid'], 400);
        }

        // Simpan alamat ke session
        session([
            'checkout_shipping_address' => $request->idAlamat
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil diperbarui',
            'alamat' => [
                'id' => $alamat->idAlamat,
                'jenis' => $alamat->jenis,
                'alamat_lengkap' => $alamat->alamatLengkap
            ]
        ]);
    }

    /**
     * Update penggunaan poin (Fungsionalitas 61)
     */
    public function updatePointUsage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poin_digunakan' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);

        // Validasi poin yang digunakan tidak melebihi poin yang dimiliki
        $poinDigunakan = min($request->poin_digunakan, $pembeli->poin);

        if ($poinDigunakan != $request->poin_digunakan) {
            return response()->json([
                'error' => 'Poin yang digunakan melebihi poin yang dimiliki',
                'max_poin' => $pembeli->poin
            ], 400);
        }

        // Simpan penggunaan poin ke session
        session([
            'checkout_points_used' => $poinDigunakan
        ]);

        $diskonPoin = $poinDigunakan * 10; // 1 poin = Rp 10

        return response()->json([
            'success' => true,
            'message' => 'Penggunaan poin berhasil diperbarui',
            'poin_digunakan' => $poinDigunakan,
            'diskon_poin' => $diskonPoin,
            'sisa_poin' => $pembeli->poin - $poinDigunakan
        ]);
    }

    /**
     * Mendapatkan informasi lengkap checkout
     */
    public function getCheckoutInfo()
    {
        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);

        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Keranjang kosong'], 400);
        }

        // Data checkout dari session
        $metodePengiriman = session('checkout_shipping_method', 'kurir');
        $idAlamat = session('checkout_shipping_address');
        $poinDigunakan = session('checkout_points_used', 0);

        // Hitung total
        $productIds = array_keys($cart);
        $products = Produk::whereIn('idProduk', $productIds)
            ->where('status', 'Tersedia')
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json(['error' => 'Beberapa produk sudah tidak tersedia'], 400);
        }

        $subtotal = $products->sum('hargaJual');

        // Ongkir
        $ongkir = 0;
        if ($metodePengiriman === 'kurir') {
            $ongkir = $subtotal < 1500000 ? 100000 : 0;
        }

        // Diskon poin
        $diskonPoin = $poinDigunakan * 10;
        $totalAkhir = $subtotal + $ongkir - $diskonPoin;

        // Poin yang akan didapat
        $poinDapat = floor($totalAkhir / 10000);
        if ($totalAkhir > 500000) {
            $poinDapat += floor($poinDapat * 0.2);
        }

        // Alamat jika kurir
        $alamat = null;
        if ($metodePengiriman === 'kurir' && $idAlamat) {
            $alamat = Alamat::find($idAlamat);
        }

        return response()->json([
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'poin_digunakan' => $poinDigunakan,
            'diskon_poin' => $diskonPoin,
            'total_akhir' => $totalAkhir,
            'poin_didapat' => $poinDapat,
            'metode_pengiriman' => $metodePengiriman,
            'alamat' => $alamat,
            'cart_items' => $products->map(function ($product) {
                return [
                    'id' => $product->idProduk,
                    'nama' => $product->deskripsi,
                    'harga' => $product->hargaJual,
                    'gambar' => $product->gambar
                ];
            })
        ]);
    }

    /**
     * Clear cart - helper method
     */
    private function clearCart()
    {
        session()->forget([
            'cart',
            'direct_buy', // TAMBAHAN BARU
            'checkout_shipping_method',
            'checkout_shipping_address',
            'checkout_points_used'
        ]);
    }

    /**
     * Get cart count for navbar
     */
    public function getCartCount()
    {
        try {
            // Check authentication
            if (!session('user') || session('role') !== 'pembeli') {
                return response()->json(['count' => 0], 200);
            }

            $cart = session('cart', []);
            $count = count($cart);

            return response()->json([
                'count' => $count,
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting cart count: ' . $e->getMessage());
            return response()->json([
                'count' => 0,
                'success' => false,
                'error' => 'Terjadi kesalahan'
            ], 200); // Still return 200 to prevent JS errors
        }
    }

    // TAMBAHAN UNTUK TransaksiPenjualanController.php
    // Tambahkan method-method ini ke dalam class TransaksiPenjualanController yang sudah ada

    /**
     * Melakukan Checkout - Menambah data transaksi di database (Fungsionalitas 63)
     * UPDATED: Menambahkan penyimpanan poin yang digunakan ke database
     */
    // File: app/Http/Controllers/TransaksiPenjualanController.php
    // Method: proceedCheckout() - PERBAIKAN VALIDATION

    public function proceedCheckout(Request $request)
    {
        // PERBAIKAN: Validation rule yang benar
        $validator = Validator::make($request->all(), [
            'metode_pengiriman' => 'required|in:kurir,ambil_sendiri',
            // PERBAIKAN: Tambahkan 'nullable' agar exists tidak dijalankan jika null
            'idAlamat' => 'required_if:metode_pengiriman,kurir|nullable|exists:alamat,idAlamat',
            'poin_digunakan' => 'nullable|integer|min:0',
        ]);

        // ALTERNATIF: Validasi kondisional yang lebih eksplisit
        /*
        $rules = [
            'metode_pengiriman' => 'required|in:kurir,ambil_sendiri',
            'poin_digunakan' => 'nullable|integer|min:0',
        ];

        // Hanya tambahkan rule idAlamat jika metode pengiriman adalah kurir
        if ($request->metode_pengiriman === 'kurir') {
            $rules['idAlamat'] = 'required|exists:alamat,idAlamat';
        }

        $validator = Validator::make($request->all(), $rules);
        */

        if ($validator->fails()) {
            \Log::error('CHECKOUT DEBUG: Validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            \Log::error('CHECKOUT DEBUG: Authentication failed');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();
        try {
            $idPembeli = session('user')['idPembeli'];
            $pembeli = Pembeli::findOrFail($idPembeli);

            // Validasi cart (cek apakah ini dari direct buy atau cart biasa)
            $isDirectBuy = session()->has('direct_buy');
            $cart = $isDirectBuy ? session('direct_buy', []) : session('cart', []);

            if (empty($cart)) {
                return response()->json(['error' => 'Keranjang belanja kosong'], 400);
            }

            // Validasi produk masih tersedia
            $productIds = array_keys($cart);
            $products = Produk::whereIn('idProduk', $productIds)
                ->where('status', 'Tersedia')
                ->get();

            if ($products->count() !== count($productIds)) {
                return response()->json(['error' => 'Beberapa produk sudah tidak tersedia'], 400);
            }

            // Persiapkan data alamat pengiriman
            $alamatPengiriman = null;
            $metodePengiriman = $request->metode_pengiriman;

            if ($metodePengiriman === 'kurir') {
                // Validasi dan ambil alamat
                if ($request->idAlamat) {
                    $alamat = Alamat::where('idAlamat', $request->idAlamat)
                        ->where('idPembeli', $idPembeli)
                        ->first();

                    if (!$alamat) {
                        return response()->json(['error' => 'Alamat tidak valid'], 400);
                    }

                    // Simpan alamat sebagai JSON
                    $alamatPengiriman = json_encode([
                        'jenis' => $alamat->jenis,
                        'alamatLengkap' => $alamat->alamatLengkap,
                        'idAlamat' => $alamat->idAlamat
                    ]);
                } else {
                    return response()->json(['error' => 'Alamat pengiriman wajib dipilih untuk pengiriman kurir'], 400);
                }
            } else {
                // Ambil sendiri - alamat gudang
                $alamatPengiriman = json_encode([
                    'jenis' => 'Gudang ReUseMart',
                    'alamatLengkap' => 'Jl. Green Eco Park No. 456 Yogyakarta (Jam operasional: 08:00 - 20:00)',
                    'idAlamat' => null
                ]);
            }

            // Hitung total dan validasi
            $subtotal = $products->sum('hargaJual');
            $ongkir = 0;
            if ($metodePengiriman === 'kurir') {
                $ongkir = $subtotal >= 1500000 ? 0 : 100000;
            }

            // Validasi dan hitung penggunaan poin
            $poinDigunakan = min($request->poin_digunakan ?? 0, $pembeli->poin);
            $diskonPoin = $poinDigunakan * 10;
            $totalSebelumDiskon = $subtotal + $ongkir;
            $diskonPoin = min($diskonPoin, $totalSebelumDiskon);
            $poinDigunakan = floor($diskonPoin / 10);
            $totalAkhir = $totalSebelumDiskon - $diskonPoin;

            // Hitung poin yang akan didapat
            $poinDidapat = floor($totalAkhir / 10000);
            if ($totalAkhir > 500000) {
                $bonusPoin = floor($poinDidapat * 0.2);
                $poinDidapat += $bonusPoin;
            }

            // Generate nomor transaksi
            $nomorTransaksi = $this->generateTransactionNumber();

            // Buat transaksi penjualan
            $tanggalPesan = Carbon::now();
            $tanggalBatasLunas = $tanggalPesan->copy()->addMinutes(1);

            $transaksi = TransaksiPenjualan::create([
                'status' => 'menunggu_pembayaran',
                'tanggalLaku' => null,
                'tanggalPesan' => $tanggalPesan,
                'tanggalBatasLunas' => $tanggalBatasLunas,
                'tanggalLunas' => null,
                'tanggalBatasAmbil' => null,
                'tanggalKirim' => null,
                'tanggalAmbil' => null,
                'idPembeli' => $idPembeli,
                'idPegawai' => null,
                'alamatPengiriman' => $alamatPengiriman,
                'metodePengiriman' => $metodePengiriman,
                'poinDidapat' => $poinDidapat,
                'poinDigunakan' => $poinDigunakan,
            ]);

            // Simpan detail transaksi dan update status produk
            foreach ($products as $product) {
                DetailTransaksiPenjualan::create([
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'idProduk' => $product->idProduk,
                ]);

                // Update status produk menjadi sold out
                $product->update(['status' => 'Terjual']);
            }

            // Kurangi poin yang digunakan
            if ($poinDigunakan > 0) {
                $pembeli->update(['poin' => $pembeli->poin - $poinDigunakan]);
            }

            // Simpan data checkout ke session untuk keperluan payment
            session([
                'checkout_data' => [
                    'idTransaksi' => $transaksi->idTransaksiPenjualan,
                    'nomorTransaksi' => $nomorTransaksi,
                    'subtotal' => $subtotal,
                    'ongkir' => $ongkir,
                    'diskon_poin' => $diskonPoin,
                    'total_akhir' => $totalAkhir,
                    'metode_pengiriman' => $metodePengiriman,
                    'alamat_pengiriman' => $alamatPengiriman,
                    'idAlamat' => $request->idAlamat,
                    'poin_digunakan' => $poinDigunakan,
                    'poin_didapat' => $poinDidapat,
                ]
            ]);

            // Clear cart
            if ($isDirectBuy) {
                session()->forget('direct_buy');
            } else {
                $this->clearCart();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'idTransaksi' => $transaksi->idTransaksiPenjualan,
                'nomorTransaksi' => $nomorTransaksi,
                'redirect_url' => route('pembeli.payment.show', $transaksi->idTransaksiPenjualan)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in proceedCheckout: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memproses transaksi'], 500);
        }
    }

    /**
     * Beli langsung tanpa masuk keranjang (langsung ke checkout)
     */
    public function buyDirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idProduk' => 'required|exists:produk,idProduk',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Cek apakah user sudah login sebagai pembeli
        if (!session('user') || session('role') !== 'pembeli') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('loginPage')->with('error', 'Silakan login sebagai pembeli terlebih dahulu');
        }

        $idProduk = $request->idProduk;

        // Cek apakah produk masih tersedia
        $produk = Produk::where('idProduk', $idProduk)
            ->where('status', 'Tersedia')
            ->first();

        if (!$produk) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Produk tidak tersedia'], 404);
            }
            return back()->with('error', 'Produk tidak tersedia');
        }

        // Buat session khusus untuk beli langsung (berbeda dari cart biasa)
        session([
            'direct_buy' => [
                $idProduk => 1
            ]
        ]);

        // Clear cart biasa untuk menghindari konflik
        session()->forget('cart');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mengarahkan ke checkout...',
                'redirect_url' => route('pembeli.checkout.show')
            ]);
        }

        return redirect()->route('pembeli.checkout.show');
    }

    /**
     * Generate nomor transaksi (Fungsionalitas 64)
     */
    private function generateTransactionNumber()
    {
        $year = date('y'); // 2 digit tahun
        $month = date('m'); // 2 digit bulan

        // Ambil nomor urut terakhir untuk bulan ini
        $lastTransaction = TransaksiPenjualan::whereYear('tanggalPesan', date('Y'))
            ->whereMonth('tanggalPesan', date('m'))
            ->orderBy('idTransaksiPenjualan', 'desc')
            ->first();

        $nomorUrut = 1;
        if ($lastTransaction) {
            $nomorUrut = $lastTransaction->idTransaksiPenjualan + 1;
        }

        return sprintf('%s.%s.%03d', $year, $month, $nomorUrut);
    }

    /**
     * PERBAIKAN: Update showPayment untuk load bukti pembayaran dari database
     */
    public function showPayment($idTransaksi)
    {
        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return redirect()->route('loginPage')->with('error', 'Silakan login sebagai pembeli terlebih dahulu');
        }

        $idPembeli = session('user')['idPembeli'];

        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('idPembeli', $idPembeli)
            ->with(['detailTransaksiPenjualan.produk', 'pegawaiVerifikasi'])
            ->first();

        if (!$transaksi) {
            return redirect()->route('pembeli.profile')->with('error', 'Transaksi tidak ditemukan');
        }

        // Auto-cancel logic remains the same...
        if ($transaksi->status === 'menunggu_pembayaran') {
            $now = \Carbon\Carbon::now();
            $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);

            if ($now->gt($batasLunas)) {
                \Log::info('Transaction expired, cancelling...', ['id' => $idTransaksi]);
                $this->cancelExpiredTransaction($transaksi);
                return redirect()->route('pembeli.profile')->with('error', 'Transaksi telah kedaluwarsa dan dibatalkan otomatis');
            }
        }

        $checkoutData = session('checkout_data', []);

        return view('customer.pembeli.payment.index', compact('transaksi', 'checkoutData'));
    }

    /**
     * Upload bukti pembayaran (Fungsionalitas 68) - DIPERBAIKI
     */
    public function uploadPaymentProof(Request $request, $idTransaksi)
    {
        $validator = Validator::make($request->all(), [
            'bukti_pembayaran' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];

        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('idPembeli', $idPembeli)
            ->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }

        // Check if transaction is still valid for payment upload
        if ($transaksi->status !== 'menunggu_pembayaran') {
            return response()->json(['error' => 'Status transaksi tidak valid untuk upload pembayaran'], 400);
        }

        // Check if payment deadline has passed
        if (Carbon::now()->gt($transaksi->tanggalBatasLunas)) {
            // Transaction expired - auto cancel
            $this->cancelExpiredTransaction($transaksi);
            return response()->json(['error' => 'Transaksi telah kedaluwarsa'], 400);
        }

        DB::beginTransaction();
        try {
            // PERBAIKAN: Upload file dan simpan ke database
            $buktiFile = $request->file('bukti_pembayaran');
            $filename = 'bukti_' . $idTransaksi . '_' . time() . '.' . $buktiFile->getClientOriginalExtension();

            // Ensure directory exists
            $uploadPath = public_path('uploads/bukti_pembayaran');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move uploaded file
            $buktiFile->move($uploadPath, $filename);
            $filePath = 'uploads/bukti_pembayaran/' . $filename;

            // PERBAIKAN: Update transaksi dengan data bukti pembayaran di database
            $transaksi->update([
                'status' => 'menunggu_verifikasi',
                'buktiPembayaran' => $filePath, // Simpan path file ke database
                'tanggalUploadBukti' => Carbon::now(), // Simpan timestamp upload
            ]);

            // PERBAIKAN: Hapus penggunaan session storage karena sekarang sudah di database
            // session()->forget('bukti_pembayaran_' . $idTransaksi);

            DB::commit();

            \Log::info('Payment proof uploaded successfully', [
                'transaction_id' => $idTransaksi,
                'customer_id' => $idPembeli,
                'file_path' => $filePath,
                'uploaded_at' => Carbon::now()->toDateTimeString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran berhasil diupload dan sedang diverifikasi',
                    'data' => [
                        'file_path' => $filePath,
                        'upload_time' => $transaksi->tanggalUploadBukti->format('Y-m-d H:i:s'),
                        'new_status' => $transaksi->status
                    ]
                ]);
            }

            return redirect()->route('pembeli.payment.show', $idTransaksi)
                ->with('success', 'Bukti pembayaran berhasil diupload dan sedang diverifikasi');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if transaction failed
            if (isset($filePath) && file_exists(public_path($filePath))) {
                unlink(public_path($filePath));
            }

            \Log::error('Error uploading payment proof', [
                'transaction_id' => $idTransaksi,
                'customer_id' => $idPembeli,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengupload bukti pembayaran'], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengupload bukti pembayaran');
        }
    }

    /**
     * Menampilkan daftar transaksi yang perlu diverifikasi (untuk CS) - DIPERBAIKI
     */
    public function indexVerification()
    {
        // PERBAIKAN: Include relasi pegawaiVerifikasi dan load bukti pembayaran dari database
        $transaksiList = TransaksiPenjualan::where('status', 'menunggu_verifikasi')
            ->with(['pembeli', 'detailTransaksiPenjualan.produk', 'pegawaiVerifikasi'])
            ->whereNotNull('buktiPembayaran') // TAMBAHAN: Pastikan ada bukti pembayaran
            ->whereNotNull('tanggalUploadBukti') // TAMBAHAN: Pastikan sudah diupload
            ->orderBy('tanggalUploadBukti', 'asc') // PERBAIKAN: Urutkan berdasarkan waktu upload
            ->paginate(10);

        return view('pegawai.cs.verification.index', compact('transaksiList'));
    }

    /**
     * Menampilkan detail transaksi untuk verifikasi (untuk CS) - DIPERBAIKI
     */
    public function showVerification($idTransaksi)
    {
        // PERBAIKAN: Include relasi dan load bukti pembayaran dari database
        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('status', 'menunggu_verifikasi')
            ->with(['pembeli', 'detailTransaksiPenjualan.produk', 'pegawaiVerifikasi'])
            ->whereNotNull('buktiPembayaran') // TAMBAHAN: Pastikan ada bukti pembayaran
            ->first();

        if (!$transaksi) {
            return redirect()->route('cs.verification.index')
                ->with('error', 'Transaksi tidak ditemukan atau belum upload bukti pembayaran');
        }

        return view('pegawai.cs.verification.show', compact('transaksi'));
    }

    /**
     * Verifikasi bukti pembayaran (Fungsionalitas 69-70) - DIPERBAIKI
     * TAMBAHAN: Auto generate tanggal antar/ambil berdasarkan metode pengiriman
     */
    public function verifyPayment(Request $request, $idTransaksi)
    {
        $validator = Validator::make($request->all(), [
            'status_verifikasi' => 'required|in:valid,tidak_valid',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Authentication check
        $user = session('user');
        $role = session('role');
        
        // Log untuk debugging
        \Log::info('Verifikasi pembayaran - Auth check', [
            'has_user_session' => !is_null($user),
            'user_data' => $user,
            'role' => $role,
            'request_method' => $request->method(),
            'csrf_token' => $request->header('X-CSRF-TOKEN') ?? 'not_set',
            'session_id' => session()->getId()
        ]);

        // Check authentication - CS bisa berupa 'pegawai' atau 'cs'
        if (!$user || !in_array($role, ['pegawai', 'cs'])) {
            \Log::warning('Verifikasi pembayaran - Unauthorized access attempt', [
                'user' => $user,
                'role' => $role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect()->route('loginPage')->with('error', 'Session tidak valid, silakan login ulang sebagai CS');
        }

        // Pastikan user memiliki ID pegawai (untuk CS yang login sebagai pegawai)
        $idPegawai = null;
        if (isset($user['idPegawai'])) {
            $idPegawai = $user['idPegawai'];
        } else {
            \Log::error('Verifikasi pembayaran - No pegawai ID found in session', [
                'user_session' => $user,
                'role' => $role
            ]);
            return redirect()->route('loginPage')->with('error', 'Data pegawai tidak ditemukan dalam session');
        }

        // Cari transaksi
        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('status', 'menunggu_verifikasi')
            ->whereNotNull('buktiPembayaran')
            ->first();

        if (!$transaksi) {
            \Log::warning('Verifikasi pembayaran - Transaction not found or invalid', [
                'transaksi_id' => $idTransaksi,
                'verified_by' => $idPegawai
            ]);
            return redirect()->route('cs.verification.index')
                ->with('error', 'Transaksi tidak ditemukan atau tidak valid untuk verifikasi');
        }

        DB::beginTransaction();
        try {
            if ($request->status_verifikasi === 'valid') {
                // FUNGSIONALITAS 70: Pembayaran valid - Update ke status disiapkan
                $tanggalLunas = Carbon::now();
                $tanggalLaku = Carbon::now();
                
                // TAMBAHAN BARU: Generate tanggal antar/ambil berdasarkan metode pengiriman
                $jadwalData = $this->generateScheduleDates($transaksi->metodePengiriman, $tanggalLunas);
                
                $updateData = [
                    'status' => 'disiapkan',
                    'tanggalLunas' => $tanggalLunas,
                    'tanggalLaku' => $tanggalLaku,
                    'catatanVerifikasi' => $request->catatan ?: 'Pembayaran diverifikasi dan diterima',
                    'idPegawaiVerifikasi' => $idPegawai,
                ];
                
                // Tambahkan data jadwal berdasarkan metode pengiriman
                if ($transaksi->metodePengiriman === 'kurir') {
                    $updateData['tanggalKirim'] = $jadwalData['tanggal_kirim'];
                    \Log::info('Jadwal pengiriman kurir ditetapkan', [
                        'transaction_id' => $idTransaksi,
                        'tanggal_kirim' => $jadwalData['tanggal_kirim']->format('Y-m-d H:i:s'),
                        'alasan' => $jadwalData['alasan']
                    ]);
                } else {
                    // ambil_sendiri
                    $updateData['tanggalAmbil'] = $jadwalData['tanggal_ambil'];
                    $updateData['tanggalBatasAmbil'] = $jadwalData['tanggal_batas_ambil'];
                    \Log::info('Jadwal pengambilan mandiri ditetapkan', [
                        'transaction_id' => $idTransaksi,
                        'tanggal_ambil' => $jadwalData['tanggal_ambil']->format('Y-m-d H:i:s'),
                        'tanggal_batas_ambil' => $jadwalData['tanggal_batas_ambil']->format('Y-m-d H:i:s'),
                        'alasan' => $jadwalData['alasan']
                    ]);
                }
                
                $transaksi->update($updateData);

                // Hitung dan berikan poin yang didapat (Fungsionalitas 62)
                $this->calculateAndAwardPoints($transaksi);

                $message = 'Pembayaran telah diverifikasi dan transaksi sedang disiapkan. ' . $jadwalData['alasan'];

                \Log::info('Payment verified and approved', [
                    'transaction_id' => $idTransaksi,
                    'verified_by' => $idPegawai,
                    'customer_id' => $transaksi->idPembeli,
                    'points_awarded' => $transaksi->poinDidapat,
                    'metode_pengiriman' => $transaksi->metodePengiriman,
                    'jadwal_info' => $jadwalData,
                    'note' => $request->catatan
                ]);
            } else {
                // FUNGSIONALITAS 69: Pembayaran tidak valid - Tolak dan cancel transaksi
                $transaksi->update([
                    'status' => 'batal',
                    'catatanVerifikasi' => $request->catatan ?: 'Bukti pembayaran tidak valid atau tidak sesuai',
                    'idPegawaiVerifikasi' => $idPegawai,
                ]);

                // Kembalikan poin yang sudah digunakan
                if ($transaksi->poinDigunakan > 0) {
                    $pembeli = $transaksi->pembeli;
                    $pembeli->update(['poin' => $pembeli->poin + $transaksi->poinDigunakan]);
                }

                // Kembalikan status produk ke tersedia
                $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)->get();
                foreach ($detailTransaksi as $detail) {
                    $produk = Produk::find($detail->idProduk);
                    if ($produk) {
                        $produk->update(['status' => 'Tersedia']);
                    }
                }

                $message = 'Pembayaran ditolak dan transaksi dibatalkan';

                \Log::info('Payment rejected and transaction cancelled', [
                    'transaction_id' => $idTransaksi,
                    'verified_by' => $idPegawai,
                    'customer_id' => $transaksi->idPembeli,
                    'points_refunded' => $transaksi->poinDigunakan,
                    'reason' => $request->catatan
                ]);
            }

            DB::commit();

            return redirect()->route('cs.verification.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error verifying payment', [
                'transaction_id' => $idTransaksi,
                'verified_by' => $idPegawai,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memverifikasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * METODE BARU: Generate tanggal antar/ambil berdasarkan ketentuan bisnis
     * 
     * Ketentuan:
     * - Jam operasional: 08:00 - 20:00
     * - Verifikasi setelah jam 16:00 = jadwal esok hari
     * - Verifikasi sebelum jam 16:00 = jadwal hari ini atau esok hari
     * - Batas ambil = tanggal ambil + 2 hari
     */
    private function generateScheduleDates($metodePengiriman, $tanggalVerifikasi)
    {
        $now = Carbon::parse($tanggalVerifikasi);
        $jamVerifikasi = $now->format('H:i');
        
        // Tentukan tanggal dasar untuk penjadwalan
        if ($jamVerifikasi >= '16:00') {
            // Verifikasi setelah jam 16:00 = jadwal esok hari
            $tanggalDasar = $now->copy()->addDay();
            $alasan = "Dijadwalkan esok hari karena verifikasi setelah jam 16:00";
        } else {
            // Verifikasi sebelum jam 16:00 = bisa hari ini atau esok hari
            // Untuk konsistensi, kita jadwalkan esok hari juga kecuali hari ini masih pagi
            if ($jamVerifikasi <= '10:00') {
                $tanggalDasar = $now->copy();
                $alasan = "Dijadwalkan hari ini karena verifikasi di pagi hari";
            } else {
                $tanggalDasar = $now->copy()->addDay();
                $alasan = "Dijadwalkan esok hari untuk persiapan";
            }
        }
        
        // Set jam operasional (09:00 untuk pengiriman, 08:00 untuk pengambilan)
        if ($metodePengiriman === 'kurir') {
            $tanggalKirim = $tanggalDasar->copy()->setTime(9, 0, 0); // Jam 09:00 untuk pengiriman
            
            return [
                'tanggal_kirim' => $tanggalKirim,
                'alasan' => $alasan . ". Pengiriman dijadwalkan " . $tanggalKirim->format('d M Y H:i')
            ];
        } else {
            // ambil_sendiri
            $tanggalAmbil = $tanggalDasar->copy()->setTime(8, 0, 0); // Jam 08:00 untuk pengambilan
            $tanggalBatasAmbil = $tanggalAmbil->copy()->addDays(2)->setTime(20, 0, 0); // +2 hari jam 20:00
            
            return [
                'tanggal_ambil' => $tanggalAmbil,
                'tanggal_batas_ambil' => $tanggalBatasAmbil,
                'alasan' => $alasan . ". Pengambilan tersedia mulai " . $tanggalAmbil->format('d M Y H:i') . 
                        " sampai " . $tanggalBatasAmbil->format('d M Y H:i')
            ];
        }
    }

    /**
     * METODE BARU: Cek dan update transaksi yang melebihi batas ambil (untuk scheduler)
     * Fungsionalitas 77: Status menjadi "hangus" jika tidak diambil dalam 2 hari
     */
    public function checkExpiredPickupTransactions()
    {
        $expiredPickupTransactions = TransaksiPenjualan::where('status', 'disiapkan')
            ->where('metodePengiriman', 'ambil_sendiri')
            ->whereNotNull('tanggalBatasAmbil')
            ->where('tanggalBatasAmbil', '<', Carbon::now())
            ->get();

        foreach ($expiredPickupTransactions as $transaksi) {
            $this->markTransactionAsExpired($transaksi);
        }

        return response()->json([
            'message' => 'Expired pickup transactions checked',
            'expired_count' => $expiredPickupTransactions->count()
        ]);
    }

    /**
     * METODE BARU: Tandai transaksi sebagai hangus dan ubah barang menjadi donasi
     */
    private function markTransactionAsExpired($transaksi)
    {
        DB::beginTransaction();
        try {
            \Log::info('Marking transaction as expired due to pickup timeout', [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'customer_id' => $transaksi->idPembeli,
                'tanggal_batas_ambil' => $transaksi->tanggalBatasAmbil,
                'current_time' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            // Update status transaksi menjadi hangus
            $transaksi->update(['status' => 'hangus']);

            // Update status barang menjadi "barang untuk donasi" (sesuai Fungsionalitas 77)
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->get();
            foreach ($detailTransaksi as $detail) {
                $produk = Produk::find($detail->idProduk);
                if ($produk) {
                    $produk->update(['status' => 'Untuk Donasi']); // atau status yang sesuai dengan ketentuan
                }
            }

            // CATATAN: Poin tidak dikembalikan karena ini bukan pembatalan, tapi keterlambatan pengambilan
            // Pembayaran dianggap hangus sesuai ketentuan

            DB::commit();

            \Log::info('Transaction marked as expired successfully', [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'new_status' => 'hangus',
                'products_marked_for_donation' => $detailTransaksi->count()
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error marking transaction as expired: ' . $e->getMessage(), [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Update status transaksi menjadi "Disiapkan" (Fungsionalitas 70)
     */
    private function updateStatusToDisiapkan($transaksi)
    {
        // Update status transaksi
        $transaksi->update([
            'status' => 'disiapkan',
            'tanggalLunas' => Carbon::now(),
            'tanggalLaku' => Carbon::now(),
        ]);

        // Hitung dan berikan poin yang didapat (Fungsionalitas 62)
        $this->calculateAndAwardPoints($transaksi);

        // TODO: Kirim notifikasi ke penitip
        // Implementasi notifikasi bisa ditambahkan di sini

        return $transaksi;
    }

    /**
     * Hitung dan berikan poin ke pembeli (Fungsionalitas 62)
     * UPDATED: Menggunakan data poin dari database transaksi
     */
    private function calculateAndAwardPoints($transaksi)
    {
        // PERBAIKAN: Ambil poin dari database transaksi, bukan dari session
        $poinDidapat = $transaksi->poinDidapat;

        if ($poinDidapat > 0) {
            // Tambahkan poin ke pembeli
            $pembeli = $transaksi->pembeli;
            $pembeli->update(['poin' => $pembeli->poin + $poinDidapat]);

            \Log::info('Points awarded to customer', [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'customer_id' => $pembeli->idPembeli,
                'points_awarded' => $poinDidapat,
                'new_total_points' => $pembeli->poin
            ]);
        }
    }

    /**
     * Cancel transaksi yang expired atau tidak valid (Fungsionalitas 67)
     */
    private function cancelExpiredTransaction($transaksi)
    {
        return $this->cancelTransaction($transaksi, 'Transaksi kedaluwarsa - pembayaran tidak dilakukan dalam 1 menit');
    }

    /**
     * Cancel transaksi dan kembalikan semua perubahan - UPDATED
     * PERBAIKAN: Menambahkan option untuk hard delete transaksi
     */
    private function cancelTransaction($transaksi, $reason = 'Transaksi dibatalkan', $hardDelete = false)
    {
        DB::beginTransaction();
        try {
            \Log::info('Cancelling transaction', [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'reason' => $reason,
                'hard_delete' => $hardDelete
            ]);

            // 1. Kembalikan status produk ke tersedia
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->get();
            $restoredProducts = [];

            foreach ($detailTransaksi as $detail) {
                $produk = Produk::find($detail->idProduk);
                if ($produk) {
                    $produk->update(['status' => 'Tersedia']);
                    $restoredProducts[] = $detail->idProduk;
                }
            }

            // 2. Kembalikan poin berdasarkan data dari database transaksi
            if ($transaksi->poinDigunakan > 0) {
                $pembeli = $transaksi->pembeli;
                $pembeli->update(['poin' => $pembeli->poin + $transaksi->poinDigunakan]);

                \Log::info('Points refunded to customer due to cancellation', [
                    'transaction_id' => $transaksi->idTransaksiPenjualan,
                    'customer_id' => $pembeli->idPembeli,
                    'points_refunded' => $transaksi->poinDigunakan,
                    'new_total_points' => $pembeli->poin,
                    'reason' => $reason
                ]);
            }

            if ($hardDelete) {
                // HARD DELETE: Hapus transaksi dari database
                DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->delete();
                $transaksi->delete();

                \Log::info('Transaction hard deleted', [
                    'transaction_id' => $transaksi->idTransaksiPenjualan,
                    'reason' => $reason
                ]);
            } else {
                // SOFT DELETE: Update status menjadi batal
                $transaksi->update(['status' => 'batal']);

                // Simpan catatan pembatalan di session
                session(['catatan_batal_' . $transaksi->idTransaksiPenjualan => $reason]);
            }

            // Hapus data checkout dari session
            session()->forget([
                'checkout_data',
                'bukti_pembayaran_' . $transaksi->idTransaksiPenjualan
            ]);

            DB::commit();

            \Log::info('Transaction cancellation completed', [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'restored_products' => count($restoredProducts),
                'hard_deleted' => $hardDelete
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error canceling transaction: ' . $e->getMessage(), [
                'transaction_id' => $transaksi->idTransaksiPenjualan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    /**
     * Check dan cancel transaksi yang expired (untuk dijalankan via cron/scheduler)
     */
    public function checkExpiredTransactions()
    {
        $expiredTransactions = TransaksiPenjualan::where('status', 'menunggu_pembayaran')
            ->where('tanggalBatasLunas', '<', Carbon::now())
            ->get();

        foreach ($expiredTransactions as $transaksi) {
            $this->cancelExpiredTransaction($transaksi);
        }

        return response()->json([
            'message' => 'Expired transactions checked',
            'cancelled' => $expiredTransactions->count()
        ]);
    }

    /**
     * Get transaction status - untuk AJAX polling
     */
    public function getTransactionStatus($idTransaksi)
    {
        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];

        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('idPembeli', $idPembeli)
            ->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }

        $timeRemaining = 0;
        if ($transaksi->status === 'menunggu_pembayaran') {
            $timeRemaining = max(0, Carbon::parse($transaksi->tanggalBatasLunas)->diffInSeconds(Carbon::now()));
        }

        return response()->json([
            'status' => $transaksi->status,
            'timeRemaining' => $timeRemaining,
            'tanggalBatasLunas' => $transaksi->tanggalBatasLunas,
            'isExpired' => $transaksi->status === 'menunggu_pembayaran' && Carbon::now()->gt($transaksi->tanggalBatasLunas)
        ]);
    }

    /**
     * PERBAIKAN: Helper method untuk mengambil bukti pembayaran dari database
     */
    private function getBuktiPembayaran($idTransaksi)
    {
        $transaksi = TransaksiPenjualan::find($idTransaksi);
        return $transaksi ? $transaksi->buktiPembayaran : null;
    }

    /**
     * Helper method untuk cek apakah bukti pembayaran sudah diupload
     */
    private function hasUploadedPaymentProof($idTransaksi)
    {
        $transaksi = TransaksiPenjualan::find($idTransaksi);
        return $transaksi && $transaksi->hasBuktiPembayaran();
    }

    /**
     * Helper method untuk mengambil catatan pembatalan dari session  
     */
    private function getCatatanBatal($idTransaksi)
    {
        return session('catatan_batal_' . $idTransaksi);
    }

    public function printNota($id)
    {
        $penjualan = DB::table('transaksi_penjualan as tp')
            ->leftJoin('pembeli as p', 'tp.idPembeli', '=', 'p.idPembeli')
            ->leftJoin('pegawai as pg', 'tp.idPegawai', '=', 'pg.idPegawai')
            ->select(
                'tp.*',
                'p.nama as namaPembeli',
                'p.email as emailPembeli',
                'pg.nama as namaPegawai'
            )
            ->where('tp.idTransaksiPenjualan', $id)
            ->first();

        $qc = session('user');

        $pembeli = Pembeli::find($penjualan->idPembeli);

        if (!$penjualan) {
            return redirect()->route('gudang.pengiriman.index')->with('error', 'Transaksi Penjualan tidak ditemukan!');
        }

        $detail = DB::table('detail_transaksi_penjualan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->join('kategori_produk as kp', 'pr.idKategori', '=', 'kp.idKategori')
            ->select(
                'pr.idProduk',
                'pr.deskripsi as namaProduk',
                'pr.harga',
                'pr.hargaJual',
                'pr.berat',
                'pr.gambar',
                'pr.tanggalGaransi',
                'pr.status',
                'pr.ratingProduk',
                'kp.nama as kategori',
            )
            ->where('dtp.idTransaksiPenjualan', $id)
            ->get();

        $now = Carbon::now();
        $tahun = $now->format('y');
        $bulan = $now->format('m');
        $noNota = $tahun . '.' . $bulan . '.' . $id;

        $penjualan->total = $detail->sum(function ($item) {
            return $item->hargaJual;
        });

        $penjualan->ongkir = $penjualan->total > 1500000 ? 0 : 100000;

        $penjualan->tanggalKirim = $penjualan->tanggalKirim ? Carbon::parse($penjualan->tanggalKirim)->format('Y-m-d') : '-';
        $penjualan->tanggalAmbil = $penjualan->tanggalAmbil ? Carbon::parse($penjualan->tanggalAmbil)->format('Y-m-d') : '-';;

        $penjualan->alamatPengiriman = json_decode($penjualan->alamatPengiriman, true)['alamatLengkap'] ?? NULL;


        $data = [
            'pembeli' => $pembeli,
            'qc' => $qc,
            'transaksi' => $penjualan,
            'detail' => $detail,
            'tanggal_cetak' => now(),
            'nomor_nota' => $noNota,
        ];

        $pdf = Pdf::loadView('pegawai.gudang.penjualan.print-nota', $data);
        $pdf->setPaper('A5', 'portrait');
        $filename = 'Nota_penjualan_' . $noNota . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * API endpoint untuk cancel transaksi yang expired dari frontend
     * TAMBAHAN BARU: Method untuk auto-cancel ketika countdown habis
     */
    public function cancelExpiredTransactionAPI($idTransaksi)
    {
        try {
            // Check authentication
            if (!session('user') || session('role') !== 'pembeli') {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $idPembeli = session('user')['idPembeli'];

            // Cari transaksi yang sesuai
            $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
                ->where('idPembeli', $idPembeli)
                ->where('status', 'menunggu_pembayaran')
                ->first();

            if (!transaksi) {
                return response()->json(['error' => 'Transaksi tidak ditemukan atau sudah tidak valid'], 404);
            }

            // Cek apakah memang sudah expired
            $now = \Carbon\Carbon::now();
            $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);

            if ($now->lt($batasLunas)) {
                // Masih dalam batas waktu, tidak perlu cancel
                return response()->json(['error' => 'Transaksi belum expired'], 400);
            }

            \Log::info('Auto-cancelling expired transaction from frontend', [
                'transaction_id' => $idTransaksi,
                'customer_id' => $idPembeli,
                'expired_at' => $batasLunas->format('Y-m-d H:i:s'),
                'cancelled_at' => $now->format('Y-m-d H:i:s')
            ]);

            DB::beginTransaction();
            try {
                // 1. Kembalikan status produk ke 'Tersedia'
                $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)->get();
                $restoredProducts = [];

                foreach ($detailTransaksi as $detail) {
                    $produk = Produk::find($detail->idProduk);
                    if ($produk && $produk->status !== 'Tersedia') {
                        $produk->update(['status' => 'Tersedia']);
                        $restoredProducts[] = $detail->idProduk;

                        \Log::info('Product status restored to Tersedia', [
                            'product_id' => $detail->idProduk,
                            'transaction_id' => $idTransaksi
                        ]);
                    }
                }

                // 2. Kembalikan poin yang sudah digunakan ke pembeli
                if ($transaksi->poinDigunakan > 0) {
                    $pembeli = $transaksi->pembeli;
                    $pembeli->update(['poin' => $pembeli->poin + $transaksi->poinDigunakan]);

                    \Log::info('Points refunded due to auto-cancellation', [
                        'transaction_id' => $idTransaksi,
                        'customer_id' => $pembeli->idPembeli,
                        'points_refunded' => $transaksi->poinDigunakan,
                        'new_total_points' => $pembeli->poin
                    ]);
                }

                // 3. OPTION A: Update status menjadi 'batal' (SOFT DELETE)
                $transaksi->update([
                    'status' => 'batal'
                ]);

                // 4. OPTION B: HARD DELETE - Hapus transaksi dari database (uncomment jika ingin hard delete)
                /*
                // Hapus detail transaksi terlebih dahulu
                DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)->delete();
                
                // Hapus transaksi utama
                $transaksi->delete();
                
                \Log::info('Transaction hard deleted from database', [
                    'transaction_id' => $idTransaksi,
                    'customer_id' => $idPembeli
                ]);
                */

                // 5. Bersihkan session data terkait
                session()->forget([
                    'checkout_data',
                    'cart',
                    'direct_buy',
                    'checkout_shipping_method',
                    'checkout_shipping_address',
                    'checkout_points_used',
                    'bukti_pembayaran_' . $idTransaksi
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil dibatalkan karena waktu pembayaran habis',
                    'cancelled_at' => $now->format('Y-m-d H:i:s'),
                    'restored_products' => count($restoredProducts),
                    'points_refunded' => $transaksi->poinDigunakan,
                    'redirect_url' => route('pembeli.profile')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error in auto-cancelling transaction', [
                    'transaction_id' => $idTransaksi,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json(['error' => 'Gagal membatalkan transaksi'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in cancelExpiredTransactionAPI', [
                'transaction_id' => $idTransaksi,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Auto cancel expired payment transaction (untuk AJAX call)
     * BARU: Method khusus untuk auto cancel yang dipanggil JavaScript
     */
    public function cancelExpiredPayment(Request $request, $idTransaksi)
    {
        // Check authentication
        if (!session('user') || session('role') !== 'pembeli') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idPembeli = session('user')['idPembeli'];

        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('idPembeli', $idPembeli)
            ->where('status', 'menunggu_pembayaran')
            ->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi tidak ditemukan atau sudah tidak valid'], 404);
        }

        // Check jika memang sudah expired
        $now = \Carbon\Carbon::now();
        $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);

        if ($now->lte($batasLunas)) {
            return response()->json(['error' => 'Transaksi belum expired'], 400);
        }

        DB::beginTransaction();
        try {
            \Log::info('Auto cancelling expired transaction via AJAX', [
                'transaction_id' => $idTransaksi,
                'customer_id' => $idPembeli,
                'expired_at' => $batasLunas->format('Y-m-d H:i:s'),
                'cancelled_at' => $now->format('Y-m-d H:i:s')
            ]);

            // 1. Kembalikan status produk ke tersedia
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)->get();
            $restoredProductIds = [];

            foreach ($detailTransaksi as $detail) {
                $produk = Produk::find($detail->idProduk);
                if ($produk && $produk->status !== 'Tersedia') {
                    $produk->update(['status' => 'Tersedia']);
                    $restoredProductIds[] = $detail->idProduk;
                }
            }

            // 2. Kembalikan poin berdasarkan data dari database transaksi
            $pointsRefunded = 0;
            if ($transaksi->poinDigunakan > 0) {
                $pembeli = $transaksi->pembeli;
                $pembeli->update(['poin' => $pembeli->poin + $transaksi->poinDigunakan]);
                $pointsRefunded = $transaksi->poinDigunakan;

                \Log::info('Points refunded due to auto cancel', [
                    'transaction_id' => $idTransaksi,
                    'customer_id' => $idPembeli,
                    'points_refunded' => $pointsRefunded,
                    'new_total_points' => $pembeli->poin
                ]);
            }

            // 3. Update status transaksi
            $transaksi->update(['status' => 'batal']);

            // 4. Simpan catatan pembatalan
            session(['catatan_batal_' . $idTransaksi => 'Transaksi dibatalkan otomatis karena waktu pembayaran habis']);

            // 5. Hapus data checkout dari session
            session()->forget('checkout_data');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan otomatis',
                'data' => [
                    'transaction_id' => $idTransaksi,
                    'restored_products' => count($restoredProductIds),
                    'product_ids' => $restoredProductIds,
                    'points_refunded' => $pointsRefunded,
                    'cancelled_at' => $now->format('Y-m-d H:i:s')
                ],
                'redirect_url' => route('pembeli.profile')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error in auto cancel expired payment', [
                'transaction_id' => $idTransaksi,
                'customer_id' => $idPembeli,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}

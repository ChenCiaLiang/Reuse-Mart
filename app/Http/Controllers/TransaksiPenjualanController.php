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
    public function proceedCheckout(Request $request)
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

            // Validasi dan hitung penggunaan poin (Fungsionalitas 65)
            $poinDigunakan = min($request->poin_digunakan ?? 0, $pembeli->poin);
            $diskonPoin = $poinDigunakan * 10;
            $totalSebelumDiskon = $subtotal + $ongkir;
            $diskonPoin = min($diskonPoin, $totalSebelumDiskon);
            $poinDigunakan = floor($diskonPoin / 10);
            $totalAkhir = $totalSebelumDiskon - $diskonPoin;

            // Hitung poin yang akan didapat (Fungsionalitas 62) - HITUNG DI AWAL
            $poinDidapat = floor($totalAkhir / 10000);
            if ($totalAkhir > 500000) {
                $bonusPoin = floor($poinDidapat * 0.2);
                $poinDidapat += $bonusPoin;
            }

            // Generate nomor transaksi (Fungsionalitas 64)
            $nomorTransaksi = $this->generateTransactionNumber();
            
            // Buat transaksi penjualan (Fungsionalitas 63) - UPDATED dengan poin
            $tanggalPesan = Carbon::now();
            $tanggalBatasLunas = $tanggalPesan->copy()->addMinutes(1); // 1 menit timeout

            $transaksi = TransaksiPenjualan::create([
                'status' => 'menunggu_pembayaran',
                'tanggalLaku' => null,
                'tanggalPesan' => $tanggalPesan,
                'tanggalBatasLunas' => $tanggalBatasLunas,
                'tanggalLunas' => null,
                'tanggalBatasAmbil' => null,
                'tanggalKirim' => null,
                'tanggalAmbil' => null,
                'alamat' => $request->idAlamat,
                'idPembeli' => $idPembeli,
                'idPegawai' => null,
                'alamatPengiriman' => $alamatPengiriman,
                'metodePengiriman' => $metodePengiriman,
                'poinDidapat' => $poinDidapat, // BARU: Simpan poin yang akan didapat
                'poinDigunakan' => $poinDigunakan, // BARU: Simpan poin yang digunakan
            ]);

            // Simpan detail transaksi dan update status produk (Fungsionalitas 66)
            foreach ($products as $product) {
                DetailTransaksiPenjualan::create([
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'idProduk' => $product->idProduk,
                ]);

                // Update status produk menjadi sold out
                $product->update(['status' => 'Terjual']);
            }

            // Kurangi poin yang digunakan (Fungsionalitas 65)
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
                    'poin_didapat' => $poinDidapat, // BARU: Simpan ke session juga
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
     * Menampilkan halaman pembayaran
     * FIXED: Perbaiki perhitungan timer dan auto-cancel expired transactions
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
            ->with(['detailTransaksiPenjualan.produk'])
            ->first();

        if (!$transaksi) {
            return redirect()->route('pembeli.profile')->with('error', 'Transaksi tidak ditemukan');
        }
        // FIXED: Perbaiki pengecekan expired transaction
        if ($transaksi->status === 'menunggu_pembayaran') {
            $now = \Carbon\Carbon::now();
            $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);
            // Log untuk debugging
            \Log::info('Payment Timer Debug', [
                'transaksi_id' => $idTransaksi,
                'now' => $now->format('Y-m-d H:i:s'),
                'batas_lunas' => $batasLunas->format('Y-m-d H:i:s'),
                'is_past' => $now->gt($batasLunas),
                'diff_seconds' => $batasLunas->diffInSeconds($now, false) // false = don't use absolute value
            ]);
            // Jika sudah expired, cancel transaksi
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
     * Upload bukti pembayaran (Fungsionalitas 68)
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

        // Check if transaction is still valid
        if ($transaksi->status !== 'menunggu_pembayaran') {
            return response()->json(['error' => 'Status transaksi tidak valid untuk upload pembayaran'], 400);
        }

        if (Carbon::now()->gt($transaksi->tanggalBatasLunas)) {
            // Transaction expired
            $this->cancelExpiredTransaction($transaksi);
            return response()->json(['error' => 'Transaksi telah kedaluwarsa'], 400);
        }

        DB::beginTransaction();
        try {
            // Upload file
            $buktiFile = $request->file('bukti_pembayaran');
            $filename = 'bukti_' . $idTransaksi . '_' . time() . '.' . $buktiFile->getClientOriginalExtension();
            $buktiFile->move(public_path('uploads/bukti_pembayaran'), $filename);

            // Update transaksi - gunakan kolom yang ada
            $transaksi->update([
                'status' => 'menunggu_verifikasi'
            ]);

            // Simpan path bukti pembayaran di session untuk sementara
            // Karena tidak boleh tambah kolom, gunakan session storage
            session(['bukti_pembayaran_' . $idTransaksi => 'uploads/bukti_pembayaran/' . $filename]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran berhasil diupload dan sedang diverifikasi'
                ]);
            }

            return redirect()->route('pembeli.payment.show', $idTransaksi)
                ->with('success', 'Bukti pembayaran berhasil diupload dan sedang diverifikasi');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error uploading payment proof: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengupload bukti pembayaran'], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengupload bukti pembayaran');
        }
    }

    /**
     * Menampilkan daftar transaksi yang perlu diverifikasi (untuk CS)
     */
    public function indexVerification()
    {
        $transaksiList = TransaksiPenjualan::where('status', 'menunggu_verifikasi')
            ->with(['pembeli', 'detailTransaksiPenjualan.produk'])
            ->orderBy('tanggalPesan', 'desc')
            ->paginate(10);

        return view('pegawai.cs.verification.index', compact('transaksiList'));
    }

    /**
     * Menampilkan detail transaksi untuk verifikasi (untuk CS)
     */
    public function showVerification($idTransaksi)
    {
        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('status', 'menunggu_verifikasi')
            ->with(['pembeli', 'detailTransaksiPenjualan.produk'])
            ->first();

        if (!$transaksi) {
            return redirect()->route('cs.verification.index')->with('error', 'Transaksi tidak ditemukan');
        }

        return view('pegawai.cs.verification.show', compact('transaksi'));
    }

    /**
     * Verifikasi bukti pembayaran (Fungsionalitas 69)
     */
    public function verifyPayment(Request $request, $idTransaksi)
    {
        $validator = Validator::make($request->all(), [
            'status_verifikasi' => 'required|in:valid,tidak_valid',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksi)
            ->where('status', 'menunggu_verifikasi')
            ->first();

        if (!$transaksi) {
            return redirect()->route('cs.verification.index')->with('error', 'Transaksi tidak ditemukan');
        }

        DB::beginTransaction();
        try {
            if ($request->status_verifikasi === 'valid') {
                // Pembayaran valid - Update ke status disiapkan (Fungsionalitas 70)
                $this->updateStatusToDisiapkan($transaksi);
                $message = 'Pembayaran telah diverifikasi dan transaksi sedang disiapkan';
            } else {
                // Pembayaran tidak valid - Cancel transaksi (Fungsionalitas 67)
                $this->cancelTransaction($transaksi, 'Bukti pembayaran tidak valid: ' . $request->catatan);
                $message = 'Pembayaran ditolak dan transaksi dibatalkan';
            }

            DB::commit();

            return redirect()->route('cs.verification.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error verifying payment: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memverifikasi pembayaran');
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
     * Cancel transaksi dan kembalikan semua perubahan
     * UPDATED: Menggunakan data poin dari database transaksi
     */
    private function cancelTransaction($transaksi, $reason = 'Transaksi dibatalkan')
    {
        DB::beginTransaction();
        try {
            // Kembalikan status produk ke tersedia
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->get();
            foreach ($detailTransaksi as $detail) {
                $produk = Produk::find($detail->idProduk);
                if ($produk) {
                    $produk->update(['status' => 'Tersedia']);
                }
            }

            // PERBAIKAN: Kembalikan poin berdasarkan data dari database transaksi, bukan session
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

            // Update status transaksi
            $transaksi->update([
                'status' => 'batal'
            ]);

            // Simpan catatan pembatalan di session
            session(['catatan_batal_' . $transaksi->idTransaksiPenjualan => $reason]);

            // Hapus data checkout dari session
            session()->forget('checkout_data');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error canceling transaction: ' . $e->getMessage());
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
     * Helper method untuk mengambil bukti pembayaran dari session
     */
    private function getBuktiPembayaran($idTransaksi)
    {
        return session('bukti_pembayaran_' . $idTransaksi);
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

        if (!$penjualan) {
            return redirect()->route('gudang.pengiriman.index')->with('error', 'Transaksi Penjualan tidak ditemukan!');
        }

        $detail = DB::table('detail_transaksi_penjualan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->join('kategori_produk as kp', 'pr.idKategori', '=', 'kp.idKategori')
            // ->join('detail_transaksi_penitipan as dtpt', 'dtpt.idDetailTransaksiPenitipan', '=', 'pr.idDetailTransaksiPenitipan')
            // ->join('transaksi_penitipan as tp', 'tp.idTransaksiPenitipan', '=', 'dtpt.idTransaksiPenitipan')
            // ->join('pegawai as p', 'p.idPegawai', '=', 'dtpt.idPegawai')
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
                // 'p.idPegawai as idPegawai',
                // 'p.nama as namaPegawai',
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

        $data = [
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
}

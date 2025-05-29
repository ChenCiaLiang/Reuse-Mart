<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use App\Models\Pembeli;
use App\Models\Alamat;
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
                'cartCount' => count($cart)
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Menghapus produk dari keranjang (Fungsionalitas 57)
     */
    public function removeFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idProduk' => 'required|exists:produk,idProduk',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
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
                    'cartCount' => count($cart)
                ]);
            }
            return back()->with('success', 'Produk berhasil dihapus dari keranjang');
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Produk tidak ditemukan dalam keranjang'], 404);
        }
        return back()->with('error', 'Produk tidak ditemukan dalam keranjang');
    }

    /**
     * Menampilkan halaman checkout
     */
    public function showCheckout()
    {
        $idPembeli = session('user')['idPembeli'];
        $pembeli = Pembeli::findOrFail($idPembeli);
        
        // Ambil alamat pembeli
        $alamatList = Alamat::where('idPembeli', $idPembeli)->get();
        $alamatDefault = $alamatList->where('statusDefault', true)->first();
        
        // Ambil data cart
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('pembeli.cart.show')->with('error', 'Keranjang belanja kosong');
        }
        
        $cartItems = [];
        $subtotal = 0;
        
        $productIds = array_keys($cart);
        $products = Produk::whereIn('idProduk', $productIds)
            ->where('status', 'Tersedia')
            ->get();
        
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
            'subtotal'
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
        
        $subtotal = 0;
        foreach ($products as $product) {
            $subtotal += $product->hargaJual;
        }
        
        // Hitung ongkir (Fungsionalitas 60)
        $ongkir = 0;
        if ($request->metode_pengiriman === 'kurir') {
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
        $products = Produk::whereIn('idProduk', $productIds)->get();
        
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
            'cart_items' => $products->map(function($product) {
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
        session()->forget('cart');
        session()->forget('checkout_shipping_method');
        session()->forget('checkout_shipping_address');
        session()->forget('checkout_points_used');
    }

    /**
     * Get cart count for navbar
     */
    public function getCartCount()
    {
        $cart = session('cart', []);
        return response()->json(['count' => count($cart)]);
    }
}
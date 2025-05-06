<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\KategoriProduk;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
     /**
     * Menampilkan daftar produk
     *
     *
     */
    public function index()
    {
        // Ambil produk yang tersedia
        $produk = Produk::where('status', '!=', 'Terjual')
                    ->where('status', '!=', 'Didonasikan')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        // Ambil gambar thumbnail untuk setiap produk (yang paling awal/pertama upload)
        foreach ($produk as $p) {
            // Ambil gambar pertama untuk produk ini
            $gambarPertama = DB::table('gambar_produk')
                            ->where('idProduk', $p->idProduk)
                            ->orderBy('created_at', 'asc') // Ambil yang paling awal upload
                            ->first();
            
            // Tambahkan path gambar ke objek produk
            $p->thumbnail = $gambarPertama ? $gambarPertama->gambar : 'default.jpg';
        }
        
        // Ambil kategori untuk filter
        $kategori = KategoriProduk::all();
        
        return view('produk.index', compact('produk', 'kategori'));
    }

    public function show($id)
    {
        // Ambil data produk berdasarkan ID
        $produk = Produk::findOrFail($id);
        
        // Ambil semua gambar produk dari tabel gambar_produk
        $gambarProduk = DB::table('gambar_produk')
                        ->where('idProduk', $id)
                        ->orderBy('created_at', 'asc')
                        ->get();
        
        // Ambil produk terkait (dari kategori yang sama)
        $produkTerkait = Produk::where('idKategori', $produk->idKategori)
                            ->where('idProduk', '!=', $id)
                            ->where('status', '!=', 'Terjual')
                            ->where('status', '!=', 'Didonasikan')
                            ->limit(4)
                            ->get();
        
        // Ambil gambar thumbnail untuk produk terkait
        foreach ($produkTerkait as $p) {
            $gambarPertama = DB::table('gambar_produk')
                            ->where('idProduk', $p->idProduk)
                            ->orderBy('created_at', 'asc')
                            ->first();
            
            $p->thumbnail = $gambarPertama ? $gambarPertama->gambar : 'default.jpg';
        }
        
        return view('produk.show', compact('produk', 'gambarProduk', 'produkTerkait'));
    }
}

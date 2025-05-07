<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\KategoriProduk;

class ProdukController extends Controller
{
     /**
     * Menampilkan daftar produk
     *
     *
     */
    public function index()
    {
        // Ambil produk yang tersedia (tidak terjual atau didonasikan)
        $produk = Produk::where('status', '!=', 'Terjual')
                    ->where('status', '!=', 'Didonasikan')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        // Ambil kategori untuk filter
        $kategori = KategoriProduk::all();
        
        return view('produk.index', compact('produk', 'kategori'));
    }

    public function show($id)
    {
        // Ambil data produk berdasarkan ID
        $produk = Produk::findOrFail($id);
        
        // Ambil gambar-gambar produk dari field gambar
        $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];
        
        // Ambil produk terkait (dari kategori yang sama)
        $produkTerkait = Produk::where('idKategori', $produk->idKategori)
                            ->where('idProduk', '!=', $id)
                            ->where('status', '!=', 'Terjual')
                            ->where('status', '!=', 'Didonasikan')
                            ->limit(4)
                            ->get();
        
        return view('produk.show', compact('produk', 'gambarArray', 'produkTerkait'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\DiskusiProduk;
use App\Models\KategoriProduk;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->input('search');
        $kategori = $request->input('kategori');

        // Query dasar
        $query = Produk::where('status', '!=', 'Terjual')
            ->where('status', '!=', 'Didonasikan');

        // Filter berdasarkan pencarian teks
        if ($search) {
            $query->where('deskripsi', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan kategori
        if ($kategori) {
            $query->where('idKategori', $kategori);
        }

        // Ambil produk dengan filter yang telah ditentukan
        $produk = $query->orderBy('created_at', 'desc')->get();

        // Ambil kategori untuk filter dropdown
        $kategoriList = KategoriProduk::all();

        return view('produk.index', compact('produk', 'kategoriList', 'search', 'kategori'));
    }
    public function indexPopup()
    {
        // Ambil produk yang tersedia (tidak terjual atau didonasikan)
        $produk = Produk::where('status', '!=', 'Terjual')
            ->where('status', '!=', 'Didonasikan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil kategori untuk filter
        $kategori = KategoriProduk::all();

        return view('produk.showPopup', compact('produk', 'kategori'));
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

        // Tambahkan kode ini untuk mengambil diskusi produk
        $diskusi = DiskusiProduk::where('idProduk', $id)
            ->with(['pembeli', 'pegawai'])
            ->orderBy('tanggalDiskusi', 'desc')
            ->get();

        return view('produk.show', compact('produk', 'gambarArray', 'produkTerkait', 'diskusi'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\DiskusiProduk;
use App\Models\KategoriProduk;
use App\Models\DetailTransaksiPenitipan;
use App\Models\Penitip;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $status = $request->input('status'); // Filter status baru

        // Query dasar - tampilkan semua produk untuk memberikan konteks visual
        $query = Produk::query();

        // Filter berdasarkan pencarian teks
        if ($search) {
            $query->where('deskripsi', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan kategori
        if ($kategori) {
            $query->where('idKategori', $kategori);
        }

        // Filter berdasarkan status
        if ($status) {
            $query->where('status', $status);
        }

        // Ambil produk dengan filter yang telah ditentukan
        $produk = $query->orderBy('created_at', 'desc')->get();

        // Ambil kategori untuk filter dropdown
        $kategoriList = KategoriProduk::all();

        // Data untuk statistik status
        $statusStats = [
            'tersedia' => Produk::where('status', 'Tersedia')->count(),
            'terjual' => Produk::where('status', 'Terjual')->count(),
            'didonasikan' => Produk::where('status', 'Didonasikan')->count(),
        ];

        return view('produk.index', compact('produk', 'kategoriList', 'search', 'kategori', 'status', 'statusStats'));
    }
    
    public function indexPopup()
    {
        // Untuk popup, tetap hanya tampilkan produk yang tersedia
        $produk = Produk::where('status', 'Tersedia')
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

        // Ambil data penitip dari produk melalui detail transaksi penitipan
        $penitip = null;
        $ratingPenitip = 0;
        
        // Cari penitip melalui detail transaksi penitipan
        $detailPenitipan = DetailTransaksiPenitipan::where('idProduk', $id)
            ->with(['transaksiPenitipan.penitip'])
            ->first();
            
        if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
            $penitip = $detailPenitipan->transaksiPenitipan->penitip;
            $ratingPenitip = $penitip ? $penitip->rating : 0;
        }

        // Ambil gambar-gambar produk dari field gambar
        $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];

        // Ambil produk terkait (dari kategori yang sama, hanya yang tersedia)
        $produkTerkait = Produk::where('idKategori', $produk->idKategori)
            ->where('idProduk', '!=', $id)
            ->where('status', 'Tersedia')
            ->limit(4)
            ->get();

        // Tambahkan kode ini untuk mengambil diskusi produk
        $diskusi = DiskusiProduk::where('idProduk', $id)
            ->with(['pembeli', 'pegawai'])
            ->orderBy('tanggalDiskusi', 'desc')
            ->get();

        return view('produk.show', compact('produk', 'gambarArray', 'produkTerkait', 'diskusi', 'penitip', 'ratingPenitip'));
    }
}
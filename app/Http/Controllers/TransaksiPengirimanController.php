<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\KategoriProduk;
use App\Models\Pegawai;
use App\Models\Pembeli;

class TransaksiPengirimanController extends Controller
{
    //Menampilkan daftar transaksi (dikirim/diambil)
    public function index(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->input('search');

        $pengiriman = TransaksiPenjualan::when($search, function ($query) use ($search) {
            return $query->where('idTransaksiPenjualan', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%')
                ->orWhere('tanggalLaku', 'like', '%' . $search . '%')
                ->orWhere('tanggalKirim', 'like', '%' . $search . '%')
                ->orWhere('tanggalAmbil', 'like', '%' . $search . '%')
                ->orWhere('idPembeli', 'like', '%' . $search . '%')
                ->orWhere('idPegawai', 'like', '%' . $search . '%');
        })
            ->whereIn('status', ['kirim', 'diambil'])
            ->orderBy('idTransaksiPenjualan')
            ->paginate(10);

        return view('pegawai.gudang.pengiriman.index', compact('pengiriman', 'search'));
    }
    public function show($id)
    {
        // Ambil data produk berdasarkan ID
        $pengiriman = TransaksiPenjualan::findOrFail($id);
        $produk = Produk::findOrFail($pengiriman->detailTransaksiPenjualan[0]->idProduk);
        $pengiriman->produk = $produk->deskripsi;
        $pengiriman->namaPembeli = Pembeli::findOrFail($pengiriman->idPembeli)->nama;
        $pengiriman->namaPegawai = Pegawai::find($pengiriman->idPegawai)->nama ?? '-';
        // Ambil gambar-gambar produk dari field gambar
        $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];

        return view('pegawai.gudang.pengiriman.show', compact('pengiriman', 'gambarArray'));
    }
}

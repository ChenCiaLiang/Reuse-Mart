<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\KategoriProduk;
use App\Models\Pegawai;
use App\Models\Pembeli;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
            ->where('status', 'terjual')
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

    public function penjadwalanPage($id)
    {
        $pengiriman = TransaksiPenjualan::findOrFail($id);
        $kurir = Pegawai::where('idJabatan', 6)->get();
        return view('pegawai.gudang.pengiriman.penjadwalan', compact('pengiriman', 'kurir'));
    }

    public function penjadwalan(Request $request, $id)
    {
        $pengiriman = TransaksiPenjualan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kurir' => 'required',
            'tanggalKirim' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalKirimRequest = Carbon::parse($request->tanggalKirim);
        $tanggalLaku = Carbon::parse($pengiriman->tanggalLaku);
        $sekarang = Carbon::now();

        if ($tanggalKirimRequest->lt($sekarang->startOfDay())) {
            $errorMessage = 'Tanggal kirim tidak boleh sebelum hari ini';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
        }

        // Validasi 2: Cek apakah pembelian terjadi antara jam 16:00 - 23:59
        $jamPembelian = (int) $tanggalLaku->format('H.i');
        $hariPembelian = $tanggalLaku->format('Y-m-d');
        $hariPengiriman = $tanggalKirimRequest->format('Y-m-d');

        // Jika pembelian di jam 16:00 - 23:59 (4 sore - 12 malam)
        if ($jamPembelian >= 16.00 && $jamPembelian <= 23.59) {
            // Tanggal kirim tidak boleh pada hari yang sama dengan pembelian
            if ($hariPembelian === $hariPengiriman) {
                $tanggalMinimal = $tanggalLaku->copy()->addDay()->format('d/m/Y');
                $errorMessage = "Pembelian setelah jam 16:00 tidak bisa dikirim di hari yang sama. Minimal tanggal kirim: {$tanggalMinimal}";

                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
                }
                return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
            }
        }

        // // Validasi 3: Jika pengiriman dijadwalkan hari ini, pastikan masih dalam jam operasional
        // if ($tanggalKirimRequest->format('Y-m-d') === $sekarang->format('Y-m-d')) {
        //     $jamSekarang = (int) $sekarang->format('H');

        //     // Asumsi jam operasional pengiriman: 08:00 - 20:00
        //     if ($jamSekarang >= 20) {
        //         $besok = $sekarang->copy()->addDay()->format('d/m/Y');
        //         $errorMessage = "Pengiriman hari ini sudah tutup (setelah jam 20:00). Minimal tanggal kirim: {$besok}";

        //         if ($request->expectsJson()) {
        //             return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
        //         }
        //         return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
        //     }
        // }

        $kurir = Pegawai::where('nama', $request->kurir);

        // Kirim notifikasi (jika diperlukan)
        $this->kirimNotifikasiPengiriman($pengiriman, $kurir);

        $pengiriman->update([
            'idPegawai' => $kurir->idPegawai,
            'tanggalKirim' => $request->tanggalKirim,
            'status' => 'kirim',
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Pengiriman berhasil dijadwalkan.');
    }

    public function konfirmasiAmbil($id) {}
}

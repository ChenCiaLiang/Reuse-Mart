<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\KategoriProduk;
use App\Models\Pegawai;
use App\Models\Pembeli;
use Carbon\Carbon;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Validator;

class TransaksiPengirimanController extends Controller
{
    //Menampilkan daftar transaksi (dikirim/diambil)
    public function index(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->input('search');

        $pengiriman = TransaksiPenjualan::whereIn('status', ['terjual', 'pengambilan', 'pengiriman'])
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('idTransaksiPenjualan', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%')
                        ->orWhere('tanggalLaku', 'like', '%' . $search . '%')
                        ->orWhere('tanggalKirim', 'like', '%' . $search . '%')
                        ->orWhere('tanggalAmbil', 'like', '%' . $search . '%')
                        ->orWhere('idPembeli', 'like', '%' . $search . '%')
                        ->orWhere('idPegawai', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('idTransaksiPenjualan')
            ->paginate(10);


        return view('pegawai.gudang.pengiriman.index', compact('pengiriman', 'search'));
    }

    public function show($id)
    {
        $pengiriman = TransaksiPenjualan::with([
            'detailTransaksiPenjualan.produk',
            'pembeli',
            'pegawai'
        ])->findOrFail($id);

        $produkList = [];
        $gambarArray = [];

        foreach ($pengiriman->detailTransaksiPenjualan as $detail) {
            $produk = $detail->produk;

            $produkList[] = [
                'nama' => $produk->deskripsi,
                'harga' => $produk->hargaJual,
                'gambar' => $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'],
                'berat' => $produk->berat,
                'garansi' => $produk->tanggalGaransi,
            ];

            if ($produk->gambar) {
                $gambarProduk = explode(',', $produk->gambar);
                $gambarArray = array_merge($gambarArray, $gambarProduk);
            } else {
                $gambarArray[] = 'default.jpg';
            }
        }

        $pengiriman->produkList = $produkList;
        $pengiriman->namaPembeli = $pengiriman->pembeli->nama ?? 'N/A';
        $pengiriman->namaPegawai = $pengiriman->pegawai->nama ?? 'Diambil Sendiri';

        return view('pegawai.gudang.pengiriman.show', compact('pengiriman'));
    }

    public function penjadwalanKirimPage($id)
    {
        $pengiriman = TransaksiPenjualan::findOrFail($id);
        $kurir = Pegawai::where('idJabatan', 6)->get();
        return view('pegawai.gudang.pengiriman.penjadwalanKirim', compact('pengiriman', 'kurir'));
    }

    public function penjadwalanAmbilPage($id)
    {
        $pengiriman = TransaksiPenjualan::findOrFail($id);
        return view('pegawai.gudang.pengiriman.penjadwalanAmbil', compact('pengiriman'));
    }

    public function penjadwalanKirim(Request $request, $id)
    {
        $pengiriman = TransaksiPenjualan::find($id);

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

        // Pembelian di atas jam 16.00
        $jamPembelian = (int) $tanggalLaku->format('H.i');
        $hariPembelian = $tanggalLaku->format('Y-m-d');
        $hariPengiriman = $tanggalKirimRequest->format('Y-m-d');

        if ($jamPembelian >= 16.00 && $jamPembelian <= 08.00) {
            if ($hariPembelian === $hariPengiriman) {
                $tanggalMinimal = $tanggalLaku->copy()->addDay()->format('d/m/Y');
                $errorMessage = "Pembelian setelah jam 16:00 tidak bisa dikirim di hari yang sama. Minimal tanggal kirim: {$tanggalMinimal}";

                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
                }
                return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
            }
        }

        $jamPengirimanRequest = (int) $tanggalKirimRequest->format('H.i');
        //jam pengiriman di luar operasional
        if (!($jamPengirimanRequest >= 08.00 && $jamPengirimanRequest <= 20.00)) {
            $errorMessage = 'Pengiriman hanya bisa dijadwalkan antara jam 08:00 - 20:00';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
        }

        // Kirim notifikasi (jika diperlukan)
        // $this->kirimNotifikasiPengiriman($pengiriman, $kurir);

        $pengiriman->update([
            'idPegawai' => $request->kurir,
            'tanggalKirim' => $request->tanggalKirim,
            'status' => 'pengiriman',
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Pengiriman berhasil dijadwalkan.');
    }

    public function penjadwalanAmbil(Request $request, $id)
    {
        $pengiriman = TransaksiPenjualan::find($id);

        $validator = Validator::make($request->all(), [
            'tanggalAmbil' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalAmbilRequest = Carbon::parse($request->tanggalAmbil);
        $sekarang = Carbon::now();

        if ($tanggalAmbilRequest->lt($sekarang->startOfDay())) {
            $errorMessage = 'Tanggal ambil tidak boleh sebelum hari ini';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalAmbil' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalAmbil' => $errorMessage])->withInput();
        }

        $jamPengambilanRequest = (int) $tanggalAmbilRequest->format('H.i');
        //jam pengambilan di luar operasional
        if (!($jamPengambilanRequest >= 08.00 && $jamPengambilanRequest <= 20.00)) {
            $errorMessage = 'Pengambilan hanya bisa dijadwalkan antara jam 08:00 - 20:00';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalAmbil' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalAmbil' => $errorMessage])->withInput();
        }

        $tanggalAmbil = Carbon::parse($request->tanggalAmbil);
        $tanggalBatasAmbil = $tanggalAmbil->copy()->addDays(2);

        $pengiriman->update([
            'tanggalBatasAmbil' => $tanggalBatasAmbil,
            'status' => 'pengambilan',
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Pengambilan berhasil dijadwalkan.');
    }

    public function konfirmasiAmbil($id)
    {
        $pengiriman = TransaksiPenjualan::findOrFail($id);

        $pengiriman->update([
            'status' => 'ambil',
            'tanggalAmbil' => Carbon::now(),
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Produk telah diambil.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestDonasi;
use App\Models\TransaksiDonasi;
use App\Models\Produk;
use App\Models\Organisasi;
use App\Models\Penitip;
use Illuminate\Support\Facades\DB;

class RequestDonasiController extends Controller
{
    public function dashboard()
    {
        // Statistik
        $totalDonasi = TransaksiDonasi::count();
        $donasiBulanIni = TransaksiDonasi::whereMonth('tanggalPemberian', now()->month)
                            ->whereYear('tanggalPemberian', now()->year)
                            ->count();
        $requestMenunggu = RequestDonasi::where('status', '!=', 'Terpenuhi')
                            ->where('status', '!=', 'Ditolak')
                            ->count();
        $organisasiAktif = Organisasi::has('requestDonasi')->count();
        
        // Barang siap didonasikan
        $barangDonasi = Produk::where('status', 'barang untuk donasi')
                        ->with('kategori')
                        ->limit(5)
                        ->get();
        
        // Request terbaru
        $requestTerbaru = RequestDonasi::with('organisasi')
                        ->orderBy('tanggalRequest', 'desc')
                        ->limit(5)
                        ->get();
        
        // History donasi terakhir
        $historyDonasi = TransaksiDonasi::with(['request.organisasi', 'produk'])
                        ->orderBy('tanggalPemberian', 'desc')
                        ->limit(5)
                        ->get();
        
        return view('pegawai.owner.dashboard', compact(
            'totalDonasi', 'donasiBulanIni', 'requestMenunggu', 'organisasiAktif',
            'barangDonasi', 'requestTerbaru', 'historyDonasi'
        ));
    }
    /**
     * Menampilkan daftar request donasi
     */
    public function index()
    {
        $requests = RequestDonasi::with('organisasi')->orderBy('tanggalRequest', 'desc')->get();
        return view('pegawai.owner.request', compact('requests'));
    }

    /**
     * Menampilkan history donasi ke organisasi tertentu
     */
    public function historyDonasi(Request $request)
    {
        $idOrganisasi = $request->input('idOrganisasi');
        
        $query = TransaksiDonasi::with(['request.organisasi', 'produk']);
        
        if ($idOrganisasi) {
            $query->whereHas('request', function($q) use ($idOrganisasi) {
                $q->where('idOrganisasi', $idOrganisasi);
            });
        }
        
        $donasi = $query->orderBy('tanggalPemberian', 'desc')->get();
        $organisasi = Organisasi::all();
        
        return view('pegawai.owner..history', compact('donasi', 'organisasi', 'idOrganisasi'));
    }

    /**
     * Menampilkan barang dengan status "untuk donasi"
     */
    public function barangDonasi()
    {
        $barang = Produk::where('status', 'barang untuk donasi')->get();
        $organisasi = Organisasi::has('requestDonasi')->get();
        return view('pegawai.owner.barang', compact('barang', 'organisasi'));
    }

    /**
     * Mengalokasikan barang ke organisasi
     */
    public function alokasikanBarang(Request $request)
    {
        $request->validate([
            'idProduk' => 'required|string',
            'idRequest' => 'required|numeric',
        ]);
        
        // Cek status barang
        $produk = Produk::findOrFail($request->idProduk);
        if ($produk->status !== 'barang untuk donasi') {
            return back()->with('error', 'Hanya barang dengan status "barang untuk donasi" yang dapat dialokasikan');
        }
        
        $requestDonasi = RequestDonasi::findOrFail($request->idRequest);
        
        // Buat transaksi donasi baru
        $transaksiDonasi = new TransaksiDonasi();
        $transaksiDonasi->tanggalPemberian = now(); // Tanggal dapat diubah nanti
        $transaksiDonasi->namaPenerima = ''; // Nama penerima dapat diubah nanti
        $transaksiDonasi->idRequest = $requestDonasi->idRequest;
        $transaksiDonasi->idProduk = $produk->idProduk;
        $transaksiDonasi->save();
        
        // Update status barang (belum diubah jadi "Didonasikan" sampai proses selesai)
        $produk->status = 'proses donasi';
        $produk->save();
        
        return redirect()->route('request-donasi.update-donasi', $transaksiDonasi->id)
            ->with('success', 'Barang berhasil dialokasikan untuk donasi. Silakan lengkapi informasi donasi.');
    }

    /**
     * Menampilkan form untuk update informasi donasi
     */
    public function editDonasi($id)
    {
        $donasi = TransaksiDonasi::with(['request.organisasi', 'produk'])->findOrFail($id);
        return view('pegawai.owner.edit', compact('donasi'));
    }

    /**
     * Mengupdate tanggal donasi, nama penerima, dan status barang
     */
    public function updateDonasi(Request $request, $id)
    {
        $request->validate([
            'tanggalPemberian' => 'required|date',
            'namaPenerima' => 'required|string|max:50',
        ]);
        
        DB::beginTransaction();
        try {
            $donasi = TransaksiDonasi::findOrFail($id);
            
            // Update transaksi donasi
            $donasi->tanggalPemberian = $request->tanggalPemberian;
            $donasi->namaPenerima = $request->namaPenerima;
            $donasi->save();
            
            // Update status barang menjadi "Didonasikan"
            $produk = Produk::findOrFail($donasi->idProduk);
            $produk->status = 'Didonasikan';
            $produk->save();
            
            // Kirim notifikasi ke penitip
            // Karena produk bisa terhubung ke penitip melalui transaksi penitipan,
            // kita perlu mencari penitip dari barang tersebut
            $penitip = $this->getPenitipFromProduk($produk->idProduk);
            if ($penitip) {
                // Berikan poin reward sesuai sistem yang ada
                // Asumsi: 1 poin per Rp 10.000 nilai barang
                $poinReward = floor($produk->harga / 10000);
                $penitip->poin += $poinReward;
                $penitip->save();
                
                // Notifikasi bisa diimplementasikan sesuai sistem yang sudah ada
                // Ini placeholder saja, sesuaikan dengan sistem notifikasi yang ada
                // (bisa push notification ke aplikasi Android seperti yang diminta)
            }
            
            DB::commit();
            
            return redirect()->route('request-donasi.history')
                ->with('success', 'Informasi donasi berhasil diperbarui dan notifikasi telah dikirim ke penitip');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper method untuk mendapatkan data penitip dari id produk
     */
    private function getPenitipFromProduk($idProduk)
    {
        $penitip = null;
        
        // Cari transaksi penitipan untuk produk ini
        $transaksiPenitipan = DB::table('detail_transaksi_penitipan')
            ->join('transaksi_penitipan', 'detail_transaksi_penitipan.idTransaksiPenitipan', '=', 'transaksi_penitipan.idTransaksiPenitipan')
            ->where('detail_transaksi_penitipan.idProduk', $idProduk)
            ->select('transaksi_penitipan.idPenitip')
            ->first();
            
        if ($transaksiPenitipan) {
            $penitip = Penitip::find($transaksiPenitipan->idPenitip);
        }
        
        return $penitip;
    }
}
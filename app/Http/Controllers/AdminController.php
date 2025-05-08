<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Penitip;
use App\Models\Pembeli;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\TransaksiPenitipan;
use App\Models\TransaksiDonasi;
use App\Models\Organisasi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Data jumlah pengguna
        $totalPegawai = Pegawai::count();
        $totalPenitip = Penitip::count();
        $totalPembeli = Pembeli::count();
        $totalOrganisasi = Organisasi::count();
        
        // Data produk
        $totalProduk = Produk::count();
        $produkTersedia = Produk::where('status', '!=', 'Terjual')
                                ->where('status', '!=', 'Didonasikan')
                                ->count();
        $produkTerjual = Produk::where('status', 'Terjual')->count();
        $produkDidonasikan = Produk::where('status', 'Didonasikan')->count();
        
        // Data transaksi bulan ini
        $bulanIni = Carbon::now()->startOfMonth();
        $penjualanBulanIni = TransaksiPenjualan::whereMonth('tanggalLunas', $bulanIni->month)
                                              ->whereYear('tanggalLunas', $bulanIni->year)
                                              ->count();
        $penitipanBulanIni = TransaksiPenitipan::whereMonth('tanggalMasukPenitipan', $bulanIni->month)
                                              ->whereYear('tanggalMasukPenitipan', $bulanIni->year)
                                              ->count();
        $donasiBulanIni = TransaksiDonasi::whereMonth('tanggalPemberian', $bulanIni->month)
                                        ->whereYear('tanggalPemberian', $bulanIni->year)
                                        ->count();
        
        // Data penjualan per bulan untuk grafik
        $penjualanPerBulan = TransaksiPenjualan::select(
                DB::raw('MONTH(tanggalLunas) as bulan'),
                DB::raw('YEAR(tanggalLunas) as tahun'),
                DB::raw('COUNT(*) as total_penjualan')
            )
            ->whereNotNull('tanggalLunas')
            ->whereYear('tanggalLunas', Carbon::now()->year)
            ->groupBy('bulan', 'tahun')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();
            
        // Format data penjualan untuk chart
        $chartData = [];
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Inisialisasi data untuk semua bulan (1-12)
        for ($i = 1; $i <= 12; $i++) {
            $chartData[$i] = [
                'bulan' => $namaBulan[$i],
                'total_penjualan' => 0
            ];
        }
        
        // Isi data penjualan
        foreach ($penjualanPerBulan as $data) {
            $chartData[$data->bulan]['total_penjualan'] = $data->total_penjualan;
        }
        
        // Konversi ke array untuk chart
        $chartData = array_values($chartData);
        
        // Produk terlaris
        $produkTerlaris = DB::table('detail_transaksi_penjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('transaksi_penjualan', 'detail_transaksi_penjualan.idTransaksi', '=', 'transaksi_penjualan.idTransaksi')
            ->whereNotNull('transaksi_penjualan.tanggalLunas')
            ->select('produk.idProduk', 'produk.deskripsi', DB::raw('COUNT(*) as total_terjual'))
            ->groupBy('produk.idProduk', 'produk.deskripsi')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->get();
            
        // Transaksi terakhir
        $transaksiTerakhir = TransaksiPenjualan::with(['pembeli', 'detailTransaksiPenjualan.produk'])
            ->whereNotNull('tanggalLunas')
            ->orderBy('tanggalLunas', 'desc')
            ->limit(5)
            ->get();
            
        // Barang yang masa penitipannya hampir habis
        $barangHampirHabis = TransaksiPenitipan::with(['penitip', 'detailTransaksiPenitipan.produk'])
            ->where('tanggalAkhirPenitipan', '>=', Carbon::now())
            ->where('tanggalAkhirPenitipan', '<=', Carbon::now()->addDays(7))
            ->where('statusPenitipan', '!=', 'Selesai')
            ->orderBy('tanggalAkhirPenitipan')
            ->limit(5)
            ->get();
        
            return view('admin.dashboard', compact(
                'totalPegawai', 'totalPenitip', 'totalPembeli', 'totalOrganisasi',
                'totalProduk', 'produkTersedia', 'produkTerjual', 'produkDidonasikan',
                'penjualanBulanIni', 'penitipanBulanIni', 'donasiBulanIni',
                'chartData', 'produkTerlaris', 'transaksiTerakhir', 'barangHampirHabis'
        ));
    }
}
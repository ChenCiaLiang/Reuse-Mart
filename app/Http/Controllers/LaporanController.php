<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\RequestDonasi;
use App\Models\TransaksiDonasi; 
use App\Models\Penitip;
use App\Models\DetailTransaksiPenitipan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // Tidak perlu constructor middleware karena sudah ada di routes level

    // Laporan Penjualan Bulanan Keseluruhan
    public function penjualanBulanan(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        $dataPenjualan = DB::table('transaksi_penjualan as tp')
            ->join('detail_transaksi_penjualan as dtp', 'tp.idTransaksiPenjualan', '=', 'dtp.idTransaksiPenjualan')
            ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
            ->where('tp.status', 'terjual')
            ->whereYear('tp.tanggalLaku', $tahun)
            ->selectRaw('
                MONTH(tp.tanggalLaku) as bulan,
                COUNT(DISTINCT dtp.idProduk) as jumlah_barang,
                SUM(p.hargaJual) as jumlah_penjualan
            ')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Format data untuk grafik dan tabel
        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $dataFormatted = [];
        $totalBarang = 0;
        $totalPenjualan = 0;

        for ($i = 1; $i <= 12; $i++) {
            $found = $dataPenjualan->firstWhere('bulan', $i);
            $jumlahBarang = $found ? $found->jumlah_barang : 0;
            $jumlahPenjualan = $found ? $found->jumlah_penjualan : 0;

            $dataFormatted[] = [
                'bulan' => $bulanIndonesia[$i],
                'bulan_num' => $i,
                'jumlah_barang' => $jumlahBarang,
                'jumlah_penjualan' => $jumlahPenjualan
            ];

            $totalBarang += $jumlahBarang;
            $totalPenjualan += $jumlahPenjualan;
        }

        return view('pegawai.owner.laporan.penjualan-bulanan-index', compact(
            'dataFormatted',
            'tahun',
            'totalBarang',
            'totalPenjualan'
        ));
    }

    public function downloadPenjualanBulanan(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        $dataPenjualan = DB::table('transaksi_penjualan as tp')
            ->join('detail_transaksi_penjualan as dtp', 'tp.idTransaksiPenjualan', '=', 'dtp.idTransaksiPenjualan')
            ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
            ->where('tp.status', 'terjual')
            ->whereYear('tp.tanggalLaku', $tahun)
            ->selectRaw('
                MONTH(tp.tanggalLaku) as bulan,
                COUNT(DISTINCT dtp.idProduk) as jumlah_barang,
                SUM(p.hargaJual) as jumlah_penjualan
            ')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $dataFormatted = [];
        $totalBarang = 0;
        $totalPenjualan = 0;

        for ($i = 1; $i <= 12; $i++) {
            $found = $dataPenjualan->firstWhere('bulan', $i);
            $jumlahBarang = $found ? $found->jumlah_barang : 0;
            $jumlahPenjualan = $found ? $found->jumlah_penjualan : 0;

            $dataFormatted[] = [
                'bulan' => $bulanIndonesia[$i],
                'jumlah_barang' => $jumlahBarang,
                'jumlah_penjualan' => $jumlahPenjualan
            ];

            $totalBarang += $jumlahBarang;
            $totalPenjualan += $jumlahPenjualan;
        }

        $pdf = PDF::loadView('pegawai.owner.laporan.pdf.penjualan-bulanan', compact(
            'dataFormatted',
            'tahun',
            'totalBarang',
            'totalPenjualan'
        ));

        return $pdf->download("Laporan_Penjualan_Bulanan_{$tahun}.pdf");
    }

    // Laporan Komisi Bulanan per Produk
    public function komisiBulanan(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $dataKomisi = DB::table('komisi as k')
            ->join('detail_transaksi_penjualan as dtp', 'k.idDetailTransaksiPenjualan', '=', 'dtp.idDetailTransaksiPenjualan')
            ->join('transaksi_penjualan as tp', 'dtp.idTransaksiPenjualan', '=', 'tp.idTransaksiPenjualan')
            ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
            ->join('penitip as pen', 'k.idPenitip', '=', 'pen.idPenitip')
            ->join('detail_transaksi_penitipan as dtpen', 'p.idProduk', '=', 'dtpen.idProduk')
            ->join('transaksi_penitipan as tpen', 'dtpen.idTransaksiPenitipan', '=', 'tpen.idTransaksiPenitipan')
            ->leftJoin('pegawai as hunter', 'tpen.idHunter', '=', 'hunter.idPegawai')
            ->where('tp.status', 'terjual')
            ->whereMonth('tp.tanggalLaku', $bulan)
            ->whereYear('tp.tanggalLaku', $tahun)
            ->select(
                'p.idProduk',
                DB::raw("CONCAT(UPPER(LEFT(p.deskripsi, 1)), RIGHT(p.idProduk, 3)) as kode_produk"),
                'p.deskripsi as nama_produk',
                'p.hargaJual as harga_jual',
                'tpen.tanggalMasukPenitipan as tanggal_masuk',
                'tp.tanggalLaku as tanggal_laku',
                'k.komisiHunter',
                'k.komisiReuse',
                'pen.bonus',
                'hunter.nama as nama_hunter'
            )
            ->get();

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $namaBulan = $bulanIndonesia[(int)$bulan];

        return view('pegawai.owner.laporan.komisi-bulanan-index', compact(
            'dataKomisi',
            'bulan',
            'tahun',
            'namaBulan'
        ));
    }

    public function downloadKomisiBulanan(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $dataKomisi = DB::table('komisi as k')
            ->join('detail_transaksi_penjualan as dtp', 'k.idDetailTransaksiPenjualan', '=', 'dtp.idDetailTransaksiPenjualan')
            ->join('transaksi_penjualan as tp', 'dtp.idTransaksiPenjualan', '=', 'tp.idTransaksiPenjualan')
            ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
            ->join('penitip as pen', 'k.idPenitip', '=', 'pen.idPenitip')
            ->join('detail_transaksi_penitipan as dtpen', 'p.idProduk', '=', 'dtpen.idProduk')
            ->join('transaksi_penitipan as tpen', 'dtpen.idTransaksiPenitipan', '=', 'tpen.idTransaksiPenitipan')
            ->leftJoin('pegawai as hunter', 'tpen.idHunter', '=', 'hunter.idPegawai')
            ->where('tp.status', 'terjual')
            ->whereMonth('tp.tanggalLaku', $bulan)
            ->whereYear('tp.tanggalLaku', $tahun)
            ->select(
                'p.idProduk',
                DB::raw("CONCAT(UPPER(LEFT(p.deskripsi, 1)), RIGHT(p.idProduk, 3)) as kode_produk"),
                'p.deskripsi as nama_produk',
                'p.hargaJual as harga_jual',
                'tpen.tanggalMasukPenitipan as tanggal_masuk',
                'tp.tanggalLaku as tanggal_laku',
                'k.komisiHunter',
                'k.komisiReuse',
                'pen.bonus',
                'hunter.nama as nama_hunter'
            )
            ->get();

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $namaBulan = $bulanIndonesia[(int)$bulan];

        $pdf = PDF::loadView('pegawai.owner.laporan.pdf.komisi-bulanan', compact(
            'dataKomisi',
            'bulan',
            'tahun',
            'namaBulan'
        ));

        return $pdf->download("Laporan_Komisi_Bulanan_{$namaBulan}_{$tahun}.pdf");
    }

    // Laporan Stok Gudang
    public function stokGudang()
    {
        $dataStok = DB::table('produk as p')
            ->join('detail_transaksi_penitipan as dtp', 'p.idProduk', '=', 'dtp.idProduk')
            ->join('transaksi_penitipan as tp', 'dtp.idTransaksiPenitipan', '=', 'tp.idTransaksiPenitipan')
            ->join('penitip as pen', 'tp.idPenitip', '=', 'pen.idPenitip')
            ->leftJoin('pegawai as hunter', 'tp.idHunter', '=', 'hunter.idPegawai')
            ->where('p.status', 'Tersedia')
            ->select(
                DB::raw("CONCAT(UPPER(LEFT(p.deskripsi, 1)), RIGHT(p.idProduk, 3)) as kode_produk"),
                'p.deskripsi as nama_produk',
                'pen.idPenitip',
                'pen.nama as nama_penitip',
                'tp.tanggalMasukPenitipan as tanggal_masuk',
                'tp.statusPerpanjangan',
                'hunter.idPegawai as id_hunter',
                'hunter.nama as nama_hunter',
                'p.hargaJual as harga'
            )
            ->orderBy('tp.tanggalMasukPenitipan', 'desc')
            ->get();

        return view('pegawai.owner.laporan.stok-gudang-index', compact('dataStok'));
    }

    public function downloadStokGudang()
    {
        $dataStok = DB::table('produk as p')
            ->join('detail_transaksi_penitipan as dtp', 'p.idProduk', '=', 'dtp.idProduk')
            ->join('transaksi_penitipan as tp', 'dtp.idTransaksiPenitipan', '=', 'tp.idTransaksiPenitipan')
            ->join('penitip as pen', 'tp.idPenitip', '=', 'pen.idPenitip')
            ->leftJoin('pegawai as hunter', 'tp.idHunter', '=', 'hunter.idPegawai')
            ->where('p.status', 'Tersedia')
            ->select(
                DB::raw("CONCAT(UPPER(LEFT(p.deskripsi, 1)), RIGHT(p.idProduk, 3)) as kode_produk"),
                'p.deskripsi as nama_produk',
                'pen.idPenitip',
                'pen.nama as nama_penitip',
                'tp.tanggalMasukPenitipan as tanggal_masuk',
                'tp.statusPerpanjangan',
                'hunter.idPegawai as id_hunter',
                'hunter.nama as nama_hunter',
                'p.hargaJual as harga'
            )
            ->orderBy('tp.tanggalMasukPenitipan', 'desc')
            ->get();

        $pdf = PDF::loadView('pegawai.owner.laporan.pdf.stok-gudang', compact('dataStok'));

        return $pdf->download('Laporan_Stok_Gudang_' . date('Y-m-d') . '.pdf');
    }

    public function laporanKategori(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        // Ambil semua kategori
        $kategoris = KategoriProduk::all();

        $data = [];
        $totalTerjual = 0;
        $totalGagalTerjual = 0;

        foreach ($kategoris as $kategori) {
            // Hitung item terjual per kategori berdasarkan tahun tanggalLaku
            $itemTerjual = DB::table('produk')
                ->join('detail_transaksi_penjualan', 'produk.idProduk', '=', 'detail_transaksi_penjualan.idProduk')
                ->join('transaksi_penjualan', 'detail_transaksi_penjualan.idTransaksiPenjualan', '=', 'transaksi_penjualan.idTransaksiPenjualan')
                ->where('produk.idKategori', $kategori->idKategori)
                ->where('produk.status', 'Terjual')
                ->whereNotNull('transaksi_penjualan.tanggalLaku')  // Pastikan ada tanggalLaku
                ->whereYear('transaksi_penjualan.tanggalLaku', $tahun)  // Filter berdasarkan tahun tanggalLaku
                ->count();

            // Hitung item gagal terjual per kategori berdasarkan status produk
            $itemGagalTerjual = DB::table('produk')
                ->where('produk.idKategori', $kategori->idKategori)
                ->whereIn('produk.status', ['barang untuk donasi', 'Didonasikan', 'Diambil Kembali'])
                ->count();

            $data[] = [
                'kategori' => $kategori->nama,
                'item_terjual' => $itemTerjual,
                'item_gagal_terjual' => $itemGagalTerjual
            ];

            $totalTerjual += $itemTerjual;
            $totalGagalTerjual += $itemGagalTerjual;
        }

        $reportData = [
            'data' => $data,
            'tahun' => $tahun,
            'tanggal_cetak' => Carbon::now()->format('d F Y'),
            'total_terjual' => $totalTerjual,
            'total_gagal_terjual' => $totalGagalTerjual
        ];

        // Jika request untuk PDF
        if ($request->get('format') == 'pdf') {
            $pdf = Pdf::loadView('pegawai.owner.laporan.pdf.laporanKategori', $reportData);
            return $pdf->download('laporan-penjualan-per-kategori-' . $tahun . '.pdf');
        }

        // Return view laporan untuk tampilan web
        return view('pegawai.owner.laporan.laporanKategori', $reportData);
    }

    public function laporanKategoriForm()
    {
        return view('pegawai.owner.laporan.laporanKategori-form');
    }

    public function masaPenitipanHabis()
    {
        try {
            // Query untuk mengambil data barang yang masa penitipannya sudah habis
            $dataBarang = DB::table('transaksi_penitipan as tp')
                ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
                ->join('penitip as pen', 'tp.idPenitip', '=', 'pen.idPenitip')
                ->select(
                    'p.idProduk',
                    'p.deskripsi as namaProduk',
                    'p.idKategori',
                    'pen.idPenitip',
                    'pen.nama as namaPenitip',
                    'tp.tanggalMasukPenitipan',
                    'tp.tanggalAkhirPenitipan',
                    'tp.batasAmbil'
                )
                ->where('tp.tanggalAkhirPenitipan', '<', Carbon::now())
                //CEKME
                ->where('tp.statusPenitipan', '!=', 'Diambil')
                ->where('tp.statusPenitipan', '!=', 'Selesai')
                ->orderBy('tp.tanggalAkhirPenitipan', 'asc')
                ->get();
            // Generate kode produk: K + idKategori + idProduk
            foreach ($dataBarang as $item) {
                $item->kodeProduk = strtoupper(substr($item->namaProduk, 0, 1)) . $item->idProduk;
            }

            $tanggalCetak = Carbon::now()->format('d F Y');

            // Data untuk PDF
            $data = [
                'dataBarang' => $dataBarang,
                'tanggalCetak' => $tanggalCetak,
                'totalBarang' => $dataBarang->count()
            ];

            // Generate PDF
            $pdf = PDF::loadView('pegawai.owner.laporan.pdf.laporanMasaPenitipanHabis', $data);
            $pdf->setPaper('A4', 'landscape');

            $namaFile = 'Laporan_Barang_Penitipan_Habis_' . Carbon::now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($namaFile);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menggenerate laporan: ' . $e->getMessage());
        }
    }

    // Laporan Request Donasi - DIPERBAIKI
    public function laporanRequestDonasi(Request $request)
    {
        $query = RequestDonasi::with('organisasi');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('request', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('organisasi', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest('tanggalRequest')->paginate(10);
        return view('pegawai.owner.laporan.request-donasi-index', compact('requests'));
    }

    public function downloadLaporanRequestDonasiPdf(Request $request)
    {
        $query = RequestDonasi::with('organisasi');

         if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('request', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('organisasi', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest('tanggalRequest')->get();
        $pdf = Pdf::loadView('pegawai.owner.laporan.pdf.request-donasi', [
            'requests' => $requests,
            'search' => $request->input('search')
        ]);
        return $pdf->download('laporan-request-donasi-'.date('Y-m-d').'.pdf');
    }

    // Laporan Donasi Barang - DIPERBAIKI
    public function laporanDonasiBarang(Request $request)
    {
        $query = TransaksiDonasi::with('requestDonasi.organisasi', 'produk');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('produk', function($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%");
            })->orWhereHas('requestDonasi.organisasi', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $donations = $query->latest('tanggalPemberian')->paginate(10);
        return view('pegawai.owner.laporan.donasi-barang-index', compact('donations'));
    }

    public function downloadLaporanDonasiBarangPdf(Request $request)
    {
         $query = TransaksiDonasi::with('requestDonasi.organisasi', 'produk');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('produk', function($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%");
            })->orWhereHas('requestDonasi.organisasi', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }
        
        $donations = $query->latest('tanggalPemberian')->get();
        $pdf = Pdf::loadView('pegawai.owner.laporan.pdf.donasi-barang', [
            'donations' => $donations,
            'search' => $request->input('search')
        ]);
        return $pdf->download('laporan-donasi-barang-'.date('Y-m-d').'.pdf');
    }

    // Laporan Transaksi Penitip - DIPERBAIKI
    public function laporanTransaksiPenitipForm()
    {
        // Hapus filter 'status' karena model Penitip tidak memiliki kolom status
        $penitips = Penitip::orderBy('nama')->get();
        return view('pegawai.owner.laporan.transaksi-penitip-form', compact('penitips'));
    }
    
    public function generateLaporanTransaksiPenitip(Request $request)
    {
        $request->validate([
            'idPenitip' => 'required|exists:penitip,idPenitip',
            'tahun' => 'required|digits:4|integer|min:2020',
        ]);

        $idPenitip = $request->input('idPenitip');
        $tahun = $request->input('tahun');
        $penitip = Penitip::findOrFail($idPenitip);

        // Query yang diperbaiki berdasarkan struktur database yang ada
        $transaksi = DB::table('transaksi_penitipan as tp')
            ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
            ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
            ->join('detail_transaksi_penjualan as dtpj', 'p.idProduk', '=', 'dtpj.idProduk')
            ->join('transaksi_penjualan as tpj', 'dtpj.idTransaksiPenjualan', '=', 'tpj.idTransaksiPenjualan')
            ->leftJoin('komisi as k', 'dtpj.idDetailTransaksiPenjualan', '=', 'k.idDetailTransaksiPenjualan')
            ->where('tp.idPenitip', $idPenitip)
            ->whereYear('tpj.tanggalLaku', $tahun)
            ->where('tpj.status', 'terjual')
            ->select(
                'p.deskripsi as nama_produk',
                'tp.tanggalMasukPenitipan',
                'tpj.tanggalLaku',
                'p.hargaJual',
                'k.komisiPenitip',
                'k.komisiReuse',
                'k.komisiHunter'
            )
            ->get();

        $totals = [
            'harga_jual' => $transaksi->sum('hargaJual'),
            'komisi' => $transaksi->sum('komisiReuse') + $transaksi->sum('komisiHunter'),
            'pendapatan' => $transaksi->sum('komisiPenitip')
        ];

        if ($request->has('download_pdf')) {
            $pdf = Pdf::loadView('pegawai.owner.laporan.pdf.transaksi-penitip', compact('penitip', 'transaksi', 'tahun', 'totals'));
            return $pdf->download('laporan-transaksi-'.$penitip->nama.'-'.$tahun.'.pdf');
        }

        return view('pegawai.owner.laporan.transaksi-penitip-index', compact('penitip', 'transaksi', 'tahun', 'totals'));
    }
}

<?php

namespace App\Http\Controllers;

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
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
            'dataFormatted', 'tahun', 'totalBarang', 'totalPenjualan'
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
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
            'dataFormatted', 'tahun', 'totalBarang', 'totalPenjualan'
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
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $namaBulan = $bulanIndonesia[(int)$bulan];

        return view('pegawai.owner.laporan.komisi-bulanan-index', compact(
            'dataKomisi', 'bulan', 'tahun', 'namaBulan'
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
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $namaBulan = $bulanIndonesia[(int)$bulan];

        $pdf = PDF::loadView('pegawai.owner.laporan.pdf.komisi-bulanan', compact(
            'dataKomisi', 'bulan', 'tahun', 'namaBulan'
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
}
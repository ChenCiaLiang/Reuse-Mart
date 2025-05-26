<?php

namespace App\Http\Controllers;

use App\Models\Pembeli;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembeliController extends Controller
{
    public function profile()
    {
        // Ambil ID pembeli dari session
        $idPembeli = session('user')['idPembeli'];

        // Dapatkan data pembeli
        $pembeli = Pembeli::findOrFail($idPembeli);

        // Dapatkan transaksi penjualan terbaru (5 terakhir)
        // Kita perlu join beberapa tabel untuk mendapatkan transaksi penjualan dari barang yang dititipkan
        $transaksiPenjualan = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->where('transaksi_penjualan.idPembeli', $idPembeli)
            ->whereNotNull('transaksi_penjualan.tanggalLunas') // Hanya transaksi yang sudah lunas
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->limit(5)
            ->get();
        return view('customer.pembeli.profile', compact('pembeli', 'transaksiPenjualan'));
    }

    /**
     * Menampilkan histori transaksi
     */
    public function historyTransaksi(Request $request)
    {
        // Ambil ID penitip dari session
        $idPembeli = session('user')['idPembeli'];

        // Filter berdasarkan tanggal jika ada
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // Query yang diperbaiki menggunakan relasi komisi
        $transaksiPenjualan = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->whereBetween('transaksi_penjualan.tanggalLunas', [$startDate, $endDate])
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->distinct()
            ->paginate(10);

        // Tambahkan variabel debug untuk troubleshooting
        $debug = [
            'idPembeli' => $idPembeli,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'count' => $transaksiPenjualan->count(),
            'total' => $transaksiPenjualan->total()
        ];

        return view('customer.pembeli.history', compact('transaksiPenjualan', 'startDate', 'endDate', 'debug'));
    }
    /**
     * Menampilkan detail transaksi
     */
    public function detailTransaksi($idTransaksiPenjualan)
    {
        try {
            // Ambil ID pembeli dari session
            $idPembeli = session('user')['idPembeli'];

            // Dapatkan detail transaksi
            $transaksi = TransaksiPenjualan::findOrFail($idTransaksiPenjualan);

            // Dapatkan produk yang dijual
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksiPenjualan)
                ->with('produk')
                ->get();

            // Dapatkan pembeli
            $pembeli = $transaksi->pembeli;

            return view('customer.pembeli.transaksi.detail', compact('transaksi', 'detailTransaksi', 'pembeli'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in detailTransaksi: " . $e->getMessage());
            return redirect()->route('pembeli.profile')->with('error', 'Terjadi kesalahan saat mengakses detail transaksi');
        }
    }

    // public function list(Request $request)
    // {
    //     // Ambil ID penitip dari session
    //     $idPembeli = session('user')['idPembeli'];
    //     $pembeli = Pembeli::findOrFail($idPembeli);

    //     // Query yang diperbaiki menggunakan relasi komisi
    //     $transaksiPenjualan = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
    //         ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
    //         ->orderBy('transaksi_penjualan.idTransaksiPenjualan', 'desc')
    //         ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
    //         ->distinct()
    //         ->paginate(5);


    //     return view('customer.pembeli.list', compact('transaksiPenjualan', 'pembeli'));
    // }
}

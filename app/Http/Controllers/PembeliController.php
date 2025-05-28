<?php

namespace App\Http\Controllers;

use App\Models\Pembeli;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use App\Models\Penitip;
use App\Models\Komisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Menampilkan halaman rating produk yang sudah dibeli
     */
    public function indexRating()
    {
        $idPembeli = session('user')['idPembeli'];
        
        // Ambil produk yang sudah dibeli pembeli dan transaksi sudah selesai
        $produkDibeli = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('kategori_produk', 'produk.idKategori', '=', 'kategori_produk.idKategori')
            ->where('transaksi_penjualan.idPembeli', $idPembeli)
            ->whereIn('transaksi_penjualan.status', ['diambil', 'kirim']) // Hanya yang sudah selesai
            ->whereNotNull('transaksi_penjualan.tanggalLunas')
            ->select(
                'produk.idProduk',
                'produk.deskripsi',
                'produk.gambar',
                'produk.hargaJual',
                'produk.ratingProduk',
                'kategori_produk.nama as kategori',
                'transaksi_penjualan.tanggalLunas',
                'transaksi_penjualan.idTransaksiPenjualan'
            )
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->get();

        return view('customer.pembeli.rating.index', compact('produkDibeli'));
    }

    /**
     * Menyimpan rating produk
     */
    public function storeRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idProduk' => 'required|exists:produk,idProduk',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $idPembeli = session('user')['idPembeli'];
            $idProduk = $request->idProduk;
            $ratingBaru = $request->rating;

            // Verifikasi apakah pembeli memang pernah membeli produk ini
            $transaksiExists = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
                ->where('transaksi_penjualan.idPembeli', $idPembeli)
                ->where('detail_transaksi_penjualan.idProduk', $idProduk)
                ->whereIn('transaksi_penjualan.status', ['diambil', 'kirim'])
                ->whereNotNull('transaksi_penjualan.tanggalLunas')
                ->exists();

            if (!$transaksiExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat memberikan rating untuk produk yang belum dibeli'
                ], 403);
            }

            // Update rating produk (dalam kode ini kita anggap hanya ada satu rating per produk)
            // Jika ingin multiple rating, perlu tabel terpisah
            $produk = Produk::findOrFail($idProduk);
            $produk->ratingProduk = $ratingBaru;
            $produk->save();

            // Hitung ulang rata-rata rating penitip
            $this->updateRatingPenitip($idProduk);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan rating'
            ], 500);
        }
    }

    /**
     * Update rata-rata rating penitip berdasarkan rating produk-produknya
     */
    private function updateRatingPenitip($idProduk)
    {
        // Cari penitip dari produk
        $penitip = DB::table('detail_transaksi_penitipan')
            ->join('transaksi_penitipan', 'detail_transaksi_penitipan.idTransaksiPenitipan', '=', 'transaksi_penitipan.idTransaksiPenitipan')
            ->where('detail_transaksi_penitipan.idProduk', $idProduk)
            ->select('transaksi_penitipan.idPenitip')
            ->first();

        if ($penitip) {
            // Hitung rata-rata rating dari semua produk penitip yang memiliki rating
            $rataRating = DB::table('detail_transaksi_penitipan')
                ->join('transaksi_penitipan', 'detail_transaksi_penitipan.idTransaksiPenitipan', '=', 'transaksi_penitipan.idTransaksiPenitipan')
                ->join('produk', 'detail_transaksi_penitipan.idProduk', '=', 'produk.idProduk')
                ->where('transaksi_penitipan.idPenitip', $penitip->idPenitip)
                ->where('produk.ratingProduk', '>', 0)
                ->avg('produk.ratingProduk');

            // Update rating penitip
            Penitip::where('idPenitip', $penitip->idPenitip)
                ->update(['rating' => round($rataRating, 2)]);
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
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
use Illuminate\Support\Facades\Log;
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
        // Ambil ID pembeli dari session
        $idPembeli = session('user')['idPembeli'];

        // Filter berdasarkan tanggal jika ada - UBAH ke tanggalPesan agar semua status bisa tampil
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // Filter status jika ada
        $statusFilter = $request->input('status');

        // Query yang diperbaiki - tampilkan SEMUA status transaksi pembeli
        $query = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->where('transaksi_penjualan.idPembeli', $idPembeli) // TAMBAHAN: Filter berdasarkan pembeli
            ->whereBetween('transaksi_penjualan.tanggalPesan', [$startDate, $endDate]) // UBAH: Gunakan tanggalPesan bukan tanggalLunas
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->distinct();

        // Filter berdasarkan status jika dipilih
        if ($statusFilter && $statusFilter !== 'semua') {
            $query->where('transaksi_penjualan.status', $statusFilter);
        }

        $transaksiPenjualan = $query->orderBy('transaksi_penjualan.tanggalPesan', 'desc')->paginate(10);

        // Hitung statistik untuk semua status
        $allTransactions = TransaksiPenjualan::where('idPembeli', $idPembeli)
            ->whereBetween('tanggalPesan', [$startDate, $endDate])
            ->get();

        $statistics = [
            'total' => $allTransactions->count(),
            'menunggu_pembayaran' => $allTransactions->where('status', 'menunggu_pembayaran')->count(),
            'menunggu_verifikasi' => $allTransactions->where('status', 'menunggu_verifikasi')->count(),
            'disiapkan' => $allTransactions->where('status', 'disiapkan')->count(),
            'kirim' => $allTransactions->where('status', 'kirim')->count(),
            'diambil' => $allTransactions->where('status', 'diambil')->count(),
            'terjual' => $allTransactions->where('status', 'terjual')->count(),
            'batal' => $allTransactions->where('status', 'batal')->count(),
            'bulan_ini' => $allTransactions->filter(function($t) {
                return \Carbon\Carbon::parse($t->tanggalPesan)->isCurrentMonth();
            })->count(),
            'total_belanja' => $allTransactions->where('status', '!=', 'batal')->sum(function($t) {
                return $t->detailTransaksiPenjualan->sum(function($detail) {
                    return $detail->produk->hargaJual ?? 0;
                });
            })
        ];

        // Tambahkan variabel debug untuk troubleshooting
        $debug = [
            'idPembeli' => $idPembeli,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'count' => $transaksiPenjualan->count(),
            'total' => $transaksiPenjualan->total(),
            'statusFilter' => $statusFilter
        ];

        return view('customer.pembeli.history', compact(
            'transaksiPenjualan', 
            'startDate', 
            'endDate', 
            'debug', 
            'statistics',
            'statusFilter'
        ));
    }
    /**
     * Menampilkan detail transaksi
     * UPDATED: Menambahkan data alamat pengiriman
     */
    public function detailTransaksi($idTransaksiPenjualan)
    {
        try {
            // Ambil ID pembeli dari session
            $idPembeli = session('user')['idPembeli'];

            // Dapatkan detail transaksi
            $transaksi = TransaksiPenjualan::findOrFail($idTransaksiPenjualan);

            // Pastikan transaksi ini milik pembeli yang login
            if ($transaksi->idPembeli !== $idPembeli) {
                return redirect()->route('pembeli.profile')->with('error', 'Anda tidak memiliki akses ke transaksi ini');
            }

            // Dapatkan produk yang dijual
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksiPenjualan)
                ->with('produk')
                ->get();

            // Dapatkan pembeli
            $pembeli = $transaksi->pembeli;
            
            // TAMBAHAN BARU: Parse alamat pengiriman
            $alamatPengiriman = null;
            if ($transaksi->alamatPengiriman) {
                $alamatPengiriman = json_decode($transaksi->alamatPengiriman, true);
            }

            return view('customer.pembeli.transaksi.detail', compact(
                'transaksi', 
                'detailTransaksi', 
                'pembeli',
                'alamatPengiriman' // TAMBAHAN BARU
            ));
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
            ->whereIn('transaksi_penjualan.status', ['diambil', 'dikirim',]) // Hanya yang sudah selesai
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

    // Perbaikan method storeRating untuk menangani tipe data yang benar

    public function storeRating(Request $request)
    {
        // Log input untuk debugging
        Log::info('Rating request received', [
            'idProduk' => $request->idProduk,
            'rating' => $request->rating,
            'types' => [
                'idProduk' => gettype($request->idProduk),
                'rating' => gettype($request->rating)
            ]
        ]);
        
        $validator = Validator::make($request->all(), [
            'idProduk' => 'required|integer|exists:produk,idProduk',  // Pastikan integer
            'rating' => 'required|numeric|min:1|max:5',              // Numeric untuk double
        ]);

        if ($validator->fails()) {
            Log::warning('Rating validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Pastikan user session ada
            if (!session('user') || !isset(session('user')['idPembeli'])) {
                Log::error('User session not found or invalid');
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak valid, silakan login ulang'
                ], 401);
            }
            
            $idPembeli = (int) session('user')['idPembeli'];           // Cast ke integer
            $idProduk = (int) $request->idProduk;                     // Cast ke integer  
            $ratingBaru = (float) $request->rating;                   // Cast ke float/double

            Log::info('Processing rating with correct types', [
                'idPembeli' => $idPembeli,
                'idProduk' => $idProduk,
                'rating' => $ratingBaru,
                'types' => [
                    'idPembeli' => gettype($idPembeli),
                    'idProduk' => gettype($idProduk),
                    'rating' => gettype($ratingBaru)
                ]
            ]);

            // Verifikasi apakah pembeli memang pernah membeli produk ini
            $transaksiExists = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
                ->where('transaksi_penjualan.idPembeli', $idPembeli)
                ->where('detail_transaksi_penjualan.idProduk', $idProduk)
                ->whereIn('transaksi_penjualan.status', ['diambil', 'kirim'])
                ->whereNotNull('transaksi_penjualan.tanggalLunas')
                ->exists();

            if (!$transaksiExists) {
                Log::warning('User tried to rate product they did not purchase', [
                    'idPembeli' => $idPembeli,
                    'idProduk' => $idProduk
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat memberikan rating untuk produk yang belum dibeli'
                ], 403);
            }

            // Update rating produk dengan tipe data yang benar
            $produk = Produk::findOrFail($idProduk);
            $oldRating = $produk->ratingProduk;
            
            // Pastikan rating disimpan sebagai double dengan 2 decimal places
            $produk->ratingProduk = round($ratingBaru, 2);
            $produk->save();

            Log::info('Rating updated successfully', [
                'idProduk' => $idProduk,
                'oldRating' => $oldRating,
                'newRating' => round($ratingBaru, 2),
                'ratingType' => gettype(round($ratingBaru, 2))
            ]);

            // Hitung ulang rata-rata rating penitip
            $this->updateRatingPenitip($idProduk);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan!',
                'data' => [
                    'idProduk' => $idProduk,
                    'newRating' => round($ratingBaru, 2)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving rating', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan rating: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update rata-rata rating penitip berdasarkan rating produk-produknya
     */
    private function updateRatingPenitip($idProduk)
    {
        try {
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

                if ($rataRating) {
                    // Update rating penitip dengan format double(8,2)
                    $formattedRating = round((float) $rataRating, 2);
                    
                    Penitip::where('idPenitip', $penitip->idPenitip)
                        ->update(['rating' => $formattedRating]);
                    
                    Log::info('Penitip rating updated', [
                        'idPenitip' => $penitip->idPenitip,
                        'newRating' => $formattedRating,
                        'ratingType' => gettype($formattedRating)
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating penitip rating: ' . $e->getMessage());
            // Tidak throw error karena ini tidak critical untuk proses utama
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
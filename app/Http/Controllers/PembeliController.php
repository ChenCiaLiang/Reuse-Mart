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
use Illuminate\Support\Facades\Auth;

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
                ->whereIn('transaksi_penjualan.status', ['diambil', 'dikirim'])
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

    /**
     * Menampilkan profil diri sendiri dan poin reward
     * Mobile API Endpoint: GET /api/pembeli/profile
     */
    public function getProfile()
    {
        try {
            // Ambil data pembeli yang sedang login
            $pembeli = Pembeli::find(Auth::id());

            // Jika pembeli tidak ditemukan, return error
            if (!$pembeli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembeli tidak ditemukan'
                ], 401);
            }

            // Format response data profil
            $profileData = [
                'idPembeli' => $pembeli->idPembeli,
                'nama' => $pembeli->nama,
                'email' => $pembeli->email,
                'poin' => $pembeli->poin,
                'total_transaksi' => $this->getTotalTransaksi($pembeli->idPembeli),  // Pastikan fungsi ini bekerja sesuai kebutuhan
                'total_pembelian' => $this->getTotalPembelian($pembeli->idPembeli)   // Sama dengan getTotalTransaksi
            ];

            // Response sukses dengan data profil
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diambil',
                'data' => $profileData
            ], 200);

        } catch (\Exception $e) {
            // Jika ada error, tangkap exception dan kembalikan pesan error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan history transaksi pembelian dan detailnya
     * Mobile API Endpoint: GET /api/pembeli/history-transaksi
     * Parameters: tanggal_mulai, tanggal_selesai (optional)
     */
    public function getHistoryTransaksi(Request $request)
    {
        try {
            // ✅ Debug untuk melihat apa yang terjadi
            Log::info('Auth Debug', [
                'auth_id' => Auth::id(),
                'auth_user_type' => get_class(Auth::user()),
                'auth_check' => Auth::check(),
                'guard' => Auth::getDefaultDriver()
            ]);
            
            // ✅ Langsung gunakan Auth::user()
            $pembeli = Auth::user();
            
            // ✅ Cek apakah user adalah instance Pembeli
            if (!$pembeli || !($pembeli instanceof \App\Models\Pembeli)) {
                Log::warning('Unauthorized access attempt', [
                    'user_type' => $pembeli ? get_class($pembeli) : 'null',
                    'user_id' => $pembeli ? $pembeli->id : 'null'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            Log::info('Pembeli authenticated', [
                'pembeli_id' => $pembeli->idPembeli,
                'pembeli_email' => $pembeli->email
            ]);

            // Validasi input tanggal lunas jika ada
            $validator = Validator::make($request->all(), [
                'tanggal_lunas_mulai' => 'nullable|date|date_format:Y-m-d',
                'tanggal_lunas_selesai' => 'nullable|date|date_format:Y-m-d|after_or_equal:tanggal_lunas_mulai'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Query transaksi penjualan pembeli
            $query = TransaksiPenjualan::where('idPembeli', $pembeli->idPembeli)
                ->with(['detailTransaksiPenjualan.produk.kategori'])
                ->orderBy('tanggalPesan', 'desc');

            // Filter berdasarkan periode tanggal lunas jika ada
            if ($request->tanggal_lunas_mulai && $request->tanggal_lunas_selesai) {
                $tanggalLunasMulai = Carbon::parse($request->tanggal_lunas_mulai)->startOfDay();
                $tanggalLunasSelesai = Carbon::parse($request->tanggal_lunas_selesai)->endOfDay();
                
                $query->whereBetween('tanggalLunas', [$tanggalLunasMulai, $tanggalLunasSelesai]);
            } elseif ($request->tanggal_lunas_mulai) {
                $tanggalLunasMulai = Carbon::parse($request->tanggal_lunas_mulai)->startOfDay();
                $query->where('tanggalLunas', '>=', $tanggalLunasMulai);
            } elseif ($request->tanggal_lunas_selesai) {
                $tanggalLunasSelesai = Carbon::parse($request->tanggal_lunas_selesai)->endOfDay();
                $query->where('tanggalLunas', '<=', $tanggalLunasSelesai);
            }

            $transaksi = $query->get();

            Log::info('Query result', [
                'transaksi_count' => $transaksi->count()
            ]);

            // Format data transaksi
            $historyData = $transaksi->map(function ($trans) {
                return [
                    'idTransaksiPenjualan' => $trans->idTransaksiPenjualan,
                    'tanggal_pesan' => $trans->tanggalPesan ? Carbon::parse($trans->tanggalPesan)->format('d/m/Y H:i') : null,
                    'tanggal_lunas' => $trans->tanggalLunas ? Carbon::parse($trans->tanggalLunas)->format('d/m/Y H:i') : null,
                    'tanggal_kirim' => $trans->tanggalKirim ? Carbon::parse($trans->tanggalKirim)->format('d/m/Y H:i') : null,
                    'tanggal_ambil' => $trans->tanggalAmbil ? Carbon::parse($trans->tanggalAmbil)->format('d/m/Y H:i') : null,
                    'status' => $this->getStatusTransaksi($trans),
                    'metode_pengiriman' => $trans->metodePengiriman,
                    'alamat_pengiriman' => $this->formatAlamatPengiriman($trans->alamatPengiriman),
                    'poin_didapat' => $trans->poinDidapat ?? 0,
                    'poin_digunakan' => $trans->poinDigunakan ?? 0,
                    'total_harga' => $this->calculateTotalHarga($trans),
                    'ongkos_kirim' => $this->calculateOngkosKirim($trans),
                    'total_bayar' => $this->calculateTotalBayar($trans),
                    'detail_produk' => $this->formatDetailProduk($trans->detailTransaksiPenjualan)
                ];
            });

            // Summary data
            $summary = [
                'total_transaksi' => $historyData->count(),
                'filter_tanggal_lunas' => [
                    'tanggal_lunas_mulai' => $request->tanggal_lunas_mulai,
                    'tanggal_lunas_selesai' => $request->tanggal_lunas_selesai
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'History transaksi berhasil diambil',
                'summary' => $summary,
                'data' => $historyData
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getHistoryTransaksi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Helper Functions
     */

    private function getTotalTransaksi($idPembeli)
    {
        return TransaksiPenjualan::where('idPembeli', $idPembeli)->count();
    }

    private function getTotalPembelian($idPembeli)
    {
        return TransaksiPenjualan::where('idPembeli', $idPembeli)
            ->whereIn('status', ['terjual', 'kirim', 'diambil'])
            ->with('detailTransaksiPenjualan.produk')
            ->get()
            ->sum(function ($transaksi) {
                return $this->calculateTotalBayar($transaksi);
            });
    }

    private function getStatusTransaksi($transaksi)
    {
        $status = $transaksi->status;
        $statusLabels = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'dibayar' => 'Sudah Dibayar',
            'disiapkan' => 'Sedang Disiapkan',
            'kirim' => 'Sedang Dikirim',
            'diambil' => 'Sudah Diambil',
            'terjual' => 'Selesai',
            'batal' => 'Dibatalkan',
            'hangus' => 'Hangus'
        ];

        return [
            'code' => $status,
            'label' => $statusLabels[$status] ?? $status
        ];
    }

    private function formatAlamatPengiriman($alamatPengiriman)
    {
        if (!$alamatPengiriman) {
            return null;
        }

        $alamat = is_string($alamatPengiriman) ? json_decode($alamatPengiriman, true) : $alamatPengiriman;
        
        return [
            'jenis' => $alamat['jenis'] ?? null,
            'alamat_lengkap' => $alamat['alamatLengkap'] ?? null,
            'id_alamat' => $alamat['idAlamat'] ?? null
        ];
    }

    private function calculateTotalHarga($transaksi)
    {
        return $transaksi->detailTransaksiPenjualan->sum(function ($detail) {
            return $detail->produk->hargaJual ?? 0;
        });
    }

    private function calculateOngkosKirim($transaksi)
    {
        $totalHarga = $this->calculateTotalHarga($transaksi);
        
        // Berdasarkan requirements: gratis jika >= 1.5 juta, 100rb jika < 1.5 juta
        if ($transaksi->metodePengiriman === 'kurir') {
            return $totalHarga >= 1500000 ? 0 : 100000;
        }
        
        return 0; // Ambil sendiri gratis
    }

    private function calculateTotalBayar($transaksi)
    {
        $totalHarga = $this->calculateTotalHarga($transaksi);
        $ongkosKirim = $this->calculateOngkosKirim($transaksi);
        $potonganPoin = ($transaksi->poinDigunakan ?? 0) * 100; // 1 poin = Rp 100
        
        return $totalHarga + $ongkosKirim - $potonganPoin;
    }

    private function formatDetailProduk($detailTransaksi)
    {
        return $detailTransaksi->map(function ($detail) {
            $produk = $detail->produk;
            $gambar = $produk->gambar ? explode(',', $produk->gambar)[0] : null;
            
            return [
                'idProduk' => $produk->idProduk,
                'nama' => $produk->deskripsi,
                'harga_jual' => $produk->hargaJual,
                'kategori' => $produk->kategori->nama ?? null,
                'gambar_utama' => $gambar,
                'status_garansi' => $produk->tanggalGaransi ? 
                    (Carbon::parse($produk->tanggalGaransi)->isFuture() ? 'Bergaransi' : 'Tidak Bergaransi') : 
                    'Tidak Bergaransi',
                'tanggal_garansi' => $produk->tanggalGaransi ? 
                    Carbon::parse($produk->tanggalGaransi)->format('d/m/Y') : null
            ];
        });
    }
}
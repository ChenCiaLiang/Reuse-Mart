<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MerchandiseController extends Controller
{
    /**
     * Menampilkan halaman pengelolaan klaim merchandise untuk CS
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'semua');

        $query = DB::table('penukaran')
            ->join('pembeli', 'penukaran.idPembeli', '=', 'pembeli.idPembeli')
            ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
            ->select(
                'penukaran.idPenukaran',
                'pembeli.nama as namaPembeli',
                'pembeli.email',
                'merchandise.nama as namaMerchandise',
                'merchandise.jumlahPoin',
                'penukaran.tanggalPengajuan',
                'penukaran.tanggalPenerimaan',
                'penukaran.statusPenukaran' // Menggunakan status bahasa Indonesia
            )
            ->orderBy('penukaran.tanggalPengajuan', 'desc');

        // Filter berdasarkan statusPenukaran bahasa Indonesia
        if ($status !== 'semua') {
            if ($status === 'belum_diambil') {
                $query->where('penukaran.statusPenukaran', 'belum diambil');
            } elseif ($status === 'sudah_diambil') {
                $query->where('penukaran.statusPenukaran', 'sudah diambil');
            }
        }

        // Filter berdasarkan search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pembeli.nama', 'LIKE', "%{$search}%")
                    ->orWhere('pembeli.email', 'LIKE', "%{$search}%")
                    ->orWhere('merchandise.nama', 'LIKE', "%{$search}%");
            });
        }

        $klaimMerchandise = $query->paginate(10);

        // Hitung statistik berdasarkan statusPenukaran
        $stats = $this->getStatistik();

        return view('pegawai.cs.merchandise.index', compact('klaimMerchandise', 'search', 'status', 'stats'));
    }

    /**
     * Menampilkan detail klaim merchandise
     */
    public function show($id)
    {
        $klaim = DB::table('penukaran')
            ->join('pembeli', 'penukaran.idPembeli', '=', 'pembeli.idPembeli')
            ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
            ->select(
                'penukaran.*',
                'pembeli.nama as namaPembeli',
                'pembeli.email',
                'merchandise.nama as namaMerchandise',
                'merchandise.jumlahPoin'
            )
            ->where('penukaran.idPenukaran', $id)
            ->first();

        if (!$klaim) {
            return redirect()->route('cs.merchandise.index')
                ->with('error', 'Data klaim merchandise tidak ditemukan.');
        }

        return view('pegawai.cs.merchandise.show', compact('klaim'));
    }

    /**
     * Menampilkan form konfirmasi pengambilan merchandise
     */
    public function konfirmasiForm($id)
    {
        $klaim = DB::table('penukaran')
            ->join('pembeli', 'penukaran.idPembeli', '=', 'pembeli.idPembeli')
            ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
            ->select(
                'penukaran.*',
                'pembeli.nama as namaPembeli',
                'pembeli.email',
                'merchandise.nama as namaMerchandise',
                'merchandise.jumlahPoin'
            )
            ->where('penukaran.idPenukaran', $id)
            ->where('penukaran.statusPenukaran', 'belum diambil') // Status bahasa Indonesia dengan spasi
            ->first();

        if (!$klaim) {
            return redirect()->route('cs.merchandise.index')
                ->with('error', 'Data klaim tidak ditemukan atau sudah dikonfirmasi.');
        }

        return view('pegawai.cs.merchandise.konfirmasi', compact('klaim'));
    }

    /**
     * Memproses konfirmasi pengambilan merchandise
     * Fungsionalitas: tanggal dan status terupdate
     */
    public function konfirmasiPengambilan(Request $request, $id)
    {
        $request->validate([
            'tanggalPenerimaan' => 'required|date',
            'catatan' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah klaim ada dan statusnya masih belum diambil
            $penukaran = DB::table('penukaran')
                ->where('idPenukaran', $id)
                ->where('statusPenukaran', 'belum diambil')
                ->first();

            if (!$penukaran) {
                return redirect()->route('cs.merchandise.index')
                    ->with('error', 'Data klaim tidak ditemukan atau sudah pernah dikonfirmasi.');
            }

            // Update tanggal penerimaan dan status
            DB::table('penukaran')
                ->where('idPenukaran', $id)
                ->update([
                    'tanggalPenerimaan' => $request->tanggalPenerimaan,
                    'statusPenukaran' => 'sudah diambil', // Update ke status bahasa Indonesia dengan spasi
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('cs.merchandise.index')
                ->with('success', 'Pengambilan merchandise berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('cs.merchandise.index')
                ->with('error', 'Terjadi kesalahan saat mengkonfirmasi pengambilan merchandise.');
        }
    }

    /**
     * Mendapatkan statistik klaim merchandise berdasarkan statusPenukaran
     */
    private function getStatistik()
    {
        $total = DB::table('penukaran')->count();
        $belumDiambil = DB::table('penukaran')->where('statusPenukaran', 'belum diambil')->count();
        $sudahDiambil = DB::table('penukaran')->where('statusPenukaran', 'sudah diambil')->count();

        return [
            'total' => $total,
            'belum_diambil' => $belumDiambil,
            'sudah_diambil' => $sudahDiambil
        ];
    }

    /**
     * Mendapatkan daftar merchandise yang tersedia
     */
    public function getMerchandiseList()
    {
        $merchandise = DB::table('merchandise')
            ->select('idMerchandise', 'nama', 'jumlahPoin', 'stok')
            ->where('stok', '>', 0)
            ->orderBy('jumlahPoin', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $merchandise
        ]);
    }

    /**
     * API untuk mendapatkan data klaim dengan filter status
     */
    public function getKlaimData(Request $request)
    {
        $status = $request->get('status', 'semua');

        $query = DB::table('penukaran')
            ->join('pembeli', 'penukaran.idPembeli', '=', 'pembeli.idPembeli')
            ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
            ->select(
                'penukaran.idPenukaran',
                'pembeli.nama as namaPembeli',
                'pembeli.email',
                'merchandise.nama as namaMerchandise',
                'merchandise.jumlahPoin',
                'penukaran.tanggalPengajuan',
                'penukaran.tanggalPenerimaan',
                'penukaran.statusPenukaran'
            );

        // Filter berdasarkan statusPenukaran bahasa Indonesia
        if ($status !== 'semua') {
            if ($status === 'belum_diambil') {
                $query->where('penukaran.statusPenukaran', 'belum diambil');
            } elseif ($status === 'sudah_diambil') {
                $query->where('penukaran.statusPenukaran', 'sudah diambil');
            }
        }

        $data = $query->orderBy('penukaran.tanggalPengajuan', 'desc')->get();
        $stats = $this->getStatistik();

        return response()->json([
            'success' => true,
            'data' => $data,
            'stats' => $stats
        ]);
    }

    /**
     * Helper method untuk mendapatkan class CSS badge berdasarkan status
     */
    public function getStatusBadgeClass($status)
    {
        $classes = [
            'belum diambil' => 'bg-yellow-100 text-yellow-800',
            'sudah diambil' => 'bg-green-100 text-green-800',
            'dibatalkan' => 'bg-red-100 text-red-800'
        ];

        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Helper method untuk mendapatkan icon berdasarkan status
     */
    public function getStatusIcon($status)
    {
        $icons = [
            'belum diambil' => 'fas fa-clock',
            'sudah diambil' => 'fas fa-check',
            'dibatalkan' => 'fas fa-times'
        ];

        return $icons[$status] ?? 'fas fa-question';
    }
    /**
     * Melihat katalog merchandise (Mobile)
     * Fungsionalitas: Ada fotonya
     */
    public function katalog(Request $request)
    {
        try {
            $merchandise = DB::table('merchandise')
                ->select('idMerchandise', 'nama', 'jumlahPoin', 'stok')
                ->where('stok', '>', 0)
                ->orderBy('jumlahPoin', 'asc')
                ->get();

            // Transform data untuk mobile
            $merchandiseData = $merchandise->map(function ($item) {
                return [
                    'idMerchandise' => $item->idMerchandise,
                    'nama' => $item->nama,
                    'jumlahPoin' => $item->jumlahPoin,
                    'stok' => $item->stok,
                    'gambar' => $this->getMerchandiseImage($item->idMerchandise),
                    'tersedia' => $item->stok > 0
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Katalog merchandise berhasil diambil',
                'data' => $merchandiseData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil katalog merchandise',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melakukan Klaim merchandise (Mobile)
     * Fungsionalitas: Jika poin cukup, maka poin berkurang, tersimpan merch yang diklaim, 
     * stok merch berkurang, tanggal klaim tercatat.
     */
    public function klaimMerchandise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idMerchandise' => 'required|integer|exists:merchandise,idMerchandise'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cek authentication (asumsi menggunakan JWT atau Sanctum)
            $user = Auth::guard('sanctum')->user();
            if (!$user || !isset($user->idPembeli)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Silakan login terlebih dahulu.'
                ], 401);
            }

            $idPembeli = $user->idPembeli;
            $idMerchandise = $request->idMerchandise;

            // Ambil data merchandise
            $merchandise = DB::table('merchandise')
                ->where('idMerchandise', $idMerchandise)
                ->first();

            if (!$merchandise) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Merchandise tidak ditemukan'
                ], 404);
            }

            // Cek stok merchandise
            if ($merchandise->stok <= 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, stok merchandise habis'
                ], 400);
            }

            // Ambil data pembeli
            $pembeli = DB::table('pembeli')
                ->where('idPembeli', $idPembeli)
                ->first();

            if (!$pembeli) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembeli tidak ditemukan'
                ], 404);
            }

            // Cek poin pembeli
            if ($pembeli->poin < $merchandise->jumlahPoin) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Poin Anda tidak mencukupi',
                    'data' => [
                        'poinDibutuhkan' => $merchandise->jumlahPoin,
                        'poinAnda' => $pembeli->poin,
                        'kekurangan' => $merchandise->jumlahPoin - $pembeli->poin
                    ]
                ], 400);
            }

            // 1. Kurangi poin pembeli
            $poinBaru = $pembeli->poin - $merchandise->jumlahPoin;
            DB::table('pembeli')
                ->where('idPembeli', $idPembeli)
                ->update([
                    'poin' => $poinBaru,
                    'updated_at' => now()
                ]);

            // 2. Kurangi stok merchandise  
            $stokBaru = $merchandise->stok - 1;
            DB::table('merchandise')
                ->where('idMerchandise', $idMerchandise)
                ->update([
                    'stok' => $stokBaru,
                    'updated_at' => now()
                ]);

            // 3. Simpan transaksi klaim ke table penukaran
            $idPenukaran = DB::table('penukaran')->insertGetId([
                'tanggalPengajuan' => now(),
                'tanggalPenerimaan' => null,
                'idMerchandise' => $idMerchandise,
                'idPembeli' => $idPembeli,
                'statusPenukaran' => 'belum diambil',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merchandise berhasil diklaim! Silakan ambil di Customer Service ReUseMart.',
                'data' => [
                    'idPenukaran' => $idPenukaran,
                    'merchandise' => [
                        'nama' => $merchandise->nama,
                        'gambar' => $this->getMerchandiseImage($idMerchandise)
                    ],
                    'poinDigunakan' => $merchandise->jumlahPoin,
                    'sisaPoin' => $poinBaru,
                    'tanggalKlaim' => now()->format('Y-m-d H:i:s'),
                    'statusPenukaran' => 'belum diambil',
                    'instruksi' => 'Silakan datang ke Customer Service ReUseMart untuk mengambil merchandise Anda dengan membawa identitas diri.'
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan klaim merchandise',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melihat history klaim merchandise pembeli (Mobile)
     */
    public function historyKlaim(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user || !isset($user->idPembeli)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Silakan login terlebih dahulu.'
                ], 401);
            }

            $idPembeli = $user->idPembeli;

            $historyKlaim = DB::table('penukaran')
                ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
                ->select(
                    'penukaran.idPenukaran',
                    'merchandise.nama as namaMerchandise',
                    'merchandise.jumlahPoin',
                    'penukaran.tanggalPengajuan',
                    'penukaran.tanggalPenerimaan',
                    'penukaran.statusPenukaran'
                )
                ->where('penukaran.idPembeli', $idPembeli)
                ->orderBy('penukaran.tanggalPengajuan', 'desc')
                ->get();

            // Transform data untuk mobile
            $historyData = $historyKlaim->map(function ($item) {
                return [
                    'idPenukaran' => $item->idPenukaran,
                    'namaMerchandise' => $item->namaMerchandise,
                    'jumlahPoin' => $item->jumlahPoin,
                    'tanggalPengajuan' => $item->tanggalPengajuan,
                    'tanggalPenerimaan' => $item->tanggalPenerimaan,
                    'statusPenukaran' => $item->statusPenukaran,
                    'statusLabel' => $this->getStatusLabel($item->statusPenukaran),
                    'gambar' => $this->getMerchandiseImageByName($item->namaMerchandise)
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'History klaim berhasil diambil',
                'data' => $historyData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil history klaim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melihat detail klaim merchandise (Mobile)
     */
    public function detailKlaim(Request $request, $idPenukaran)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user || !isset($user->idPembeli)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Silakan login terlebih dahulu.'
                ], 401);
            }

            $idPembeli = $user->idPembeli;

            $detailKlaim = DB::table('penukaran')
                ->join('merchandise', 'penukaran.idMerchandise', '=', 'merchandise.idMerchandise')
                ->select(
                    'penukaran.*',
                    'merchandise.nama as namaMerchandise',
                    'merchandise.jumlahPoin'
                )
                ->where('penukaran.idPenukaran', $idPenukaran)
                ->where('penukaran.idPembeli', $idPembeli)
                ->first();

            if (!$detailKlaim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data klaim tidak ditemukan'
                ], 404);
            }

            $detailData = [
                'idPenukaran' => $detailKlaim->idPenukaran,
                'merchandise' => [
                    'nama' => $detailKlaim->namaMerchandise,
                    'jumlahPoin' => $detailKlaim->jumlahPoin,
                    'gambar' => $this->getMerchandiseImage($detailKlaim->idMerchandise)
                ],
                'tanggalPengajuan' => $detailKlaim->tanggalPengajuan,
                'tanggalPenerimaan' => $detailKlaim->tanggalPenerimaan,
                'statusPenukaran' => $detailKlaim->statusPenukaran,
                'statusLabel' => $this->getStatusLabel($detailKlaim->statusPenukaran),
                'catatan' => $this->getCatatanStatus($detailKlaim->statusPenukaran)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail klaim berhasil diambil',
                'data' => $detailData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail klaim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melihat poin pembeli (Mobile)
     */
    public function getPoin(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user || !isset($user->idPembeli)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Silakan login terlebih dahulu.'
                ], 401);
            }

            $pembeli = DB::table('pembeli')
                ->select('nama', 'poin')
                ->where('idPembeli', $user->idPembeli)
                ->first();

            if (!$pembeli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembeli tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data poin berhasil diambil',
                'data' => [
                    'nama' => $pembeli->nama,
                    'poin' => $pembeli->poin
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data poin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method untuk mendapatkan gambar merchandise
     */
    private function getMerchandiseImage($idMerchandise)
    {
        // Mapping gambar merchandise berdasarkan nama
        $imageMap = [
            1 => 'ballpoin.jpg',      // Ballpoin ReUseMart
            2 => 'stiker.jpg',        // Stiker ReUseMart  
            3 => 'mug.jpg',           // Mug ReUseMart
            4 => 'topi.jpg',          // Topi ReUseMart
            5 => 'tumbler.jpg',       // Tumbler ReUseMart
            6 => 'tshirt.jpg',        // T-Shirt ReUseMart
            7 => 'jam_dinding.jpg',   // Jam Dinding ReUseMart
            8 => 'tas_travel.jpg',    // Tas Travel ReUseMart
            9 => 'payung.jpg'         // Payung ReUseMart
        ];

        $imageName = $imageMap[$idMerchandise] ?? 'default.jpg';
        return ('assets/images/' . $imageName);
    }

    /**
     * Helper method untuk mendapatkan gambar merchandise berdasarkan nama
     */
    private function getMerchandiseImageByName($namaMerchandise)
    {
        $imageMap = [
            'Ballpoin ReUseMart' => 'ballpoin.jpg',
            'Stiker ReUseMart' => 'stiker.jpg',
            'Mug ReUseMart' => 'mug.jpg',
            'Topi ReUseMart' => 'topi.jpg',
            'Tumbler ReUseMart' => 'tumbler.jpg',
            'T-Shirt ReUseMart' => 'tshirt.jpg',
            'Jam Dinding ReUseMart' => 'jam_dinding.jpg',
            'Tas Travel ReUseMart' => 'tas_travel.jpg',
            'Payung ReUseMart' => 'payung.jpg'
        ];

        $imageName = $imageMap[$namaMerchandise] ?? 'default.jpg';
        return url('storage/merchandise/' . $imageName);
    }

    /**
     * Helper method untuk mendapatkan label status
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'belum diambil' => 'Belum Diambil',
            'sudah diambil' => 'Sudah Diambil',
            'dibatalkan' => 'Dibatalkan'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Helper method untuk mendapatkan catatan status
     */
    private function getCatatanStatus($status)
    {
        $catatan = [
            'belum diambil' => 'Silakan datang ke Customer Service ReUseMart untuk mengambil merchandise Anda. Bawa identitas diri sebagai bukti.',
            'sudah diambil' => 'Merchandise telah berhasil diambil. Terima kasih!',
            'dibatalkan' => 'Klaim merchandise telah dibatalkan.'
        ];

        return $catatan[$status] ?? '';
    }
}

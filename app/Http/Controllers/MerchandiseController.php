<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $query->where(function($q) use ($search) {
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
     * Helper method untuk mendapatkan label status dalam bahasa Indonesia
     */
    public function getStatusLabel($status)
    {
        $labels = [
            'belum diambil' => 'Belum Diambil',
            'sudah diambil' => 'Sudah Diambil',
            'dibatalkan' => 'Dibatalkan'
        ];

        return $labels[$status] ?? ucfirst($status);
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
}
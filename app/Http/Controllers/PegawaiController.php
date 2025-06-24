<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Komisi;
use App\Models\Organisasi;
use App\Models\Pegawai;
use App\Models\Pembeli;
use App\Models\Penitip;
use App\Models\Produk;
use App\Models\TransaksiDonasi;
use App\Models\TransaksiPenitipan;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $pegawai = Pegawai::when($search, function ($query) use ($search) {
            return $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('noTelp', 'like', '%' . $search . '%')
                ->orWhere('alamat', 'like', '%' . $search . '%')
                ->orWhere('tanggalLahir', 'like', '%' . $search . '%')
                ->orWhereHas('jabatan', function ($jq) use ($search) {
                    $jq->where('nama', 'like', '%' . $search . '%');
                });
        })
            ->orderBy('idPegawai')
            ->paginate(10);

        return view('pegawai.admin.manajemenPegawai.index', compact('pegawai', 'search'));
    }

    public function create()
    {
        $jabatan = Jabatan::all();
        return view('pegawai.admin.manajemenPegawai.create', compact('jabatan'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email|unique:pegawai,email',
            'password' => 'required|string|min:6|confirmed',
            'noTelp' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
            'tanggalLahir' => 'required|date',
            'idJabatan' => 'required|exists:jabatan,idJabatan',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $pegawai = Pegawai::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'noTelp' => $request->noTelp,
            'alamat' => $request->alamat,
            'tanggalLahir' => $request->tanggalLahir,
            'idJabatan' => $request->idJabatan,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pegawai berhasil ditambahkan',
                'data' => $pegawai
            ], 201);
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan');
    }

    public function show($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('pegawai.admin.manajemenPegawai.show', compact('pegawai'));
    }

    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $jabatan = Jabatan::orderBy('nama')->get();
        return view('pegawai.admin.manajemenPegawai.edit', compact('pegawai', 'jabatan'));
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email|unique:pegawai,email,' . $id . ',idPegawai',
            'password' => 'nullable|string|min:6|confirmed',
            'noTelp' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
            'tanggalLahir' => 'required|date',
            'idJabatan' => 'required|exists:jabatan,idJabatan',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $pegawai->nama = $request->nama;
        $pegawai->email = $request->email;
        if ($request->filled('password')) {
            $pegawai->password = Hash::make($request->password);
        }
        $pegawai->noTelp = $request->noTelp;
        $pegawai->alamat = $request->alamat;
        $pegawai->tanggalLahir = $request->tanggalLahir;
        $pegawai->idJabatan = $request->idJabatan;
        $pegawai->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data pegawai berhasil diperbarui',
                'data' => $pegawai
            ]);
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pegawai berhasil dihapus'
            ]);
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus');
    }
    public function adminDashboard()
    {
        $totalPegawai = Pegawai::count();
        $totalPenitip = Penitip::count();
        $totalPembeli = Pembeli::count();
        $totalOrganisasi = Organisasi::count();

        $totalProduk = Produk::count();
        $produkTersedia = Produk::where('status', '!=', 'Terjual')
            ->where('status', '!=', 'Didonasikan')
            ->count();
        $produkTerjual = Produk::where('status', 'Terjual')->count();
        $produkDidonasikan = Produk::where('status', 'Didonasikan')->count();

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

        $chartData = [];
        $namaBulan = [
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

        for ($i = 1; $i <= 12; $i++) {
            $chartData[$i] = [
                'bulan' => $namaBulan[$i],
                'total_penjualan' => 0
            ];
        }

        foreach ($penjualanPerBulan as $data) {
            $chartData[$data->bulan]['total_penjualan'] = $data->total_penjualan;
        }

        $chartData = array_values($chartData);

        $produkTerlaris = DB::table('detail_transaksi_penjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('transaksi_penjualan', 'detail_transaksi_penjualan.idTransaksiPenjualan', '=', 'transaksi_penjualan.idTransaksiPenjualan')
            ->whereNotNull('transaksi_penjualan.tanggalLunas')
            ->select('produk.idProduk', 'produk.deskripsi', DB::raw('COUNT(*) as total_terjual'))
            ->groupBy('produk.idProduk', 'produk.deskripsi')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->get();

        $transaksiTerakhir = TransaksiPenjualan::with(['pembeli', 'detailTransaksiPenjualan.produk'])
            ->whereNotNull('tanggalLunas')
            ->orderBy('tanggalLunas', 'desc')
            ->limit(5)
            ->get();

        $barangHampirHabis = TransaksiPenitipan::with(['penitip', 'detailTransaksiPenitipan.produk'])
            ->where('tanggalAkhirPenitipan', '>=', Carbon::now())
            ->where('tanggalAkhirPenitipan', '<=', Carbon::now()->addDays(7))
            ->where('statusPenitipan', '!=', 'Selesai')
            ->orderBy('tanggalAkhirPenitipan')
            ->limit(5)
            ->get();

        return view('pegawai.admin.dashboard', compact(
            'totalPegawai',
            'totalPenitip',
            'totalPembeli',
            'totalOrganisasi',
            'totalProduk',
            'produkTersedia',
            'produkTerjual',
            'produkDidonasikan',
            'penjualanBulanIni',
            'penitipanBulanIni',
            'donasiBulanIni',
            'chartData',
            'produkTerlaris',
            'transaksiTerakhir',
            'barangHampirHabis'
        ));
    }

    /**
     * Get Hunter Profile with total komisi
     */
    public function getHunterProfile()
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah Hunter (jabatan ID = 5)
            if (!$user || $user->idJabatan != 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Hunter access required.'
                ], 403);
            }

            // Ambil data hunter
            $hunter = Pegawai::with('jabatan')
                ->where('idPegawai', $user->idPegawai)
                ->first();

            if (!$hunter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hunter not found'
                ], 404);
            }

            // Hitung total komisi hunter
            // $totalKomisi = Komisi::where('idPegawai', $user->idPegawai)
            //     ->sum('komisiHunter');

            // Hitung jumlah transaksi yang ada komisinya
            $totalTransaksi = Komisi::where('idPegawai', $user->idPegawai)
                ->where('komisiHunter', '>', 0)
                ->count();

            // Komisi bulan ini
            $komisiBulanIni = Komisi::where('idPegawai', $user->idPegawai)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('komisiHunter');

            $data = [
                'idPegawai' => $hunter->idPegawai,
                'nama' => $hunter->nama,
                'email' => $hunter->email,
                'noTelp' => $hunter->noTelp,
                'alamat' => $hunter->alamat,
                'tanggalLahir' => $hunter->tanggalLahir,
                'jabatan' => $hunter->jabatan->nama ?? 'Hunter',
                'totalKomisi' => (float) $hunter->komisi,
                'komisiBulanIni' => (float) $komisiBulanIni,
                'totalTransaksi' => $totalTransaksi,
                'joinDate' => $hunter->created_at ? $hunter->created_at->format('Y-m-d') : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Hunter profile retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving hunter profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Hunter History Komisi (All)
     */
    public function getHunterHistoryKomisi()
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah Hunter (jabatan ID = 5)
            if (!$user || $user->idJabatan != 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Hunter access required.'
                ], 403);
            }

            // Base query - ambil semua komisi hunter
            $query = Komisi::with([
                'detailTransaksiPenjualan.produk.kategori',
                'detailTransaksiPenjualan.transaksiPenjualan',
                'penitip'
            ])
                ->where('idPegawai', $user->idPegawai)
                ->where('komisiHunter', '>', 0);

            // Get summary data
            $totalKomisi = (clone $query)->sum('komisiHunter');
            $totalTransaksi = (clone $query)->count();

            // Get all data, ordered by latest
            $historyKomisi = $query->orderBy('created_at', 'desc')
                ->get();

            // Format data
            $formattedHistory = $historyKomisi->map(function ($komisi) {
                $produk = $komisi->detailTransaksiPenjualan->produk ?? null;
                $transaksi = $komisi->detailTransaksiPenjualan->transaksiPenjualan ?? null;

                return [
                    'idKomisi' => $komisi->idDetailTransaksiPenjualan,
                    'tanggal' => $komisi->created_at,
                    'komisiHunter' => (float) $komisi->komisiHunter,
                    'komisiReuse' => (float) $komisi->komisiReuse,
                    'komisiPenitip' => (float) $komisi->komisiPenitip,
                    'produk' => [
                        'idProduk' => $produk->idProduk ?? null,
                        'nama' => $produk->deskripsi ?? 'Produk tidak ditemukan',
                        'hargaJual' => $produk ? (float) $produk->hargaJual : 0,
                        'kategori' => $produk->kategori->nama ?? null,
                        'gambarUtama' => $produk && $produk->gambar ?
                            explode(',', $produk->gambar)[0] : null,
                    ],
                    'penitip' => [
                        'idPenitip' => $komisi->penitip->idPenitip ?? null,
                        'nama' => $komisi->penitip->nama ?? 'Penitip tidak ditemukan',
                    ],
                    'transaksi' => [
                        'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan ?? null,
                        'tanggalLaku' => $transaksi && $transaksi->tanggalLaku ?
                            $transaksi->tanggalLaku->format('Y-m-d H:i:s') : null,
                        'status' => $transaksi->status ?? null,
                    ]
                ];
            });

            // Create summary
            $summary = [
                'totalKomisi' => (float) $totalKomisi,
                'totalTransaksi' => $totalTransaksi,
                'rataRataKomisi' => $totalTransaksi > 0 ? (float) ($totalKomisi / $totalTransaksi) : 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Hunter history komisi retrieved successfully',
                'summary' => $summary,
                'data' => $formattedHistory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving hunter history komisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Hunter Stats Dashboard
     */
    public function getHunterStats()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->idJabatan != 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Hunter access required.'
                ], 403);
            }

            // Komisi hari ini
            $komisiHariIni = Komisi::where('idPegawai', $user->idPegawai)
                ->whereDate('created_at', today())
                ->sum('komisiHunter');

            // Komisi minggu ini
            $komisiMingguIni = Komisi::where('idPegawai', $user->idPegawai)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('komisiHunter');

            // Komisi bulan ini
            $komisiBulanIni = Komisi::where('idPegawai', $user->idPegawai)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('komisiHunter');

            // Total komisi all time
            $totalKomisi = Komisi::where('idPegawai', $user->idPegawai)
                ->sum('komisiHunter');

            // Transaksi bulan ini
            $transaksiBulanIni = Komisi::where('idPegawai', $user->idPegawai)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('komisiHunter', '>', 0)
                ->count();

            $stats = [
                'komisiHariIni' => (float) $komisiHariIni,
                'komisiMingguIni' => (float) $komisiMingguIni,
                'komisiBulanIni' => (float) $komisiBulanIni,
                'totalKomisi' => (float) $totalKomisi,
                'transaksiBulanIni' => $transaksiBulanIni,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Hunter stats retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving hunter stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsionalitas 117: Menampilkan profil diri sendiri (Kurir)
     */
    public function getKurirProfile()
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah Kurir (jabatan ID = 6)
            if (!$user || $user->idJabatan != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Kurir access required.'
                ], 403);
            }

            // Ambil data kurir
            $kurir = Pegawai::with('jabatan')
                ->where('idPegawai', $user->idPegawai)
                ->first();

            if (!$kurir) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kurir not found'
                ], 404);
            }

            // Hitung statistik pengiriman
            $totalPengiriman = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereNotNull('tanggalKirim')
                ->count();

            $pengirimanBulanIni = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereNotNull('tanggalKirim')
                ->whereMonth('tanggalKirim', now()->month)
                ->whereYear('tanggalKirim', now()->year)
                ->count();

            $pengirimanHariIni = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereNotNull('tanggalKirim')
                ->whereDate('tanggalKirim', today())
                ->count();

            $data = [
                'idPegawai' => $kurir->idPegawai,
                'nama' => $kurir->nama,
                'email' => $kurir->email,
                'noTelp' => $kurir->noTelp,
                'alamat' => $kurir->alamat,
                'tanggalLahir' => $kurir->tanggalLahir,
                'jabatan' => $kurir->jabatan->nama ?? 'Kurir',
                'joinDate' => $kurir->created_at ? $kurir->created_at->format('Y-m-d') : null,
                'totalPengiriman' => $totalPengiriman,
                'pengirimanBulanIni' => $pengirimanBulanIni,
                'pengirimanHariIni' => $pengirimanHariIni,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Kurir profile retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving kurir profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsionalitas 118: Menampilkan history tugas pengiriman (Kurir)
     */
    public function getKurirHistoryPengiriman(Request $request)
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah Kurir (jabatan ID = 6)
            if (!$user || $user->idJabatan != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Kurir access required.'
                ], 403);
            }

            // Get filters
            $tanggalMulai = $request->input('tanggal_mulai');
            $tanggalSelesai = $request->input('tanggal_selesai');
            $status = $request->input('status');
            $limit = $request->input('limit', 50);

            // Base query untuk transaksi yang ditangani kurir ini
            $query = TransaksiPenjualan::with([
                'pembeli',
                'detailTransaksiPenjualan.produk.kategori'
            ])
                ->where('idPegawai', $user->idPegawai)
                ->whereNotNull('tanggalKirim');

            // Apply date filters
            if ($tanggalMulai) {
                $query->whereDate('tanggalKirim', '>=', $tanggalMulai);
            }
            if ($tanggalSelesai) {
                $query->whereDate('tanggalKirim', '<=', $tanggalSelesai);
            }
            if ($status) {
                $query->where('status', $status);
            }

            // Get data
            $historyPengiriman = $query->orderBy('tanggalKirim', 'desc')
                ->limit($limit)
                ->get();

            // Format data
            $formattedHistory = $historyPengiriman->map(function ($transaksi) {
                $alamatArray = json_decode($transaksi->alamatPengiriman, true);
                $alamatLengkap = $alamatArray['alamatLengkap'] ?? 'Alamat tidak tersedia';

                // Generate nomor nota
                $nomorNota = $transaksi->created_at->year . '.' .
                    str_pad($transaksi->created_at->month, 2, '0', STR_PAD_LEFT) . '.' .
                    str_pad($transaksi->idTransaksiPenjualan, 3, '0', STR_PAD_LEFT);

                // Ambil items dari detail transaksi
                $items = $transaksi->detailTransaksiPenjualan->map(function ($detail) {
                    return [
                        'namaProduk' => $detail->produk->deskripsi ?? 'Produk tidak ditemukan',
                        'quantity' => 1, // Karena setiap produk hanya 1 stock
                        'harga' => $detail->produk->hargaJual ?? 0,
                        'gambar' => $detail->produk->gambar ? explode(',', $detail->produk->gambar)[0] : null,
                    ];
                });

                // Hitung total harga
                $totalHarga = $transaksi->detailTransaksiPenjualan->sum(function ($detail) {
                    return $detail->produk->hargaJual ?? 0;
                });

                return [
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'nomorNota' => $nomorNota,
                    'tanggalPesan' => $transaksi->tanggalPesan ? $transaksi->tanggalPesan->toISOString() : null,
                    'tanggalKirim' => $transaksi->tanggalKirim ? $transaksi->tanggalKirim->toISOString() : null,
                    'tanggalSelesai' => ($transaksi->status === 'terjual' && $transaksi->tanggalKirim) ?
                        $transaksi->tanggalKirim->toISOString() : null,
                    'status' => $transaksi->status,
                    'namaPembeli' => $transaksi->pembeli->nama ?? 'Pembeli tidak ditemukan',
                    'alamatPengiriman' => $alamatLengkap,
                    'metodePengiriman' => $transaksi->metodePengiriman,
                    'items' => $items,
                    'totalHarga' => $totalHarga,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Kurir history pengiriman retrieved successfully',
                'data' => $formattedHistory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving kurir history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsionalitas 119: Mengupdate status pengiriman menjadi "Selesai" (Kurir)
     */
    public function updateStatusPengirimanSelesai(Request $request)
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah Kurir (jabatan ID = 6)
            if (!$user || $user->idJabatan != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Kurir access required.'
                ], 403);
            }

            $request->validate([
                'idTransaksiPenjualan' => 'required|integer|exists:transaksi_penjualan,idTransaksiPenjualan'
            ]);

            $idTransaksiPenjualan = $request->input('idTransaksiPenjualan');

            // Cari transaksi yang akan diupdate
            $transaksi = TransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksiPenjualan)
                ->where('idPegawai', $user->idPegawai) // Pastikan ini tugas kurir yang login
                ->where('status', 'kirim') // Hanya bisa update jika status masih "kirim"
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi pengiriman tidak ditemukan atau tidak dapat diupdate.'
                ], 404);
            }

            DB::beginTransaction();
            try {
                // Update status transaksi menjadi "terjual" (selesai)
                $transaksi->status = 'terjual';
                $transaksi->tanggalLaku = now();
                $transaksi->save();

                // Proses komisi dan update saldo penitip (mirip dengan konfirmasi di gudang)
                foreach ($transaksi->detailTransaksiPenjualan as $detail) {
                    $produk = $detail->produk;

                    // Cari data penitipan untuk produk ini
                    $detailPenitipan = $produk->detailTransaksiPenitipan()->first();
                    if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
                        $transaksiPenitipan = $detailPenitipan->transaksiPenitipan;
                        $penitip = $transaksiPenitipan->penitip;

                        // Hitung komisi
                        $hargaJual = $produk->hargaJual;
                        $komisiRate = $transaksiPenitipan->statusPerpanjangan ? 0.30 : 0.20;
                        $komisiTotal = $hargaJual * $komisiRate;

                        // Komisi hunter (jika ada)
                        $komisiHunter = 0;
                        $hunterPegawai = $transaksiPenitipan->idHunter;
                        if ($hunterPegawai) {
                            $komisiHunter = $komisiTotal * 0.25; // 5% dari harga jual untuk hunter

                            // Update komisi hunter
                            $hunter = Pegawai::find($hunterPegawai);
                            if ($hunter) {
                                $hunter->komisi += $komisiHunter;
                                $hunter->save();
                            }
                        }

                        // Komisi ReuseMart
                        $komisiReuse = $komisiTotal - $komisiHunter;

                        // Komisi untuk penitip
                        $komisiPenitip = $hargaJual - $komisiTotal;

                        // Bonus jika terjual cepat (< 7 hari)
                        $bonus = 0;
                        $tanggalMasuk = $transaksiPenitipan->tanggalMasukPenitipan;
                        $tanggalLaku = $transaksi->tanggalLaku;
                        $selisihHari = $tanggalMasuk->diffInDays($tanggalLaku);

                        if ($selisihHari < 7) {
                            $bonus = $komisiTotal * 0.10; // 10% dari komisi ReuseMart
                            $komisiReuse -= $bonus;
                            $komisiPenitip += $bonus;
                        }

                        // Simpan data komisi
                        Komisi::updateOrCreate(
                            ['idDetailTransaksiPenjualan' => $detail->idDetailTransaksiPenjualan],
                            [
                                'komisiPenitip' => $komisiPenitip,
                                'komisiHunter' => $komisiHunter,
                                'komisiReuse' => $komisiReuse,
                                'idPegawai' => $user->idPegawai, // Kurir yang mengkonfirmasi
                                'idPenitip' => $penitip->idPenitip,
                            ]
                        );

                        // Update saldo penitip
                        $penitip->saldo += $komisiPenitip;
                        $penitip->save();

                        // Update status penitipan
                        $transaksiPenitipan->statusPenitipan = 'Selesai';
                        $transaksiPenitipan->pendapatan += $komisiPenitip;
                        $transaksiPenitipan->save();
                    }

                    // Update status produk
                    $produk->status = 'Terjual';
                    $produk->save();
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Status pengiriman berhasil diupdate menjadi selesai'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik kurir
     */
    public function getKurirStats()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->idJabatan != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Kurir access required.'
                ], 403);
            }

            // Total pengiriman
            $totalPengiriman = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereNotNull('tanggalKirim')
                ->count();

            // Pengiriman hari ini
            $pengirimanHariIni = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereDate('tanggalKirim', today())
                ->count();

            // Pengiriman minggu ini
            $pengirimanMingguIni = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereBetween('tanggalKirim', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            // Pengiriman bulan ini
            $pengirimanBulanIni = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->whereMonth('tanggalKirim', now()->month)
                ->whereYear('tanggalKirim', now()->year)
                ->count();

            // Pengiriman selesai
            $pengirimanSelesai = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->where('status', 'terjual')
                ->count();

            // Pengiriman dalam proses
            $pengirimanDalamProses = TransaksiPenjualan::where('idPegawai', $user->idPegawai)
                ->where('status', 'kirim')
                ->count();

            $stats = [
                'totalPengiriman' => $totalPengiriman,
                'pengirimanHariIni' => $pengirimanHariIni,
                'pengirimanMingguIni' => $pengirimanMingguIni,
                'pengirimanBulanIni' => $pengirimanBulanIni,
                'pengirimanSelesai' => $pengirimanSelesai,
                'pengirimanDalamProses' => $pengirimanDalamProses,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Kurir stats retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving kurir stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan tugas pengiriman hari ini
     */
    public function getTugasHariIni()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->idJabatan != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Kurir access required.'
                ], 403);
            }

            // Ambil tugas pengiriman hari ini
            $tugasHariIni = TransaksiPenjualan::with([
                'pembeli',
                'detailTransaksiPenjualan.produk.kategori'
            ])
                ->where('idPegawai', $user->idPegawai)
                ->whereDate('tanggalKirim', today())
                ->orWhere(function ($query) use ($user) {
                    $query->where('idPegawai', $user->idPegawai)
                        ->where('status', 'disiapkan')
                        ->whereNotNull('tanggalLunas');
                })
                ->orderBy('tanggalKirim', 'asc')
                ->get();

            // Format data sama seperti history
            $formattedTugas = $tugasHariIni->map(function ($transaksi) {
                $alamatArray = json_decode($transaksi->alamatPengiriman, true);
                $alamatLengkap = $alamatArray['alamatLengkap'] ?? 'Alamat tidak tersedia';

                $nomorNota = $transaksi->created_at->year . '.' .
                    str_pad($transaksi->created_at->month, 2, '0', STR_PAD_LEFT) . '.' .
                    str_pad($transaksi->idTransaksiPenjualan, 3, '0', STR_PAD_LEFT);

                $items = $transaksi->detailTransaksiPenjualan->map(function ($detail) {
                    return [
                        'namaProduk' => $detail->produk->deskripsi ?? 'Produk tidak ditemukan',
                        'quantity' => 1,
                        'harga' => $detail->produk->hargaJual ?? 0,
                        'gambar' => $detail->produk->gambar ? explode(',', $detail->produk->gambar)[0] : null,
                    ];
                });

                $totalHarga = $transaksi->detailTransaksiPenjualan->sum(function ($detail) {
                    return $detail->produk->hargaJual ?? 0;
                });

                return [
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'nomorNota' => $nomorNota,
                    'tanggalPesan' => $transaksi->tanggalPesan ? $transaksi->tanggalPesan->toISOString() : null,
                    'tanggalKirim' => $transaksi->tanggalKirim ? $transaksi->tanggalKirim->toISOString() : null,
                    'tanggalSelesai' => ($transaksi->status === 'terjual' && $transaksi->tanggalKirim) ?
                        $transaksi->tanggalKirim->toISOString() : null,
                    'status' => $transaksi->status,
                    'namaPembeli' => $transaksi->pembeli->nama ?? 'Pembeli tidak ditemukan',
                    'alamatPengiriman' => $alamatLengkap,
                    'metodePengiriman' => $transaksi->metodePengiriman,
                    'items' => $items,
                    'totalHarga' => $totalHarga,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tugas hari ini retrieved successfully',
                'data' => $formattedTugas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tugas hari ini: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tambahkan di app/Http/Controllers/PegawaiController.php

    /**
     * Reset password pegawai ke tanggal lahir
     */
    public function resetPassword(Request $request, $id)
    {
        try {
            $pegawai = Pegawai::findOrFail($id);
            
            // Format tanggal lahir menjadi password (DDMMYYYY)
            $tanggalLahir = \Carbon\Carbon::parse($pegawai->tanggalLahir);
            $newPassword = $tanggalLahir->format('dmY'); // Contoh: 15052025
            
            // Update password
            $pegawai->password = Hash::make($newPassword);
            $pegawai->save();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Password berhasil direset ke tanggal lahir',
                    'new_password' => $newPassword
                ]);
            }
            
            return redirect()->back()->with('success', 
                "Password pegawai {$pegawai->nama} berhasil direset ke tanggal lahir ({$newPassword})"
            );
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal mereset password: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal mereset password pegawai');
        }
    }
}

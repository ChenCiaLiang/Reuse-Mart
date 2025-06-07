<?php

namespace App\Http\Controllers;

use App\Models\Penitip;
use App\Models\TransaksiPenitipan;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenitipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $penitip = Penitip::when($search, function ($query) use ($search) {
            return $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('idPenitip', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('alamat', 'like', '%' . $search . '%');
        })
            ->orderBy('idPenitip')
            ->paginate(10);

        return view('pegawai.cs.penitip.index', compact('penitip', 'search'));
    }

    public function create()
    {
        return view('pegawai.cs.penitip.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email|unique:pegawai,email',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string|max:200',
            'nik' => 'required|string|max:16|unique:penitip,nik',
            'fotoKTP' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('fotoKTP')) {
            $fotoKTP = $request->file('fotoKTP');
            $filename = 'penitip_' . $request->nama . '.' . $fotoKTP->getClientOriginalExtension();
            $fotoKTP->move(public_path('user/penitip'), $filename);
            $fotoPath = 'user/penitip/' . $filename;
        } else {
            $fotoPath = 'penitip/default_fotoKTP.png';
        }

        $penitip = Penitip::create([
            'idPenitip' => $request->idPenitip,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'alamat' => $request->alamat,
            'nik' => $request->nik,
            'fotoKTP' => $fotoPath,
            'poin' => 0,
            'bonus' => 0.0,
            'komisi' => 0.0,
            'saldo' => 0.0,
            'rating' => 0.0,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Penitip berhasil diupdate',
                'data' => $penitip,
            ], 201);
        }

        return redirect()->route('cs.dashboard')->with('success', 'Penitip berhasil ditambahkan!');
    }

    public function show($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('pegawai.cs.penitip.show', compact('penitip'));
    }

    public function edit($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('pegawai.cs.penitip.edit', compact('penitip'));
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50|unique:penitip,nama,' . $id . ',idPenitip',
            'email' => 'required|string|email|max:50|unique:penitip,email,' . $id . ',idPenitip|unique:pembeli,email|unique:organisasi,email|unique:pegawai,email',
            'alamat' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update pegawai
        $penitip->nama = $request->nama;
        $penitip->email = $request->email;
        $penitip->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Penitip berhasil diupdate',
                'data' => $penitip,
            ], 201);
        }

        return redirect()->route('cs.dashboard')->with('success', 'Data penitip berhasil diperbarui!');
    }

    /**
     * Hapus data pegawai
     */
    public function destroy(Request $request, $id)
    {
        $penitip = Penitip::findOrFail($id);
        $penitip->delete();

        if ($penitip->fotoKTP && file_exists(public_path($penitip->fotoKTP))) {
            unlink(public_path($penitip->fotoKTP));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Penitip berhasil dihapus',
                'data' => $penitip,
            ], 201);
        }

        return redirect()->route('cs.dashboard')->with('success', 'Penitip berhasil dihapus!');
    }

    /**
     * Menampilkan profil penitip
     */
    public function profile()
    {
        // Ambil ID penitip dari session
        $idPenitip = session('user')['idPenitip'];

        // Dapatkan data penitip
        $penitip = Penitip::findOrFail($idPenitip);

        // Dapatkan transaksi penitipan terbaru (5 terakhir)
        $transaksiPenitipan = TransaksiPenitipan::where('idPenitip', $idPenitip)
            ->orderBy('tanggalMasukPenitipan', 'desc')
            ->limit(5)
            ->get();

        // Dapatkan transaksi penjualan terbaru (5 terakhir)
        // Kita perlu join beberapa tabel untuk mendapatkan transaksi penjualan dari barang yang dititipkan
        $transaksiPenjualan = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('detail_transaksi_penitipan', 'produk.idProduk', '=', 'detail_transaksi_penitipan.idProduk')
            ->join('transaksi_penitipan', 'detail_transaksi_penitipan.idTransaksiPenitipan', '=', 'transaksi_penitipan.idTransaksiPenitipan')
            ->where('transaksi_penitipan.idPenitip', $idPenitip)
            ->whereNotNull('transaksi_penjualan.tanggalLunas') // Hanya transaksi yang sudah lunas
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->limit(5)
            ->get();

        return view('customer.penitip.profile', compact('penitip', 'transaksiPenitipan', 'transaksiPenjualan'));
    }

    /**
     * Menampilkan histori transaksi
     */
    public function historyTransaksi(Request $request)
    {
        // Ambil ID penitip dari session
        $idPenitip = session('user')['idPenitip'];

        // Filter berdasarkan tanggal jika ada
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // Query yang diperbaiki menggunakan relasi komisi
        $transaksiPenjualan = TransaksiPenjualan::join('komisi', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'komisi.idTransaksiPenjualan')
            ->join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'detail_transaksi_penjualan.idTransaksiPenjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->where('komisi.idPenitip', $idPenitip)
            ->whereBetween('transaksi_penjualan.tanggalLunas', [$startDate, $endDate])
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->distinct()
            ->paginate(10);

        // Tambahkan variabel debug untuk troubleshooting
        $debug = [
            'idPenitip' => $idPenitip,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'count' => $transaksiPenjualan->count(),
            'total' => $transaksiPenjualan->total()
        ];

        return view('customer.penitip.history', compact('transaksiPenjualan', 'startDate', 'endDate', 'debug'));
    }
    /**
     * Menampilkan detail transaksi
     */
    public function detailTransaksi($idTransaksiPenjualan)
    {
        try {
            // Ambil ID penitip dari session
            $idPenitip = session('user')['idPenitip'];

            // Dapatkan detail transaksi
            $transaksi = TransaksiPenjualan::findOrFail($idTransaksiPenjualan);

            // Dapatkan produk yang dijual
            $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $idTransaksiPenjualan)
                ->with('produk')
                ->get();

            // Pastikan produk ini milik penitip yang login melalui komisi
            $isOwner = \App\Models\Komisi::where('idTransaksiPenjualan', $idTransaksiPenjualan)
                ->where('idPenitip', $idPenitip)
                ->exists();

            if (!$isOwner) {
                return redirect()->route('penitip.profile')->with('error', 'Anda tidak memiliki akses ke transaksi ini');
            }

            // Dapatkan pembeli
            $pembeli = $transaksi->pembeli;

            // Dapatkan komisi
            $komisi = $transaksi->komisi;

            return view('customer.penitip.detail-transaksi', compact('transaksi', 'detailTransaksi', 'pembeli', 'komisi'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in detailTransaksi: " . $e->getMessage());
            return redirect()->route('penitip.profile')->with('error', 'Terjadi kesalahan saat mengakses detail transaksi');
        }
    }

    public function getProfile()
    {
        try {
            // ✅ Gunakan Auth::user() seperti di PembeliController
            Log::info('Penitip Auth Debug', [
                'auth_id' => Auth::id(),
                'auth_user_type' => get_class(Auth::user()),
                'auth_check' => Auth::check(),
            ]);
            
            $penitip = Auth::user();
            
            // ✅ Cek apakah user adalah instance Penitip
            if (!$penitip || !($penitip instanceof \App\Models\Penitip)) {
                Log::warning('Unauthorized penitip access attempt', [
                    'user_type' => $penitip ? get_class($penitip) : 'null',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            Log::info('Penitip authenticated', [
                'penitip_id' => $penitip->idPenitip,
                'penitip_email' => $penitip->email
            ]);

            // Format data profil (tanpa transaksi)
            $profileData = [
                'idPenitip' => $penitip->idPenitip,
                'nama' => $penitip->nama,
                'email' => $penitip->email,
                'alamat' => $penitip->alamat,
                'poin' => $penitip->poin ?? 0,
                'saldo' => $penitip->saldo ?? 0,
                'komisi' => $penitip->komisi ?? 0,
                'bonus' => $penitip->bonus ?? 0,
                'rating' => $penitip->rating ?? 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diambil',
                'data' => [
                    'profile' => $profileData,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getProfile penitip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHistoryTransaksi(Request $request)
    {
        try {
            $penitip = Auth::user();
            
            if (!$penitip || !($penitip instanceof \App\Models\Penitip)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }
            // Ambil parameter pagination
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            // Query yang disesuaikan dengan struktur database
            $transaksiQuery = DB::table('transaksi_penitipan as tp')
                ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
                ->leftJoin('detail_transaksi_penjualan as dtpj', 'p.idProduk', '=', 'dtpj.idProduk')
                ->leftJoin('transaksi_penjualan as tpj', 'dtpj.idTransaksiPenjualan', '=', 'tpj.idTransaksiPenjualan')
                ->leftJoin('komisi as k', function($join) use ($penitip) {
                    $join->on('dtpj.idDetailTransaksiPenjualan', '=', 'k.idDetailTransaksiPenjualan')
                        ->where('k.idPenitip', '=', $penitip->idPenitip);
                })
                ->where('tp.idPenitip', $penitip->idPenitip)
                ->where(function($query) {
                    $query->where('p.status', 'Terjual')
                        ->orWhere('tpj.status', 'terjual')
                        ->orWhere('tpj.status', 'selesai')
                        ->orWhere('tpj.status', 'diambil');
                })
                ->whereNotNull('tpj.tanggalLunas')
                ->orderBy('tpj.tanggalLunas', 'desc')
                ->select(
                    'tpj.idTransaksiPenjualan',
                    'tpj.tanggalPesan',
                    'tpj.tanggalLunas', 
                    'tpj.status',
                    'p.idProduk',
                    'p.deskripsi as nama_produk',
                    'p.hargaJual',
                    'p.gambar',
                    'tp.tanggalMasukPenitipan',
                    'tp.statusPenitipan',
                    'tp.statusPerpanjangan',
                    'k.komisiPenitip',
                    'k.komisiHunter', 
                    'k.komisiReuse'
                )
                ->distinct();

            // Jika tidak ada transaksi terjual, ambil semua history penitipan
            $totalSold = $transaksiQuery->count();
            
            if ($totalSold == 0) {
                // Fallback: ambil semua barang yang pernah dititipkan
                $transaksiQuery = DB::table('transaksi_penitipan as tp')
                    ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                    ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
                    ->where('tp.idPenitip', $penitip->idPenitip)
                    ->orderBy('tp.tanggalMasukPenitipan', 'desc')
                    ->select(
                        DB::raw('NULL as idTransaksiPenjualan'),
                        'tp.tanggalMasukPenitipan as tanggalPesan',
                        DB::raw('NULL as tanggalLunas'),
                        'p.status',
                        'p.idProduk',
                        'p.deskripsi as nama_produk',
                        'p.hargaJual',
                        'p.gambar',
                        'tp.tanggalMasukPenitipan',
                        'tp.statusPenitipan',
                        'tp.statusPerpanjangan',
                        DB::raw('0 as komisiPenitip'),
                        DB::raw('0 as komisiHunter'),
                        DB::raw('0 as komisiReuse')
                    );
            }

            // Pagination manual untuk query builder
            $total = $transaksiQuery->count();
            $results = $transaksiQuery->offset(($page - 1) * $perPage)
                                    ->limit($perPage)
                                    ->get();

            // Format data untuk mobile
            $transaksiData = $results->map(function ($transaksi) {
                $gambar = $transaksi->gambar ? explode(',', $transaksi->gambar)[0] : null;
                
                return [
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'idProduk' => $transaksi->idProduk,
                    'nama_produk' => $transaksi->nama_produk,
                    'harga_jual' => (float) $transaksi->hargaJual,
                    'gambar' => $gambar,
                    'tanggal_pesan' => $transaksi->tanggalPesan ? 
                        Carbon::parse($transaksi->tanggalPesan)->format('d/m/Y') : 
                        Carbon::parse($transaksi->tanggalMasukPenitipan)->format('d/m/Y'),
                    'tanggal_lunas' => $transaksi->tanggalLunas ? 
                        Carbon::parse($transaksi->tanggalLunas)->format('d/m/Y') : null,
                    'status' => $transaksi->status,
                    'status_penitipan' => $transaksi->statusPenitipan ?? null,
                    'komisi_penitip' => (float) ($transaksi->komisiPenitip ?? 0),
                    'komisi_hunter' => (float) ($transaksi->komisiHunter ?? 0),
                    'komisi_reuse' => (float) ($transaksi->komisiReuse ?? 0),
                ];
            });

            // Calculate pagination info
            $lastPage = ceil($total / $perPage);
            $hasMore = $page < $lastPage;

            return response()->json([
                'success' => true,
                'message' => 'History transaksi berhasil diambil',
                'data' => [
                    'transactions' => $transaksiData,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => $lastPage,
                        'has_more' => $hasMore,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getHistoryTransaksi penitip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetailTransaksi($idTransaksiPenjualan)
    {
        try {
            $penitip = Auth::user();
            
            if (!$penitip || !($penitip instanceof \App\Models\Penitip)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            // Cek apakah transaksi ini milik penitip - dengan query yang lebih spesifik
            $isOwner = DB::table('transaksi_penitipan as tp')
                ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
                ->join('detail_transaksi_penjualan as dtpj', 'p.idProduk', '=', 'dtpj.idProduk')
                ->where('dtpj.idTransaksiPenjualan', $idTransaksiPenjualan)
                ->where('tp.idPenitip', $penitip->idPenitip)
                ->exists();

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke transaksi ini'
                ], 403);
            }

            // Ambil detail transaksi dengan relasi yang benar
            $transaksi = TransaksiPenjualan::with(['pembeli', 'detailTransaksiPenjualan.produk.kategori'])
                ->findOrFail($idTransaksiPenjualan);

            // Ambil komisi hanya untuk produk milik penitip ini
            $komisiData = DB::table('komisi as k')
                ->join('detail_transaksi_penjualan as dtpj', 'k.idDetailTransaksiPenjualan', '=', 'dtpj.idDetailTransaksiPenjualan')
                ->join('produk as p', 'dtpj.idProduk', '=', 'p.idProduk')
                ->join('detail_transaksi_penitipan as dtp', 'p.idProduk', '=', 'dtp.idProduk')
                ->join('transaksi_penitipan as tp', 'dtp.idTransaksiPenitipan', '=', 'tp.idTransaksiPenitipan')
                ->where('dtpj.idTransaksiPenjualan', $idTransaksiPenjualan)
                ->where('tp.idPenitip', $penitip->idPenitip)
                ->select('k.*')
                ->get();

            // Format data detail
            $detailData = [
                'transaksi' => [
                    'idTransaksiPenjualan' => $transaksi->idTransaksiPenjualan,
                    'tanggal_pesan' => Carbon::parse($transaksi->tanggalPesan)->format('d/m/Y H:i'),
                    'tanggal_lunas' => $transaksi->tanggalLunas ? Carbon::parse($transaksi->tanggalLunas)->format('d/m/Y H:i') : null,
                    'status' => $transaksi->status,
                    'metode_pengiriman' => $transaksi->metodePengiriman,
                    'alamat_pengiriman' => $transaksi->alamatPengiriman,
                ],
                'pembeli' => [
                    'nama' => $transaksi->pembeli->nama,
                    'email' => $transaksi->pembeli->email,
                ],
                'produk' => $transaksi->detailTransaksiPenjualan
                    ->filter(function($detail) use ($penitip) {
                        // Filter hanya produk milik penitip ini
                        return DB::table('detail_transaksi_penitipan as dtp')
                            ->join('transaksi_penitipan as tp', 'dtp.idTransaksiPenitipan', '=', 'tp.idTransaksiPenitipan')
                            ->where('dtp.idProduk', $detail->produk->idProduk)
                            ->where('tp.idPenitip', $penitip->idPenitip)
                            ->exists();
                    })
                    ->map(function ($detail) {
                        $gambar = $detail->produk->gambar ? explode(',', $detail->produk->gambar)[0] : null;
                        
                        return [
                            'idProduk' => $detail->produk->idProduk,
                            'nama' => $detail->produk->deskripsi,
                            'kategori' => $detail->produk->kategori->nama ?? null,
                            'harga_jual' => $detail->produk->hargaJual,
                            'gambar' => $gambar,
                        ];
                    })->values(),
                'komisi' => $komisiData->map(function ($komisi) {
                    return [
                        'komisi_penitip' => (float) $komisi->komisiPenitip,
                        'komisi_hunter' => (float) $komisi->komisiHunter,
                        'komisi_reuse' => (float) $komisi->komisiReuse,
                    ];
                }),
                'total_komisi_penitip' => (float) $komisiData->sum('komisiPenitip'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => $detailData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getDetailTransaksi penitip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

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
        $transaksiPenjualan = TransaksiPenjualan::join('komisi', 'transaksi_penjualan.idTransaksiPenjualan', '=', 'komisi.idDetailTransaksiPenjualan')
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

            // Validasi parameter
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;
            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;

            // Query utama berdasarkan transaksi penitipan
            $query = DB::table('transaksi_penitipan as tp')
                ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                ->join('produk as p', 'dtp.idProduk', '=', 'p.idProduk')
                ->join('kategori_produk as kp', 'p.idKategori', '=', 'kp.idKategori')
                ->leftJoin('detail_transaksi_penjualan as dtpj', 'p.idProduk', '=', 'dtpj.idProduk')
                ->leftJoin('transaksi_penjualan as tpj', 'dtpj.idTransaksiPenjualan', '=', 'tpj.idTransaksiPenjualan')
                ->leftJoin('komisi as k', 'dtpj.idDetailTransaksiPenjualan', '=', 'k.idDetailTransaksiPenjualan')
                ->where('tp.idPenitip', $penitip->idPenitip)
                ->select(
                    'tp.idTransaksiPenitipan',
                    'tpj.idTransaksiPenjualan',
                    'p.idProduk',
                    'p.deskripsi as nama_produk',
                    'p.hargaJual as harga_jual',
                    'p.gambar',
                    'p.status as status_produk',
                    'tp.tanggalMasukPenitipan as tanggal_penitipan',
                    'tp.statusPenitipan as status_penitipan',
                    'tpj.tanggalPesan as tanggal_pesan',
                    'tpj.tanggalLunas as tanggal_lunas',
                    'tpj.status as status_penjualan',
                    'k.komisiPenitip as komisi_penitip',
                    'k.komisiHunter as komisi_hunter',
                    'k.komisiReuse as komisi_reuse',
                    'kp.nama as kategori'
                );

            // Filter berdasarkan tanggal jika ada
            if ($startDate) {
                $query->where('tp.tanggalMasukPenitipan', '>=', $startDate . ' 00:00:00');
            }
            if ($endDate) {
                $query->where('tp.tanggalMasukPenitipan', '<=', $endDate . ' 23:59:59');
            }

            // Order by terbaru dulu
            $query->orderBy('tp.tanggalMasukPenitipan', 'desc')
                ->orderBy('tp.idTransaksiPenitipan', 'desc');

            // Clone query untuk total count
            $totalQuery = clone $query;
            $total = $totalQuery->count();

            // Pagination
            $offset = ($page - 1) * $perPage;
            $results = $query->offset($offset)->limit($perPage)->get();

            // Format data hasil
            $transactions = $results->map(function ($item) {
                // Ambil gambar pertama jika ada
                $gambar = null;
                if ($item->gambar) {
                    $gambarArray = explode(',', $item->gambar);
                    $gambar = trim($gambarArray[0]);
                }

                // Tentukan status dan tanggal yang akan ditampilkan
                $isTerjual = !is_null($item->idTransaksiPenjualan) && !is_null($item->tanggal_lunas);

                return [
                    'idTransaksiPenitipan' => (int) $item->idTransaksiPenitipan,
                    'idTransaksiPenjualan' => $item->idTransaksiPenjualan ? (int) $item->idTransaksiPenjualan : null,
                    'idProduk' => (int) $item->idProduk,
                    'nama_produk' => $item->nama_produk,
                    'harga_jual' => (float) $item->harga_jual,
                    'gambar' => $gambar,
                    'kategori' => $item->kategori,
                    'tanggal_pesan' => $isTerjual ?
                        Carbon::parse($item->tanggal_pesan)->format('d/m/Y') :
                        Carbon::parse($item->tanggal_penitipan)->format('d/m/Y'),
                    'tanggal_lunas' => $item->tanggal_lunas ?
                        Carbon::parse($item->tanggal_lunas)->format('d/m/Y') : null,
                    'status' => $isTerjual ? $item->status_penjualan : $item->status_produk,
                    'status_penitipan' => $item->status_penitipan,
                    'komisi_penitip' => (float) ($item->komisi_penitip ?? 0),
                    'komisi_hunter' => (float) ($item->komisi_hunter ?? 0),
                    'komisi_reuse' => (float) ($item->komisi_reuse ?? 0),
                ];
            });

            // Pagination info
            $lastPage = ceil($total / $perPage);
            $hasMore = $page < $lastPage;

            $paginationInfo = [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $total,
                'last_page' => (int) $lastPage,
                'has_more' => $hasMore,
            ];

            // Filter info
            $filterInfo = [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            return response()->json([
                'success' => true,
                'message' => 'History transaksi berhasil diambil',
                'data' => [
                    'transactions' => $transactions,
                    'pagination' => $paginationInfo,
                    'filter' => $filterInfo,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
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
                    ->filter(function ($detail) use ($penitip) {
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

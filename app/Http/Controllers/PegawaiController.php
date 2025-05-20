<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
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

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar pegawai
     */
    public function index(Request $request)
    {
        // Pencarian pegawai
        $search = $request->input('search');

        $pegawai = Pegawai::when($search, function ($query) use ($search) {
                return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('noTelp', 'like', '%' . $search . '%')
                    ->orWhere('alamat', 'like', '%' . $search . '%')
                    ->orWhere('tanggalLahir', 'like', '%' . $search . '%')
                    ->orWhereHas('jabatan', function($jq) use ($search) {
                        $jq->where('nama', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('nama')
            ->paginate(10);

        return view('pegawai.admin.manajemenPegawai.index', compact('pegawai', 'search'));
    }

    /**
     * Menampilkan form tambah pegawai
     */
    public function create()
    {
        $jabatan = Jabatan::all();
        return view('pegawai.admin.manajemenPegawai.create', compact('jabatan'));
    }

    /**
     * Menyimpan data pegawai baru
     */
    public function store(Request $request)
    {
        // Validasi input
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

        // Buat pegawai baru
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

    /**
     * Menampilkan detail pegawai
     */
    public function show($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('pegawai.admin.manajemenPegawai.show', compact('pegawai'));
    }

    /**
     * Menampilkan form edit pegawai
     */
    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $jabatan = Jabatan::orderBy('nama')->get();
        return view('pegawai.admin.manajemenPegawai.edit', compact('pegawai', 'jabatan'));
    }

    /**
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        
        // Validasi input
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

        // Update pegawai
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

    /**
     * Hapus data pegawai
     */
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
        // Data jumlah pengguna
        $totalPegawai = Pegawai::count();
        $totalPenitip = Penitip::count();
        $totalPembeli = Pembeli::count();
        $totalOrganisasi = Organisasi::count();

        // Data produk
        $totalProduk = Produk::count();
        $produkTersedia = Produk::where('status', '!=', 'Terjual')
            ->where('status', '!=', 'Didonasikan')
            ->count();
        $produkTerjual = Produk::where('status', 'Terjual')->count();
        $produkDidonasikan = Produk::where('status', 'Didonasikan')->count();

        // Data transaksi bulan ini
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

        // Data penjualan per bulan untuk grafik
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

        // Format data penjualan untuk chart
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

        // Inisialisasi data untuk semua bulan (1-12)
        for ($i = 1; $i <= 12; $i++) {
            $chartData[$i] = [
                'bulan' => $namaBulan[$i],
                'total_penjualan' => 0
            ];
        }

        // Isi data penjualan
        foreach ($penjualanPerBulan as $data) {
            $chartData[$data->bulan]['total_penjualan'] = $data->total_penjualan;
        }

        // Konversi ke array untuk chart
        $chartData = array_values($chartData);

        // Produk terlaris
        $produkTerlaris = DB::table('detail_transaksi_penjualan')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('transaksi_penjualan', 'detail_transaksi_penjualan.idTransaksiPenjualan', '=', 'transaksi_penjualan.idTransaksiPenjualan')
            ->whereNotNull('transaksi_penjualan.tanggalLunas')
            ->select('produk.idProduk', 'produk.deskripsi', DB::raw('COUNT(*) as total_terjual'))
            ->groupBy('produk.idProduk', 'produk.deskripsi')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->get();

        // Transaksi terakhir
        $transaksiTerakhir = TransaksiPenjualan::with(['pembeli', 'detailTransaksiPenjualan.produk'])
            ->whereNotNull('tanggalLunas')
            ->orderBy('tanggalLunas', 'desc')
            ->limit(5)
            ->get();

        // Barang yang masa penitipannya hampir habis
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
}
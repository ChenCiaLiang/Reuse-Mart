<?php

namespace App\Http\Controllers;

use App\Models\Penitip;
use App\Models\TransaksiPenitipan;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenitipan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PenitipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $penitip = Penitip::when($search, function ($query) use ($search) {
            return $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('idPenitip', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        })
            ->orderBy('nama')
            ->paginate(10);

        return view('cs.penitip.index', compact('penitip', 'search'));
    }

    public function create()
    {
        return view('cs.penitip.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'idPenitip' => 'required|string|max:10|unique:penitip,idPenitip',
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string|max:200',
            'nik' => 'required|string|max:16|unique:penitip,nik',
            'foto_ktp' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('foto_ktp')) {
            $foto_ktp = $request->file('foto_ktp');
            $filename = 'penitip_' . $request->nama . '.' . $foto_ktp->getClientOriginalExtension();
            $foto_ktp->move(public_path('user/penitip'), $filename);
            $fotoPath = 'user/penitip/' . $filename;
        } else {
            $fotoPath = 'penitip/default_foto_ktp.png';
        }

        Penitip::create([
            'idPenitip' => $request->idPenitip,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'alamat' => $request->alamat,
            'nik' => $request->nik,
            'foto_ktp' => $fotoPath,
            'poin' => 0,
            'bonus' => 0.0,
            'komisi' => 0.0,
            'saldo' => 0.0,
            'rating' => 0.0,
        ]);

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Penitip berhasil ditambahkan!');
    }

    public function show($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('cs.penitip.show', compact('penitip'));
    }

    public function edit($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('cs.penitip.edit', compact('penitip'));
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50|unique:penitip,nama,' . $id . ',idPenitip',
            'email' => 'required|string|email|max:50|unique:penitip,email,' . $id . ',idPenitip|unique:pembeli,email|unique:organisasi,email',
            'alamat' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update pegawai
        $penitip->nama = $request->nama;
        $penitip->email = $request->email;
        $penitip->save();

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Data penitip berhasil diperbarui!');
    }

    /**
     * Hapus data pegawai
     */
    public function destroy($id)
    {
        $penitip = Penitip::findOrFail($id);
        $penitip->delete();

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Penitip berhasil dihapus!');
    }

    /**
     * Menampilkan profil penitip
     */
    public function profile()
    {
        // Ambil ID penitip dari session
        $idPenitip = session('user_id');
        
        // Dapatkan data penitip
        $penitip = Penitip::findOrFail($idPenitip);
        
        // Dapatkan transaksi penitipan terbaru (5 terakhir)
        $transaksiPenitipan = TransaksiPenitipan::where('idPenitip', $idPenitip)
            ->orderBy('tanggalMasukPenitipan', 'desc')
            ->limit(5)
            ->get();
            
        // Dapatkan transaksi penjualan terbaru (5 terakhir)
        // Kita perlu join beberapa tabel untuk mendapatkan transaksi penjualan dari barang yang dititipkan
        $transaksiPenjualan = TransaksiPenjualan::join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksi', '=', 'detail_transaksi_penjualan.idTransaksi')
            ->join('produk', 'detail_transaksi_penjualan.idProduk', '=', 'produk.idProduk')
            ->join('detail_transaksi_penitipan', 'produk.idProduk', '=', 'detail_transaksi_penitipan.idProduk')
            ->join('transaksi_penitipan', 'detail_transaksi_penitipan.idTransaksiPenitipan', '=', 'transaksi_penitipan.idTransaksiPenitipan')
            ->where('transaksi_penitipan.idPenitip', $idPenitip)
            ->whereNotNull('transaksi_penjualan.tanggalLunas') // Hanya transaksi yang sudah lunas
            ->orderBy('transaksi_penjualan.tanggalLunas', 'desc')
            ->select('transaksi_penjualan.*', 'produk.deskripsi', 'produk.hargaJual')
            ->limit(5)
            ->get();
            
        return view('penitip.profile', compact('penitip', 'transaksiPenitipan', 'transaksiPenjualan'));
    }
    
    /**
     * Menampilkan histori transaksi
     */
    public function historyTransaksi(Request $request)
    {
        // Ambil ID penitip dari session
        $idPenitip = session('user_id');
        
        // Filter berdasarkan tanggal jika ada
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        
        // Query yang diperbaiki menggunakan relasi komisi
        $transaksiPenjualan = TransaksiPenjualan::join('komisi', 'transaksi_penjualan.idTransaksi', '=', 'komisi.idTransaksi')
            ->join('detail_transaksi_penjualan', 'transaksi_penjualan.idTransaksi', '=', 'detail_transaksi_penjualan.idTransaksi')
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
        
        return view('penitip.history', compact('transaksiPenjualan', 'startDate', 'endDate', 'debug'));
    }    
    /**
     * Menampilkan detail transaksi
     */
    public function detailTransaksi($idTransaksi)
    {
        try {
        // Ambil ID penitip dari session
        $idPenitip = session('user_id');
        
        // Dapatkan detail transaksi
        $transaksi = TransaksiPenjualan::findOrFail($idTransaksi);
        
        // Dapatkan produk yang dijual
        $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksi', $idTransaksi)
            ->with('produk')
            ->get();
            
        // Pastikan produk ini milik penitip yang login melalui komisi
        $isOwner = \App\Models\Komisi::where('idTransaksi', $idTransaksi)
            ->where('idPenitip', $idPenitip)
            ->exists();
        
        if (!$isOwner) {
            return redirect()->route('penitip.profile')->with('error', 'Anda tidak memiliki akses ke transaksi ini');
        }
        
        // Dapatkan pembeli
        $pembeli = $transaksi->pembeli;
        
        // Dapatkan komisi
        $komisi = $transaksi->komisi;
        
    return view('penitip.detail-transaksi', compact('transaksi', 'detailTransaksi', 'pembeli', 'komisi'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in detailTransaksi: " . $e->getMessage());
            return redirect()->route('penitip.profile')->with('error', 'Terjadi kesalahan saat mengakses detail transaksi');
        }
    }
}

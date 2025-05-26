<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Pegawai;
use App\Models\Pembeli;
use App\Models\TransaksiPenitipan;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class TransaksiPenitipanController extends Controller
{
    // Dashboard method - FIXED
    public function dashboard()
    {
        $totalTransaksi = DB::table('transaksi_penitipan')->count();
        $transaksiAktif = DB::table('transaksi_penitipan')->where('statusPenitipan', 'Aktif')->count();
        $totalPendapatan = DB::table('transaksi_penitipan')->sum('pendapatan');
        $transaksiHariIni = DB::table('transaksi_penitipan')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Transaksi yang akan expired dalam 7 hari - FIXED
        $transaksiExpiringSoon = DB::table('transaksi_penitipan as tp')
            ->join('penitip as p', 'tp.idPenitip', '=', 'p.idPenitip')
            ->select(
                'tp.idTransaksiPenitipan',
                'tp.batasAmbil',
                'tp.statusPenitipan',
                'p.nama as namaPenitip'
            )
            ->whereBetween('tp.batasAmbil', [Carbon::now(), Carbon::now()->addDays(7)])
            ->where('tp.statusPenitipan', 'Aktif')
            ->orderBy('tp.batasAmbil', 'asc')
            ->get();

        // Transaksi terbaru - FIXED
        $transaksiTerbaru = DB::table('transaksi_penitipan as tp')
            ->join('penitip as p', 'tp.idPenitip', '=', 'p.idPenitip')
            ->join('pegawai as pg', 'tp.idPegawai', '=', 'pg.idPegawai')
            ->select(
                'tp.idTransaksiPenitipan',
                'tp.pendapatan',
                'tp.statusPenitipan',
                'tp.statusPerpanjangan',
                'tp.created_at',
                'p.nama as namaPenitip',
                'pg.nama as namaPegawai'
            )
            ->orderBy('tp.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pegawai.gudang.dashboard', compact(
            'totalTransaksi',
            'transaksiAktif',
            'totalPendapatan',
            'transaksiHariIni',
            'transaksiExpiringSoon',
            'transaksiTerbaru'
        ));
    }

    // Index method - FIXED
    public function indexGudang(Request $request)
    {
        $query = DB::table('transaksi_penitipan as tp')
            ->join('penitip as p', 'tp.idPenitip', '=', 'p.idPenitip')
            ->join('pegawai as pg', 'tp.idPegawai', '=', 'pg.idPegawai')
            ->select(
                'tp.idTransaksiPenitipan',
                'tp.tanggalMasukPenitipan',
                'tp.tanggalAkhirPenitipan',
                'tp.batasAmbil',
                'tp.statusPenitipan',
                'tp.statusPerpanjangan',
                'tp.pendapatan',
                'tp.created_at',
                'tp.updated_at',
                'p.nama as namaPenitip',
                'p.email as emailPenitip', // Menggunakan email sebagai kontak
                'pg.nama as namaPegawai'
            );

        // Apply filters
        $this->applyFilters($query, $request);

        $transaksi = $query->orderBy('tp.created_at', 'desc')->paginate(10);

        return view('pegawai.gudang.penitipan.index', compact('transaksi'));
    }

    // //Menampilkan daftar transaksi (dikirim/diambil)
    public function indexPenitip(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->input('search');

        $penitipan = TransaksiPenitipan::when($search, function ($query) use ($search) {
            return $query->where('idTransaksiPenitipan', 'like', '%' . $search . '%')
                ->orWhere('tanggalMasukPenitipan', 'like', '%' . $search . '%')
                ->orWhere('tanggalAkhirPenitipan', 'like', '%' . $search . '%')
                ->orWhere('statusPenitipan', 'like', '%' . $search . '%')
                ->orWhere('statusPerpanjangan', 'like', '%' . $search . '%')
                ->orWhere('pendapatan', 'like', '%' . $search . '%');
        })
            ->where('idPenitip', session('user')['idPenitip'])
            ->orderBy('idTransaksiPenitipan')
            ->paginate(10);


        return view('customer.penitip.penitipan.index', compact('penitipan', 'search'));
    }

    // Show method - FIXED
    public function showGudang($id)
    {
        $transaksi = DB::table('transaksi_penitipan as tp')
            ->join('penitip as p', 'tp.idPenitip', '=', 'p.idPenitip')
            ->join('pegawai as pg', 'tp.idPegawai', '=', 'pg.idPegawai')
            ->select(
                'tp.*',
                'p.nama as namaPenitip',
                'p.email as emailPenitip',
                'p.alamat as alamatPenitip',
                'p.nik as nikPenitip',
                'pg.nama as namaPegawai'
            )
            ->where('tp.idTransaksiPenitipan', $id)
            ->first();

        if (!$transaksi) {
            return redirect()->route('gudang.penitipan.index')->with('error', 'Transaksi tidak ditemukan!');
        }

        $detail = DB::table('detail_transaksi_penitipan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->join('kategori_produk as kp', 'pr.idKategori', '=', 'kp.idKategori')
            ->select(
                'pr.idProduk',
                'pr.deskripsi as namaProduk',
                'pr.harga',
                'pr.hargaJual',
                'pr.berat',
                'pr.gambar',
                'pr.tanggalGaransi',
                'pr.status',
                'pr.ratingProduk',
                'kp.nama as kategori'
            )
            ->where('dtp.idTransaksiPenitipan', $id)
            ->get();

        return view('pegawai.gudang.penitipan.show', compact('transaksi', 'detail'));
    }

    public function showPenitip($id)
    {
        // Ambil data produk berdasarkan ID
        $penitipan = TransaksiPenitipan::findOrFail($id);
        $produk = Produk::findOrFail($penitipan->detailTransaksiPenjualan[0]->idProduk);
        $penitipan->produk = $produk->deskripsi;
        $penitipan->namaPembeli = Pembeli::findOrFail($penitipan->idPembeli)->nama;
        $penitipan->namaPegawai = Pegawai::find($penitipan->idPegawai)->nama ?? '-';
        // Ambil gambar-gambar produk dari field gambar
        $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];

        return view('pegawai.gudang.pengiriman.show', compact('pengiriman', 'gambarArray'));
    }

    public function penjadwalanKirimPage($id)
    {
        dd(Carbon::now()->format('d/m/Y H:i:s'));
        $pengiriman = TransaksiPenitipan::findOrFail($id);
        $kurir = Pegawai::where('idJabatan', 6)->get();
        return view('pegawai.gudang.pengiriman.penjadwalanKirim', compact('pengiriman', 'kurir'));
    }

    public function penjadwalanAmbilPage($id)
    {
        $pengiriman = TransaksiPenitipan::findOrFail($id);
        return view('pegawai.gudang.pengiriman.penjadwalanAmbil', compact('pengiriman'));
    }

    public function penjadwalanKirim(Request $request, $id)
    {
        $pengiriman = TransaksiPenitipan::find($id);

        $validator = Validator::make($request->all(), [
            'kurir' => 'required',
            'tanggalKirim' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalKirimRequest = Carbon::parse($request->tanggalKirim);
        $tanggalLaku = Carbon::parse($pengiriman->tanggalLaku);
        $sekarang = Carbon::now();

        if ($tanggalKirimRequest->lt($sekarang->startOfDay())) {
            $errorMessage = 'Tanggal kirim tidak boleh sebelum hari ini';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
        }

        // Pembelian di atas jam 16.00
        $jamPembelian = (int) $tanggalLaku->format('H.i');
        $hariPembelian = $tanggalLaku->format('Y-m-d');
        $hariPengiriman = $tanggalKirimRequest->format('Y-m-d');

        if ($jamPembelian >= 16.00 && $jamPembelian <= 08.00) {
            if ($hariPembelian === $hariPengiriman) {
                $tanggalMinimal = $tanggalLaku->copy()->addDay()->format('d/m/Y');
                $errorMessage = "Pembelian setelah jam 16:00 tidak bisa dikirim di hari yang sama. Minimal tanggal kirim: {$tanggalMinimal}";

                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
                }
                return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
            }
        }

        $jamPengirimanRequest = (int) $tanggalKirimRequest->format('H.i');
        //jam pengiriman di luar operasional
        if (!($jamPengirimanRequest >= 08.00 && $jamPengirimanRequest <= 20.00)) {
            $errorMessage = 'Pengiriman hanya bisa dijadwalkan antara jam 08:00 - 20:00';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalKirim' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalKirim' => $errorMessage])->withInput();
        }

        // Kirim notifikasi (jika diperlukan)
        // $this->kirimNotifikasiPengiriman($pengiriman, $kurir);

        $pengiriman->update([
            'idPegawai' => $request->kurir,
            'tanggalKirim' => $request->tanggalKirim,
            'status' => 'kirim',
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Pengiriman berhasil dijadwalkan.');
    }

    public function penjadwalanAmbil(Request $request, $id)
    {
        $pengiriman = TransaksiPenitipan::find($id);

        $validator = Validator::make($request->all(), [
            'tanggalAmbil' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalAmbilRequest = Carbon::parse($request->tanggalAmbil);
        $sekarang = Carbon::now();

        if ($tanggalAmbilRequest->lt($sekarang->startOfDay())) {
            $errorMessage = 'Tanggal ambil tidak boleh sebelum hari ini';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalAmbil' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalAmbil' => $errorMessage])->withInput();
        }

        $jamPengambilanRequest = (int) $tanggalAmbilRequest->format('H.i');
        //jam pengambilan di luar operasional
        if (!($jamPengambilanRequest >= 08.00 && $jamPengambilanRequest <= 20.00)) {
            $errorMessage = 'Pengambilan hanya bisa dijadwalkan antara jam 08:00 - 20:00';
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['tanggalAmbil' => $errorMessage]], 422);
            }
            return redirect()->back()->withErrors(['tanggalAmbil' => $errorMessage])->withInput();
        }

        $tanggalAmbil = Carbon::parse($request->tanggalAmbil);
        $tanggalBatasAmbil = $tanggalAmbil->copy()->addDays(2);

        $pengiriman->update([
            'tanggalBatasAmbil' => $tanggalBatasAmbil,
            'status' => 'pengambilan',
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Pengambilan berhasil dijadwalkan.');
    }

    public function konfirmasiAmbil($id)
    {
        $pengiriman = TransaksiPenitipan::findOrFail($id);

        $pengiriman->update([
            'status' => 'ambil',
            'tanggalAmbil' => Carbon::now(),
        ]);

        return redirect()->route('gudang.pengiriman.index')->with('success', 'Produk telah diambil.');
    }

    // Create method - FIXED (Menghapus referensi telepon)
    public function create()
    {
        // PERBAIKAN: Tidak mengambil kolom telepon yang tidak ada
        $penitip = DB::table('penitip')
            ->select('idPenitip', 'nama', 'email', 'alamat')
            ->orderBy('nama')
            ->get();

        $pegawai = DB::table('pegawai')
            ->join('jabatan as j', 'pegawai.idJabatan', '=', 'j.idJabatan')
            ->select('pegawai.idPegawai', 'pegawai.nama', 'j.nama as jabatan')
            ->orderBy('pegawai.nama')
            ->get();

        $kategori = DB::table('kategori_produk')
            ->select('idKategori', 'nama')
            ->orderBy('nama')
            ->get();

        return view('pegawai.gudang.penitipan.create', compact('penitip', 'pegawai', 'kategori'));
    }

    // Store method - UPDATED untuk menyimpan gambar ke tabel produk
    public function store(Request $request)
    {
        $request->validate([
            'tanggalMasukPenitipan' => 'required|date',
            'tanggalAkhirPenitipan' => 'required|date|after:tanggalMasukPenitipan',
            'batasAmbil' => 'required|date',
            'pendapatan' => 'required|numeric|min:0',
            'idPenitip' => 'required|exists:penitip,idPenitip',
            'idPegawai' => 'required|exists:pegawai,idPegawai',
            'produk_baru' => 'required|array|min:1',
            'produk_baru.*.deskripsi' => 'required|string|max:100',
            'produk_baru.*.harga' => 'required|numeric|min:0',
            'produk_baru.*.berat' => 'required|numeric|min:0',
            'produk_baru.*.hargaJual' => 'required|numeric|min:0',
            'produk_baru.*.idKategori' => 'required|exists:kategori_produk,idKategori',
            'produk_baru.*.tanggalGaransi' => 'nullable|date',
            'foto_barang' => 'required|array|min:2|max:3',
            'foto_barang.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'produk_baru.required' => 'Minimal satu produk harus diinput',
            'produk_baru.*.deskripsi.required' => 'Deskripsi produk harus diisi',
            'produk_baru.*.harga.required' => 'Harga produk harus diisi',
            'foto_barang.required' => 'Minimal 2 foto barang harus diupload',
            'foto_barang.min' => 'Minimal 2 foto barang harus diupload',
            'foto_barang.max' => 'Maksimal 3 foto barang yang dapat diupload'
        ]);

        DB::beginTransaction();

        try {
            // 1. Upload foto terlebih dahulu
            $uploadedPhotos = [];
            if ($request->hasFile('foto_barang')) {
                $uploadedPhotos = $this->uploadPhotos($request->file('foto_barang'));
            }

            // 2. Insert produk-produk baru dengan gambar
            $produkIds = [];
            foreach ($request->produk_baru as $index => $produkData) {
                // Gunakan gambar yang sama untuk semua produk dalam satu transaksi
                $gambarString = implode(',', $uploadedPhotos);

                $idProduk = DB::table('produk')->insertGetId([
                    'gambar' => $gambarString,
                    'tanggalGaransi' => $produkData['tanggalGaransi'] ?? null,
                    'harga' => $produkData['harga'],
                    'status' => 'Tersedia',
                    'berat' => $produkData['berat'],
                    'hargaJual' => $produkData['hargaJual'],
                    'deskripsi' => $produkData['deskripsi'],
                    'ratingProduk' => 0.00,
                    'idKategori' => $produkData['idKategori'],
                    'idPegawai' => $request->idPegawai,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $produkIds[] = $idProduk;
            }

            // 3. Insert transaksi penitipan
            $idTransaksi = DB::table('transaksi_penitipan')->insertGetId([
                'tanggalMasukPenitipan' => $request->tanggalMasukPenitipan,
                'tanggalAkhirPenitipan' => $request->tanggalAkhirPenitipan,
                'batasAmbil' => $request->batasAmbil,
                'statusPenitipan' => 'Aktif',
                'statusPerpanjangan' => 0,
                'pendapatan' => $request->pendapatan,
                'idPenitip' => $request->idPenitip,
                'idPegawai' => $request->idPegawai,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. Insert detail transaksi untuk setiap produk
            foreach ($produkIds as $idProduk) {
                DB::table('detail_transaksi_penitipan')->insert([
                    'idTransaksiPenitipan' => $idTransaksi,
                    'idProduk' => $idProduk,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->route('gudang.penitipan.index')->with('success', 'Transaksi penitipan berhasil dibuat dengan ' . count($uploadedPhotos) . ' foto barang!');
        } catch (\Exception $e) {
            DB::rollback();
            // Hapus foto yang sudah diupload jika terjadi error
            foreach ($uploadedPhotos ?? [] as $photo) {
                if (file_exists(public_path('uploads/produk/' . $photo))) {
                    unlink(public_path('uploads/produk/' . $photo));
                }
            }
            return back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }

    // Method untuk upload foto - BARU
    private function uploadPhotos($files)
    {
        $uploadPath = 'uploads/produk';

        // Buat direktori jika belum ada
        if (!file_exists(public_path($uploadPath))) {
            mkdir(public_path($uploadPath), 0755, true);
        }

        $uploadedFiles = [];
        foreach ($files as $index => $file) {
            $extension = $file->getClientOriginalExtension();
            $fileName = 'produk_' . time() . '_' . ($index + 1) . '_' . uniqid() . '.' . $extension;

            // Pindahkan file ke direktori tujuan
            $file->move(public_path($uploadPath), $fileName);
            $uploadedFiles[] = $fileName;
        }

        return $uploadedFiles;
    }

    // Edit method - FIXED
    public function edit($id)
    {
        $transaksi = DB::table('transaksi_penitipan')->where('idTransaksiPenitipan', $id)->first();

        if (!$transaksi) {
            return redirect()->route('gudang.penitipan.index')->with('error', 'Transaksi tidak ditemukan!');
        }

        $penitip = DB::table('penitip')
            ->select('idPenitip', 'nama', 'email', 'alamat')
            ->orderBy('nama')
            ->get();

        $pegawai = DB::table('pegawai')
            ->select('idPegawai', 'nama')
            ->orderBy('nama')
            ->get();

        $kategori = DB::table('kategori_produk')
            ->select('idKategori', 'nama')
            ->orderBy('nama')
            ->get();

        // Get produk yang sudah ada di transaksi ini
        $produkTransaksi = DB::table('detail_transaksi_penitipan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->select('pr.*')
            ->where('dtp.idTransaksiPenitipan', $id)
            ->get();

        // TAMBAHAN: Get semua produk yang tersedia untuk dipilih
        $produk = DB::table('produk as pr')
            ->join('kategori_produk as kp', 'pr.idKategori', '=', 'kp.idKategori')
            ->select(
                'pr.idProduk',
                'pr.deskripsi as nama',
                'pr.harga',
                'pr.hargaJual',
                'pr.status',
                'kp.nama as kategori'
            )
            ->where('pr.status', 'Tersedia') // Hanya produk yang tersedia
            ->orderBy('pr.deskripsi')
            ->get();

        // Get ID produk yang sudah dipilih untuk transaksi ini
        $selectedProduk = DB::table('detail_transaksi_penitipan')
            ->where('idTransaksiPenitipan', $id)
            ->pluck('idProduk')
            ->toArray();

        return view('pegawai.gudang.penitipan.edit', compact(
            'transaksi',
            'penitip',
            'pegawai',
            'produkTransaksi',
            'kategori',
            'produk',
            'selectedProduk'
        ));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggalMasukPenitipan' => 'required|date',
            'tanggalAkhirPenitipan' => 'required|date|after:tanggalMasukPenitipan',
            'batasAmbil' => 'required|date',
            'statusPenitipan' => 'required|in:Aktif,Selesai,Expired',
            'statusPerpanjangan' => 'required|boolean',
            'pendapatan' => 'required|numeric|min:0',
            'idPenitip' => 'required|exists:penitip,idPenitip',
            'idPegawai' => 'required|exists:pegawai,idPegawai',
            // Validation for existing products
            'produk_existing' => 'sometimes|array',
            'produk_existing.*.deskripsi' => 'required_with:produk_existing|string|max:100',
            'produk_existing.*.harga' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.berat' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.hargaJual' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.idKategori' => 'required_with:produk_existing|exists:kategori_produk,idKategori',
            'produk_existing.*.tanggalGaransi' => 'nullable|date',
            // Validation for new products
            'produk_baru' => 'sometimes|array',
            'produk_baru.*.deskripsi' => 'required_with:produk_baru|string|max:100',
            'produk_baru.*.harga' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.berat' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.hargaJual' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.idKategori' => 'required_with:produk_baru|exists:kategori_produk,idKategori',
            'produk_baru.*.tanggalGaransi' => 'nullable|date',
            'foto_barang_baru' => 'required_with:produk_baru|array|min:2|max:3',
            'foto_barang_baru.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();

        try {
            // 1. Update transaksi penitipan
            DB::table('transaksi_penitipan')
                ->where('idTransaksiPenitipan', $id)
                ->update([
                    'tanggalMasukPenitipan' => $request->tanggalMasukPenitipan,
                    'tanggalAkhirPenitipan' => $request->tanggalAkhirPenitipan,
                    'batasAmbil' => $request->batasAmbil,
                    'statusPenitipan' => $request->statusPenitipan,
                    'statusPerpanjangan' => $request->statusPerpanjangan,
                    'pendapatan' => $request->pendapatan,
                    'idPenitip' => $request->idPenitip,
                    'idPegawai' => $request->idPegawai,
                    'updated_at' => now()
                ]);

            // 2. Update existing products if provided
            if ($request->has('produk_existing')) {
                foreach ($request->produk_existing as $produkId => $produkData) {
                    DB::table('produk')
                        ->where('idProduk', $produkId)
                        ->update([
                            'deskripsi' => $produkData['deskripsi'],
                            'harga' => $produkData['harga'],
                            'berat' => $produkData['berat'],
                            'hargaJual' => $produkData['hargaJual'],
                            'idKategori' => $produkData['idKategori'],
                            'tanggalGaransi' => $produkData['tanggalGaransi'] ?? null,
                            'updated_at' => now()
                        ]);
                }
            }

            // 3. Add new products if provided
            if ($request->has('produk_baru') && count($request->produk_baru) > 0) {
                // Upload foto baru
                $uploadedPhotos = [];
                if ($request->hasFile('foto_barang_baru')) {
                    $uploadedPhotos = $this->uploadPhotos($request->file('foto_barang_baru'));
                }

                $gambarString = implode(',', $uploadedPhotos);

                foreach ($request->produk_baru as $produkData) {
                    $idProduk = DB::table('produk')->insertGetId([
                        'gambar' => $gambarString,
                        'tanggalGaransi' => $produkData['tanggalGaransi'] ?? null,
                        'harga' => $produkData['harga'],
                        'status' => 'Tersedia',
                        'berat' => $produkData['berat'],
                        'hargaJual' => $produkData['hargaJual'],
                        'deskripsi' => $produkData['deskripsi'],
                        'ratingProduk' => 0.00,
                        'idKategori' => $produkData['idKategori'],
                        'idPegawai' => $request->idPegawai,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Add to detail transaksi
                    DB::table('detail_transaksi_penitipan')->insert([
                        'idTransaksiPenitipan' => $id,
                        'idProduk' => $idProduk,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('gudang.penitipan.index')->with('success', 'Transaksi penitipan berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollback();
            // Hapus foto baru yang sudah diupload jika terjadi error
            if (isset($uploadedPhotos)) {
                foreach ($uploadedPhotos as $photo) {
                    if (file_exists(public_path('uploads/produk/' . $photo))) {
                        unlink(public_path('uploads/produk/' . $photo));
                    }
                }
            }
            return back()->with('error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
        }
    }

    // Destroy method
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Get produk yang akan dihapus untuk menghapus foto-fotonya
            $produkList = DB::table('detail_transaksi_penitipan as dtp')
                ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
                ->select('pr.idProduk', 'pr.gambar')
                ->where('dtp.idTransaksiPenitipan', $id)
                ->get();

            // Hapus foto-foto produk
            foreach ($produkList as $produk) {
                if ($produk->gambar) {
                    $images = explode(',', $produk->gambar);
                    foreach ($images as $image) {
                        $imagePath = public_path('uploads/produk/' . trim($image));
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                }
            }

            // Delete detail first (foreign key constraint)
            DB::table('detail_transaksi_penitipan')->where('idTransaksiPenitipan', $id)->delete();

            // Delete produk
            foreach ($produkList as $produk) {
                DB::table('produk')->where('idProduk', $produk->idProduk)->delete();
            }

            // Delete main transaction
            DB::table('transaksi_penitipan')->where('idTransaksiPenitipan', $id)->delete();

            DB::commit();
            return redirect()->route('gudang.penitipan.index')->with('success', 'Transaksi berhasil dihapus beserta semua produk dan foto!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    // Apply filters method
    private function applyFilters($query, $request)
    {
        // Quick search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('p.nama', 'LIKE', "%{$search}%")
                    ->orWhere('tp.idTransaksiPenitipan', 'LIKE', "%{$search}%")
                    ->orWhere('pg.nama', 'LIKE', "%{$search}%")
                    ->orWhere('p.email', 'LIKE', "%{$search}%"); // Tambah pencarian email
            });
        }

        if ($request->filled('status')) {
            $query->where('tp.statusPenitipan', $request->status);
        }

        // Advanced search filters
        if ($request->filled('advanced_search')) {
            if ($request->filled('adv_id')) {
                $query->where('tp.idTransaksiPenitipan', 'LIKE', '%' . $request->adv_id . '%');
            }

            if ($request->filled('adv_nama_penitip')) {
                $query->where('p.nama', 'LIKE', '%' . $request->adv_nama_penitip . '%');
            }

            if ($request->filled('adv_nama_pegawai')) {
                $query->where('pg.nama', 'LIKE', '%' . $request->adv_nama_pegawai . '%');
            }

            if ($request->filled('adv_tanggal_masuk_dari')) {
                $query->whereDate('tp.tanggalMasukPenitipan', '>=', $request->adv_tanggal_masuk_dari);
            }

            if ($request->filled('adv_tanggal_masuk_sampai')) {
                $query->whereDate('tp.tanggalMasukPenitipan', '<=', $request->adv_tanggal_masuk_sampai);
            }

            if ($request->filled('adv_status')) {
                $query->where('tp.statusPenitipan', $request->adv_status);
            }

            if ($request->filled('adv_pendapatan_min')) {
                $query->where('tp.pendapatan', '>=', $request->adv_pendapatan_min);
            }

            if ($request->filled('adv_pendapatan_max')) {
                $query->where('tp.pendapatan', '<=', $request->adv_pendapatan_max);
            }

            if ($request->filled('adv_perpanjangan')) {
                $query->where('tp.statusPerpanjangan', $request->adv_perpanjangan);
            }

            if ($request->filled('adv_expired_only')) {
                switch ($request->adv_expired_only) {
                    case 'akan_expired':
                        $query->whereBetween('tp.batasAmbil', [Carbon::now(), Carbon::now()->addDays(7)])
                            ->where('tp.statusPenitipan', 'Aktif');
                        break;
                    case 'sudah_expired':
                        $query->where('tp.batasAmbil', '<', Carbon::now())
                            ->where('tp.statusPenitipan', 'Aktif');
                        break;
                    case 'hari_ini':
                        $query->whereDate('tp.created_at', Carbon::today());
                        break;
                }
            }
        }
    }

    // Method untuk mencetak nota PDF
    public function printNota($id)
    {
        $transaksi = DB::table('transaksi_penitipan as tp')
            ->join('penitip as p', 'tp.idPenitip', '=', 'p.idPenitip')
            ->join('pegawai as pg', 'tp.idPegawai', '=', 'pg.idPegawai')
            ->select(
                'tp.*',
                'p.nama as namaPenitip',
                'p.email as emailPenitip',
                'p.alamat as alamatPenitip',
                'pg.nama as namaPegawai'
            )
            ->where('tp.idTransaksiPenitipan', $id)
            ->first();

        if (!$transaksi) {
            return redirect()->route('gudang.penitipan.index')->with('error', 'Transaksi tidak ditemukan!');
        }

        // PERBAIKAN: Query detail harus konsisten dengan template HTML
        $detail = DB::table('detail_transaksi_penitipan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->join('kategori_produk as kp', 'pr.idKategori', '=', 'kp.idKategori')
            ->select(
                'pr.idProduk',
                'pr.deskripsi as namaProduk',
                'pr.harga',           // Tidak di-alias, sesuai template
                'pr.hargaJual',       // Tidak di-alias, sesuai template
                'pr.berat',           // Tidak di-alias, sesuai template
                'pr.gambar',
                'pr.tanggalGaransi',
                'pr.status',
                'pr.ratingProduk',
                'kp.nama as kategori'
            )
            ->where('dtp.idTransaksiPenitipan', $id)
            ->get();

        // Data untuk PDF
        $data = [
            'transaksi' => $transaksi,
            'detail' => $detail,
            'tanggal_cetak' => now(),
            'nomor_nota' => 'NOTA-' . str_pad($id, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd')
        ];

        $pdf = Pdf::loadView('pegawai.gudang.penitipan.print-nota', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Nota_Penitipan_' . $transaksi->idTransaksiPenitipan . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}

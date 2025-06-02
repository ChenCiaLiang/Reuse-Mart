<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Pegawai;
use App\Models\Pembeli;
use App\Models\Penitip;
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

    // Index method - UPDATED untuk status baru
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
                'p.email as emailPenitip',
                'pg.nama as namaPegawai'
            );

        // Apply filters
        $this->applyFilters($query, $request);

        $transaksi = $query->orderBy('tp.created_at', 'desc')->paginate(10);

        return view('pegawai.gudang.penitipan.index', compact('transaksi'));
    }

    // Index method untuk penitip
    public function indexPenitip(Request $request)
    {
        $search = $request->input('search');
        $idPenitip = session('user')['idPenitip'];

        $penitipan = TransaksiPenitipan::where('idPenitip', $idPenitip)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('idTransaksiPenitipan', 'like', '%' . $search . '%')
                        ->orWhere('tanggalMasukPenitipan', 'like', '%' . $search . '%')
                        ->orWhere('tanggalAkhirPenitipan', 'like', '%' . $search . '%')
                        ->orWhere('statusPenitipan', 'like', '%' . $search . '%')
                        ->orWhere('statusPerpanjangan', 'like', '%' . $search . '%')
                        ->orWhere('pendapatan', 'like', '%' . $search . '%');
                });
            })
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
        $penitipan = TransaksiPenitipan::findOrFail($id);
        $produk = Produk::find($penitipan->detailTransaksiPenitipan[0]->idProduk);
        $penitipan->produk = $produk->deskripsi;
        $penitipan->namaPegawai = Pegawai::find($penitipan->idPegawai)->nama ?? '-';
        $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];

        return view('customer.penitip.penitipan.show', compact('penitipan', 'gambarArray'));
    }

    public function perpanjangan($id)
    {
        $penitipan = TransaksiPenitipan::findOrFail($id);
        $tanggalAkhirPenitipan = Carbon::parse($penitipan->tanggalAkhirPenitipan);

        if ($penitipan->statusPerpanjangan == 1) {
            return redirect()->route('penitip.penitipan.index')->with('error', 'Penitipan sudah diperpanjang sebelumnya');
        }
        $penitipan->update([
            'tanggalAkhirPenitipan' => $tanggalAkhirPenitipan->addDays(30),
            'statusPerpanjangan' => 1,
        ]);

        return redirect()->route('penitip.penitipan.index')->with('success', 'Perpanjangan berhasil dilakukan');
    }

    public function konfirmasiAmbil($id)
    {
        $penitipan = TransaksiPenitipan::findOrFail($id);

        if ($penitipan->statusPenitipan != 'Hangus' || !$penitipan) {
            return redirect()->route('penitip.penitipan.index')->with('error', 'Hanya penitipan hangus yang bisa dikonfirmasi ambil');
        }

        $penitipan->update([
            'statusPenitipan' => 'Ambil',
            'batasAmbil' => Carbon::now()->addDays(7),
        ]);

        return redirect()->route('penitip.penitipan.index')->with('success', 'Penitipan berhasil dikonfirmasi ambil');
    }

    // UPDATED method untuk konfirmasi diambil dari gudang
    public function konfirmasiDiambil($id)
    {
        DB::beginTransaction();

        try {
            $transaksi = DB::table('transaksi_penitipan')->where('idTransaksiPenitipan', $id)->first();

            if (!$transaksi) {
                return redirect()->route('gudang.penitipan.index')->with('error', 'Transaksi tidak ditemukan!');
            }

            if ($transaksi->statusPenitipan != 'Ambil') {
                return redirect()->route('gudang.penitipan.index')->with('error', 'Hanya transaksi dengan status "Ambil" yang bisa dikonfirmasi diambil!');
            }

            // Update status transaksi menjadi "Diambil"
            DB::table('transaksi_penitipan')
                ->where('idTransaksiPenitipan', $id)
                ->update([
                    'statusPenitipan' => 'Diambil',
                    'updated_at' => now(),
                    'tanggalPengambilan' => now(),
                ]);

            // Update status produk yang terkait menjadi "Diambil Kembali"
            $produkIds = DB::table('detail_transaksi_penitipan')
                ->where('idTransaksiPenitipan', $id)
                ->pluck('idProduk');

            DB::table('produk')
                ->whereIn('idProduk', $produkIds)
                ->update([
                    'status' => 'Diambil Kembali',
                    'updated_at' => now()
                ]);

            DB::table('komisi')
                ->whereIn('idProduk', $produkIds)
                ->update([
                    'status' => 'Diambil Kembali',
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('gudang.penitipan.index')->with('success', 'Konfirmasi berhasil! Status transaksi telah diubah menjadi "Diambil".');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('gudang.penitipan.index')->with('error', 'Gagal mengkonfirmasi pengambilan: ' . $e->getMessage());
        }
    }

    // Create method
    public function create()
    {
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

    // Store method - UPDATED untuk foto per produk
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
        ], [
            'produk_baru.required' => 'Minimal satu produk harus diinput',
            'produk_baru.*.deskripsi.required' => 'Deskripsi produk harus diisi',
            'produk_baru.*.harga.required' => 'Harga produk harus diisi',
        ]);

        // Validasi foto per produk
        foreach ($request->produk_baru as $index => $produk) {
            $fotoKey = "foto_produk_{$index}";
            if (!$request->hasFile($fotoKey)) {
                return back()->withErrors([$fotoKey => "Foto untuk produk #" . ($index + 1) . " harus diupload (minimal 2 foto)"])->withInput();
            }

            $files = $request->file($fotoKey);
            if (count($files) < 2) {
                return back()->withErrors([$fotoKey => "Minimal 2 foto untuk produk #" . ($index + 1)])->withInput();
            }

            if (count($files) > 3) {
                return back()->withErrors([$fotoKey => "Maksimal 3 foto untuk produk #" . ($index + 1)])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // 1. Insert produk-produk baru dengan foto masing-masing
            $produkIds = [];
            foreach ($request->produk_baru as $index => $produkData) {
                // Upload foto untuk produk ini
                $uploadedPhotos = [];
                $fotoKey = "foto_produk_{$index}";
                if ($request->hasFile($fotoKey)) {
                    $uploadedPhotos = $this->uploadPhotosPerProduk($request->file($fotoKey), $index);
                }

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

            // 2. Insert transaksi penitipan
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

            // 3. Insert detail transaksi untuk setiap produk
            foreach ($produkIds as $idProduk) {
                DB::table('detail_transaksi_penitipan')->insert([
                    'idTransaksiPenitipan' => $idTransaksi,
                    'idProduk' => $idProduk,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->route('gudang.penitipan.index')->with('success', 'Transaksi penitipan berhasil dibuat dengan ' . count($produkIds) . ' produk!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }

    // Method untuk upload foto per produk - BARU
    private function uploadPhotosPerProduk($files, $produkIndex)
    {
        $uploadPath = 'uploads/produk';

        if (!file_exists(public_path($uploadPath))) {
            mkdir(public_path($uploadPath), 0755, true);
        }

        $uploadedFiles = [];
        foreach ($files as $index => $file) {
            $extension = $file->getClientOriginalExtension();
            $fileName = 'produk_' . time() . '_' . $produkIndex . '_' . ($index + 1) . '_' . uniqid() . '.' . $extension;

            $file->move(public_path($uploadPath), $fileName);
            $uploadedFiles[] = $fileName;
        }

        return $uploadedFiles;
    }

    // Edit method
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

        $produkTransaksi = DB::table('detail_transaksi_penitipan as dtp')
            ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
            ->select('pr.*')
            ->where('dtp.idTransaksiPenitipan', $id)
            ->get();

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
            ->where('pr.status', 'Tersedia')
            ->orderBy('pr.deskripsi')
            ->get();

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

    // Update method - UPDATED untuk foto per produk  
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggalMasukPenitipan' => 'required|date',
            'tanggalAkhirPenitipan' => 'required|date|after:tanggalMasukPenitipan',
            'batasAmbil' => 'required|date',
            'statusPenitipan' => 'required|in:Aktif,Selesai,Hangus,Ambil,Diambil',
            'statusPerpanjangan' => 'required|boolean',
            'pendapatan' => 'required|numeric|min:0',
            'idPenitip' => 'required|exists:penitip,idPenitip',
            'idPegawai' => 'required|exists:pegawai,idPegawai',
            'produk_existing' => 'sometimes|array',
            'produk_existing.*.deskripsi' => 'required_with:produk_existing|string|max:100',
            'produk_existing.*.harga' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.berat' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.hargaJual' => 'required_with:produk_existing|numeric|min:0',
            'produk_existing.*.idKategori' => 'required_with:produk_existing|exists:kategori_produk,idKategori',
            'produk_existing.*.tanggalGaransi' => 'nullable|date',
            'produk_baru' => 'sometimes|array',
            'produk_baru.*.deskripsi' => 'required_with:produk_baru|string|max:100',
            'produk_baru.*.harga' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.berat' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.hargaJual' => 'required_with:produk_baru|numeric|min:0',
            'produk_baru.*.idKategori' => 'required_with:produk_baru|exists:kategori_produk,idKategori',
            'produk_baru.*.tanggalGaransi' => 'nullable|date',
        ]);

        // Validasi foto untuk produk baru jika ada
        if ($request->has('produk_baru') && count($request->produk_baru) > 0) {
            foreach ($request->produk_baru as $index => $produk) {
                $fotoKey = "foto_produk_baru_{$index}";
                if (!$request->hasFile($fotoKey)) {
                    return back()->withErrors([$fotoKey => "Foto untuk produk baru #" . ($index + 1) . " harus diupload (minimal 2 foto)"])->withInput();
                }

                $files = $request->file($fotoKey);
                if (count($files) < 2) {
                    return back()->withErrors([$fotoKey => "Minimal 2 foto untuk produk baru #" . ($index + 1)])->withInput();
                }

                if (count($files) > 3) {
                    return back()->withErrors([$fotoKey => "Maksimal 3 foto untuk produk baru #" . ($index + 1)])->withInput();
                }
            }
        }

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
                    $updateData = [
                        'deskripsi' => $produkData['deskripsi'],
                        'harga' => $produkData['harga'],
                        'berat' => $produkData['berat'],
                        'hargaJual' => $produkData['hargaJual'],
                        'idKategori' => $produkData['idKategori'],
                        'tanggalGaransi' => $produkData['tanggalGaransi'] ?? null,
                        'updated_at' => now()
                    ];

                    // Check if there are new photos for this existing product
                    $fotoKey = "foto_produk_existing_{$produkId}";
                    if ($request->hasFile($fotoKey)) {
                        $files = $request->file($fotoKey);
                        if (count($files) >= 2 && count($files) <= 3) {
                            // Delete old photos
                            $oldProduct = DB::table('produk')->where('idProduk', $produkId)->first();
                            if ($oldProduct && $oldProduct->gambar) {
                                $oldImages = explode(',', $oldProduct->gambar);
                                foreach ($oldImages as $image) {
                                    $imagePath = public_path('uploads/produk/' . trim($image));
                                    if (file_exists($imagePath)) {
                                        unlink($imagePath);
                                    }
                                }
                            }

                            // Upload new photos
                            $uploadedPhotos = $this->uploadPhotosPerProduk($files, 'existing_' . $produkId);
                            $updateData['gambar'] = implode(',', $uploadedPhotos);
                        }
                    }

                    DB::table('produk')->where('idProduk', $produkId)->update($updateData);
                }
            }

            // 3. Add new products if provided
            if ($request->has('produk_baru') && count($request->produk_baru) > 0) {
                foreach ($request->produk_baru as $index => $produkData) {
                    // Upload foto untuk produk baru ini
                    $uploadedPhotos = [];
                    $fotoKey = "foto_produk_baru_{$index}";
                    if ($request->hasFile($fotoKey)) {
                        $uploadedPhotos = $this->uploadPhotosPerProduk($request->file($fotoKey), 'new_' . $index);
                    }

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
            return back()->with('error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
        }
    }

    // Destroy method
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
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

    // Apply filters method - UPDATED untuk status baru
    private function applyFilters($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('p.nama', 'LIKE', "%{$search}%")
                    ->orWhere('tp.idTransaksiPenitipan', 'LIKE', "%{$search}%")
                    ->orWhere('pg.nama', 'LIKE', "%{$search}%")
                    ->orWhere('p.email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('tp.statusPenitipan', $request->status);
        }

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

            if ($request->filled('adv_filter_khusus')) {
                switch ($request->adv_filter_khusus) {
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

        $tahun = date('y');
        $bulan = date('m');
        $nomorNota = $tahun . '.' . $bulan . '.' . $id;

        $data = [
            'transaksi' => $transaksi,
            'detail' => $detail,
            'tanggal_cetak' => now(),
            'nomor_nota' => $nomorNota
        ];

        $pdf = Pdf::loadView('pegawai.gudang.penitipan.print-nota', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Nota_Penitipan_' . $transaksi->idTransaksiPenitipan . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}

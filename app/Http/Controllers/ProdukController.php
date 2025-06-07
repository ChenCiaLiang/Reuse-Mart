<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\DiskusiProduk;
use App\Models\KategoriProduk;
use App\Models\DetailTransaksiPenitipan;
use App\Models\Penitip;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        session()->forget('direct_buy');

        // Ambil parameter pencarian
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $status = $request->input('status');

        // Parameter untuk API mobile
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $isApi = $request->expectsJson() || $request->is('api/*');

        // Query dasar
        $query = Produk::with(['Kategori']);

        // Untuk API mobile, hanya tampilkan produk tersedia
        // Untuk web, tampilkan semua untuk konteks visual
        if ($isApi) {
            $query->where('status', 'Tersedia');
        }

        // Filter berdasarkan pencarian teks
        if ($search) {
            $query->where('deskripsi', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan kategori
        if ($kategori) {
            $query->where('idKategori', $kategori);
        }

        // Filter berdasarkan status (untuk web)
        if ($status && !$isApi) {
            $query->where('status', $status);
        }

        // Jika request dari API (mobile)
        if ($isApi) {
            return $this->getApiResponse($query, $page, $limit);
        }

        // Jika request dari web
        $produk = $query->orderBy('created_at', 'desc')->get();
        $kategoriList = KategoriProduk::all();

        // Data untuk statistik status (khusus web)
        $statusStats = [
            'tersedia' => Produk::where('status', 'Tersedia')->count(),
            'terjual' => Produk::where('status', 'Terjual')->count(),
            'didonasikan' => Produk::where('status', 'Didonasikan')->count(),
        ];

        return view('produk.index', compact('produk', 'kategoriList', 'search', 'kategori', 'status', 'statusStats'));
    }

    public function indexPopup()
    {
        session()->forget('direct_buy');

        // Untuk popup, tetap hanya tampilkan produk yang tersedia
        $produk = Produk::where('status', 'Tersedia')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil kategori untuk filter
        $kategori = KategoriProduk::all();

        return view('produk.showPopup', compact('produk', 'kategori'));
    }

    public function show(Request $request, $id)
    {
        session()->forget('direct_buy');

        $isApi = $request->expectsJson() || $request->is('api/*');

        try {
            // Ambil data produk berdasarkan ID
            $produk = Produk::with(['Kategori'])->findOrFail($id);

            // Ambil data penitip dari produk melalui detail transaksi penitipan
            $penitip = null;
            $ratingPenitip = 0;

            $detailPenitipan = DetailTransaksiPenitipan::where('idProduk', $id)
                ->with(['transaksiPenitipan.penitip'])
                ->first();

            if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
                $penitip = $detailPenitipan->transaksiPenitipan->penitip;
                $ratingPenitip = $penitip ? $penitip->rating : 0;
            }

            // Ambil gambar-gambar produk dari field gambar
            $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];

            // Jika request dari API (mobile)
            if ($isApi) {
                return $this->getDetailApiResponse($produk, $gambarArray, $penitip, $ratingPenitip);
            }

            // Jika request dari web
            // Ambil produk terkait (dari kategori yang sama, hanya yang tersedia)
            $produkTerkait = Produk::where('idKategori', $produk->idKategori)
                ->where('idProduk', '!=', $id)
                ->where('status', 'Tersedia')
                ->limit(4)
                ->get();

            // Ambil diskusi produk
            $diskusi = DiskusiProduk::where('idProduk', $id)
                ->with(['pembeli', 'pegawai'])
                ->orderBy('tanggalDiskusi', 'desc')
                ->get();

            return view('produk.show', compact('produk', 'gambarArray', 'produkTerkait', 'diskusi', 'penitip', 'ratingPenitip'));
        } catch (\Exception $e) {
            if ($isApi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil detail produk',
                    'error' => $e->getMessage()
                ], 500);
            }

            abort(404);
        }
    }

    public function kategori(Request $request)
    {
        $isApi = $request->expectsJson() || $request->is('api/*');

        try {
            $kategori = KategoriProduk::all();

            if ($isApi) {
                $kategoriFormatted = $kategori->map(function ($item) {
                    return [
                        'idKategori' => $item->idKategori,
                        'nama' => $item->nama
                    ];
                });

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kategori berhasil diambil',
                    'data' => $kategoriFormatted
                ], 200);
            }

            // Untuk web, return view atau data sesuai kebutuhan
            return response()->json($kategori);
        } catch (\Exception $e) {
            if ($isApi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data kategori',
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function getApiResponse($query, $page, $limit)
    {
        try {
            // Pagination
            $offset = ($page - 1) * $limit;
            $totalCount = $query->count();
            $produk = $query->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            // Format data untuk mobile
            $produkFormatted = $produk->map(function ($item) {
                // Ambil gambar utama (gambar pertama)
                $gambarArray = $item->gambar ? explode(',', $item->gambar) : ['default.jpg'];
                $thumbnailFoto = $gambarArray[0];

                // Cek status garansi
                $statusGaransi = $this->checkGaransiStatus($item->tanggalGaransi);

                return [
                    'idProduk' => $item->idProduk,
                    'deskripsi' => $item->deskripsi,
                    'hargaJual' => $item->hargaJual,
                    'thumbnailFoto' => asset('storage/produk/' . $thumbnailFoto),
                    'kategori' => $item->kategoriProduk->nama ?? '',
                    'statusGaransi' => $statusGaransi,
                    'tanggalGaransi' => $item->tanggalGaransi,
                    'ratingProduk' => $item->ratingProduk,
                    'created_at' => $item->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data produk berhasil diambil',
                'data' => [
                    'produk' => $produkFormatted,
                    'pagination' => [
                        'current_page' => (int)$page,
                        'total_items' => $totalCount,
                        'items_per_page' => (int)$limit,
                        'total_pages' => ceil($totalCount / $limit),
                        'has_next' => ($page * $limit) < $totalCount,
                        'has_prev' => $page > 1
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getDetailApiResponse($produk, $gambarArray, $penitip, $ratingPenitip)
    {
        // Ambil gambar-gambar produk (minimal 2 foto)
        $fotoProduk = array_map(function ($gambar) {
            return asset('storage/produk/' . trim($gambar));
        }, $gambarArray);

        // Cek status garansi
        $statusGaransi = $this->checkGaransiStatus($produk->tanggalGaransi);

        // Format data untuk mobile
        $produkDetail = [
            'idProduk' => $produk->idProduk,
            'deskripsi' => $produk->deskripsi,
            'harga' => $produk->harga,
            'hargaJual' => $produk->hargaJual,
            'berat' => $produk->berat,
            'status' => $produk->status,
            'ratingProduk' => $produk->ratingProduk,
            'fotoProduk' => $fotoProduk, // minimal 2 foto
            'kategori' => [
                'id' => $produk->kategoriProduk->idKategori ?? null,
                'nama' => $produk->kategoriProduk->nama ?? ''
            ],
            'garansi' => [
                'tanggalGaransi' => $produk->tanggalGaransi,
                'statusGaransi' => $statusGaransi,
                'masihBerlaku' => $statusGaransi === 'Bergaransi'
            ],
            'penitip' => [
                'nama' => $penitip->nama ?? 'Tidak diketahui',
                'rating' => $ratingPenitip
            ],
            'created_at' => $produk->created_at->format('Y-m-d H:i:s')
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk berhasil diambil',
            'data' => $produkDetail
        ], 200);
    }

    /**
     * Helper method untuk mengecek status garansi
     */
    private function checkGaransiStatus($tanggalGaransi)
    {
        if (!$tanggalGaransi) {
            return 'Tidak Bergaransi';
        }

        $today = now();
        $garansiDate = \Carbon\Carbon::parse($tanggalGaransi);

        if ($garansiDate->isFuture()) {
            return 'Bergaransi';
        } else {
            return 'Garansi Habis';
        }
    }
}

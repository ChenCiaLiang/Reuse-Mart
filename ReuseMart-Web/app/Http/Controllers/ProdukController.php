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
        $query = Produk::with(['kategori']);

        // Untuk API mobile, hanya tampilkan produk tersedia
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

    public function show(Request $request, $id)
    {
        session()->forget('direct_buy');

        $isApi = $request->expectsJson() || $request->is('api/*');

        try {
            // Ambil data produk berdasarkan ID dengan relasi
            $produk = Produk::with(['kategori'])->findOrFail($id);

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

            // Jika request dari API (mobile)
            if ($isApi) {
                return $this->getDetailApiResponse($produk, $penitip, $ratingPenitip);
            }

            // Jika request dari web
            $gambarArray = $this->parseGambarArray($produk->gambar);

            // Ambil produk terkait
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
            \Log::error('Error in produk detail: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'isApi' => $isApi
            ]);

            if ($isApi) {
                return response()->json([
                    'success' => false,
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
                        'idKategori' => (int) $item->idKategori,
                        'nama' => (string) $item->nama
                    ];
                });
                return response()->json([
                    'success' => true,
                    'status' => 'success',
                    'message' => 'Data kategori berhasil diambil',
                    'data' => $kategoriFormatted->values()->toArray()
                ], 200);
            }

            return response()->json($kategori);
        } catch (\Exception $e) {
            \Log::error('Error in kategori: ' . $e->getMessage());

            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Gagal mengambil data kategori',
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Response untuk API mobile - List Produk
     */
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
                // Ambil thumbnail (foto pertama) - dengan path assets
                $thumbnailFoto = $this->getThumbnailAssetPath($item->gambar);

                // Cek status garansi
                $statusGaransi = $this->checkGaransiStatus($item->tanggalGaransi);

                return [
                    'idProduk' => (int) $item->idProduk,
                    'deskripsi' => (string) $item->deskripsi,
                    'harga' => (float) $item->harga,
                    'hargaJual' => (float) $item->hargaJual,
                    'berat' => (float) $item->berat,
                    'status' => (string) $item->status,
                    'thumbnailFoto' => $thumbnailFoto,
                    'thumbnail' => $thumbnailFoto, // alias untuk compatibility
                    'kategori' => (string) ($item->kategoriProduk->nama ?? ''),
                    'idKategori' => (int) $item->idKategori,
                    'statusGaransi' => (string) $statusGaransi,
                    'tanggalGaransi' => $this->formatDate($item->tanggalGaransi),
                    'ratingProduk' => (float) ($item->ratingProduk ?? 0),
                    'rating' => (float) ($item->ratingProduk ?? 0),
                    'garansi' => [
                        'tanggal' => $this->formatDate($item->tanggalGaransi),
                        'tanggalGaransi' => $this->formatDate($item->tanggalGaransi),
                        'status' => $statusGaransi,
                        'keterangan' => $statusGaransi,
                        'masihBerlaku' => $statusGaransi === 'Bergaransi',
                        'isBergaransi' => $statusGaransi === 'Bergaransi',
                        'isGaransiHabis' => $statusGaransi === 'Garansi Habis',
                        'isTidakBergaransi' => $statusGaransi === 'Tidak Bergaransi'
                    ],
                    'created_at' => $this->formatDate($item->created_at),
                    'createdAt' => $this->formatDate($item->created_at)
                ];
            });

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data produk berhasil diambil',
                'data' => [
                    'produk' => $produkFormatted->values()->toArray(),
                    'pagination' => [
                        'current_page' => (int) $page,
                        'currentPage' => (int) $page,
                        'total_items' => (int) $totalCount,
                        'totalItems' => (int) $totalCount,
                        'items_per_page' => (int) $limit,
                        'itemsPerPage' => (int) $limit,
                        'total_pages' => (int) ceil($totalCount / $limit),
                        'totalPages' => (int) ceil($totalCount / $limit),
                        'has_next' => ($page * $limit) < $totalCount,
                        'hasNext' => ($page * $limit) < $totalCount,
                        'has_prev' => $page > 1,
                        'hasPrev' => $page > 1
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in getApiResponse: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Response untuk API mobile - Detail Produk
     */
    private function getDetailApiResponse($produk, $penitip, $ratingPenitip)
    {
        try {
            // Ambil semua foto produk untuk gallery - dengan path assets
            $fotoProduk = $this->getAllFotoAssetPaths($produk->gambar);

            // Cek status garansi
            $statusGaransi = $this->checkGaransiStatus($produk->tanggalGaransi);

            // Ambil produk terkait dengan format yang konsisten
            $produkTerkait = Produk::with(['kategori'])
                ->where('idKategori', $produk->idKategori)
                ->where('idProduk', '!=', $produk->idProduk)
                ->where('status', 'Tersedia')
                ->limit(4)
                ->get()
                ->map(function ($item) {
                    $thumbnailFoto = $this->getThumbnailAssetPath($item->gambar);
                    $statusGaransi = $this->checkGaransiStatus($item->tanggalGaransi);

                    return [
                        'idProduk' => (int) $item->idProduk,
                        'deskripsi' => (string) $item->deskripsi,
                        'harga' => (float) $item->harga,
                        'hargaJual' => (float) $item->hargaJual,
                        'berat' => (float) $item->berat,
                        'status' => (string) $item->status,
                        'thumbnail' => $thumbnailFoto,
                        'thumbnailFoto' => $thumbnailFoto,
                        'kategori' => (string) ($item->kategoriProduk->nama ?? ''),
                        'idKategori' => (int) $item->idKategori,
                        'rating' => (float) ($item->ratingProduk ?? 0),
                        'ratingProduk' => (float) ($item->ratingProduk ?? 0),
                        'garansi' => [
                            'tanggal' => $this->formatDate($item->tanggalGaransi),
                            'tanggalGaransi' => $this->formatDate($item->tanggalGaransi),
                            'status' => $statusGaransi,
                            'keterangan' => $statusGaransi,
                            'masihBerlaku' => $statusGaransi === 'Bergaransi',
                            'isBergaransi' => $statusGaransi === 'Bergaransi',
                            'isGaransiHabis' => $statusGaransi === 'Garansi Habis',
                            'isTidakBergaransi' => $statusGaransi === 'Tidak Bergaransi'
                        ],
                        'created_at' => $this->formatDate($item->created_at),
                        'createdAt' => $this->formatDate($item->created_at)
                    ];
                });

            // Format data untuk mobile
            $produkDetail = [
                'idProduk' => (int) $produk->idProduk,
                'deskripsi' => (string) $produk->deskripsi,
                'harga' => (float) $produk->harga,
                'hargaJual' => (float) $produk->hargaJual,
                'berat' => (float) $produk->berat,
                'status' => (string) $produk->status,
                'ratingProduk' => (float) ($produk->ratingProduk ?? 0),
                'rating' => (float) ($produk->ratingProduk ?? 0),
                'fotoProduk' => $fotoProduk, // array semua path assets foto untuk gallery
                'gambar' => $fotoProduk, // alias untuk compatibility
                'kategori' => [
                    'id' => (int) ($produk->kategoriProduk->idKategori ?? 0),
                    'idKategori' => (int) ($produk->kategoriProduk->idKategori ?? 0),
                    'nama' => (string) ($produk->kategoriProduk->nama ?? 'Tidak Diketahui')
                ],
                'garansi' => [
                    'tanggal' => $this->formatDate($produk->tanggalGaransi),
                    'tanggalGaransi' => $this->formatDate($produk->tanggalGaransi),
                    'status' => $statusGaransi,
                    'keterangan' => $statusGaransi,
                    'masihBerlaku' => $statusGaransi === 'Bergaransi',
                    'isBergaransi' => $statusGaransi === 'Bergaransi',
                    'isGaransiHabis' => $statusGaransi === 'Garansi Habis',
                    'isTidakBergaransi' => $statusGaransi === 'Tidak Bergaransi'
                ],
                'penitip' => $penitip ? [
                    'nama' => (string) $penitip->nama,
                    'rating' => (float) ($penitip->rating ?? 0)
                ] : null,
                'produkTerkait' => $produkTerkait->values()->toArray(),
                'created_at' => $this->formatDate($produk->created_at),
                'createdAt' => $this->formatDate($produk->created_at)
            ];

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Detail produk berhasil diambil',
                'data' => $produkDetail
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in getDetailApiResponse: ' . $e->getMessage(), [
                'produk_id' => $produk->idProduk ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Gagal memformat detail produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method untuk mengambil thumbnail (foto pertama) - dengan path assets
     */
    private function getThumbnailAssetPath($gambarString)
    {
        if (!$gambarString) {
            return 'assets/images/default.jpg';
        }

        $gambarArray = explode(',', $gambarString);
        $thumbnailFile = trim($gambarArray[0]);

        if (empty($thumbnailFile)) {
            return 'assets/images/default.jpg';
        }

        return 'assets/images/' . $thumbnailFile;
    }

    /**
     * Helper method untuk mengambil semua path assets foto
     */
    private function getAllFotoAssetPaths($gambarString)
    {
        if (!$gambarString) {
            return ['assets/images/default.jpg'];
        }

        $gambarArray = explode(',', $gambarString);
        $fotoAssetPaths = [];

        foreach ($gambarArray as $gambar) {
            $trimmedGambar = trim($gambar);
            if (!empty($trimmedGambar)) {
                $fotoAssetPaths[] = 'assets/images/' . $trimmedGambar;
            }
        }

        // Pastikan minimal ada 1 foto
        if (empty($fotoAssetPaths)) {
            $fotoAssetPaths[] = 'assets/images/default.jpg';
        }

        return $fotoAssetPaths;
    }

    /**
     * Helper method untuk mengambil thumbnail (foto pertama) - hanya nama file (untuk backward compatibility)
     */
    private function getThumbnailFileName($gambarString)
    {
        if (!$gambarString) {
            return 'default.jpg';
        }

        $gambarArray = explode(',', $gambarString);
        $thumbnailFile = trim($gambarArray[0]);

        if (empty($thumbnailFile)) {
            return 'default.jpg';
        }

        return $thumbnailFile;
    }

    /**
     * Helper method untuk mengambil semua nama file foto (untuk backward compatibility)
     */
    private function getAllFotoFileNames($gambarString)
    {
        if (!$gambarString) {
            return ['default.jpg'];
        }

        $gambarArray = explode(',', $gambarString);
        $fotoFileNames = [];

        foreach ($gambarArray as $gambar) {
            $trimmedGambar = trim($gambar);
            if (!empty($trimmedGambar)) {
                $fotoFileNames[] = $trimmedGambar;
            }
        }

        // Pastikan minimal ada 1 foto
        if (empty($fotoFileNames)) {
            $fotoFileNames[] = 'default.jpg';
        }

        return $fotoFileNames;
    }

    /**
     * Helper method untuk parsing gambar array (untuk web) - dengan full URL
     */
    private function parseGambarArray($gambarString)
    {
        if (!$gambarString) {
            return ['default.jpg'];
        }

        $gambarArray = explode(',', $gambarString);
        return array_map('trim', $gambarArray);
    }

    /**
     * Helper method untuk mengecek status garansi
     */
    private function checkGaransiStatus($tanggalGaransi)
    {
        if (!$tanggalGaransi) {
            return 'Tidak Bergaransi';
        }

        try {
            $today = now();

            // Handle berbagai format tanggal
            if (is_string($tanggalGaransi)) {
                $garansiDate = \Carbon\Carbon::parse($tanggalGaransi);
            } elseif ($tanggalGaransi instanceof \Carbon\Carbon) {
                $garansiDate = $tanggalGaransi;
            } elseif ($tanggalGaransi instanceof \DateTime) {
                $garansiDate = \Carbon\Carbon::instance($tanggalGaransi);
            } else {
                return 'Tidak Bergaransi';
            }

            if ($garansiDate->isFuture()) {
                return 'Bergaransi';
            } else {
                return 'Garansi Habis';
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to parse tanggal garansi: ' . $e->getMessage(), [
                'tanggalGaransi' => $tanggalGaransi
            ]);
            return 'Tidak Bergaransi';
        }
    }

    /**
     * Helper method untuk format tanggal dengan aman
     */
    private function formatDate($date)
    {
        if (!$date) {
            return null;
        }

        try {
            // Jika sudah berupa string, cek apakah sudah format ISO
            if (is_string($date)) {
                // Jika sudah format ISO, return as is
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $date)) {
                    return $date;
                }
                $carbonDate = \Carbon\Carbon::parse($date);
                return $carbonDate->toISOString();
            }

            // Jika berupa Carbon instance
            if ($date instanceof \Carbon\Carbon) {
                return $date->toISOString();
            }

            // Jika berupa DateTime
            if ($date instanceof \DateTime) {
                return \Carbon\Carbon::instance($date)->toISOString();
            }

            // Fallback: coba parse sebagai string
            return \Carbon\Carbon::parse($date)->toISOString();
        } catch (\Exception $e) {
            \Log::warning('Failed to format date: ' . $e->getMessage(), [
                'date' => $date,
                'type' => gettype($date)
            ]);
            return null;
        }
    }
}

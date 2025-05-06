<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;
    
    protected $table = 'produk';
    protected $primaryKey = 'idProduk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idProduk',
        'gambar',
        'tanggalGaransi',
        'harga',
        'status',
        'berat',
        'hargaJual',
        'deskripsi',
        'ratingProduk',
        'idKategori',
        'idPegawai'
    ];

    protected $casts = [
        'tanggalGaransi' => 'datetime',
        'harga' => 'float',
        'berat' => 'float',
        'hargaJual' => 'float',
        'ratingProduk' => 'float'
    ];

    /**
     * Relasi dengan Kategori
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'idKategori', 'idKategori');
    }

    /**
     * Relasi dengan Pegawai
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    /**
     * Relasi dengan Diskusi Produk
     */
    public function diskusi(): HasMany
    {
        return $this->hasMany(DiskusiProduk::class, 'idProduk', 'idProduk');
    }

    /**
     * Relasi dengan Detail Transaksi Penitipan
     */
    public function detailTransaksiPenitipan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenitipan::class, 'idProduk', 'idProduk');
    }

    /**
     * Relasi dengan Detail Transaksi Penjualan
     */
    public function detailTransaksiPenjualan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'idProduk', 'idProduk');
    }

    /**
     * Relasi dengan Transaksi Donasi
     */
    public function transaksiDonasi(): HasMany
    {
        return $this->hasMany(TransaksiDonasi::class, 'idProduk', 'idProduk');
    }

    /**
     * Mendapatkan semua gambar yang terkait dengan produk ini.
     */
    public function gambarProduk()
    {
        return $this->hasMany(GambarProduk::class, 'idProduk', 'idProduk');
    }
    
    /**
     * Mendapatkan gambar utama produk ini.
     */
    public function gambarUtama()
    {
        // Jika Anda memiliki kolom isUtama di tabel gambar_produk
        // return $this->hasOne(GambarProduk::class, 'idProduk', 'idProduk')->where('isUtama', 1);
        
        // Atau cukup ambil gambar pertama sebagai gambar utama
        return $this->hasOne(GambarProduk::class, 'idProduk', 'idProduk');
    }
}
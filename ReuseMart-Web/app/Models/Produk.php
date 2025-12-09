<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'produk';
    protected $primaryKey = 'idProduk';

    protected $fillable = [
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

    public function getGambarArrayAttribute()
    {
        if (empty($this->gambar)) {
            return ['default.jpg'];
        }

        return explode(',', $this->gambar);
    }

    public function getThumbnailAttribute()
    {
        $gambarArray = $this->gambar_array;
        return $gambarArray[0];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'idKategori', 'idKategori');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    public function diskusi(): HasMany
    {
        return $this->hasMany(DiskusiProduk::class, 'idProduk', 'idProduk');
    }

    public function detailTransaksiPenitipan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenitipan::class, 'idProduk', 'idProduk');
    }

    public function detailTransaksiPenjualan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'idProduk', 'idProduk');
    }

    public function transaksiDonasi(): HasMany
    {
        return $this->hasMany(TransaksiDonasi::class, 'idProduk', 'idProduk');
    }

    public function getPenitipAttribute()
    {
        $detailPenitipan = $this->detailTransaksiPenitipan()->first();
        if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
            return $detailPenitipan->transaksiPenitipan->penitip;
        }
        return null;
    }

    public function isInWarranty()
    {
        if (!$this->tanggalGaransi) {
            return false;
        }

        return $this->tanggalGaransi->isFuture();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiPenitipan extends Model
{
    protected $table = 'detail_transaksi_penitipan';

    protected $fillable = [
        'idTransaksiPenitipan',
        'idProduk'
    ];

    /**
     * Relasi dengan Transaksi Penitipan
     */
    public function transaksiPenitipan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenitipan::class, 'idTransaksiPenitipan', 'idTransaksiPenitipan');
    }

    /**
     * Relasi dengan Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

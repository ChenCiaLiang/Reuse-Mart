<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiPenjualan extends Model
{
    protected $table = 'detail_transaksi_penjualan';

    protected $fillable = [
        'idTransaksi',
        'idProduk'
    ];

    /**
     * Relasi dengan Transaksi Penjualan
     */
    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'idTransaksi', 'idTransaksi');
    }

    /**
     * Relasi dengan Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiPenjualan extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'detail_transaksi_penjualan';
    protected $primaryKey = 'idDetailTransaksiPenjualan';

    protected $fillable = [
        'idTransaksiPenjualan',
        'idProduk'
    ];

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

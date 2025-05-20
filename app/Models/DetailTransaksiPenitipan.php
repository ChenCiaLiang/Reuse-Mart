<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiPenitipan extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'detail_transaksi_penitipan';
    protected $primaryKey = 'idDetailTransaksiPenitipan';

    protected $fillable = [
        'idTransaksiPenitipan',
        'idProduk'
    ];

    public function transaksiPenitipan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenitipan::class, 'idTransaksiPenitipan', 'idTransaksiPenitipan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

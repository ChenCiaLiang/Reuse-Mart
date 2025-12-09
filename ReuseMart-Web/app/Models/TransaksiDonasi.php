<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiDonasi extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'transaksi_donasi';
    protected $primaryKey = 'idTransaksiDonasi';

    protected $fillable = [
        'tanggalPemberian',
        'namaPenerima',
        'idRequest',
        'idProduk'
    ];

    public function requestDonasi(): BelongsTo
    {
        return $this->belongsTo(RequestDonasi::class, 'idRequest', 'idRequest');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDonasi extends Model
{
    protected $table = 'transaksi_donasi';

    protected $fillable = [
        'tanggalPemberian',
        'namaPenerima',
        'idRequest',
        'idProduk'
    ];

    protected $casts = [
        'tanggalPemberian' => 'datetime'
    ];

    /**
     * Relasi dengan Request Donasi
     */
    public function requestDonasi(): BelongsTo
    {
        return $this->belongsTo(RequestDonasi::class, 'idRequest', 'idRequest');
    }

    /**
     * Relasi dengan Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }
}

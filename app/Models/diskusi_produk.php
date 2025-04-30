<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class diskusi_produk extends Model
{
    protected $table = 'diskusi_produk';
    protected $primaryKey = 'idDiskusi';

    protected $fillable = [
        'pesan', 'tanggalDiskusi', 'idPegawai', 'idProduk', 'idPembeli'
    ];

    protected $casts = [
        'tanggalDiskusi' => 'datetime'
    ];

    /**
     * Relasi dengan Pegawai (CS)
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    /**
     * Relasi dengan Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }

    /**
     * Relasi dengan Pembeli
     */
    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

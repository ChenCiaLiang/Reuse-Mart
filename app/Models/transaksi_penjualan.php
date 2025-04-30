<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transaksi_penjualan extends Model
{
    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'idTransaksi';

    protected $fillable = [
        'bonus', 'tanggalLaku', 'tanggalPesan', 'tanggalBatasLunas',
        'tanggalLunas', 'tanggalKirim', 'tanggalAmbil', 'idPembeli'
    ];

    protected $casts = [
        'bonus' => 'float',
        'tanggalLaku' => 'datetime',
        'tanggalPesan' => 'datetime',
        'tanggalBatasLunas' => 'datetime',
        'tanggalLunas' => 'datetime',
        'tanggalKirim' => 'datetime',
        'tanggalAmbil' => 'datetime'
    ];

    /**
     * Relasi dengan Pembeli
     */
    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }

    /**
     * Relasi dengan Detail Transaksi Penjualan
     */
    public function detailTransaksiPenjualan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'idTransaksi', 'idTransaksi');
    }

    /**
     * Relasi dengan Komisi
     */
    public function komisi(): HasOne
    {
        return $this->hasOne(Komisi::class, 'idTransaksi', 'idTransaksi');
    }
}

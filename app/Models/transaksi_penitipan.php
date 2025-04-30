<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transaksi_penitipan extends Model
{
    protected $table = 'transaksi_penitipan';
    protected $primaryKey = 'idTransaksiPenitipan';

    protected $fillable = [
        'tanggalMasukPenitipan', 'tanggalAkhirPenitipan', 'batasAmbil',
        'statusPenitipan', 'statusPerpanjangan', 'pendapatan', 'idPenitip'
    ];

    protected $casts = [
        'tanggalMasukPenitipan' => 'datetime',
        'tanggalAkhirPenitipan' => 'datetime',
        'batasAmbil' => 'datetime',
        'statusPerpanjangan' => 'boolean',
        'pendapatan' => 'float'
    ];

    /**
     * Relasi dengan Penitip
     */
    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }

    /**
     * Relasi dengan Detail Transaksi Penitipan
     */
    public function detailTransaksiPenitipan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenitipan::class, 'idTransaksiPenitipan', 'idTransaksiPenitipan');
    }
}

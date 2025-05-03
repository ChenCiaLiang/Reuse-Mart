<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komisi extends Model
{
    protected $table = 'komisi';
    protected $primaryKey = 'idTransaksi';
    public $incrementing = false;

    protected $fillable = [
        'idTransaksi',
        'komisiPenitip',
        'komisiHunter',
        'komisiReuse',
        'idPegawai',
        'idPenitip'
    ];

    protected $casts = [
        'komisiPenitip' => 'float',
        'komisiHunter' => 'float',
        'komisiReuse' => 'float'
    ];

    /**
     * Relasi dengan Transaksi Penjualan
     */
    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'idTransaksi', 'idTransaksi');
    }

    /**
     * Relasi dengan Pegawai (Hunter)
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    /**
     * Relasi dengan Penitip
     */
    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }
}

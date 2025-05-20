<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komisi extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'komisi';
    protected $primaryKey = 'idTransaksiPenjualan';
    public $incrementing = false;

    protected $fillable = [
        'idTransaksiPenjualan',
        'komisiPenitip',
        'komisiHunter',
        'komisiReuse',
        'idPegawai',
        'idPenitip'
    ];

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }
}

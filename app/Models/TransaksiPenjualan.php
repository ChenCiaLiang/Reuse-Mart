<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenjualan extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'idTransaksiPenjualan';

    protected $fillable = [
        'bonus',
        'status',
        'tanggalLaku',
        'tanggalPesan',
        'tanggalBatasLunas',
        'tanggalLunas',
        'tanggalKirim',
        'tanggalAmbil',
        'idPembeli',
        'idPegawai', //KURIR
    ];

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }

    public function detailTransaksiPenjualan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }

    public function komisi(): HasOne
    {
        return $this->hasOne(Komisi::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }
}

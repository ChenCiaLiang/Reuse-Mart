<?php
// File: app/Models/TransaksiPenjualan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenjualan extends Model
{
    use HasFactory;

    public $timestamps = true; // UBAH JADI TRUE untuk enable created_at/updated_at
    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'idTransaksiPenjualan';

    protected $fillable = [
        'status',
        'tanggalLaku',
        'tanggalPesan',
        'tanggalBatasLunas',
        'tanggalLunas',
        'tanggalBatasAmbil',
        'tanggalKirim',
        'tanggalAmbil',
        'idPembeli',
        'idPegawai',
        'alamatPengiriman',
        'metodePengiriman',
        'poinDidapat',
        'poinDigunakan',
    ];

    protected $casts = [
        'poinDidapat' => 'integer',
        'poinDigunakan' => 'integer',
        'tanggalLaku' => 'datetime',
        'tanggalPesan' => 'datetime',
        'tanggalBatasLunas' => 'datetime',
        'tanggalLunas' => 'datetime',
        'tanggalBatasAmbil' => 'datetime',
        'tanggalKirim' => 'datetime',
        'tanggalAmbil' => 'datetime',
    ];

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    public function detailTransaksiPenjualan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }

    public function komisi(): HasOne
    {
        return $this->hasOne(Komisi::class, 'idTransaksiPenjualan', 'idTransaksiPenjualan');
    }

    public function getAlamatPengirimanArrayAttribute()
    {
        return $this->alamatPengiriman ? json_decode($this->alamatPengiriman, true) : null;
    }

    public function setAlamatFromAlamatModel($alamat)
    {
        if ($alamat) {
            $this->alamatPengiriman = json_encode([
                'jenis' => $alamat->jenis,
                'alamatLengkap' => $alamat->alamatLengkap,
                'idAlamat' => $alamat->idAlamat
            ]);
        }
    }

    public function getDiskonPoinRupiahAttribute()
    {
        return $this->poinDigunakan * 10;
    }

    public function getNilaiPoinDidapatAttribute()
    {
        return $this->poinDidapat * 10;
    }
}
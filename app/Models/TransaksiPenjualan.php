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
        'status',
        'tanggalLaku',
        'tanggalPesan',
        'tanggalBatasLunas',
        'tanggalLunas',
        'tanggalBatasAmbil',
        'tanggalKirim',
        'tanggalAmbil',
        'idPembeli',
        'idPegawai', //KURIR
        'alamatPengiriman', // TAMBAHAN BARU
        'metodePengiriman', // TAMBAHAN BARU
        'poinDidapat', // TAMBAHAN BARU - Poin yang didapat dari transaksi
        'poinDigunakan', // TAMBAHAN BARU - Poin yang digunakan untuk transaksi
    ];

    protected $casts = [
        'poinDidapat' => 'integer',
        'poinDigunakan' => 'integer',
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

    /**
     * Get alamat pengiriman sebagai array
     */
    public function getAlamatPengirimanArrayAttribute()
    {
        return $this->alamatPengiriman ? json_decode($this->alamatPengiriman, true) : null;
    }

    /**
     * Set alamat pengiriman dari array
     */
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

    /**
     * Get total diskon poin dalam rupiah
     */
    public function getDiskonPoinRupiahAttribute()
    {
        return $this->poinDigunakan * 10; // 1 poin = Rp 10
    }

    /**
     * Get nilai poin yang didapat dalam rupiah (untuk referensi)
     */
    public function getNilaiPoinDidapatAttribute()
    {
        return $this->poinDidapat * 10; // 1 poin = Rp 10
    }
}
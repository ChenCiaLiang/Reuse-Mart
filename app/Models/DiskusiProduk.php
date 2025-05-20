<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiskusiProduk extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'diskusi_produk';
    protected $primaryKey = 'idDiskusi';

    protected $fillable = [
        'pesan',
        'tanggalDiskusi',
        'idPegawai',
        'idProduk',
        'idPembeli'
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'idProduk', 'idProduk');
    }

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

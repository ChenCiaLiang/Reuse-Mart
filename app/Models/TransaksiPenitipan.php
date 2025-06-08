<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
class TransaksiPenitipan extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'transaksi_penitipan';
    protected $primaryKey = 'idTransaksiPenitipan';

    protected $fillable = [
        'tanggalMasukPenitipan',
        'tanggalAkhirPenitipan',
        'tanggalPengambilan',
        'batasAmbil',
        'statusPenitipan',
        'statusPerpanjangan',
        'pendapatan',
        'idPenitip',
        'idPegawai',
        'idHunter',
    ];

    protected $casts = [
        'tanggalMasukPenitipan' => 'datetime',
        'tanggalAkhirPenitipan' => 'datetime',
        'batasAmbil' => 'datetime',
        'tanggalPengambilan' => 'datetime',
        'statusPerpanjangan' => 'boolean'
    ];

    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idPegawai', 'idPegawai');
    }

    public function hunter(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'idHunter', 'idPegawai');
    }

    public function detailTransaksiPenitipan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenitipan::class, 'idTransaksiPenitipan', 'idTransaksiPenitipan');
    }
}

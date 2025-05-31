<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'idPenitip'
    ];

    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }

    public function detailTransaksiPenitipan(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenitipan::class, 'idTransaksiPenitipan', 'idTransaksiPenitipan');
    }
}

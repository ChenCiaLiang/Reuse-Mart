<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penukaran extends Model
{
    protected $table = 'penukaran';

    protected $fillable = [
        'tanggalPenerimaan',
        'tanggalPengajuan',
        'idMerchandise',
        'idPembeli'
    ];

    protected $casts = [
        'tanggalPenerimaan' => 'datetime',
        'tanggalPengajuan' => 'datetime'
    ];

    /**
     * Relasi dengan Merchandise
     */
    public function merchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class, 'idMerchandise', 'idMerchandise');
    }

    /**
     * Relasi dengan Pembeli
     */
    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

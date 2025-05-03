<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class request_donasi extends Model
{
    protected $table = 'request_donasi';
    protected $primaryKey = 'idRequest';

    protected $fillable = [
        'tanggalRequest', 'request', 'status', 'penerima', 'idOrganisasi'
    ];

    protected $casts = [
        'tanggalRequest' => 'datetime'
    ];

    /**
     * Relasi dengan Organisasi
     */
    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'idOrganisasi', 'idOrganisasi');
    }

    /**
     * Relasi dengan Transaksi Donasi
     */
    public function transaksiDonasi(): HasMany
    {
        return $this->hasMany(TransaksiDonasi::class, 'idRequest', 'idRequest');
    }
}

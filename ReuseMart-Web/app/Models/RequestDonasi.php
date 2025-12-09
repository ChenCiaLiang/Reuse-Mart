<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestDonasi extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'request_donasi';
    protected $primaryKey = 'idRequest';

    protected $fillable = [
        'tanggalRequest',
        'request',
        'status',
        'penerima',
        'idOrganisasi'
    ];

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'idOrganisasi', 'idOrganisasi');
    }

    public function transaksiDonasi(): HasMany
    {
        return $this->hasMany(TransaksiDonasi::class, 'idRequest', 'idRequest');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'idAlamat';

    protected $fillable = [
        'alamatLengkap',
        'jenis',
        'statusDefault',
        'idPembeli'
    ];

    protected $casts = [
        'statusDefault' => 'boolean'
    ];

    /**
     * Relasi dengan Pembeli
     */
    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

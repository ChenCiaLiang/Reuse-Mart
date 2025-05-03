<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class top_seller extends Model
{
    protected $table = 'top_seller';
    protected $primaryKey = 'idTopSeller';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idTopSeller', 'tanggal_mulai', 'tanggal_selesai', 'idPenitip'
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime'
    ];

    /**
     * Relasi dengan Penitip
     */
    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }
}

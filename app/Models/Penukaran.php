<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penukaran extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'penukaran';
    protected $primaryKey = 'idPenukaran';

    protected $fillable = [
        'tanggalPenerimaan',
        'tanggalPengajuan',
        'idMerchandise',
        'idPembeli',
        'statusPenukaran'
    ];

    public function merchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class, 'idMerchandise', 'idMerchandise');
    }

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

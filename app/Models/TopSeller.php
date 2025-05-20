<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopSeller extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'top_seller';
    protected $primaryKey = 'idTopSeller';

    protected $fillable = [
        'tanggal_mulai',
        'tanggal_selesai',
        'idPenitip'
    ];

    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }
}

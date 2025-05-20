<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alamat extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'alamat';
    protected $primaryKey = 'idAlamat';

    protected $fillable = [
        'alamatLengkap',
        'jenis',
        'statusDefault',
        'idPembeli'
    ];

    protected $casts = [
        'statusDefault' => 'boolean',
    ];

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'idPembeli', 'idPembeli');
    }
}

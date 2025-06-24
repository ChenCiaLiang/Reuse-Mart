<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchandise extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'merchandise';
    protected $primaryKey = 'idMerchandise';

    protected $fillable = [
        'nama',
        'jumlahPoin',
        'stok',
        'gambar'
    ];

    public function penukaran(): HasMany
    {
        return $this->hasMany(Penukaran::class, 'idMerchandise', 'idMerchandise');
    }
}

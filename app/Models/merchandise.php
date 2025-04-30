<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class merchandise extends Model
{
    protected $table = 'merchandise';
    protected $primaryKey = 'idMerchandise';

    protected $fillable = [
        'nama', 'jumlahPoin', 'stok'
    ];

    protected $casts = [
        'jumlahPoin' => 'integer',
        'stok' => 'integer'
    ];

    /**
     * Relasi dengan Penukaran
     */
    public function penukaran(): HasMany
    {
        return $this->hasMany(Penukaran::class, 'idMerchandise', 'idMerchandise');
    }
}

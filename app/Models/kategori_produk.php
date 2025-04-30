<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kategori_produk extends Model
{
    protected $table = 'kategori_produk';
    protected $primaryKey = 'idKategori';

    protected $fillable = ['nama'];

    /**
     * Relasi dengan Produk
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'idKategori', 'idKategori');
    }
}

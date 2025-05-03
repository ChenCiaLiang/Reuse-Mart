<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriProduk extends Model
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

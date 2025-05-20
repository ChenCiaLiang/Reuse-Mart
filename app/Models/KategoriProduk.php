<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriProduk extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'kategori_produk';
    protected $primaryKey = 'idKategori';

    protected $fillable = [
        'nama'
    ];

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'idKategori', 'idKategori');
    }
}

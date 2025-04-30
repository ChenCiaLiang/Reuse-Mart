<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pembeli extends Model
{
    protected $table = 'pembeli';
    protected $primaryKey = 'idPembeli';

    protected $fillable = [
        'nama', 'email', 'password', 'foto_profile', 'poin'
    ];

    protected $hidden = ['password'];

    /**
     * Relasi dengan User untuk autentikasi
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'user', 'user_type', 'user_id');
    }

    /**
     * Relasi dengan Alamat
     */
    public function alamat(): HasMany
    {
        return $this->hasMany(Alamat::class, 'idPembeli', 'idPembeli');
    }

    /**
     * Relasi dengan Transaksi Penjualan
     */
    public function transaksiPenjualan(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'idPembeli', 'idPembeli');
    }

    /**
     * Relasi dengan Diskusi Produk
     */
    public function diskusiProduk(): HasMany
    {
        return $this->hasMany(DiskusiProduk::class, 'idPembeli', 'idPembeli');
    }

    /**
     * Relasi dengan Penukaran
     */
    public function penukaran(): HasMany
    {
        return $this->hasMany(Penukaran::class, 'idPembeli', 'idPembeli');
    }

    /**
     * Mendapatkan alamat default
     */
    public function getDefaultAlamat()
    {
        return $this->alamat()->where('statusDefault', true)->first();
    }
}

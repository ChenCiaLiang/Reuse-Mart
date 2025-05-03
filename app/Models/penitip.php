<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penitip extends Model
{
    protected $table = 'penitip';
    protected $primaryKey = 'idPenitip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idPenitip', 'nama', 'email', 'password', 'alamat', 'nik',
        'foto_ktp', 'poin', 'bonus', 'komisi', 'saldo', 'rating'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'poin' => 'integer',
        'bonus' => 'float',
        'komisi' => 'float',
        'saldo' => 'float',
        'rating' => 'float'
    ];

    /**
     * Relasi dengan User untuk autentikasi
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'user', 'user_type', 'user_id');
    }

    /**
     * Relasi dengan Transaksi Penitipan
     */
    public function transaksiPenitipan(): HasMany
    {
        return $this->hasMany(TransaksiPenitipan::class, 'idPenitip', 'idPenitip');
    }

    /**
     * Relasi dengan Komisi
     */
    public function komisi(): HasMany
    {
        return $this->hasMany(Komisi::class, 'idPenitip', 'idPenitip');
    }

    /**
     * Relasi dengan Top Seller
     */
    public function topSeller(): HasMany
    {
        return $this->hasMany(TopSeller::class, 'idPenitip', 'idPenitip');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'idPegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idPegawai', 'nama', 'username', 'password', 'foto_profile', 'idJabatan'
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
     * Relasi dengan Jabatan
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'idJabatan', 'idJabatan');
    }

    /**
     * Relasi dengan Produk
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'idPegawai', 'idPegawai');
    }

    /**
     * Relasi dengan Komisi
     */
    public function komisi(): HasMany
    {
        return $this->hasMany(Komisi::class, 'idPegawai', 'idPegawai');
    }

    /**
     * Relasi dengan Diskusi Produk
     */
    public function diskusiProduk(): HasMany
    {
        return $this->hasMany(DiskusiProduk::class, 'idPegawai', 'idPegawai');
    }
}

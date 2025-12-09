<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;
    protected $table = 'pegawai';
    protected $primaryKey = 'idPegawai';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'noTelp',
        'alamat',
        'tanggalLahir',
        'komisi',
        'idJabatan',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idJabatan', 'idJabatan');
    }

    public function diskusi(): HasMany
    {
        return $this->hasMany(DiskusiProduk::class, 'idPegawai', 'idPegawai');
    }
}

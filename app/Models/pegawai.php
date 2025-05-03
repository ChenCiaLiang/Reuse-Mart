<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pegawai';
    protected $primaryKey = 'idPegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idPegawai',
        'nama',
        'username',
        'password',
        'foto_profile',
        'idJabatan',
    ];

    protected $hidden = [
        'password',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idJabatan');
    }

    public function getJabatanNama()
    {
        return $this->jabatan ? strtolower($this->jabatan->nama) : '';
    }

    public function isAdmin()
    {
        return $this->getJabatanNama() === 'admin';
    }

    public function isCS()
    {
        return $this->getJabatanNama() === 'customer service';
    }

    public function isPegawaiGudang()
    {
        $nama = $this->getJabatanNama();
        return $nama === 'pegawai gudang' || $nama === 'gudang';
    }

    public function isHunter()
    {
        return $this->getJabatanNama() === 'hunter';
    }
}

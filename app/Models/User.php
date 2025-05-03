<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'userable_id',
        'userable_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function userable()
    {
        return $this->morphTo();
    }

    public function isPegawai()
    {
        return $this->role === 'admin' ||
            $this->role === 'cs' ||
            $this->role === 'pegawai_gudang' ||
            $this->role === 'hunter';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCS()
    {
        return $this->role === 'cs';
    }

    public function isPegawaiGudang()
    {
        return $this->role === 'pegawai_gudang';
    }

    public function isHunter()
    {
        return $this->role === 'hunter';
    }

    public function isPembeli()
    {
        return $this->role === 'pembeli';
    }

    public function isPenitip()
    {
        return $this->role === 'penitip';
    }

    public function isOrganisasi()
    {
        return $this->role === 'organisasi';
    }
}

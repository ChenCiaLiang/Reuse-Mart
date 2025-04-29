<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'username', 'password', 'user_type', 'user_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Menentukan apakah login menggunakan email atau username
     */
    public function getLoginIdentifierName()
    {
        if ($this->isPegawai()) {
            return 'username';
        }
        return 'email';
    }

    public function userable(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function isPembeli()
    {
        return $this->user_type === 'App\\Models\\Pembeli';
    }

    public function isPenitip()
    {
        return $this->user_type === 'App\\Models\\Penitip';
    }

    public function isOrganisasi()
    {
        return $this->user_type === 'App\\Models\\Organisasi';
    }

    public function isPegawai()
    {
        return $this->user_type === 'App\\Models\\Pegawai';
    }

    /**
     * Mendapatkan jabatan pegawai
     */
    public function getJabatan()
    {
        if ($this->isPegawai()) {
            $pegawai = $this->userable;
            return $pegawai->jabatan;
        }
        return null;
    }

    /**
     * Mendapatkan role pegawai
     */
    public function getPegawaiRole()
    {
        $jabatan = $this->getJabatan();
        if ($jabatan) {
            return $jabatan->nama ?? 'pegawai';
        }
        return null;
    }

    /**
     * Mendapatkan ability/permission berdasarkan user type
     */
    public function getAbilities()
    {
        if ($this->isPembeli()) {
            return ['pembeli'];
        } elseif ($this->isPenitip()) {
            return ['penitip'];
        } elseif ($this->isOrganisasi()) {
            return ['organisasi'];
        } elseif ($this->isPegawai()) {
            $jabatan = $this->getPegawaiRole();
            switch ($jabatan) {
                case 'Admin':
                    return ['admin'];
                case 'Customer Service':
                    return ['cs'];
                case 'Pegawai Gudang':
                case 'Gudang':
                    return ['gudang'];
                case 'Kurir':
                    return ['kurir'];
                case 'Hunter':
                    return ['hunter'];
                default:
                    return ['pegawai'];
            }
        }
        return [];
    }
}
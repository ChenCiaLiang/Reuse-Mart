<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Penitip extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'penitip';
    protected $primaryKey = 'idPenitip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idPenitip',
        'nama',
        'email',
        'password',
        'alamat',
        'nik',
        'foto_ktp',
        'poin',
        'bonus',
        'komisi',
        'saldo',
        'rating',
    ];

    protected $hidden = [
        'password',
    ];
}

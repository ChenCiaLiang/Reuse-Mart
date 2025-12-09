<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Penitip extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    public $timestamps = false;
    protected $table = 'penitip';
    protected $primaryKey = 'idPenitip';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'alamat',
        'nik',
        'fotoKTP',
        'poin',
        'bonus',
        'komisi',
        'saldo',
        'rating',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

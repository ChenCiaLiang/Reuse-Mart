<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pembeli extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'pembeli';
    protected $primaryKey = 'idPembeli';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'foto_profile',
        'poin',
    ];

    protected $hidden = [
        'password',
    ];

    public function alamats()
    {
        return $this->hasMany(Alamat::class, 'idPembeli');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Organisasi extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'organisasi';
    protected $primaryKey = 'idOrganisasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idOrganisasi',
        'nama',
        'email',
        'password',
        'logo',
        'alamat',
    ];

    protected $hidden = [
        'password',
    ];

    public function requestDonasis()
    {
        return $this->hasMany(RequestDonasi::class, 'idOrganisasi');
    }
}

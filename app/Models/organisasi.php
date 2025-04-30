<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class organisasi extends Model
{
    protected $table = 'organisasi';
    protected $primaryKey = 'idOrganisasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idOrganisasi', 'nama', 'email', 'password', 'logo', 'alamat'
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
     * Relasi dengan Request Donasi
     */
    public function requestDonasi(): HasMany
    {
        return $this->hasMany(RequestDonasi::class, 'idOrganisasi', 'idOrganisasi');
    }
}

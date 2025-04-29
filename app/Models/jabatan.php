<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $table = 'jabatan';
    protected $primaryKey = 'idJabatan';

    protected $fillable = ['nama'];

    /**
     * Relasi dengan pegawai
     */
    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'idJabatan', 'idJabatan');
    }
}
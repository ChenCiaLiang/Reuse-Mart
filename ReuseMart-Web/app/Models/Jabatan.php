<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'jabatan';
    protected $primaryKey = 'idJabatan';

    protected $fillable = [
        'nama'
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'idJabatan', 'idJabatan');
    }
}

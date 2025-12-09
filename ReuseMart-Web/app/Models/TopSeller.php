<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TopSeller extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'top_seller';
    protected $primaryKey = 'idTopSeller';

    protected $fillable = [
        'tanggal_mulai',
        'tanggal_selesai',
        'idPenitip'
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime'
    ];

    public function penitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'idPenitip', 'idPenitip');
    }

    /**
     * Cek apakah periode top seller sudah berakhir
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->tanggal_selesai);
    }

    /**
     * Scope untuk top seller yang masih aktif
     */
    public function scopeActive($query)
    {
        return $query->where('tanggal_selesai', '>=', Carbon::now());
    }

    /**
     * Scope untuk top seller yang sudah expired
     */
    public function scopeExpired($query)
    {
        return $query->where('tanggal_selesai', '<', Carbon::now());
    }

    /**
     * Get current active top seller
     */
    public static function getCurrentTopSeller()
    {
        return self::with('penitip')->active()->first();
    }

    /**
     * Cleanup expired top seller records
     */
    public static function cleanupExpired()
    {
        return self::expired()->delete();
    }
}
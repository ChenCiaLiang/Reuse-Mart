<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;

class SanctumServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Daftarkan model token kustom
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Tambahkan logika untuk memeriksa token dan menyimpan last_used_at
        Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid) {
            if ($isValid && $accessToken) {
                $accessToken->forceFill(['last_used_at' => now()])->save();
            }
            return $isValid;
        });
    }
}

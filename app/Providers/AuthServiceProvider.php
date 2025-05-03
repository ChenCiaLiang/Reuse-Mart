<?php

namespace App\Providers;

use App\Models\Organisasi;
use App\Models\Pegawai;
use App\Models\Pembeli;
use App\Models\Penitip;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Konfigurasi Sanctum untuk multi-model autentikasi
        Auth::viaRequest('sanctum', function ($request) {
            $token = $request->bearerToken();

            if (!$token) {
                return null;
            }

            // Cek di semua model yang bisa diautentikasi
            $models = [
                Pegawai::class,
                Pembeli::class,
                Penitip::class,
                Organisasi::class,
            ];

            foreach ($models as $model) {
                $user = (new $model)->whereHas('tokens', function ($query) use ($token) {
                    $query->where('token', hash('sha256', $token));
                })->first();

                if ($user) {
                    return $user;
                }
            }

            return null;
        });
    }
}

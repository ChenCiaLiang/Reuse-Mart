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

        // Gate::define('admin', function ($user) {
        //     return $user instanceof Pegawai && $user->isAdmin();
        // });

        // Gate::define('customer-service', function ($user) {
        //     return $user instanceof Pegawai && $user->isCS();
        // });

        // Gate::define('pegawai-gudang', function ($user) {
        //     return $user instanceof Pegawai && $user->isPegawaiGudang();
        // });

        // Gate::define('hunter', function ($user) {
        //     return $user instanceof Pegawai && $user->isHunter();
        // });
    }
}

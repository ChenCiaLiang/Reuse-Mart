<?php

use App\Http\Controllers\TransaksiPengirimanController;
use App\Http\Middleware\Role;
use App\Http\Middleware\RolePegawai;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Log;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log as FacadesLog;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'RolePegawai' => RolePegawai::class,
            'Role' => Role::class,
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Schedule command untuk update expired transactions
        $schedule->command('transaksi:update-status-expired')
            ->everyMinute()
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/expired-transactions.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

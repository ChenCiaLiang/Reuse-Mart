<?php

use App\Http\Controllers\TransaksiPengirimanController;
use App\Http\Middleware\Role;
use App\Http\Middleware\RolePegawai;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $schedule->call(function () {
            $controller = new TransaksiPengirimanController();
            $controller->updateStatusExpired();
        })
            ->everyMinute()
            ->name('updateExpiredTransactions')
            ->withoutOverlapping();

        // Optional: Tambahkan schedule lain jika diperlukan
        // $schedule->command('transactions:send-reminder')
        //          ->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

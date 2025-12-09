<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup expired transactions every minute
        // Karena timeout payment hanya 1 menit, kita perlu check sering
        $schedule->command('transactions:cleanup-expired')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
                
        // Alternative - jika tidak ingin terlalu sering, bisa setiap 5 menit
        // $schedule->command('transactions:cleanup-expired')
        //         ->everyFiveMinutes()
        //         ->withoutOverlapping()
        //         ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
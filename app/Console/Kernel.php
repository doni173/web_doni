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
        // Menjadwalkan pembaruan FSN setiap 1 bulan pada jam 00:00
        $schedule->call(function () {
            app(ItemController::class)->updateFSN();
        })->monthlyOn(1, '00:00');

        // Anda bisa menambahkan jadwal lainnya di sini, misalnya:
        // $schedule->command('inspire')->hourly();
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

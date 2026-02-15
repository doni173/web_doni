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
        // ⭐ FSN Analysis Otomatis Setiap Tanggal 1 Jam 02:00 Pagi
        $schedule->command('fsn:process-monthly --periode=30')
                 ->monthlyOn(1, '02:00')
                 ->timezone('Asia/Jakarta')
                 ->appendOutputTo(storage_path('logs/fsn-scheduler.log'));
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
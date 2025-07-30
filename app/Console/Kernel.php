<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // $schedule->command('inspire')->hourly();
        // $schedule->command('event:reminder')->cron('0 8-18/1 * * *');

        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('jacs:expire')->hourly();
        $schedule->command('jacs:sync-users')->weekly();
        $schedule->command('jacs:auto')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

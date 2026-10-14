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
        // $schedule->command('inspire')->hourly();
        $schedule->command('mentorshipReminder')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('cycle:change-step')->hourly();
        $schedule->command('service-messages:send-email-reminders')->everyFifteenMinutes();
        $schedule->command('importer:purge-expired')->hourly()->withoutOverlapping();
        // After the upload cutover: warn if confidential files reappear in a web-served folder.
        $schedule->command('files:protect:watch')->dailyAt('03:30');
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

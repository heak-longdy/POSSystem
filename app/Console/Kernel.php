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
    protected $commands = [
        Commands\ApplyJob::class,
        Commands\DeleteFormQueue::class,
        Commands\RunSystemBackup::class,
    ];
    protected function schedule(Schedule $schedule)
    {
       $schedule->command('applyJob:add');
       $schedule->command('deleteFormQueue:add')->daily();

       // Automated System Backup
       $schedule->command('backup:run')
           ->dailyAt('02:00')
           ->when(function () {
               try {
                   return (bool) \App\Models\BackupSetting::getActive()->auto_backup_enabled;
               } catch (\Throwable $e) {
                   return false;
               }
           });
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

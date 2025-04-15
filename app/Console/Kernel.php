<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\NotifyBadgeExpiry::class,
        \App\Console\Commands\NotifyTrainingExpiry::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Exécuter la vérification des badges expirants tous les jours à 8h00
        
        $schedule->command('badges:check-expiry')
                 ->dailyAt('08:00')
                 ->appendOutputTo(storage_path('logs/badge-expiry-check.log'));
        
        // // Exécuter la commande existante pour marquer les badges expirés
        // $schedule->call('\App\Http\Controllers\BadgeController@checkExpiredBadges')
        //          ->dailyAt('00:01')
        //          ->appendOutputTo(storage_path('logs/badge-expired-check.log'));
        
        // Exécuter la vérification des formations expirantes tous les jours à 8h30
        $schedule->command('trainings:check-expiry')
                 ->dailyAt('08:30')
                 ->appendOutputTo(storage_path('logs/training-expiry-check.log'));
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
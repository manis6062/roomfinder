<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Room;
use App\Models\Jagga;
use Log;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

          $schedule->call(function(){

            Room::checkDeleteOldRooms();

         })->daily();

          Log::info('ROOM - DAILY - cron function called for removing last two months not updated');



          $schedule->call(function(){

            Jagga::checkDeleteOldJaggas();

         })->daily();
        Log::info('Jagga - DAILY - cron function called for removing last two months not updated');

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

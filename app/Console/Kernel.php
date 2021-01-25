<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\CssSelector\Tests\Node\CombinedSelectorNodeTest;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         Commands\Push::class,
         Commands\Rank::class,
         Commands\ShowGameInDate::class,
         Commands\SchedulerPushes::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('push')->everyMinute();
        $schedule->command('showgame')->everyTenMinutes();
        $schedule->command('schedulerPushesToPushes')->everyTenMinutes();
    }
}

<?php

namespace App\Console;

use Schema;
use App\Models\Setting;
use App\Models\SystemLog;
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
        Commands\ScheduledMonitoring::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // We need this check to cancel the scheduler in case
        // we haven't yet created all the required tables (e.g
        // when you work with "artisan migrate")
        if (Schema::hasTable('settings')) {
            try {
                $status = Setting::where('sname', 'monitoring_status')->first();
                if ($status->value == 1) {
                    $period = Setting::where('sname', 'monitoring_period')->first();
                    switch ($period->value) {
                        case 10:
                            $schedule->command('monitor:scheduled')->everyTenMinutes();
                            break;
                        case 30:
                            $schedule->command('monitor:scheduled')->everyThirtyMinutes();
                            break;
                        case 60:
                            $schedule->command('monitor:scheduled')->hourly();
                            break;
                    }
                }
            } catch (Exception $ex) {
                $log = new SystemLog();
                $log->category = 'error';
                $log->message = "Scheduled Monitoring failed! ".$ex->getMessage();
                $log->when = date("Y-m-d H:i:s");
                $log->save();
            }
        }
    }
}

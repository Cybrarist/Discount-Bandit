<?php

namespace App\Console;

use App\Jobs\CalculateGroupListPriceJob;
use App\Jobs\GetProductJob;
use App\Models\Currency;
use App\Models\GroupList;
use App\Models\Store;
use App\ScheduledClasses\GroupSchedule;
use App\ScheduledClasses\ProductStoreSchedule;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(new ProductStoreSchedule)->everyFiveMinutes();
        $schedule->call(new GroupSchedule)->everyTenMinutes();

        $schedule->command('notification:clear')->daily();
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
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

<?php

namespace App\Console;

use App\Jobs\CalculateGroupListPriceJob;
use App\Jobs\GetProductJob;
use App\Models\Currency;
use App\Models\GroupList;
use App\Models\Store;
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
        $schedule->call(function (){
            try {
                Log::debug("Schedule Started");
                //clear all the jobs, in case of some items still remain after 5 mins.
                clear_job();

                //getting stores for the queue slug
                $product_stores=DB::table('product_store')
                    ->join('stores', 'stores.id', '=' , 'store_id')
                    ->orderBy('product_store.updated_at')
                    ->select([
                        "product_store.id",
                        "product_store.product_id",
                        "product_store.store_id",
                        "stores.domain",
                        "stores.slug"
                    ])
                    ->get();

                foreach ($product_stores as $index=>$product_store)
                {
                    GetProductJob::dispatch($product_store->id , $product_store->domain)
                        ->onQueue($product_store->slug)
                        ->delay(now()->addSeconds($index*5));
                }

            }
            catch (\Exception $e)
            {
                Log::error("Couldn't Run the Schedule, Error: " . $e);
            }
        })->everyFiveMinutes();

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

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

                $product_stores=DB::table('product_store')
                    ->join('stores', 'store_id', '=' , 'stores.id')
                    ->orderBy('product_store.updated_at', 'desc')
                    ->get();

                foreach ($product_stores as $index=>$product_store)
                {
                    GetProductJob::dispatch($product_store->product_id, $product_store->store_id, null , $product_store->ebay_id)
                        ->onQueue($product_store->slug)
                        ->delay(now()->addSeconds($index*5));
                }

            }
            catch (\Exception $e)
            {
                Log::error("Couldn't Run the Schedule, Error: " . $e);
            }
        })->everyFiveMinutes();


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

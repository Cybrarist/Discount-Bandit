<?php

namespace App\Console;

use App\Enums\StatusEnum;
use App\Jobs\CalculateGroupListPriceJob;
use App\Jobs\GetProductJob;
use App\Models\Currency;
use App\Models\GroupList;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;

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
                //Get products by updating date sorting
                $product_services=DB::table('product_service')->orderBy('updated_at', 'desc')->get(['id', 'product_id', 'service_id','notify_price','price']);
                $currencies=Currency::with('service')->get()->pluck('code', 'service.id')->toArray();

                foreach ($product_services as $product_service)
                    GetProductJob::dispatch(
                        $product_service->product_id,
                        $product_service->service_id,
                        $currencies[$product_service->service_id] ,
                        $product_service->notify_price ,
                        $product_service->price )->onQueue('products')->delay(Carbon::now()->addSeconds(10));
            }
            catch (\Exception $e)
            {
                Log::error("Couldn't Run the Schedule, Error: " . $e);
            }
            })->everyFiveMinutes();

            $schedule->call(function (){
                try {
                    $available_group_lists=GroupList::all();
                    foreach ($available_group_lists as $group_list)
                        CalculateGroupListPriceJob::dispatch($group_list)->onQueue('grouplists');
                }
                catch (\Throwable | \Exception $e){
                    Log::error("Couldn't schedule group list price");
                    Log::error($e);
                }
            })->everyTenMinutes();



            //check if the jobs are running
            $schedule->command(DispatchQueueCheckJobsCommand::class)->everyFifteenMinutes();


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

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
                $currencies=Currency::with('service')->get()->pluck('code', 'service.id')->toArray();

                $products = Product::whereNotIn('status', StatusEnum::ignored())->with(['services'=> function($query){
                    $query->whereNotIn('status', StatusEnum::ignored())->select('services.id');
                }])->get();

                foreach ($products as $product)
                    foreach ($product->services as $service)
                        GetProductJob::dispatch($product, $service, $currencies[$service->id])->delay(Carbon::now()->addSeconds(10));

            }
            catch (\Exception $e)
            {
                Log::error("Error in scheduling " . $e);
            }
            })->everyFiveMinutes();

            $schedule->call(function (){
                try {
                    $available_group_lists=GroupList::all();
                    foreach ($available_group_lists as $group_list)
                        CalculateGroupListPriceJob::dispatch($group_list);
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

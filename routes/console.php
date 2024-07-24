<?php


use App\Console\Commands\ClearNotificationCount;
use App\Models\ProductStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Schedule::call(function () {

    try {
        Log::info("Products Schedule Started");

        //getting stores for the queue slug
        $product_stores=ProductStore::
            with('store')
            ->orderBy("updated_at","desc")
            ->limit(60)
            ->get();

        foreach ($product_stores as $index=>$product_store)
            \App\Jobs\CrawlProductJob::dispatch($product_store->id)
                ->onQueue($product_store->store->slug)
                ->delay(now()->addSeconds($index*5));

        Log::info("Products Schedule Finished Successfully");
    }
    catch (Exception $e)  {
        Log::error("Couldn't Run the Product Schedule, Error: \n" . $e);
    }
})->everyFiveMinutes();


Schedule::command(ClearNotificationCount::class)->dailyAt("23:59");
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

<?php


use App\Console\Commands\ClearNotificationCount;
use App\Enums\StatusEnum;
use App\Jobs\CrawlProductJob;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Schedule::call(function () {
    try {

        DB::table('jobs')->truncate();

        Log::info("Products Schedule Started");
        $stores= Store::with([
            "product_stores"=>function($query){
                $query->orderBy("updated_at")->limit(300);
            }])
            ->whereHas('product_stores')
            ->where('status', StatusEnum::Published)
            ->get();

        foreach ($stores as $store)
            foreach ($store->product_stores as $index=>$product_store){
                CrawlProductJob::dispatch($product_store->id)
                    ->onQueue($product_store->store->slug)
                    ->delay( now()->addSeconds($index * 5));
            }

        Log::info("Products Schedule Finished Successfully");
    }
    catch (Exception $e)  {
        Log::error("Couldn't Run the Product Schedule, Error: \n" . $e);
    }
})->everyFiveMinutes();


Schedule::command(ClearNotificationCount::class)->dailyAt("23:59");
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

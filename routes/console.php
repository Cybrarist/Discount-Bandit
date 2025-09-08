<?php

use App\Console\Commands\ClearNotificationCount;
use App\Enums\StoreStatusEnum;
use App\Jobs\CrawlProductJob;
use App\Models\Store;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('exchange:price', function () {})
    ->dailyAt('00:00');

Schedule::call(function () {
    try {
        // clear all jobs so products won't overlap in case they didn't finish on time.
        DB::table('jobs')->truncate();

        Log::info("Products Schedule Started");

        $stores = Store::withWhereHas('product_links', function ($query) {
            $query->withoutGlobalScopes()
                ->orderBy("updated_at")
                ->distinct(['key', 'store_id'])
                ->limit(60);
        })
            ->where('status', StoreStatusEnum::Active)
            ->get();

        foreach ($stores as $store) {
            foreach ($store->product_links as $index => $product_link) {
                CrawlProductJob::dispatch($product_link->id)
                    ->onQueue($store->slug)
                    ->delay(now()->addSeconds($index * 5));
            }
        }

        Log::info("Products Schedule Finished Successfully");
    } catch (Exception $e) {
        Log::error("Couldn't Run the Product Schedule, Error: \n".$e);
    }
})->cron(config('settings.cron'));

// Schedule::job(\App\Jobs\CheckGroupPriceJob::class)->cron(config('settings.group_cron'));

Schedule::command(ClearNotificationCount::class)->dailyAt("23:59");
// Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
//

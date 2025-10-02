<?php

use App\Console\Commands\ClearNotificationCount;
use App\Enums\StoreStatusEnum;
use App\Jobs\CrawlProductJob;
use App\Jobs\DeleteLinksFromProductsThatAreOutOfStockForXDaysJob;
use App\Jobs\DeleteLinksThatDontHaveProductsJob;
use App\Models\Store;
use Illuminate\Support\Facades\Artisan;

Artisan::command('discount:exchange-rate', function () {})
    ->daily();
Schedule::job(new DeleteLinksFromProductsThatAreOutOfStockForXDaysJob)
    ->daily();
Schedule::job(new DeleteLinksThatDontHaveProductsJob)
    ->daily();

Schedule::call(function () {
    try {
        // clear all jobs so products won't overlap in case they didn't finish on time.
        DB::table('jobs')->truncate();

        Log::info("Products Schedule Started");

        $stores = Store::withWhereHas('links', function ($query) {
            $query->withoutGlobalScopes()
                ->orderBy("updated_at")
                ->distinct(['key', 'store_id'])
                ->limit(60);
        })
            ->where('status', StoreStatusEnum::Active)
            ->get();

        foreach ($stores as $store) {
            foreach ($store->links as $index => $link) {
                CrawlProductJob::dispatch($link->id)
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

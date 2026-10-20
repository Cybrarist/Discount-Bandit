<?php

namespace App\Observers;

use App\Helpers\CacheHelper;
use App\Models\Store;
use Illuminate\Support\Facades\Artisan;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function saved(Store $store): void
    {
        Artisan::call('discount:fill-supervisor-workers');

        if ($store->wasRecentlyCreated || $store->wasChanged('short_url_hosts')) {
            CacheHelper::forget_short_url_hosts();
        }
    }

    /**
     * Handle the Store "updated" event.
     */
    public function updated(Store $store): void
    {
        //
    }

    /**
     * Handle the Store "deleted" event.
     */
    public function deleted(Store $store): void
    {
        CacheHelper::forget_short_url_hosts();
    }

    /**
     * Handle the Store "restored" event.
     */
    public function restored(Store $store): void
    {
        //
    }

    /**
     * Handle the Store "force deleted" event.
     */
    public function forceDeleted(Store $store): void
    {
        //
    }
}

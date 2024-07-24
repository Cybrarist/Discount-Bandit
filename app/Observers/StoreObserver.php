<?php

namespace App\Observers;

use App\Helpers\StoreHelper;
use App\Models\Store;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function created(Store $store): void
    {
        //
    }

    /**
     * Handle the Store "updated" event.
     */
    public function updated(Store $store): void
    {
        StoreHelper::clear_caches_related_to_stores();
    }

    /**
     * Handle the Store "deleted" event.
     */
    public function deleted(Store $store): void
    {
        //
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

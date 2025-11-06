<?php

namespace App\Observers;

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

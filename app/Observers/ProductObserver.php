<?php

namespace App\Observers;

use App\Helpers\StoreHelper;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        StoreHelper::clear_caches_related_to_stores();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        StoreHelper::clear_caches_related_to_stores();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        StoreHelper::clear_caches_related_to_stores();
    }

}

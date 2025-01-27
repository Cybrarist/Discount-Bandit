<?php

namespace App\Observers;

use App\Helpers\StoreHelper;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Facades\DB;

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

        ProductStore::where('product_id', $product->id)->delete();
        PriceHistory::where('product_id', $product->id)->delete();
        DB::table('group_product')->where('product_id', $product->id)->delete();

        StoreHelper::clear_caches_related_to_stores();
    }

}

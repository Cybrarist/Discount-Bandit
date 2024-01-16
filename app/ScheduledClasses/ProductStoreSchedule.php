<?php

namespace App\ScheduledClasses;

use App\Jobs\GetProductJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductStoreSchedule
{

    public function __invoke()
    {
        try {
            Log::info("Products Schedule Started");

            //clear all the jobs, in case of some items still remain after 5 mins.
            clear_job();

            //getting stores for the queue slug
            $product_stores=DB::table('product_store')
                ->join('stores', 'stores.id', '=' , 'store_id')
                ->orderBy('product_store.updated_at')
                ->select([
                    "product_store.id",
                    "product_store.product_id",
                    "product_store.store_id",
                    "stores.domain",
                    "stores.slug"
                ])
                ->get();

            foreach ($product_stores as $index=>$product_store)
                GetProductJob::dispatch($product_store->id , $product_store->domain)
                    ->onQueue($product_store->slug)
                    ->delay(now()->addSeconds($index*5));

            Log::info("Products Schedule Finished Successfully");
        }
        catch (\Exception $e)
        {
            Log::error("Couldn't Run the Product Schedule, Error: " . $e);
        }
    }
}

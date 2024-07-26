<?php

namespace App\Helpers;

use App\Enums\StatusEnum;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreHelper
{


    public static function is_unique(UrlHelper $url) : bool
    {
        $product_store=ProductStore::where("store_id" , $url->store->id)
            ->where("key" , $url->product_unique_key)
            ->first();

        if ($product_store) {
            $product_url=route("filament.admin.resources.products.edit" , $product_store->product_id) ;
            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("This product already exists in your database. check it from <a href='$product_url' target='_blank' style='color: #dc2626'> $product_url</a>")
                ->persistent()
                ->send();
            return false;
        }
        return true;
    }


    public static function fetch_product(Product $product): void
    {
        try {
            $product->load([
                "product_stores"=>[
                    "store"
                ]
            ]);

            foreach ($product->product_stores as $product_store){
                $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$product_store->store->domain)[0]);//
                new $final_class_name($product_store->id);
            }
            Notification::make()
                ->title('Data Has been Fetched, Please refresh to see the new values.')
                ->success()
                ->send();
        }
        catch ( \Exception $e){
            Log::error("Couldn't fetch the job with error : $e" );
            Notification::make()
                ->title("Couldn't fetch the product, refer to logs")
                ->danger()
                ->send();
        }

    }

    public static function get_stores_active_for_tabs()
    {
        return Cache::remember('stores_active_for_tabs' , now()->addDay(), function () {
            return Store::where('tabs', true)->get();
        });
    }

    public static function clear_caches_related_to_stores(): void
    {
        Cache::forget('stores_active_for_tabs');
        Cache::forget('stores_with_active_products');
    }


    public static function get_stores_with_active_products()
    {
        return Cache::remember('stores_with_active_products' , now()->addDay(), function () {
            return Store::whereIn('id' , ProductStore::distinct()->get('store_id')->toArray())
                ->get(["id","name","currency_id"])
                ->keyBy("id")
                ->toArray();
        });
    }

}

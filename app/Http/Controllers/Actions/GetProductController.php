<?php

namespace App\Http\Controllers\Actions;

use App\Helpers\ProductHelper;
use App\Helpers\URLHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GetProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'url'=>["string","required","url"]
        ]);

        $url = new URLHelper($request->url);

        if(!$url->product_unique_key)
            return [];

        $product_record= ProductStore::where('key', $url->product_unique_key)->first()?->product_id;

        if (!$product_record)
            return;

        $product_histories_per_store=ProductHelper::get_product_history_per_store($product_record);

        //get the prices across the stores where the product has been crawled.
        $product_stores= ProductStore::where("product_id" , $product_record)
            ->get(["id" , "seller","highest_price","lowest_price","price" ,"store_id","updated_at","key"] );

        $all_stores_current_prices=[];
        foreach ($product_stores as $single_product_store){
            if (Arr::exists($product_histories_per_store,$single_product_store->store_id ))
                $all_stores_current_prices[$single_product_store->store_id]=[
                    "highest_price"=>$single_product_store->highest_price,
                    "lowest_price"=>$single_product_store->lowest_price,
                    "current_price"=>$single_product_store->price,
                    "seller"=>$single_product_store->seller,
                    "key"=>$single_product_store->key
                ];

        }


        return [
            "prices"=>$all_stores_current_prices,
            "current_store_id"=> $url->store->id,
            "series"=>array_values($product_histories_per_store),
            "product_id"=>$product_record
        ];

    }
}

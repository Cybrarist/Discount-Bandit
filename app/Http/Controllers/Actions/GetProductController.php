<?php

namespace App\Http\Controllers\Actions;

use App\Helpers\ProductHelper;
use App\Helpers\URLHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

        $product_record= ProductStore::where('key', $url->product_unique_key)->first()->product_id;

        $product_histories_per_store=ProductHelper::get_product_history_per_store($product_record);

        //get the prices across the stores where the product has been crawled.
        //todo implement it in the plugins
        $product_stores= ProductStore::where("product_id" , $product_record)
            ->get(["id" , "seller","highest_price","lowest_price","price" ,"store_id","updated_at"] );

        $all_stores_current_prices=[];
        foreach ($product_stores as $single_product_store){
            if (Arr::exists($product_histories_per_store,$single_product_store->store_id ))
                $all_stores_current_prices[$single_product_store->store_id]=[
                    "stores"=>$product_histories_per_store[$single_product_store->store_id]["name"],
                    "highest_price"=>$single_product_store->highest_price,
                    "lowest_price"=>$single_product_store->lowest_price,
                    "current_price"=>$single_product_store->price,
                    "seller"=>$single_product_store->seller,
                ];
        }

        return [
            "prices"=>$all_stores_current_prices,
            'series'=>array_values($product_histories_per_store),
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'theme'=>[
                "palette"=> 'palette1'
            ],
            'xaxis' => [
                "type"=> 'datetime',
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ]

        ];

    }
}

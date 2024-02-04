<?php

namespace App\Http\Controllers;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Argos;
use App\Classes\Stores\DIY;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Walmart;
use App\Classes\URLHelper;
use App\Filament\Resources\ProductResource;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function get_product()
    {
        $validated=request()->validate([
            "url"=>["string" , "url"]
        ]);

        $url = new URLHelper($validated["url"]);

        $unique_key="";
        $product=ProductStore::leftJoin("products" ,"products.id" , "=" , "product_store.product_id")
                ->leftJoin("stores" ,"stores.id" , "=" , "product_store.store_id")
                ->leftJoin("currencies" , "stores.currency_id" , "=" , "currencies.id");

        //todo minimize this code
        if (Amazon::is_amazon($url->domain)){
            $unique_key=$url->get_asin();
            $product=$product->where("products.asin" , $unique_key);
        }
        elseif (Walmart::is_walmart($url->domain)){
            $unique_key=$url->get_walmart_ip();
            $product=$product->where("products.walmart_ip" , $unique_key);
        }
        elseif ( Argos::is_argos($url->domain)){
            $unique_key=$url->get_argos_product_id();
            $product=$product->where("products.argos_id" , $unique_key);
        }
        elseif (Ebay::is_ebay($url->domain)){
            $unique_key=$url->get_ebay_item_id();
            $product=Product::where("product_store.ebay_id" , $unique_key);
        }
        elseif(DIY::is_diy($url->domain)){
            $unique_key=$url->get_diy_id();
            $product=Product::where("product_store.key" , $unique_key);
        }


        if ($product->count()){

            $all_product_stores=$product->
            select([
                "stores.name as store_name",
                "stores.id as store_id",
                "products.id as product_id",
                "currencies.code as  currency_code"
            ])->distinct()->get();

            $stores=$all_product_stores->keyBy("store_id")
                ->map(function ($record){
                return [
                    "name"=>$record->store_name . " (" . $record->currency_code .")"
                ];
            })->toArray();



            $price_history=PriceHistory::where("product_id" , $product->first()->product_id)->get(["date" , "store_id" , "price"]);


            foreach ($price_history as $single_price_history)
                    $stores[$single_price_history->store_id]["data"][]=[
                        'x'=>$single_price_history->date,
                        'y'=>$single_price_history->price
                    ];


            foreach ($stores as $index=>$store)
                if (sizeof($store) == 1)
                    $stores[$index]['data']=[];



            $get_prices_of_current_store=Store::where("domain" , $url->domain )
                    ->leftJoin("price_histories" , "price_histories.store_id" , "=" , "stores.id")
                    ->where("price_histories.product_id" , "=" , $product->first()?->product_id)
                    ->select(DB::raw("MIN(price) as price_min , MAX(price) as price_max"))
                    ->first();

            return [
                "prices"=>$get_prices_of_current_store,
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                ],
                'series'=>array_values($stores),
                'colors' => ['#6366f1','#ffffff'],
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

    public function create_amazon_product()
    {

        $validated=request()->validate([
            "url"=>["string" , "url"],
            "product_name"=>["string", "nullable" ],
            "product_image"=>["string" , "nullable" ],
            "notify_price"=>["numeric" , "nullable"],
            "official_seller"=>["boolean", "nullable"],
            "favourite"=>["boolean", "nullable" ],
            "stock_available"=>["boolean" , "nullable"],
            "lowest_within"=>["numeric" , "integer", "nullable" ],
            "number_of_rates"=>["numeric" , "integer", "nullable" ],
        ]);




        try {
            $url = new URLHelper($validated["url"]);
            $asin= $url->get_asin();
            $store= Store::where("domain" , $url->domain)->first();
            $product=Product::updateOrCreate([
                "asin"=>$asin
            ],[
                "name" => $validated["product_name"],
                "image" => $validated["product_image"],
                "favourite" => $validated["favourite"],
                "only_official" => $validated["official_seller"],
                "stock" => $validated["stock_available"],
                "lowest_within" => $validated["lowest_within"],
            ]);

            ProductStore::updateOrCreate([
                "product_id" => $product->id,
                "store_id" => $store->id,
            ],[
                "notify_price" => $validated["notify_price"],
                "number_of_rates" => $validated["number_of_rates"],

            ]);
            return response([
                "message"=>"Product Added / Updated Successfully" ,
                "link"=> ProductResource::getUrl("edit", ["record"=>$product])
                ] , 200);

        } catch (\Exception $e){
            \Log::error("API Store");
            \Log::error($e);
            return response(["message"=>"Something Wrong Happened"] , 500);
        }



    }
}

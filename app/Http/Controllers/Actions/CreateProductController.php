<?php

namespace App\Http\Controllers\Actions;

use App\Filament\Resources\ProductResource;
use App\Helpers\URLHelper;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            "url"=>["required","url"],
            "name"=>["nullable", "string"],
            "image"=>["nullable"],
            "notify_price"=>["numeric" , "nullable"],
            "official_seller"=>["boolean", "nullable"],
            "favourite"=>["boolean", "nullable" ],
            "stock_available"=>["boolean" , "nullable"],
            "lowest_within"=>["numeric" , "integer", "nullable" ],
            "number_of_rates"=>["numeric" , "integer", "nullable" ],
        ]);



        try {
            $url = new URLHelper($request->url);

            //make sure the store exists
            $store= Store::where("domain" , $url->domain)->first();
            if (!$store)
                abort(400 , "Store doesn't exist in the database, please make sure you have the right store");

            //check the product doesn't exist already
            $product_store=ProductStore::where([
                "store_id" => $store->id,
                "key" => $url->product_unique_key,
                ])->first();

            if ($product_store){
                $product=Product::where("id" , $product_store->product_id)->update([
                    "name" => $request->name,
                    "image" => $request->image,
                    "favourite" => $request->favourite,
                    "only_official" => $request->official_seller,
                    "stock" => $request->stock_available,
                    "lowest_within" => $request->lowest_within,
                ]);

                $product_id=$product_store->product_id;
            } else {
                $product=Product::create([
                    "name" => $request->name,
                    "image" => $request->image,
                    "favourite" => $request->favourite,
                    "only_official" => $request->official_seller,
                    "stock" => $request->stock_available,
                    "lowest_within" => $request->lowest_within,
                ]);
                $product_id=$product->id;
            }

           ProductStore::updateOrCreate([
               "store_id" => $store->id,
               "product_id" => $product_id,
               "key"=>$url->product_unique_key,
           ],[
               "notify_price" => $request->notify_price,
               "number_of_rates" => $request->number_of_rates,
               "price" => $request->price,
           ]);

            return response([
                "message"=>"Product Added / Updated Successfully" ,
                "link"=> ProductResource::getUrl("edit", ["record"=>$product_id])
            ] , 200);

        } catch (\Exception $e){
            Log::error($e);
            return response(["message"=>"Something Wrong Happened"] , 500);
        }


    }
}

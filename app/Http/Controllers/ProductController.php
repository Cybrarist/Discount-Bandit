<?php

namespace App\Http\Controllers;

use App\Helpers\ProductHelper;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    public function show(Product $product)
    {

        $product_histories_per_store=ProductHelper::get_product_history_per_store(product_id: $product->id);

        //get the prices across the stores where the product has been crawled.
        $product_stores= ProductStore::where("product_id" , $product->id)
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


        return view('products.show',[
            "prices"=>$all_stores_current_prices,
            "series"=>json_encode(array_values($product_histories_per_store)),
            "product"=>$product,
            "product_stores" => $product_stores,
        ]);
    }
}


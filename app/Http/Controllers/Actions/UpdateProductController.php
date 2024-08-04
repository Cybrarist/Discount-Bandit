<?php

namespace App\Http\Controllers\Actions;

use App\Helpers\StoresAvailable\StoreTemplate;
use App\Helpers\URLHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Http\Request;

class UpdateProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'url'=>['required','url'],
            'current_price'=>['required','numeric'],
        ]);

        $url=new URLHelper($request->url);

        if (!$url->product_unique_key)
            return;

        //check the store
        $store= Store::where('domain', $url->domain)->first();
        if (!$store)
            return;

        //get the record and update it
        //todo add used_price
        $product_store_record=ProductStore::where([
            'store_id'=>$store->id,
            'key'=>$url->product_unique_key,
        ])->first();

        if (!$product_store_record)
            return;


        $to_update=[];
        if ($request->current_price != $product_store_record->price)
            $to_update['price']=$request->current_price * 100;
        if ($request->current_price < $product_store_record->lowest_price ||  !$product_store_record->lowest_price )
            $to_update['lowest_price']=$request->current_price * 100;
        if ($request->current_price > $product_store_record->highest_price ||  !$product_store_record->highest_price )
            $to_update['highest_price']=$request->current_price * 100;

        ProductStore::where("id", $product_store_record->id)
            ->update($to_update);

        StoreTemplate::record_price_history(product_id: $product_store_record->product_id, store_id: $store->id, price: $request->current_price);

    }
}

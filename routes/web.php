<?php

use App\Jobs\GetProductJob;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\NewDiscountNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

//    $product_stores=DB::table('product_store')
//        ->join('stores', 'store_id', '=' , 'stores.id')
//        ->orderBy('product_store.updated_at', 'desc')
//        ->get();

//    foreach ($product_stores as $index=>$product_store)
//    {
//        GetProductJob::dispatch($product_store->product_id, $product_store->store_id)
//                ->onQueue($product_store->slug)
//                ->delay(now()->addSeconds($index*5));
//    }

//    $product_to_get=Product::where('id' ,1)
//        ->with([
//            'stores'=>function ($query){
//                $query->where('stores.id', 2);
//            }] )->first();
//
//
//    switch (explode("_" , $product_to_get->stores[0]->slug)[0]){
//        case "amazon": echo "amazonnnn";
//            break;

//    }



    $product_stores=DB::table('product_store')
        ->join('stores', 'store_id', '=' , 'stores.id')
        ->orderBy('product_store.updated_at', 'desc')
        ->get();
    dd($product_stores);

    new \App\Classes\Ebay(Product::find(81), Store::find(23), null , "364449056534");

});

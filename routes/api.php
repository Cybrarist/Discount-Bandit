<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;



//todo migrate to api controller
Route::middleware('auth:sanctum')
    ->prefix("products")
    ->name('products.')
    ->group(function (){

        Route::post("get" , \App\Http\Controllers\Actions\GetProductController::class )->name("get");
        Route::post("update" , \App\Http\Controllers\Actions\UpdateProductController::class )->name("update");
        Route::post("create", [ProductController::class, "store"])->name("create");

        //preparing to change to controller.
        Route::resource("products" , ProductController::class)->only(["store"]);
});


//
//new controller to handle all api calls
Route::middleware('auth:sanctum')
    ->prefix("mobile/")
    ->name('mobile.')
    ->group(function (){
        Route::resource("stores" , \App\Http\Controllers\StoreController::class);
        Route::resource("products" , \App\Http\Controllers\Api\ProductController::class);
});

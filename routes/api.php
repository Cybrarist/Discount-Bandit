<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:sanctum')
    ->controller(\App\Http\Controllers\ProductController::class)
    ->name("products.")
    ->prefix("products/")
    ->group(
        function (){
            Route::post("get-product" , "get_product")->name("get-product");
            Route::post("create/amazon" , "create_amazon_product")->name("create.amazon");
        }
    );



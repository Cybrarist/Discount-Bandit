<?php

use App\Models\Currency;
use App\Notifications\GroupDiscount;
use App\Notifications\NewDiscountNotification;
use Filament\Facades\Filament;
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


Route::get('/test', function () {
        new \App\Classes\Stores\Amazon(35);
});

//Route::get("/" , function (){
//    return view("homepage");
//});

//
//Route::get("/test", [\App\Http\Controllers\ProductController::class ,"get_product"]);

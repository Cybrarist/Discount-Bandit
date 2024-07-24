<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:sanctum')
    ->group(function (){



        Route::post("get-product" , \App\Http\Controllers\Actions\GetProductController::class )->name("products.get");

});

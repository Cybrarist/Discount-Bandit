<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:sanctum')
    ->prefix("products")
    ->name('products.')
    ->group(function (){
        Route::post("get" , \App\Http\Controllers\Actions\GetProductController::class )->name("get");
        Route::post("update" , \App\Http\Controllers\Actions\UpdateProductController::class )->name("update");
        Route::post("create" , \App\Http\Controllers\Actions\CreateProductController::class)->name("create");
});

<?php

use Illuminate\Support\Facades\Route;

Route::feeds();


Route::middleware('signed')
    ->group(function () {
        Route::withoutMiddleware('auth')
            ->get('temp/products/{product}', [\App\Http\Controllers\ProductController::class , 'show'])->name('products.show');
    });

<?php

use Illuminate\Support\Facades\Route;

Route::feeds();

Route::middleware('signed')
    ->withoutMiddleware('auth')
    ->group(function () {
        Route::get('temp/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])
            ->name('products.show');

        Route::get('temp/groups/{group}', [\App\Http\Controllers\GroupController::class, 'show'])
            ->name('groups.show');

        Route::get('/product/{product}/snooze', [\App\Http\Controllers\ProductController::class, 'snooze'])
            ->name('products.snooze');
    });



<?php

use Illuminate\Support\Facades\Route;

Route::feeds();

Route::middleware('signed')
    ->group(function () {
        Route::withoutMiddleware('auth')
            ->get('temp/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

        Route::withoutMiddleware('auth')
            ->get('temp/groups/{group}', [\App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');

        Route::withoutMiddleware('auth')
            ->get('temp/products/{product}/snooze', [\App\Http\Controllers\ProductController::class, 'snooze'])
            ->name('products.snooze');

    });

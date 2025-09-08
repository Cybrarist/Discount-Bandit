<?php

use App\Models\ProductLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Feed\Http\FeedController;

Route::withoutMiddleware('auth') -> get('/feed', FeedController::class)->name('feeds.main');

Route::get('/test', function (Request $request) {

    $link = ProductLink::firstWhere('id', 2);

    new \App\Classes\Stores\Amazon($link);

});

Route::middleware('signed')
    ->group(function () {
        Route::withoutMiddleware('auth')
            ->get('temp/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

        //        Route::withoutMiddleware('auth')
        //            ->get('temp/groups/{group}', [\App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');

        Route::withoutMiddleware('auth')
            ->get('temp/products/{product}/snooze', [\App\Http\Controllers\ProductController::class, 'snooze'])
            ->name('products.snooze');

    });



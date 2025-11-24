<?php

use App\Http\Controllers\Actions\GetChartForCurrentLinkAndItsRelatedLinksForTheUserProducts;
use App\Http\Controllers\Actions\GetOtherRelatedLinksController;
use App\Http\Controllers\AddLinkFromBrowserPluginAction;
use App\Http\Controllers\GetLinkUsingUrlController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

// Route::get('/user', function (Request $request) {
//    return $request->user();
// })->middleware('auth:sanctum');
//
Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('/links/add', AddLinkFromBrowserPluginAction::class);

        Route::apiResource('stores', StoreController::class);
        Route::apiResource('products', ProductController::class);
        //        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/link/search-url', GetLinkUsingUrlController::class);
        Route::get('/link/{link}/related', GetOtherRelatedLinksController::class);
        Route::get('/link/{link}/history', GetChartForCurrentLinkAndItsRelatedLinksForTheUserProducts::class);

    });

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::firstWhere('email', $request->email);

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);

    }

    return $user->createToken($request->device_name)->toJson();
});

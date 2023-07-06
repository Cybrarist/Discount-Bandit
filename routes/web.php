<?php

use Illuminate\Support\Facades\Http;
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

Route::get('/test', function (){
    return  view('mail.reached_desired_price',[
        'product' => \App\Models\Product::first(),
        'service' => \App\Models\Service::first(),
        'current_price' => 100,
        'notify_price' => 200,
        'currency' => 'AED'
    ]);
});
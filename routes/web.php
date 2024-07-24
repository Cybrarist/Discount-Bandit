<?php

use Illuminate\Support\Facades\Route;



Route::feeds();


Route::get('/test', \App\Http\Controllers\Actions\GetProductController::class);

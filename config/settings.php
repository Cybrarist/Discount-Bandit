<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Top Navigation
    |--------------------------------------------------------------------------
    |
    | Enable top navigation in your application
    |
    |
    */

    'top_nav' => env('TOP_NAVIGATION', false),

    /*
    |--------------------------------------------------------------------------
    | Top Bar
    |--------------------------------------------------------------------------
    |
    | Disable Top bar completely, if this option used with top_nav then you
    | won't be able to navigate
    |
    */

    'disable_top_bar' => env("TOP_BAR" , true),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb
    |--------------------------------------------------------------------------
    |
    | Enable Breadcrumb in your application
    |
    */

    'breadcrumbs' => env("BREADCRUMBS" , false),

    /*
    |--------------------------------------------------------------------------
    | SPA
    |--------------------------------------------------------------------------
    |
    | Turn filament to SPA version ( Application Feel Like )
    |
    */

    'spa' => env("SPA" , false),


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'disable_auth' => env("DISABLE_AUTH" , false),

    /*
    |--------------------------------------------------------------------------
    | Theme Color
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'theme_color' => env("THEME_COLOR" , 'Blue'),


    /*
    |--------------------------------------------------------------------------
    | Notify on any change in the price
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'notify_any_change' => env("NOTIFY_ON_ANY_PRICE_CHANGE" , 'false'),


];

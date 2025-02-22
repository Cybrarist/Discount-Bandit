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

    'disable_top_bar' => env("TOP_BAR", true),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb
    |--------------------------------------------------------------------------
    |
    | Enable Breadcrumb in your application
    |
    */

    'breadcrumbs' => env("BREADCRUMBS", false),

    /*
    |--------------------------------------------------------------------------
    | SPA
    |--------------------------------------------------------------------------
    |
    | Turn filament to SPA version ( Application Feel Like )
    |
    */

    'spa' => env("SPA", false),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'disable_auth' => env("DISABLE_AUTH", false),

    /*
    |--------------------------------------------------------------------------
    | Theme Color
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'theme_color' => env("THEME_COLOR", 'Blue'),

    /*
    |--------------------------------------------------------------------------
    | Notify on any change in the price
    |--------------------------------------------------------------------------
    |
    | Disable Authentication and keep the panel open
    |
    */

    'notify_any_change' => env("NOTIFY_ON_ANY_PRICE_CHANGE", false),

    /*
    |--------------------------------------------------------------------------
    | Select the time you want the crawler to run for all stors
    |--------------------------------------------------------------------------
    |
    |
    */

    'cron' => env("CRON", '*/5 * * * *'),

    'group_cron' => env("GROUP_CRON", '*/5 * * * *'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    |
    */

    'apprise_url' => env('APPRISE_URL'),

    'ntfy_user' => env("NTFY_USER"),
    'ntfy_password' => env("NTFY_PASSWORD"),
    'ntfy_base_url' => env("NTFY_BASE_URL", "https://ntfy.sh/"),
    'ntfy_token' => env("NTFY_TOKEN"),
    'ntfy_channel_id' => env("NTFY_CHANNEL_ID"),

    'rss_feed' => env("RSS_FEED", false),

    'telegram_bot_token' => env("TELEGRAM_BOT_TOKEN"),
    'telegram_channel' => env("TELEGRAM_CHANNEL_ID"),


    'gotify_base_url' => env("GOTIFY_BASE_URL"),
    'gotify_token' => env("GOTIFY_TOKEN"),
];

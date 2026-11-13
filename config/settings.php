<?php

return [

    'exchange_rate_api_key' => env("EXCHANGE_RATE_API_KEY"),

    // where Amazon prices come from: "crawler" (default, loads the product page) or "scavio" (Scavio API, needs SCAVIO_API_KEY)
    'amazon_provider' => env("AMAZON_PROVIDER", 'crawler'),

    'scavio_api_key' => env("SCAVIO_API_KEY"),

    // each Scavio call uses a credit, so the schedule checks those Amazon links once every this many hours
    'scavio_refresh_hours' => (int) env("SCAVIO_REFRESH_HOURS", 24),

    'cron' => env("CRON", '*/5 * * * *'),

    'theme_color' => env("THEME_COLOR", 'Red'),

    'github_community_store_repo' => env("COMMUNITY_STORE_GITHUB_REPO", 'https://api.github.com/repos/Cybrarist/Discount-Bandit-Community-Stores/contents/'),
    'github_community_store_gist_base' => env("COMMUNITY_STORE_GITHUB_GIST_REPO", 'https://raw.githubusercontent.com/Cybrarist/Discount-Bandit-Community-Stores/refs/heads/Master/'),

];

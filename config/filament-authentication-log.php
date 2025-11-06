<?php

use App\Models\User;
use Tapp\FilamentAuthenticationLog\Resources\AuthenticationLogResource;

return [
    // 'user-resource' => \App\Filament\Resources\UserResource::class,
    'resources' => [
        'AutenticationLogResource' => \App\Filament\Resources\AuthenticationLogs\AuthenticationLogsResource::class,
    ],

    'authenticable-resources' => [
        User::class,
    ],

    'authenticatable' => [
        'field-to-display' => null,
    ],

    'navigation' => [
        'authentication-log' => [
            'register' => true,
            'sort' => 1,
            'icon' => 'heroicon-o-shield-check',
            'group' => 'Settings',
        ],
    ],

    'sort' => [
        'column' => 'login_at',
        'direction' => 'desc',
    ],
];

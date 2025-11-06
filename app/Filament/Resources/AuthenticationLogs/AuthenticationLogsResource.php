<?php

namespace App\Filament\Resources\AuthenticationLogs;

use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Auth;
use Tapp\FilamentAuthenticationLog\Resources\AuthenticationLogResource;

class AuthenticationLogsResource extends AuthenticationLogResource
{
    public static function canAccess(): bool
    {
        return Auth::user()->role === RoleEnum::Admin;
    }

}

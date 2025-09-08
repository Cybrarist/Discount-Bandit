<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResetUserSettingToDefaultAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user)
    {
        $notification_settings = [
            'ntfy_url' => null,
            'ntfy_auth_username' => null,
            'ntfy_auth_password' => null,
            'telegram_bot_token' => null,
            'telegram_channel_id' => null,
            'timezone' => 'UTC',
            'enable_rss_feed' => false,
            'enable_top_navigation' => true,
            'notify_percentage' => null,
        ];

        $customization_settings = [
            'timezone' => 'UTC',
            'enable_rss_feed' => false,
            'enable_top_navigation' => true,
        ];

        $other_settings = [
            'max_links' => 10,
        ];

        $user->update([
            'notification_settings' => $notification_settings,
            'customization_settings' => $customization_settings,
            'other_settings' => $other_settings,
            'rss_feed' => Str::random(256),
        ]);

    }
}

<?php

namespace App\NotificationsChannels;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Ntfy
{
    public function __construct(
        public Http $httpClient
    ) {}

    public function send(array $notification, string $notification_content, User $user)
    {
        $auth = [];

        $ntfy_url = Str::of($user->notification_settings['ntfy_url'])
            ->remove(['https://', 'http://'])
            ->explode('/');

        if ($user->notification_settings['ntfy_auth_username'] && $user->notification_settings['ntfy_auth_password']) {
            $auth["Authorization"] = "Basic ".base64_encode($user->notification_settings['ntfy_auth_username'].":".$user->notification_settings['ntfy_auth_password']);
        } elseif ($user->notification_settings['ntfy_auth_token']) {
            $auth["Authorization"] = "Bearer ".$user->notification_settings['ntfy_auth_token'];
        }

        $data = [
            "topic" => $ntfy_url[1],
            "message" => Str::replace("<br>", "\n", $notification_content),
            "title" => $notification['Title'],
            "tags" => explode(',', $notification['X-Tags']),
            "attach" => $notification['Attach'],
            "actions" => $notification['Actions'],
        ];

        Http::withHeaders($auth)
            ->asJson()
            ->post($ntfy_url[0], $data);
    }
}

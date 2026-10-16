<?php

namespace App\NotificationsChannels;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class Ntfy
{
    public function __construct(
        public Http $httpClient
    ) {}

    public function send(array $notification, string $notification_content, User $user)
    {
        $auth = [];

        $original_ntfy = $user->notification_settings['ntfy_url'];

        $ntfy_url = parse_url($original_ntfy);

        $ntfy_topic = str_replace('/', '', $ntfy_url['path']);
        $ntfy_post_url = "{$ntfy_url['scheme']}://{$ntfy_url['host']}";

        if (isset($ntfy_url['port'])) {
            $ntfy_post_url .= ":{$ntfy_url['port']}";
        }


        if ($user->notification_settings['ntfy_auth_username'] && $user->notification_settings['ntfy_auth_password']) {
            $auth["Authorization"] = "Basic ".base64_encode($user->notification_settings['ntfy_auth_username'].":".$user->notification_settings['ntfy_auth_password']);
        } elseif ($user->notification_settings['ntfy_auth_token']) {
            $auth["Authorization"] = "Bearer ".$user->notification_settings['ntfy_auth_token'];
        }

        $data = [
            "topic" => $ntfy_topic,
            "message" => Str::replace("<br>", "\n", $notification_content),
            "title" => $notification['Title'],
            "tags" => explode(',', $notification['X-Tags']),
            "attach" => $notification['Attach'],
            "actions" => $notification['Actions'],
        ];

        Http::withHeaders($auth)
            ->asJson()
            ->post($ntfy_post_url, $data);
    }
}

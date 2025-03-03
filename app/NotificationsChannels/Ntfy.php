<?php

namespace App\NotificationsChannels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Ntfy
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Http;
    }

    public function send(array $notification_title, string $notification_content)
    {
        $this->request($notification_title, $notification_content);
    }

    protected function request(array $notification_title, string $notification_content): void
    {
        $auth = [];

        $url = config('settings.ntfy_base_url');

        if (config('settings.ntfy_user') && config('settings.ntfy_password')) {
            $auth["Authorization"] = "Basic ".base64_encode(config('settings.ntfy_user').":".config('settings.ntfy_password'));
        } elseif (config('settings.ntfy_token')) {
            $auth["Authorization"] = "Bearer ".config('settings.ntfy_token');
        }


        $data = [
            "topic" => config('settings.ntfy_channel_id'),
            "message" => Str::replace("<br>", "\n", $notification_content),
            "title" => $notification_title['Title'],
            "tags" => explode(',', $notification_title['X-Tags']),
            "attach" => $notification_title['Attach'],
            "actions" => $notification_title['Actions'],
        ];

        Http::asJson()
            ->withHeaders($auth)
            ->post($url, $data);
    }
}

<?php

namespace App\NotificationsChannels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gotify
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
        $baseUrl = config('settings.gotify_base_url');
        $token = config('settings.gotify_token');

        $url = "{$baseUrl}/message?token={$token}";

        $data = [
            "title" => $notification_title['title'],
            "message" => Str::replace("<br>", "\n", $notification_content),
        ];

        Http::asJson()->post($url, $data);
    }
}

<?php

namespace App\NotificationsChannels;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class Apprise
{
    protected $http_client;

    public function __construct()
    {
        $this->http_client = new Http;
    }

    public function send(array $notification, string $notification_content, User $user)
    {
        if (! isset($user->notification_settings['apprise_url']) || ! $user->notification_settings['apprise_url']) {
            return null;
        }

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "Cache: no",
        ])
            ->post($user->notification_settings['apprise_url'], [
                'tags' => 'all',
                "title" => $notification['title'],
                'body' => $notification['body'],
                'attach' => $notification['attach'],
                'format' => 'html',
            ]);

        return $response->json();
    }
}

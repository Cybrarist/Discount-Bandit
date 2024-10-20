<?php

namespace App\NotificationsChannels;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;


class Ntfy
{
    protected string $baseUrl = 'https://ntfy.sh/';

    protected $httpClient;


    public function __construct()
    {
        $this->httpClient = new Http();
    }


    public function send( array $notification_title , string $notification_content )
    {
        return $this->request($notification_title , $notification_content);
    }


    protected function request( array $notification_title, string $notification_content)
    {
        $auth=[];

        $url=env('NTFY_BASE_URL') ?? $this->baseUrl;

        if (env("NTFY_USER") && env("NTFY_PASSWORD"))
            $auth["Authorization"] = "Basic " . base64_encode(env("NTFY_USER") .":" . env("NTFY_PASSWORD") );
        elseif (env("NTFY_TOKEN"))
            $auth["Authorization"] = "Bearer " . env("NTFY_TOKEN");

        $data = [
            "topic" => env("NTFY_CHANNEL_ID"),
            "message" =>  Str::replace("<br>" , "\n" ,  $notification_content),
            "title" => $notification_title['Title'],
            "tags" => explode(',' , $notification_title['X-Tags']),
            "attach" => $notification_title['Attach'],
            "actions" =>$notification_title['Actions'],
        ];

        Http::asJson()
            ->withHeaders($auth)
            ->post($url, $data);
    }

}

<?php

namespace App\NotificationsChannels;


use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
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
        $url=$this->baseUrl . env('NTFY_CHANNEL_ID');

        if (env("NTFY_USER") && env("NTFY_PASSWORD"))
            $auth["Authorization"] = "Basic " . base64_encode(env("NTFY_USER") .":" . env("NTFY_PASSWORD") );
        elseif (env("NTFY_TOKEN"))
            $auth["Authorization"] = "Bearer " . env("NTFY_TOKEN");

        $response = Http::withHeaders($auth + [
                "Content-Type"=>"text/markdown",
                'X-Markdown'=>"1",
                'Markdown'=>"1",
                'md'=>"1",
                "Cache: no",
            ] + $notification_title)
            ->post($url, $notification_content);

        $body = json_decode($response->getBody(), true);

        return $body;
    }

}

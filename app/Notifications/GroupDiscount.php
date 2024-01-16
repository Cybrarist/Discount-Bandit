<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupDiscount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public  $group , public  $current_price , public $currency){}

    /**
     * Get the notification's delivery channels.
     *
     * @return string
     */
    public function via(object $notifiable): string
    {
        return NtfyChannel::class;
    }

    public function toNtfy($notifiable)
    {
        $auth=[];

        if (env("NTFY_USER") && env("NTFY_PASSWORD"))
            $auth["Authorization"] = "Basic " . base64_encode(env("NTFY_USER") .":" . env("NTFY_PASSWORD") );
        elseif (env("NTFY_TOKEN"))
            $auth["Authorization"] = "Bearer " . env("NTFY_TOKEN");


        \Http::withHeaders(
            array_merge($auth ,
                [
                    "Content-Type"=>"text/markdown",
                    'X-Markdown'=>"1",
                    'Markdown'=>"1",
                    'md'=>"1",
                    "Cache: no",
                    'Title'=>"Your Group '$this->group' Has Reached the Desired Price. $this->currency $this->current_price ",
                ]
            ))
            ->withBody("Please refer to the website")
            ->post(env("NTFY_LINK"));

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

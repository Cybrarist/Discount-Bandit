<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class ProductDiscount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct( public $product_name,
                                 public $store_name ,
                                 public $price ,
                                 public $product_url,
                                 public $image ,
                                 public $currency){}



    /**
     * Get the notification channels.
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


        $respo=\Http::withHeaders(
            array_merge($auth ,
            [
            "Content-Type"=>"text/markdown",
            'X-Markdown'=>"1",
            'Markdown'=>"1",
            'md'=>"1",
            "Cache: no",
            'Title'=>"For Just $this->price -  Discount For " . \Str::words($this->product_name , 5),
            "Actions"=> "view, Open in $this->store_name, $this->product_url",
            "Attach"=>"$this->image"]
            ))

            ->withBody("Your product $this->product_name, is at discount with price  $this->currency $this->price
            ")
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

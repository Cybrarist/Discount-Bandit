<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ItemDesiredPriceReached extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $product;
    public $service;
    public $notify_price;
    public $live_price;
    public $currency;
    public function __construct($product, $service, $live_price,$notify_price, $currency)
    {
        $this->product=$product;
        $this->service=$service;
        $this->notify_price=$notify_price;
        $this->live_price=$live_price;
        $this->currency=$currency;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::debug("Email Sent");
        return (new MailMessage)
                    ->from("pricealert@cybrarist.com" , "Price Alert ")
                    ->view('mail.reached_desired_price', [
                        'product'=>$this->product,
                        'service'=>$this->service,
                        'live_price'=>$this->live_price,
                        'notify_price'=>$this->notify_price,
                        'currency'=>$this->currency
                    ])
                    ->subject($this->product->name . " is less than the desired price you have set");
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

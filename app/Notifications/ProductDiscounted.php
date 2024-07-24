<?php

namespace App\Notifications;

use App\NotificationsChannels\NtfyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ProductDiscounted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $product_name,
                                public $store_name ,
                                public $price,
                                public $highest_price,
                                public $lowest_price,
                                public $product_url,
                                public $image,
                                public $currency,
                                public $tags
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */

    public function via(object $notifiable): array
    {
        return [NtfyChannel::class];
    }
    public function toNtfy(object $notifiable): array {


        $extra_headers=[
            'Title'=>"For Just $this->price -  Discount For " . Str::words($this->product_name , 5),
            "Actions"=> "view, Open in $this->store_name, $this->product_url",
            "Attach"=>$this->image,
            "X-Tags"=>"money_with_wings" . $this->tags
        ];

        $content="$this->product_name, is at $this->currency $this->price. <br>" .
            "**Highest Price**: $this->highest_price  \n" .
            "**Lowest Price**: $this->lowest_price \n"
        ;

        return ["content" => $content ,"headers"=> $extra_headers  ];
    }
}

<?php

namespace App\Notifications;

use App\Models\RssFeedItem;
use App\NotificationsChannels\AppriseChannel;
use App\NotificationsChannels\GotifyChannel;
use App\NotificationsChannels\NtfyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramFile;

class ProductDiscounted extends Notification
{
    use Queueable;

    public string $product_temp_link;

    public string $notification_title;

    public string $notification_text;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $product_id,
        public $product_name,
        public $store_name,
        public $price,
        public $highest_price,
        public $lowest_price,
        public $product_url,
        public $image,
        public $currency,
        public $tags,
    ) {
        $this->product_temp_link = URL::temporarySignedRoute("products.show", now()->addMinutes(15), ['product' => $this->product_id]);

        $this->notification_title = "For Just $this->price -  Discount For ".Str::words($this->product_name, 5);
        $this->notification_text = "{$this->product_name}, is at {$this->currency}{$this->price} <br>".
                "----------------<br>".
                "Highest Price: {$this->highest_price} <br>".
                "Lowest Price: {$this->lowest_price} <br>";

        RssFeedItem::create([
            "data" => [
                'title' => $this->notification_title,
                'summary' => Str::replace("<br>", "&#xa;", $this->notification_text),
                'updated' => now()->toDateTimeString(),
                'product_id' => $this->product_id,
                'image' => $this->image,
                'name' => $this->product_name,
                'link' => $this->product_url,
                'authorName' => "Discount Bandit",
            ],
        ]);

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        if (config('settings.ntfy_channel_id')) {
            $channels[] = NtfyChannel::class;
        }

        if (config('settings.apprise_url')) {
            $channels[] = AppriseChannel::class;
        }

        if (config('settings.telegram_bot_token') && config('settings.telegram_channel')) {
            $channels[] = 'telegram';
        }

        if (config('settings.gotify_token')) {
            $channels[] = GotifyChannel::class;
        }

        return $channels;
    }

    public function toApprise(object $notifiable): array
    {

        $content = [
            'title' => "For Just $this->price -  Discount For ".Str::words($this->product_name, 5),
            'body' => $this->notification_text.
                "----------------<br>
                Product URL: . {$this->product_url}",
            'attach' => [$this->image],
            'format' => 'html',
        ];

        return ["content" => $content];
    }

    public function toGotify(object $notifiable): array
    {
        return [
            "title" => $this->notification_title,
            "message" => Str::replace("<br>", "\n", $this->notification_text),
        ];
    }

    public function toNtfy(object $notifiable): array
    {
        $extra_headers = [
            'Title' => $this->notification_title,
            "Actions" => [
                [
                    "action" => "view",
                    "label" => "Open in $this->store_name",
                    "url" => $this->product_url,
                ], [
                    "action" => "view",
                    "label" => "See Trend",
                    "url" => $this->product_temp_link,
                ],
            ],
            "Attach" => $this->image,
            "X-Tags" => "money_with_wings".$this->tags,
        ];

        return ["content" => $this->notification_text, "headers" => $extra_headers];
    }

    public function toTelegram($notifiable)
    {

        return TelegramFile::create()
            ->photo($this->image)
            ->token(config('settings.telegram_bot_token'))
            ->to(config('settings.telegram_channel'))
            ->content(
                "$this->product_name, is at $this->currency $this->price \n ".
                "--------------------------\n".
                "Highest Price: $this->highest_price  \n".
                "Lowest Price: $this->lowest_price \n".
                "--------------------------\n".
                $this->tags
            )
            ->button('View Product', $this->product_url)
            ->button('View Trend', $this->product_temp_link);
    }




}

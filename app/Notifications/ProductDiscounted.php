<?php

namespace App\Notifications;

// use App\Models\RssFeedItem;
// use App\NotificationsChannels\AppriseChannel;
// use App\NotificationsChannels\GotifyChannel;
use App\Models\Link;
use App\Models\RssFeedItem;
use App\Models\User;
use App\NotificationsChannels\GotifyChannel;
use App\NotificationsChannels\NtfyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramFile;

// use NotificationChannels\Telegram\TelegramFile;

class ProductDiscounted extends Notification
{
    //    use Queueable;

    public string $product_temp_link;

    public string $product_temp_snooze;

    public string $notification_title;

    public string $notification_text;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $product_id,
        public ?string $product_name,
        public ?string $product_image,
        public string $store_name,
        public Link $new_link,
        public float $highest_price,
        public float $lowest_price,
        public string $currency_code,
        public array $notification_reasons,
        public string $product_url = "",
    ) {
        $this->product_temp_link = URL::temporarySignedRoute("products.show", now()->addMinutes(15), ['product' => $this->product_id]);
        $this->product_temp_snooze = URL::temporarySignedRoute("products.snooze", today()->endOfDay(), ['product' => $this->product_id]);

        $this->notification_title = "For Just {$this->currency_code} {$this->new_link->price} -  Discount For ".Str::words($this->product_name, 5);
        $this->notification_text = "{$this->product_name}, is at {$this->currency_code}{$this->new_link->price} <br>".
                "----------------<br>".
                "Used Price: {$this->new_link->used_price} <br>".
                "Shipping Price: {$this->new_link->shipping_price} <br>".
                "Highest Price: {$this->highest_price} <br>".
                "Lowest Price: {$this->lowest_price} <br>".
                "Seller : {$this->new_link->seller} <br>".
                "In Stock : {$this->new_link->is_in_stock} <br>".
                "Condition: {$this->new_link->condition} <br>".
                "total reviews: {$this->new_link->total_reviews} <br>".
                "rating : {$this->new_link->rating} <br>";

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {

        $channels = [];
        if ($notifiable->notification_settings['ntfy_url']) {
            $channels[] = NtfyChannel::class;
        }

        if ($notifiable->notification_settings['telegram_channel_id'] && $notifiable->notification_settings['telegram_bot_token']) {
            $channels[] = 'telegram';
        }

        if ($notifiable->notification_settings['gotify_url']) {
            $channels[] = GotifyChannel::class;
        }

        if ($notifiable->notification_settings['enable_rss_feed']) {
            RssFeedItem::create([
                'user_id' => $notifiable->id,
                'data' => [
                    'title' => $this->notification_title,
                    'summary' => Str::replace("<br>", "&#xa;", $this->notification_text),
                    'updated' => now()->toDateTimeString(),
                    'product_id' => $this->product_id,
                    'image' => $this->product_image,
                    'name' => $this->product_name,
                    'link' => $this->product_url,
                    'authorName' => "Discount Bandit",
                ],
            ]);
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
                ],
                //                [
                //                    "action" => "view",
                //                    "label" => "See Trend",
                //                    "url" => $this->product_temp_link,
                //                ],
                [
                    "action" => "view",
                    "label" => "Snooze For Today",
                    "url" => $this->product_temp_snooze,
                ],
            ],
            "Attach" => $this->product_image,
            "X-Tags" => "money_with_wings, ".implode(", ", $this->notification_reasons),
        ];

        return ["content" => $this->notification_text, "headers" => $extra_headers];
    }

    public function toTelegram($notifiable)
    {

        $notification_text = str_replace("<br>", "\n", $this->notification_text);
        $notification_title = Str::remove([":", "_", "-", "|", "*"], "For Just {$this->currency_code} {$this->new_link->price} -  Discount For ".Str::words($this->product_name, 5));

        return TelegramFile::create()
            ->photo($this->product_image)
            ->token($notifiable->notification_settings['telegram_bot_token'])
            ->to($notifiable->notification_settings['telegram_channel_id'])
            ->content(
                "{$notification_title}\n".
                "--------------------------\n$notification_text--------------------------\n".
                implode(", ", $this->notification_reasons)
            )
            ->button('View Product', $this->product_url)
        //            ->button('View Trend', $this->product_temp_link)
            ->button('Snooze For Today', $this->product_temp_snooze);
    }
}

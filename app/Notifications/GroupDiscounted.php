<?php

namespace App\Notifications;

use App\Models\Group;
use App\NotificationsChannels\AppriseChannel;
use App\NotificationsChannels\NtfyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramFile;

class GroupDiscounted extends Notification
{
    use Queueable;

    public string $group_temp_link;

    public string $notification_title;

    public string $notification_text;
    public function __construct(
        public Group $group,
        public $price,
        public $highest_price,
        public $lowest_price,
        public $product_url,
        public $currency,
        public $tags,
    ) {
        $this->group_temp_link = URL::temporarySignedRoute("groups.show", now()->addMinutes(15), ['group' => $this->group->id]);

        $this->notification_title = "Group {$this->group->name} reached the price you like";
        $this->notification_text = "{$this->product_name}, is at {$this->currency}{$this->price} <br>".
                "----------------<br>".
                "Highest Price: {$this->highest_price} <br>".
                "Lowest Price: {$this->lowest_price} <br>";

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {

        $channels = [];
        if (env('NTFY_CHANNEL_ID')) {
            $channels[] = NtfyChannel::class;
        }

        if (env('APPRISE_URL')) {
            $channels[] = AppriseChannel::class;
        }

        if (env('TELEGRAM_BOT_TOKEN') && env('TELEGRAM_CHANNEL_ID')) {
            $channels[] = 'telegram';
        }

        return $channels;
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
            ->token(env("TELEGRAM_BOT_TOKEN"))
            ->to(env('TELEGRAM_CHANNEL_ID'))
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
}

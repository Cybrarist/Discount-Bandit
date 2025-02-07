<?php

namespace App\Notifications;

use App\Models\Group;
use App\NotificationsChannels\AppriseChannel;
use App\NotificationsChannels\NtfyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramFile;

class GroupDiscounted extends Notification
{
    use Queueable;

    public string $notification_title;

    public string $notification_text;

    public function __construct(
        public Group $group,
        public $price,
        public $highest_price,
        public $lowest_price,
        public $currency,
    ) {
        //        $this->group_temp_link = URL::temporarySignedRoute("groups.show", now()->addMinutes(15), ['group' => $this->group->id]);

        $this->notification_title = "Group {$this->group->name} reached the price you like";

        $this->notification_text = "{$this->group->name}, is at {$this->currency}{$this->price} <br>".
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
        if (config('settings.ntfy_channel_id')) {
            $channels[] = NtfyChannel::class;
        }

        if (config('settings.apprise_url')) {
            $channels[] = AppriseChannel::class;
        }

        if (config('settings.telegram_bot_token') && config('settings.telegram_channel')) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    public function toNtfy(object $notifiable): array
    {
        $extra_headers = [
            'Title' => $this->notification_title,
            "Actions" => [],
            "X-Tags" => "group",
            "Attach" => "",

        ];

        return ["content" => $this->notification_text, "headers" => $extra_headers];
    }

    public function toTelegram($notifiable)
    {

        return TelegramFile::create()
            ->token(config('settings.telegram_bot_token'))
            ->to(config('settings.telegram_channel'))
            ->content(
                "{$this->group->name}, is at {$this->currency} {$this->price} \n ".
                "--------------------------\n".
                "Highest Price: $this->highest_price  \n".
                "Lowest Price: $this->lowest_price \n".
                "--------------------------\n"
            );
        //            ->button('View Trend', $this->product_temp_link);
    }

    public function toApprise(object $notifiable): array
    {

        $content = [
            'title' => "Group {$this->group->name} reached the price you like",
            'body' => $this->notification_text.
                "----------------<br>",
            //                Product URL: . {$this->product_url}",
            //            'attach' => [$this->image],
            'format' => 'html',
        ];

        return ["content" => $content];
    }
}

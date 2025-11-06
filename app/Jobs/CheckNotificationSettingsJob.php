<?php

namespace App\Jobs;

use App\Models\NotificationSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckNotificationSettingsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $link,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification_settings = NotificationSetting::where('link_id', $this->link)->get();

        foreach ($notification_settings as $notification_setting) {

        }
    }
}

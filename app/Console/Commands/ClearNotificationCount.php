<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ClearNotificationCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the count of the notification that has been sent daily.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Product::withoutGlobalScopes()
            ->where('notifications_sent', '>', 0)
            ->update([
                'notifications_sent' => 0,
            ]);
    }
}

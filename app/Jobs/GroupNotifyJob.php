<?php

namespace App\Jobs;

use App\Classes\GroupHelper;
use App\Models\Group;
use App\Models\User;
use App\Notifications\GroupDiscount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GroupNotifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public  Group $group , public $current_price){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user=User::first();
            $user->notify(
                new GroupDiscount(
                    group: $this->group->name,
                    current_price: $this->current_price,
                    currency: $this->group->currency->code
                ));
        }
        catch (\Exception $e)
        {
           \Log::error("Sending Group Notification");
        }

    }
}

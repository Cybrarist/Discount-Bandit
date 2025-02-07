<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Helpers\CurrencyHelper;
use App\Helpers\GroupHelper;
use App\Models\Group;
use App\Models\RssFeedItem;
use App\Models\User;
use App\Notifications\GroupDiscounted;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckGroupPriceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $groups = Group::where('status', StatusEnum::Published)->get();

        foreach ($groups as $group) {
            $current_price = GroupHelper::get_current_price($group);

            // if the current price didn't change, even if items has changed price
            // we don't continue.

            if ($current_price == $group->current_price) {
                continue;
            } else {
                $group->update([
                    'current_price' => $current_price,
                    'highest_price' => ($current_price < $group->highest_price) ?: $current_price,
                    'lowest_price' => ($current_price > $group->lowest_price) ?: $current_price,
                    'notifications_sent' => ($this->check_notification($group, $current_price)) ? ++$group->notifications_sent : $group->notifications_sent,
                ]);

            }

        }
    }

    private function check_notification(Group $group, int|float $current_price): bool
    {
        // check if snoozed
        if ($group->snoozed_until && $group->snoozed_until->isFuture()) {
            return false;
        }

        // todo check if price lowest within

        // check if max notification to send has been reached
        if ($group->max_notifications && $group->notifications_sent >= $group->max_notifications) {
            return false;
        }

        if ($group->notify_price && $current_price && $current_price <= $group->notify_price) {
            $this->notify($group, $current_price);
            return true;
        }
        if ($current_price &&
            $group->notify_percentage &&
            (($group->current_price - $current_price) / $group->current_price) * 100 >= $group->notify_percentage
        ) {
            $this->notify($group, $current_price);
            return true;
        }

        return false;
    }

    private function notify(Group $group, int|float $current_price): void
    {
        try {
            $user = User::first();

            $user->notify(
                new GroupDiscounted(
                    group: $group,
                    price: $current_price,
                    highest_price: $group->highest_price,
                    lowest_price: $group->lowest_price,
                    currency: CurrencyHelper::get_currencies($group->currency_id)
                ));

            if (config('settings.rss_feed')) {
                RssFeedItem::create([
                    "data" => [
                        'title' => "New Discount for group {$group->name}",
                        'summary' => "your discount for group {$group->name}",
                        'updated' => now()->toDateTimeString(),
                        'product_id' => $group->id,
                        'image' => "",
                        'name' => "Discount Bandit",
                    ],
                ]);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

    }
}

<?php

namespace App\ScheduledClasses;

use App\Classes\GroupHelper;
use App\Enums\StatusEnum;
use App\Jobs\GetProductJob;
use App\Jobs\GroupNotifyJob;
use App\Models\Group;
use Carbon\Carbon;

class GroupSchedule
{
    public function __invoke(){


        $groups =Group::whereNot("status" , StatusEnum::Disabled)->get();
        $index=0;
        foreach ($groups as $group){
            $current_price=GroupHelper::get_current_price($group);
            if (
                ($current_price &&  $current_price <= $group->notify_price) &&
                (!$group->snoozed_until || Carbon::create($group->snoozed_until)->isPast()) &&
                (!$group->max_notifications || $group->max_notifications > $group->notifications_sent)
            ){
                GroupNotifyJob::dispatch($group, $current_price)
                    ->onQueue("groups")
                    ->delay(now()->addSeconds($index * 5));

                $group->update([
                    "notifications_sent" => $group->notifications_sent +1
                ]);
                $index++;

            }


        }


    }
}

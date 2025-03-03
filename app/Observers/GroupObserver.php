<?php

namespace App\Observers;

use App\Jobs\CheckGroupPriceJob;
use App\Models\Group;
use App\Models\GroupPriceHistory;
use Illuminate\Support\Facades\DB;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function saved(Group $group): void
    {
        CheckGroupPriceJob::dispatch();
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleting(Group $group): void
    {
        DB::table('group_product')
            ->where('group_id', $group->id)
            ->delete();

        GroupPriceHistory::where('group_id', $group->id)->delete();
    }

}

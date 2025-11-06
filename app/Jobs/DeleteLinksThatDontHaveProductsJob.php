<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\NotificationSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteLinksThatDontHaveProductsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $orphan_links = Link::whereDoesntHave(
            'products',
            function ($query) {
                $query->withoutGlobalScopes();
            }
        )->pluck('id');

        LinkHistory::whereIn('link_id', $orphan_links)->delete();

        NotificationSetting::withoutGlobalScopes()
            ->whereIn('link_id', $orphan_links)
            ->delete();

        Link::whereIn('id', $orphan_links)->delete();

    }
}

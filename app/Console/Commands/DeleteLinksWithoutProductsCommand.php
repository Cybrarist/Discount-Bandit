<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\NotificationSetting;
use Illuminate\Console\Command;

class DeleteLinksWithoutProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:delete-orphan-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete orphan links that are not linked to any products";

    /**
     * Execute the console command.
     */
    public function handle()
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

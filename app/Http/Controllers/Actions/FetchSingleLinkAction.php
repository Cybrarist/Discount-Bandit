<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlProductJob;
use App\Models\Product;
use App\Models\ProductLink;
use App\Models\ProductStore;
use Filament\Notifications\Notification;

class FetchSingleLinkAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductLink $link)
    {
        CrawlProductJob::dispatch($link->id);

        Notification::make()
            ->title('Link Has Been Sent To Crawler')
            ->success()
            ->send();
    }
}

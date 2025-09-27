<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlProductJob;
use App\Models\Product;
use Illuminate\Http\Request;

class FetchAllLinksForProductAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Product $product)
    {
        $product->links()->each(function ($link) {
            CrawlProductJob::dispatch($link->id);
        });

    }
}

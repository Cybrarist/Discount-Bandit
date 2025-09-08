<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlProductJob;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Http\Request;

class FetchAllLinksForProductAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Product $product)
    {
        ProductStore::where('product_id', $product->id)
            ->pluck('id')
            ->each(function ($id) {
                CrawlProductJob::dispatch($id);
            });

    }
}

<?php

namespace App\Console\Commands;

use App\Models\LinkHistory;
use App\Models\Product;
use Illuminate\Console\Command;

class DeleteLinksFromProductsThatAreOutOfStockForXDaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:delete-links-out-of-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Product::withoutGlobalScopes()
            ->where('products.remove_link_if_out_of_stock_for_x_days', '>', 0)
            ->with(['links' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->chunkById(30, function ($products) {

                $products->each(function ($product) {

                    $last_crawl = LinkHistory::whereIn('link_id', $product->links->pluck('id'))
                        ->select('link_id', \DB::raw('MAX(date) as date'))
                        ->groupBy('link_id')
                        ->pluck('date', 'link_id')
                        ->toArray();


                    $links_out_of_stock = [];
                    foreach ($product->links as $link) {
                        if (isset($last_crawl[$link->id]) && $last_crawl[$link->id]->diffInDays(today()) > $product->remove_link_if_out_of_stock_for_x_days)
                            $links_out_of_stock[] = $link->id;
                    }

                    $product->links()->detach($links_out_of_stock);

                });
            });
    }
}

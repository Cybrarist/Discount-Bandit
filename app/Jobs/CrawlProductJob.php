<?php

namespace App\Jobs;

use App\Classes\CustomStoreTemplate;
use App\Classes\Stores\Aliexpress;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Canadiantire;
use App\Classes\Stores\Currys;
use App\Classes\Stores\Diy;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Emaxme;
use App\Classes\Stores\Eprice;
use App\Classes\Stores\Flipkart;
use App\Classes\Stores\Fnac;
use App\Classes\Stores\Homedepot;
use App\Classes\Stores\Mediamarkt;
use App\Classes\Stores\Microless;
use App\Classes\Stores\Myntra;
use App\Classes\Stores\Newegg;
use App\Classes\Stores\Nexths;
use App\Classes\Stores\Noon;
use App\Classes\Stores\Nykaa;
use App\Classes\Stores\Otakume;
use App\Classes\Stores\Princessauto;
use App\Classes\Stores\Target;
use App\Classes\Stores\Tatacliq;
use App\Classes\Stores\Walmart;
use App\Models\ProductLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CrawlProductJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $link_id
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $product_link = ProductLink::withoutGlobalScopes()
            ->with('store')
            ->find($this->link_id);

        match (true) {
            str_contains($product_link->store->name, 'Aliexpress') => new Aliexpress($product_link),
            str_contains($product_link->store->name, 'Amazon') => new Amazon($product_link),
            str_contains($product_link->store->name, 'Currys') => new Currys($product_link),
            str_contains($product_link->store->name, 'Canadian Tire') => new Canadiantire($product_link),
            str_contains($product_link->store->name, 'DIY') => new Diy($product_link),
            str_contains($product_link->store->name, 'Ebay') => new Ebay($product_link),
            str_contains($product_link->store->name, 'Eprice') => new Eprice($product_link),
            str_contains($product_link->store->name, 'Emaxme') => new Emaxme($product_link),
            str_contains($product_link->store->name, 'Fnac') => new Fnac($product_link),
            str_contains($product_link->store->name, 'FlipKart') => new Flipkart($product_link),
            str_contains($product_link->store->name, 'Homedepot') => new Homedepot($product_link),
            str_contains($product_link->store->name, 'Media Market') => new Mediamarkt($product_link),
            str_contains($product_link->store->name, 'Microless') => new Microless($product_link),
            str_contains($product_link->store->name, 'Myntra') => new Myntra($product_link),
            str_contains($product_link->store->name, 'Next Hardware') => new Nexths($product_link),
            str_contains($product_link->store->name, 'Newegg') => new Newegg($product_link),
            str_contains($product_link->store->name, 'Noon') => new Noon($product_link),
            str_contains($product_link->store->name, 'Nykaa') => new Nykaa($product_link),
            str_contains($product_link->store->name, 'Otaku ME') => new Otakume($product_link),
            str_contains($product_link->store->name, 'Princess Auto') => new Princessauto($product_link),
            str_contains($product_link->store->name, 'Target') => new Target($product_link),
            str_contains($product_link->store->name, 'Tata Cliq') => new Tatacliq($product_link),
            str_contains($product_link->store->name, 'Walmart') => new Walmart($product_link),
            default => new CustomStoreTemplate($product_link),
        };
    }
}

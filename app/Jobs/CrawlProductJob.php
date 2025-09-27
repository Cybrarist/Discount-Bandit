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
use App\Models\Link;
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
        $link = Link::withoutGlobalScopes()
            ->with('store')
            ->find($this->link_id);

        //todo remove some stores and make them custom
        match (true) {
            str_contains($link->store->name, 'Aliexpress') => new Aliexpress($link),
            str_contains($link->store->name, 'Amazon') => new Amazon($link),
            str_contains($link->store->name, 'Currys') => new Currys($link),
            str_contains($link->store->name, 'Canadian Tire') => new Canadiantire($link),
            str_contains($link->store->name, 'DIY') => new Diy($link),
            str_contains($link->store->name, 'Ebay') => new Ebay($link),
            str_contains($link->store->name, 'Eprice') => new Eprice($link),
            str_contains($link->store->name, 'Emaxme') => new Emaxme($link),
            str_contains($link->store->name, 'Fnac') => new Fnac($link),
            str_contains($link->store->name, 'FlipKart') => new Flipkart($link),
            str_contains($link->store->name, 'Homedepot') => new Homedepot($link),
            str_contains($link->store->name, 'Media Market') => new Mediamarkt($link),
            str_contains($link->store->name, 'Microless') => new Microless($link),
            str_contains($link->store->name, 'Myntra') => new Myntra($link),
            str_contains($link->store->name, 'Next Hardware') => new Nexths($link),
            str_contains($link->store->name, 'Newegg') => new Newegg($link),
            str_contains($link->store->name, 'Noon') => new Noon($link),
            str_contains($link->store->name, 'Nykaa') => new Nykaa($link),
            str_contains($link->store->name, 'Otaku ME') => new Otakume($link),
            str_contains($link->store->name, 'Princess Auto') => new Princessauto($link),
            str_contains($link->store->name, 'Target') => new Target($link),
            str_contains($link->store->name, 'Tata Cliq') => new Tatacliq($link),
            str_contains($link->store->name, 'Walmart') => new Walmart($link),
            default => new CustomStoreTemplate($link),
        };
    }
}

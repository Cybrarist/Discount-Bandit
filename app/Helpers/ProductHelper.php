<?php

namespace App\Helpers;

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
use Illuminate\Support\Str;

class ProductHelper
{

    public static function get_url(ProductLink $product_link): string
    {

        $store_name = Str::of($product_link->store->name);

        return match (true) {
            str_contains($store_name, 'Aliexpress') =>  Aliexpress::prepare_url($product_link),
            str_contains($store_name, 'Amazon') =>  Amazon::prepare_url($product_link),
            str_contains($store_name, 'Currys') =>  Currys::prepare_url($product_link),
            str_contains($store_name, 'Canadian Tire') =>  Canadiantire::prepare_url($product_link),
            str_contains($store_name, 'DIY') =>  Diy::prepare_url($product_link),
            str_contains($store_name, 'Ebay') =>  Ebay::prepare_url($product_link),
            str_contains($store_name, 'Eprice') =>  Eprice::prepare_url($product_link),
            str_contains($store_name, 'Emaxme') =>  Emaxme::prepare_url($product_link),
            str_contains($store_name, 'Fnac') =>  Fnac::prepare_url($product_link),
            str_contains($store_name, 'FlipKart') =>  Flipkart::prepare_url($product_link),
            str_contains($store_name, 'Homedepot') =>  Homedepot::prepare_url($product_link),
            str_contains($store_name, 'Media Market') =>  Mediamarkt::prepare_url($product_link),
            str_contains($store_name, 'Microless') =>  Microless::prepare_url($product_link),
            str_contains($store_name, 'Myntra') =>  Myntra::prepare_url($product_link),
            str_contains($store_name, 'Next Hardware') =>  Nexths::prepare_url($product_link),
            str_contains($store_name, 'egg') =>  Newegg::prepare_url($product_link),
            str_contains($store_name, 'Noon') =>  Noon::prepare_url($product_link),
            str_contains($store_name, 'Nykaa') =>  Nykaa::prepare_url($product_link),
            str_contains($store_name, 'Otaku ME') =>  Otakume::prepare_url($product_link),
            str_contains($store_name, 'Princess Auto') =>  Princessauto::prepare_url($product_link),
            str_contains($store_name, 'Target') =>  Target::prepare_url($product_link),
            str_contains($store_name, 'Tata Cliq') =>  Tatacliq::prepare_url($product_link),
            str_contains($store_name, 'Walmart') =>  Walmart::prepare_url($product_link),

            default => $product_link->key
        };
    }

}

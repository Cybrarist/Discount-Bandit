<?php

namespace App\Helpers;

use App\Classes\CustomStoreTemplate;
use App\Classes\Stores\Ajio;
use App\Classes\Stores\Aliexpress;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\BestBuy;
use App\Classes\Stores\Canadiantire;
use App\Classes\Stores\Costco;
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
use Illuminate\Support\Str;

class LinkHelper
{
    public static function get_url(Link $link): string
    {

        $store_name = Str::of($link->store->name);

        return match (true) {
            str_contains($store_name, 'Aliexpress') => Aliexpress::prepare_url($link),
            str_contains($store_name, 'Ajio') => Ajio::prepare_url($link),
            str_contains($store_name, 'Amazon') => Amazon::prepare_url($link),
            str_contains($store_name, 'Best Buy') => BestBuy::prepare_url($link),
            str_contains($store_name, 'Canadian Tire') => Canadiantire::prepare_url($link),
            str_contains($store_name, 'Costco') => Costco::prepare_url($link),
            str_contains($store_name, 'Currys') => Currys::prepare_url($link),
            str_contains($store_name, 'DIY') => Diy::prepare_url($link),
            str_contains($store_name, 'Ebay') => Ebay::prepare_url($link),
            str_contains($store_name, 'Emaxme') => Emaxme::prepare_url($link),
            str_contains($store_name, 'Eprice') => Eprice::prepare_url($link),
            str_contains($store_name, 'FlipKart') => Flipkart::prepare_url($link),
            str_contains($store_name, 'Fnac') => Fnac::prepare_url($link),
            str_contains($store_name, 'Homedepot') => Homedepot::prepare_url($link),
            str_contains($store_name, 'Media Market') => Mediamarkt::prepare_url($link),
            str_contains($store_name, 'Microless') => Microless::prepare_url($link),
            str_contains($store_name, 'Myntra') => Myntra::prepare_url($link),
            str_contains($store_name, 'Newegg') => Newegg::prepare_url($link),
            str_contains($store_name, 'Next Hardware') => Nexths::prepare_url($link),
            str_contains($store_name, 'Noon') => Noon::prepare_url($link),
            str_contains($store_name, 'Nykaa') => Nykaa::prepare_url($link),
            str_contains($store_name, 'Otaku ME') => Otakume::prepare_url($link),
            str_contains($store_name, 'Princess Auto') => Princessauto::prepare_url($link),
            str_contains($store_name, 'Target') => Target::prepare_url($link),
            str_contains($store_name, 'Tata Cliq') => Tatacliq::prepare_url($link),
            str_contains($store_name, 'Walmart') => Walmart::prepare_url($link),
            default => CustomStoreTemplate::prepare_url($link),
        };
    }

    public static function prepare_base_key_and_params(Link $link): array
    {
        [$link_base_key, $params] = array_pad(explode('?', $link->key), 2, null);

        // check if the key has params
        if (! str_contains($link->key, '?') || ! $link->store->are_params_allowed)
            return [$link_base_key, ''];

        if (filled($link->store->allowed_params)) {
            parse_str($params, $param_value_pairs);

            $key_value_pairs = array_intersect_key($param_value_pairs, array_flip($link->store->allowed_params));

            return [$link_base_key, http_build_query($key_value_pairs)];
        }

        return [$link_base_key, $params];
    }
}

<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Currency;
use App\Models\Link;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Aliexpress extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/item/[product_key].html?[ref]";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        return Str::replace(
            ["[domain]", "[product_key]", "[ref]"],
            [$link->store->domain, $link_base, $link->store->referral],

            self::MAIN_URL)."&{$link_params}";
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        $ids_and_tag_selector = [
            'title',
            'h1[data-pl="product-title"]',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        if (isset($this->schema['image'])) {
            $this->product_data['image'] = $this->schema['image'][0];
        }
    }

    public function get_total_reviews(): void
    {

        if (isset($this->schema['aggregateRating']['reviewCount'])) {
            $this->product_data['total_reviews'] = $this->schema['aggregateRating']['reviewCount'];

            return;
        }

        $ids_and_tag_selector = [
            "a[class^='reviewer--reviews']",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results_using_ids[0]?->textContent);
    }

    public function get_rating(): void
    {

        if (isset($this->schema['aggregateRating']['ratingValue'])) {
            $this->product_data['rating'] = $this->schema['aggregateRating']['ratingValue'];

            return;
        }

        $general_selectors = [
            "a[class^='reviewer--rating'] strong",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['rating'] = Str::trim($results[0]?->textContent);
    }

    public function get_seller(): void
    {

        $general_selectors = [
            "span[class^='store-detail--storeName']",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['seller'] = Str::trim($results[0]?->textContent);
        $this->product_data['is_official'] = false;
    }

    public function get_price(): void
    {

        if (isset($this->schema['offers'])) {
            $this->product_data['price'] = (float) $this->schema['offers']['price'];
            $currency_detected = $this->schema['offers']['priceCurrency'];
        } else {

            $selector = [
                "span[class^='price-default--current']",
            ];
            $results = $this->dom->querySelectorAll(implode(',', $selector));

            $currency_detected = GeneralHelper::get_letters_only($results[0]?->textContent);

            $this->product_data['price'] = GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

        }

        $currency = Currency::firstWhere('code', $currency_detected);

        if ($currency?->rate) {
            Log::warning("Didn't convert Aliexpress price because currency rate is not set");
            $this->product_data['price'] = $this->product_data['price'] / $currency->rate;
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['offers'])) {
            $this->product_data['is_in_stock'] = $this->schema['offers']['availability'] == 'https://schema.org/InStock';

            return;
        }

        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {
        $this->schema = $this->schema['product'][0];
    }
}

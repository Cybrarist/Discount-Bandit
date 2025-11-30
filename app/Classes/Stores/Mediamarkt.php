<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Mediamarkt extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/[language]/product/-[product_key].html";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;

        $this->chromium_options['timeout_ms'] = 5000;
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        $language = Str::of($link->store->domain)
            ->explode(".")
            ->last();

        return Str::replace(
            ["[domain]", "[product_key]", "[language]"],
            [$link->store->domain, $link_base, $language],

            self::MAIN_URL)."?{$link_params}";
    }

    public function get_name(): void
    {
        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        $ids_and_tag_selector = [
            'title',
            'div[data-test="mms-select-details-header"] > h1',

        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {

        if (isset($this->schema['image'])) {
            $this->product_data['image'] = $this->schema['image'];

            return;
        }

        $ids_and_tag_selector = [
            "meta[property='og:image']",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
            'content',
        ];

        foreach ($attributes as $attribute) {
            $image_url = $results_using_ids[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->product_data['image'] = $image_url;
                break;
            }
        }

    }

    public function get_total_reviews(): void
    {
        if (isset($this->schema['aggregateRating']['ratingCount'])) {
            $this->product_data['total_reviews'] = $this->schema['aggregateRating']['ratingCount'];

        }
    }

    public function get_rating(): void
    {
        if (isset($this->schema['aggregateRating']['ratingValue'])) {
            $this->product_data['rating'] = $this->schema['aggregateRating']['ratingValue'];
        }
    }

    public function get_seller(): void
    {
        $this->product_data['seller'] = "Mediamarkt";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {

        if (isset($this->schema['offers']['price'])) {
            $this->product_data['price'] = $this->schema['offers']['price'];

            return;
        }

        $whole_price_selectors = [
            "[data-test='branded-price-whole-value']",
        ];
        $fractional_price_selectors = [
            "[data-test='branded-price-decimal-value']",
        ];

        $results_whole = $this->dom->querySelectorAll(implode(',', $whole_price_selectors));
        $results_fraction = $this->dom->querySelectorAll(implode(',', $fractional_price_selectors));

        $only_whole = GeneralHelper::get_numbers_only($results_whole[0]?->textContent);
        $this->product_data['price'] = (float) "{$only_whole}.{$results_fraction[0]?->textContent}";

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['offers']['availability'])) {
            $this->product_data['is_in_stock'] = $this->schema['offers']['availability'] == 'https://schema.org/InStock';

            return;
        }

        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void
    {

        if (isset($this->schema['offers']['shippingDetails']['shippingRate']['price'])) {
            $this->product_data['shipping_price'] = $this->schema['offers']['shippingDetails']['shippingRate']['price'];
        }

    }

    public function get_condition(): void
    {
        if (isset($this->schema['offers']['itemCondition'])) {
            $this->product_data['condition'] = Str::contains($this->schema['offers']['itemCondition'], 'NewCondition', true) ? 'New' : 'Used';
        }

    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {
        if (isset($this->schema['product']['object'])) {
            $this->schema = $this->schema['product']['object'];
        }
    }
}

<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Currys extends StoreTemplate
{
    const string MAIN_URL = "https://[domain]/products/[product_key].html";

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
            'h1.product-name',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = Str::trim($results_using_ids[0]?->textContent);

    }

    public function get_image(): void
    {

        if (isset($this->schema['image'][0])) {
            $this->product_data['image'] = $this->schema['image'][0];

            return;
        }

        $ids_and_tag_selector = [
            "meta[property='og:image']",
            "div.carouselitem.active img",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
            'content',
            'data-old-hires',
        ];

        foreach ($attributes as $attribute) {
            $image_url = $results_using_ids[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->product_data['image'] = $image_url;
                break;
            }
        }

    }

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {

        $this->product_data['seller'] = "Currys";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        if (isset($this->schema['offers']['price'])) {
            $this->product_data['price'] = $this->schema['offers']['price'];

            return;
        }

        $selectors = [
            'div.price span.value',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $selectors));
        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

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

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        if (isset($this->schema['offers']['itemCondition'])) {
            $this->product_data['condition'] = $this->schema['offers']['itemCondition'] == 'https://schema.org/NewCondition' ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information()
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }
    }
}

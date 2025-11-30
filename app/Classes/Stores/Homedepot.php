<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Homedepot extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/p/[product_key]";

    const string CANADA_URL = "https://www.[domain]/product/[random]/[product_key]";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;
        $this->chromium_options['timeout_ms'] = 5000;
        $this->chromium_options['page'] = "";
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        $url_to_use = match ($link->store->domain) {
            'homedepot.com' => self::MAIN_URL,
            'homedepot.ca' => self::CANADA_URL
        };

        return Str::replace(
            ["[domain]", "[product_key]", "[random]"],
            [$link->store->domain, $link_base, Str::random(10)],

            $url_to_use)."?{$link_params}";
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        //        $ids_and_tag_selector = [
        //            'title',
        //            'h1.product-name',
        //        ];
        //        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        //        $this->product_data['name'] = Str::trim($results_using_ids[0]?->textContent);

    }

    public function get_image(): void
    {

        if (isset($this->schema['image'][0])) {
            $this->product_data['image'] = $this->schema['image'][0];

            return;
        }
        //
        //        $ids_and_tag_selector = [
        //            "meta[property='og:image']",
        //            "div.carouselitem.active img",
        //        ];
        //        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        //
        //        $attributes = [
        //            'src',
        //            'content',
        //            'data-old-hires',
        //        ];
        //
        //        foreach ($attributes as $attribute) {
        //            $image_url = $results_using_ids[0]?->getAttribute($attribute);
        //            if (! empty($image_url)) {
        //                $this->product_data['image'] = $image_url;
        //                break;
        //            }
        //        }

    }

    public function get_total_reviews(): void
    {
        if (isset($this->schema['aggregateRating']['reviewCount'])) {
            $this->product_data['total_reviews'] = $this->schema['aggregateRating']['reviewCount'];
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

        $this->product_data['seller'] = "Home Depot";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        if (isset($this->schema['offers']['price'])) {
            $this->product_data['price'] = $this->schema['offers']['price'];
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {

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

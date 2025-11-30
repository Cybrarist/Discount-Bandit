<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Walmart extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/ip/[product_key]";

    private array $current_variant = [];

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

            self::MAIN_URL) . "?{$link_params}";
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        //        $ids_and_tag_selector = [
        //            'title',
        //            'h1#main-title'
        //        ];
        //        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        //        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        //        if (isset($this->current_variant['image'])) {
        //            $this->product_data['image'] = $this->current_variant['image'];
        //            return;
        //        }

        $ids_and_tag_selector = [
            //            "meta[property='og:image']",
            "div[data-seo-id='hero-carousel-image'] img",
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
        if (isset($this->schema['aggregateRating']['reviewCount'])) {
            $this->product_data['total_reviews'] = $this->schema['aggregateRating']['reviewCount'];

            return;
        }

        $ids_and_tag_selector = [
            "a[itemprop='ratingCount']",
            "a[data-testid='item-review-section-link']",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results_using_ids[0]?->textContent);
    }

    public function get_rating(): void
    {
        if (isset($this->schema['aggregateRating']['ratingValue'])) {
            $this->product_data['rating'] = $this->schema['aggregateRating']['ratingValue'];
        }

    }

    public function get_seller(): void
    {
        $this->product_data['seller'] = "Walmart";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        $selectors = [
            "span[itemprop='price']",
            "span[data-seo-id='hero-price']",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->current_variant['availability'])) {
            $this->product_data['is_in_stock'] = $this->current_variant['availability'] == 'https://schema.org/InStock';

            return;
        }
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        if (isset($this->current_variant['offers'][0]['itemCondition'])) {
            $this->product_data['condition'] = Str::contains($this->current_variant['offers'][0]['itemCondition'], 'NewCondition', true) ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {

        if (isset($this->schema['product_group'])) {
            $this->schema = $this->schema['product_group'][0];
            foreach ($this->schema['hasVariant'] as $variant) {
                if (isset($variant['sku']) && $variant['sku'] == $this->link->key) {
                    $this->current_variant = $variant;
                    break;
                }
            }
        } elseif (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
            $this->current_variant = $this->schema;
        }

    }
}

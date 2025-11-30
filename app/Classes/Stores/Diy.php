<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Diy extends StoreTemplate
{
    const MAIN_URL = "https://[domain]/departments/random/[product_key]_BQ.prd";

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
        $ids_and_tag_selector = [
            "h1[data-testid='product-name']",
            'title',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            '#product-image-gallery img',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
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
        $ids_and_tag_selector = [
            'p[data-testid="reviews-count"]',
        ];

        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results_using_ids[0]?->textContent);
    }

    // todo implement rating
    public function get_rating(): void
    {
        $this->product_data['rating'] = 5;
    }

    public function get_seller(): void
    {
        $general_selectors = [
            "div[data-testid='seller']",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['seller'] = Str::of($results[0]?->querySelector('a')?->textContent ?? $results[0]?->textContent)
            ->trim()
            ->explode('shipped by ')
            ->last();

        $this->product_data['is_official'] = str_contains($this->product_data['seller'], 'B&Q');
    }

    public function get_price(): void
    {
        $selectors = [
            "span[data-testid='product-price']",
            "span[data-testid='primary-price']",

        ];
        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

    }

    // todo needs example to implement
    public function get_used_price(): void
    {
        $general_selectors = [
            '#dynamic-aod-ingress-box #aod-ingress-link',
        ];
    }

    // todo needs example if price is there but not stock
    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    // todo needs example with shipping price as there's no shipping price on the page
    public function get_shipping_price(): void
    {
        $this->product_data['shipping_price'] = 0;
    }

    // todo needs example
    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void
    {
        // TODO: Implement other_method_if_system_detected_as_bot() method.
    }
}

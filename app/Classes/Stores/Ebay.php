<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Ebay extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/itm/[product_key]?[ref]";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        $template_url = self::MAIN_URL;

        return Str::replace(
            ["[domain]", "[product_key]", "[ref]"],
            [$link->store->domain, $link_base, $link->store->referral],

            $template_url)."&{$link_params}";
    }

    public function get_name(): void
    {
        $ids_and_tag_selector = [
            'title',
            'h1.x-item-title__mainTitle',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent ?? $this->schema['product']['name'];

    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            'div.ux-image-carousel-container.image-container img',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
            'data-old-hires',
        ];

        foreach ($attributes as $attribute) {
            $image_url = $results_using_ids[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->product_data['image'] = $image_url;
                break;
            }
        }

        if (! $this->product_data['image']) {
            $this->product_data['image'] = $this->schema['product']['image'][0];
        }

    }

    public function get_total_reviews(): void
    {
        $this->product_data['total_reviews'] = 0;
    }

    public function get_rating(): void
    {
        $this->product_data['rating'] = 0;
    }

    public function get_seller(): void
    {
        $general_selectors = [
            "div.x-sellercard-atf__info__about-seller > a > span",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['seller'] = Str::trim($results[0]?->textContent);
        $this->product_data['is_official'] = false;
    }

    public function get_price(): void
    {
        $selectors = [
            "div [data-testid='x-price-primary']",
        ];

        if (isset($this->schema['product']['offers']['price'])) {
            $this->product_data['price'] = (float) $this->schema['product']['offers']['price'];

            return;
        }

        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {

        if (isset($this->schema['product']['offers']['availability'])) {
            $this->product_data['is_in_stock'] = Str::contains($this->schema['product']['offers']['availability'], "InStock", true);

            return;
        }

        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void
    {
        if (isset($this->schema['product']['offers']['shippingDetails']['shippingRate']['value'])) {
            $this->product_data['shipping_price'] = (float) $this->schema['product']['offers']['shippingDetails']['shippingRate']['value'];
        }
    }

    public function get_condition(): void
    {
        if (isset($this->schema['product']['offers']['itemCondition'])) {
            $this->product_data['condition'] = Str::contains($this->schema['product']['offers']['itemCondition'], "NewCondition", true) ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}
}

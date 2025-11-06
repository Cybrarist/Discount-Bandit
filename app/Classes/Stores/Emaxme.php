<?php

namespace App\Classes\Stores;

use App\Classes\Crawler\SimpleCrawler;
use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Models\Link;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class Emaxme extends StoreTemplate
{
    const string MAIN_URL = "https://uae.[domain]/[product_key].html";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {

        $this->chromium_crawler = true;
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        return Str::replace(
            ["[domain]", "[product_key]"],
            [$link->store->domain, $link->key],
            self::MAIN_URL);
    }

    public function get_name(): void
    {
        $ids_and_tag_selector = [
            'title',
            'h1.MuiTypography-body1',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            "div.slick-active img",
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

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {
        $this->product_data['seller'] = 'Emax';
    }

    public function get_price(): void
    {
        $selectors = [
            'div#details-price',
        ];

        $results_whole = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results_whole[0]?->textContent);

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void
    {
    }

    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void
    {
    }
}

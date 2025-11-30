<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Otakume extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/products/[product_key]";

    protected array $extra_headers = [
        "Cache-Control" => "no-cache",
        "Accept-Language" => "en-US,en;q=0.5",
        'Connection' => 'keep-alive',
    ];

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->extra_headers = $extra_headers + $this->extra_headers;
        $this->user_agent = ($user_agent) ?: UserAgentHelper::get_random_user_agent();

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
            'title',
            'h1.producttitle',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            'img.main-product-image',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
        ];

        foreach ($attributes as $attribute) {
            $image_url = $results_using_ids[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->product_data['image'] = "https:{$image_url}";
                break;
            }
        }

    }

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {

        $this->product_data['seller'] = "Otaku ME";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        $selectors = [
            "div.price-section",
        ];

        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        $selectors = [
            "div.restock-rocket-button-cover",
        ];
        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['is_in_stock'] = ! isset($results[0]);
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void {}

    public function before_crawl(): void
    {
        $this->chromium_crawler = $this->link->notification_settings()
            ->withoutGlobalScopes()
            ->where('is_in_stock', true)
            ->exists();
    }
}

<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Nexths extends StoreTemplate
{
    const string MAIN_URL = "https://[domain]/Products/details/sku/[product_key]";

    protected array $extra_headers = [
        'Accept' => 'application/json',
        'DNT' => 1,
        "Cache-Control" => "no-cache",
        'Sec-Fetch-User' => '1',
        "Accept-Language" => "en-US,en;q=0.5",
        'Connection' => 'keep-alive',
        "Accept-Encoding" => "gzip, deflate",
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
            ["[domain]", "[product_key]"],
            [$link->store->domain, $link_base],
            self::MAIN_URL)."?{$link_params}";
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];
        }

    }

    public function get_image(): void
    {
        if (isset($this->schema['image'])) {
            $this->product_data['image'] = $this->schema['image'];
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
        if (isset($this->schema['seller'])) {
            $this->product_data['seller'] = $this->schema['seller'];
            $this->product_data['is_official'] = Str::contains($this->schema['seller'], 'nexths', true);
        }
    }

    public function get_price(): void
    {
        if (isset($this->schema['price'])) {
            $this->product_data['price'] = $this->schema['price'];
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['availability'])) {
            $this->product_data['is_in_stock'] = Str::contains($this->schema['availability'], 'InStock', true);
        }

    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void {}

    public function is_system_detected_as_robot(): bool
    {
        return false;
    }

    public function prepare_dom_for_getting_product_information(): void
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }
    }
}

<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Princessauto extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/en/product/[product_key]";

    protected array $extra_headers = [
        'Accept' => 'application/json',
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

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {
        $this->product_data['seller'] = "Princess Auto";
        $this->product_data['is_official'] = true;

    }

    public function get_price(): void
    {
        if (isset($this->schema['offers'][0]['price'])) {
            $this->product_data['price'] = $this->schema['offers'][0]['price'];
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['offers'][0]['availability'])) {
            $this->product_data['is_in_stock'] = $this->schema['offers'][0]['availability'] == 'https://schema.org/InStock';

            return;
        }
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        if (isset($this->schema['offers'][0]['itemCondition'])) {
            $this->product_data['condition'] = $this->schema['offers'][0]['itemCondition'] == 'https://schema.org/NewCondition' ? 'New' : 'Used';
        }

    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }

    }
}

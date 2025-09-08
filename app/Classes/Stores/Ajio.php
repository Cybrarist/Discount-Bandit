<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Models\ProductStore;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Ajio extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/p/[product_key]";

    protected array $extra_headers = [
        'Accept' => '*/*',
        'DNT' => 1,
        'Sec-Fetch-User' => 1,
        'Connection' => 'closed',
        "Accept-Encoding" => "gzip, deflate",
    ];

    public function __construct(ProductStore $product_store, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->user_agent = Arr::random([
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.",
            "Mozilla/5.0 (X11; Linux i686; rv:130.0) Gecko/20100101 Firefox/130.0",
        ]);
        $this->extra_headers = $extra_headers + $this->extra_headers;
        $this->user_agent = ($user_agent) ?: $this->user_agent;

        parent::__construct($product_store);
    }

    public static function prepare_url(string $domain, string $product_key, array $extra = []): string
    {
        $template_url = self::MAIN_URL;

        return Str::replace(
            ["[domain]", "[product_key]"],
            [$domain, $product_key],
            $template_url);
    }

    public function get_name(): void
    {
        $this->product_data['name'] = $this->schema['product_group']['name'] ?? "NA";
    }

    public function get_image(): void
    {
        $this->product_data['image'] = $this->schema['product_group']['image'] ?? "";
    }

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {
        $this->product_data['seller'] = $this->product_store->store->name;
    }

    public function get_price(): void
    {
        $this->product_data['price'] = (float) $this->schema['product_group']['offers']['price'];

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = Str::contains($this->schema['product_group']['offers']['availability'], "InStock", true);
    }

    public function get_shipping_price(): void
    {
        $this->product_data['shipping_price'] = 0;
    }

    public function get_condition(): void
    {

        $this->product_data['condition'] = Str::contains($this->schema['product_group']['offers']['itemCondition'], "NewCondition", true);
    }

    public function other_method_if_system_detected_as_bot(): void {}
}

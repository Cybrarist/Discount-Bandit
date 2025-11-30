<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Currency;
use App\Models\Link;
use Illuminate\Support\Str;

class Microless extends StoreTemplate
{
    const string MAIN_URL = "https://[subdomain][domain]/product/[product_key]";

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

        $subdomain = match ($link->store->name) {
            "Microless UAE" => "uae.",
            default => ""
        };

        return Str::replace(
            ["[domain]", "[product_key]", "[subdomain]"],
            [$link->store->domain, $link_base, $subdomain],

            self::MAIN_URL)."?{$link_params}";
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        return;

        $ids_and_tag_selector = [
            '#productname',
            '#productTitle',
            '#aod-asin-title-text',
            'title',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {

        if (isset($this->schema['image'])) {
            $this->product_data['image'] = $this->schema['image'];

            return;
        }

        return;

        $ids_and_tag_selector = [
            'img#landingImage',
            '#aod-asin-image-id',
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

    }

    public function get_total_reviews(): void {}

    public function get_rating(): void {}

    public function get_seller(): void
    {

        $this->product_data['seller'] = "Microless";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        if (isset($this->schema['offers'][0]['price'])) {
            $this->product_data['price'] = $this->schema['offers'][0]['price'];

            if ($this->link->store->name == "Microless Global" && $this->schema['offers'][0]['priceCurrency'] != "USD") {
                $this->product_data['price'] = $this->product_data['price'] / Currency::firstWhere('code', $this->schema['offers'][0]['priceCurrency'])->rate;
            }
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['offers']['availability'])) {
            $this->product_data['is_in_stock'] = Str::contains($this->schema['offers']['availability'], 'InStock', true);

            return;
        }

        return;

        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        if (isset($this->schema['offers']['itemCondition'])) {
            $this->product_data['condition'] = Str::contains($this->schema['offers'][0]['availability'], 'InStock', true) ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function is_system_detected_as_robot(): bool
    {
        $selectors = [
            'input#captchacharacters',
            'label[for="captchacharacters"]',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        return count($results);
    }

    public function prepare_dom_for_getting_product_information(): void
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }
    }
}

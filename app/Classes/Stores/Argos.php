<?php

namespace App\Classes\Stores;

use App\Classes\Crawler\SimpleCrawler;
use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class Argos extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/product/[product_key]";

    private array $json_data = [];

    protected array $extra_headers = [
        "Accept-Encoding" => "gzip, deflate, br, zstd",
    ];

    protected string $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25  Version/5.0.4 Safari/533.20.27';

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->extra_headers = $extra_headers + $this->extra_headers;
        $this->user_agent = ($user_agent) ?: $this->user_agent;

        parent::__construct($link);
    }

    public static function prepare_url(Link $link, array $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        return Str::replace(
            ["[domain]", "[product_key]"],
            [$link->store->domain, $link->key],
            self::MAIN_URL)."?{$link_params}";

    }

    public function get_name(): void
    {
        $ids_and_tag_selector = [
            'title',
            '[data-test="product-title"]',
        ];
        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = Str::of($results[0]?->textContent)
            ->remove([
                'Buy',
                '|',
                'Argos',
            ], caseSensitive: false)
            ->trim()
            ->toString();

        if (! $this->product_data['name']) {
            $this->product_data['name'] = $this->json_data['productName'] ?? "NA";
        }
    }

    public function get_image(): void
    {

        $results = $this->dom->querySelectorAll('[data-test="component-media-gallery"] img');

        $this->product_data['image'] = "https:".$results[0]?->getAttribute('src');

        if (! $this->product_data['image']) {
            $this->product_data['image'] = $this->json_data["media"]["images"][0] ?? "";
        }

    }

    public function get_total_reviews(): void
    {
        $results = $this->dom->querySelector('[itemprop="ratingCount"]');

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only($results?->textContent);

        if (! $this->product_data['total_reviews']) {
            $this->product_data['total_reviews'] = $this->json_data["ratingSummary"]["attributes"]["reviewCount"] ?? "";
        }
    }

    public function get_rating(): void
    {

        $results = $this->dom->querySelector('[itemprop="ratingValue"]');

        $this->product_data['rating'] = Str::trim($results?->textContent);

        if (! $this->product_data['rating']) {
            $this->product_data['rating'] = round((float) $this->json_data["ratingSummary"]["attributes"]["avgRating"] ?? 0, 1);
        }
    }

    public function get_seller(): void
    {
        $this->product_data['seller'] = "Argos";
    }

    public function get_price(): void
    {
        $results = $this->dom->querySelector('[itemprop="price"]');

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot($results?->textContent);

        if (! $this->product_data['price']) {
            $this->product_data['price'] = (float) $this->json_data["prices"]["attributes"]["now"];
        }

    }

    public function get_used_price(): void
    {
        $this->product_data['used_price'] = 0;
    }

    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;

        if (! $this->product_data['is_in_stock']) {
            $this->product_data['is_in_stock'] = $this->json_data["attributes"]["deliverable"];
        }

    }

    public function get_shipping_price(): void
    {
        $this->product_data['shipping_price'] = $this->json_data["attributes"]["deliveryPrice"] ?? 0;

    }

    public function get_condition(): void
    {
        $result = $this->dom->querySelector('[itemprop="itemCondition"]');
        $this->product_data['condition'] = $result?->getAttribute('content');

    }

    public function other_method_if_system_detected_as_bot(): void
    {
        $general_selectors = [
            'input#captchacharacters',
            'label[for="captchacharacters"]',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        if (! $results[0]?->textContent) {
            return;
        }

        warning("system detected as bot, trying other method");

        $other_buying_url = static::prepare_url(
            $this->link,
        );

        $this->dom = new SimpleCrawler(
            url: $other_buying_url,
            extra_headers: [
                'Content-Length' => 0,
            ],
            settings: [
                'method' => 'POST',
            ]

        )->dom;

    }

    public function prepare_dom_for_getting_product_information(): void
    {
        $script_with_data = $this->dom->querySelector('body script:nth-child(3)');

        $needed_data = explode('=', $script_with_data->textContent)[1];

        $needed_data = Str::replace('undefined', 'false', $needed_data);

        $this->json_data = json_decode($needed_data, true);

        $this->json_data = $this->json_data['productStore']['data'];
    }
}

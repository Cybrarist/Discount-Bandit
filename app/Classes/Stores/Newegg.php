<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Models\Currency;
use App\Models\Link;
use Illuminate\Support\Str;

class Newegg extends StoreTemplate
{
    const string GLOBAL_URL = "https://[domain]/global/[country]-en/p/[product_key]";

    const string SINGLE_STORE_URL = "https://[domain]/p/[product_key]";

    public static array $country_short = [
        'USA' => 'us',
        'Argentina' => 'ar',
        'Australia' => 'au',
        'Bahrain' => 'bh',
        'Canada' => 'ca',
        'Hong Kong' => 'hk',
        'Occupied Palestine' => 'il',
        'Japan' => 'jp',
        'Kuwait' => 'kw',
        'Mexico' => 'mx',
        'New Zealand' => 'nz',
        'Oman' => 'om',
        'Philippines' => 'ph',
        'Qatar' => 'qa',
        'Saudi Arabia' => 'sa',
        'Singapore' => 'sg',
        'South Korea' => 'kr',
        'Thailand' => 'th',
        'UAE' => 'ae',
        'UK' => 'uk',
    ];

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;
        parent::__construct($link);
    }

    public static function prepare_url(Link $link, $extra = []): string
    {
        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        $domain_to_use = match ($link->store->domain) {
            "newegg.ca" => self::SINGLE_STORE_URL,
            default => self::GLOBAL_URL,
        };
        $country_code = self::$country_short[Str::remove('Newegg ', $link->store->name)];

        return Str::replace(
            ["[domain]", "[country]", "[product_key]"],
            [$link->store->domain, $country_code, Str::upper($link_base)],
            $domain_to_use) ."?{$link_params}";

    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }
        $ids_and_tag_selector = [
            'title',
        ];

        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = Str::trim($results_using_ids[0]?->textContent);

    }

    public function get_image(): void
    {

        if (isset($this->schema['image'])) {
            $this->product_data['image'] = $this->schema['image'];

            return;
        }

        $ids_and_tag_selector = [
            "meta[property='og:image']",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
            'content',
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

    public function get_total_reviews(): void
    {

        if (isset($this->schema['aggregateRating']['reviewCount'])) {
            $this->product_data['total_reviews'] = $this->schema['aggregateRating']['reviewCount'];
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

        $this->product_data['seller'] = "Newegg";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        $this->product_data['price'] = $this->schema['offers']['price'];

        $currency_detected = $this->schema['offers']['priceCurrency'];
        $currency = Currency::firstWhere('code', $currency_detected);

        if ($currency && $currency->code != $this->link->store->currency->code) {
            $this->product_data['price'] = ($this->product_data['price'] / $currency->rate) * $this->link->store->currency->rate;
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['offers']['availability'])) {
            $this->product_data['is_in_stock'] = Str::contains($this->schema['offers']['availability'], 'InStock', true);
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        if (isset($this->schema['itemCondition'])) {
            $this->product_data['condition'] = Str::contains($this->schema['itemCondition'], 'NewCondition', true) ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information()
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }
    }
}

<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Models\Link;
use Illuminate\Support\Str;

class BestBuy extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/product/random-string-not-needed/[product_key]";

    const string CANADA_URL = "https://www.[domain]/en-ca/product/[product_key]";

    //    const string MAIN_URL = "https://www.bestbuy.com/pricing/v1/price/item?allFinanceOffers=true&catalog=bby&context=offer-list&effectivePlanPaidMemberType=NULL&includeOpenboxPrice=true&paidMemberSkuInCart=false&salesChannel=LargeView&skuId=[product_key]&useCabo=true&usePriceWithCart=true&visitorId=7e3432cd-6f63-11ef-97ca-12662d3c815b";

    //    const string MAIN_URL_NAME_AND_IMAGE = "https://www.[domain]/site/[product_key].p?skuId=[product_key]&intl=nosplash";

    //    const string CANADA_URL = "https://www.[domain]/api/offers/v1/products/[product_key]/offers";

    protected array $extra_headers = [
        'Accept' => 'application/json',
        "Cache-Control" => "no-cache",
        "Accept-Language" => "en-US,en;q=0.5",
        'Connection' => 'keep-alive',
    ];

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {

        $this->chromium_crawler = true;

        parent::__construct($link);
    }

    public static function prepare_url(Link $link, array $extra = []): string
    {

        //        if (array_key_exists('notify', $extra)) {
        //            return Str::replace(
        //                ["[domain]", "[product_key]"],
        //                [$domain, $product_key],
        //
        //                ($domain == "bestbuy.com") ? self::MAIN_URL_NAME_AND_IMAGE : self::CANADA_URL_NAME_AND_IMAGE);
        //        }

        return Str::replace(
            ["[domain]", "[product_key]"],
            [$link->store->domain, $link->key],
            ($link->store->domain == "bestbuy.com") ? self::MAIN_URL : self::CANADA_URL);
    }

    public function get_name(): void
    {

        if (isset($this->schema['name'])) {
            $this->product_data['name'] = $this->schema['name'];

            return;
        }

        $ids_and_tag_selector = [
            'title',
            'h1',
        ];
        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results[0]?->textContent;
    }

    public function get_image(): void
    {

        if (isset($this->schema['image'][0])) {
            $this->product_data['image'] = $this->schema['image'][0];

            return;
        }

        $ids_and_tag_selector = [
            '[class^="mediaGalleryGridArea_"] img:first-child',
            'link[as="image"]',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'data-src',
            'imagesrcset',
        ];

        foreach ($attributes as $attribute) {
            $image_url = $results[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->product_data['image'] = explode(';', $image_url)[0];
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
        if (isset($this->schema['offers'][0]['seller']['name'])) {
            $this->product_data['seller'] = $this->schema['offers'][0]['seller']['name'];

            return;
        }

        $this->product_data['seller'] = 'Best Buy';
    }

    public function get_price(): void
    {
        if (isset($this->schema['offers']['price'])) {
            $this->product_data['price'] = $this->schema['offers']['price'];

            return;
        }
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
            $this->product_data['condition'] = ($this->schema['offers'][0]['itemCondition'] == 'https://schema.org/NewCondition') ? 'New' : 'Used';
        }

    }

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {
        $this->schema = $this->schema['product'] ?? $this->schema;
    }
}

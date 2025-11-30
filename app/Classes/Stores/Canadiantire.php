<?php

namespace App\Classes\Stores;

use App\Classes\Crawler\SimpleCrawler;
use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class Canadiantire extends StoreTemplate
{
    // todo add referral
    // todo only name and image are done.
    const string MAIN_URL = "https://www.[domain]/en/pdp/pcode-[product_key].html";

    //    const API_URL = "https://apim.canadiantire.ca/v1/product/api/v1/product/productFamily/";

    public function __construct(Link $link, array $extra_headers = [], ?string $user_agent = '')
    {
        $this->chromium_crawler = true;

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
        $ids_and_tag_selector = [
            'h1.nl-product__title',
            'title',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            '.snapper img',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
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
        $ids_and_tag_selector = [
            '#acrCustomerReviewText',
            '#aod-asin-reviews-block',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results_using_ids[0]?->textContent);
    }

    public function get_rating(): void
    {
        $general_selectors = [
            '.reviewCountTextLinkedHistogram > span:first-child > a > span:first-child',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['rating'] = Str::trim($results[0]?->textContent);
    }

    public function get_seller(): void
    {
        $general_selectors = [
            '#merchantInfoFeature_feature_div  #sellerProfileTriggerId ',
            '#merchantInfoFeature_feature_div > div:nth-child(2) > span',
            '#shipsFromSoldByMessage_feature_div > span',
            '.a-expander-content a-expander-partial-collapse-content  .offer-display-feature-text',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['seller'] = Str::trim($results[0]?->textContent);
        $this->product_data['is_official'] = str_contains($this->product_data['seller'], 'Amazon');
    }

    public function get_price(): void
    {
        $whole_price_selectors = [
            '#sns-base-price span.a-price-whole',
            '#corePriceDisplay_desktop_feature_div span.a-price-whole',
            '#corePrice_feature_div span.a-price-whole',
        ];
        $fractional_price_selectors = [
            '#sns-base-price span.a-price-fraction',
            '#corePriceDisplay_desktop_feature_div span.a-price-fraction',
            '#corePrice_feature_div span.a-price-fraction',
        ];

        $results_whole = $this->dom->querySelectorAll(implode(',', $whole_price_selectors));
        $results_fraction = $this->dom->querySelectorAll(implode(',', $fractional_price_selectors));

        $only_whole = GeneralHelper::get_numbers_only($results_whole[0]?->textContent);
        $this->product_data['price'] = (float) "{$only_whole}.{$results_fraction[0]?->textContent}";

    }

    public function get_used_price(): void
    {
        $general_selectors = [
            '#dynamic-aod-ingress-box #aod-ingress-link',
        ];
    }

    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void
    {
        $general_selectors = [
            '#deliveryBlockMessage span[data-csa-c-delivery-price]',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['shipping_price'] = (float) GeneralHelper::get_numbers_only_with_dot($results[0]?->getAttribute('data-csa-c-delivery-price'));
    }

    public function get_condition(): void {}

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
            $this->link
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
}

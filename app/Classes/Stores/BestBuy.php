<?php

namespace App\Classes\Stores;

use App\Classes\Crawler\SimpleCrawler;
use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Link;
use Illuminate\Support\Str;

use function Laravel\Prompts\warning;

class BestBuy extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/product/[product_key]";

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

        //        if ($this->link->store->domain === "bestbuy.ca") {
        //            $this->chromium_crawler = true;
        //            $this->extra_headers = $extra_headers + ['X-CLIENT-ID' => 'lib-price-browser'] + $this->extra_headers;
        //        } else {
        $this->extra_headers = $extra_headers + $this->extra_headers;
        //        }

        $this->user_agent = ($user_agent) ?: UserAgentHelper::get_random_user_agent();

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

        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        return Str::replace(
            ["[domain]", "[product_key]"],
            [$link->store->domain, $link_base],
            ($link->store->domain == "bestbuy.com") ? self::MAIN_URL : self::CANADA_URL) ."?{$link_params}";
    }

    public function get_name(): void
    {
        $ids_and_tag_selector = [
            'title',
        ];
        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results[0]?->textContent;
    }

    public function get_image(): void
    {
        $ids_and_tag_selector = [
            '.pdp-media-gallery img:first-child',
        ];
        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
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
        $ids_and_tag_selector = [
            '#acrCustomerReviewText',
            '#aod-asin-reviews-block',
        ];
        $results = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results[0]?->textContent);
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
}

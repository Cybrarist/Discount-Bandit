<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\LinkHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Link;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Target extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/p/-/A-[product_key]";

    protected array $extra_headers = [
        'Accept' => '*/*',
        'DNT' => 1,
        'Sec-Fetch-User' => '1',
        'Connection' => 'closed',
        "Accept-Encoding" => "gzip, deflate",
        "Cache-Control" => "no-cache",
        "Accept-Language" => "en-US,en;q=0.5",
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
        $ids_and_tag_selector = [
            //            'title',
            'h1#pdp-product-title-id',
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = $results_using_ids[0]?->textContent;

    }

    public function get_image(): void
    {

        if (isset($this->schema['item']['enrichment']['images']['primary_image_url']))
            $this->product_data['image'] = $this->schema['item']['enrichment']['images']['primary_image_url'];

        $ids_and_tag_selector = [
            'img#landingImage',
            'meta[property="og:image"]',
        ];

        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $attributes = [
            'src',
            'content',
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
        $this->product_data['total_reviews'] = $this->schema['ratings_and_reviews']['statistics']['rating']['count'];
    }

    public function get_rating(): void
    {
        $this->product_data['rating'] = $this->schema['ratings_and_reviews']['statistics']['rating']['average'];
    }

    public function get_seller(): void
    {

        $this->product_data['seller'] = "Target";
        $this->product_data['is_official'] = true;
    }

    public function get_price(): void
    {
        $this->product_data['price'] = (float) $this->schema['price']['current_retail'];
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {}

    public function other_method_if_system_detected_as_bot(): void {}

    public function prepare_dom_for_getting_product_information(): void
    {
        try {
            $script_tags = $this->dom->querySelectorAll('script');

            foreach ($script_tags as $script) {
                if (Str::contains($script->textContent, '__TGT_DATA__')) {
                    $temp = Str::of($script->textContent)
                        ->replace('\\\\', '\\')
                        ->replace('\"', '"')
                        ->explode('__TGT_DATA__')[1];

                    $base = explode("'__WEB_CLUSTER__'", $temp)[0];
                    $base = explode('deepFreeze(JSON.parse("', $base)[1];
                    $base = Str::remove('")), writable: false },', $base);

                    $available_keys = json_decode($base, true)['__PRELOADED_QUERIES__']['queries'];

                    foreach ($available_keys as $value) {
                        if ($value[0][0] == "@web/domain-product/get-pdp-v1") {
                            $this->schema = $value[1]['data']['product'];
                        }
                    }
                    break;
                }
            }
        } catch (\Throwable $throwable) {
            Log::error("Couldn't get target product. please share this link with developer: {$this->current_product_url}");
            exit();
        }

    }
}

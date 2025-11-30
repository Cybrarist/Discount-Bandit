<?php

namespace App\Classes\Stores;

use App\Classes\StoreTemplate;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Support\Str;

class Fnac extends StoreTemplate
{
    const string MAIN_URL = "https://www.[domain]/[product_key]";

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
        if (isset($this->schema['product']['name'])) {
            $this->product_data['name'] = $this->schema['product']['name'];

            return;
        }

        $ids_and_tag_selector = [
            'title',
            'h1.f-productHeader__heading',
        ];

        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));
        $this->product_data['name'] = Str::trim($results_using_ids[0]?->textContent);

    }

    public function get_image(): void
    {
        if (isset($this->schema['product']['image'])) {
            $this->product_data['image'] = $this->schema['product']['image'][0];

            return;
        }

        $ids_and_tag_selector = [
            'img.f-productMedias__viewItem--main',
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
        if (isset($this->schema['product']['aggregateRating']['ratingCount'])) {
            $this->product_data['total_reviews'] = $this->schema['product']['aggregateRating']['ratingCount'];

            return;
        }

        $ids_and_tag_selector = [
            "span.customerReviewsRating__countTotal",
        ];
        $results_using_ids = $this->dom->querySelectorAll(implode(',', $ids_and_tag_selector));

        $this->product_data['total_reviews'] = GeneralHelper::get_numbers_only_with_dot($results_using_ids[0]?->textContent);
    }

    public function get_rating(): void
    {

        if (isset($this->schema['product']['aggregateRating']['ratingValue'])) {
            $this->product_data['rating'] = $this->schema['product']['aggregateRating']['ratingValue'];
        }

        $general_selectors = [
            'b.customerReviewsRating__score',
            'customerReviewsRating b',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        $this->product_data['rating'] = Str::of($results[0]?->textContent)
            ->trim()
            ->replace(',', '.')
            ->toString();
    }

    public function get_seller(): void
    {
        if (isset($this->schema['product']['offers'])) {
            $this->product_data['seller'] = $this->schema['product']['offers']['seller']['name'];
            $this->product_data['is_official'] = Str::contains($this->product_data['seller'], 'Fnac', true);
        }

    }

    public function get_price(): void
    {
        if (isset($this->schema['product']['offers'])) {
            $this->product_data['price'] = (float) $this->schema['product']['offers']['price'];

            return;
        }

        $selectors = [
            'span.f-faPriceBox__price',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $selectors));

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_only_with_dot(
            Str::of($results[0]?->textContent)
                ->replace(',', '.')
                ->toString()
        );

    }

    // todo needs example to implement
    public function get_used_price(): void {}

    public function get_stock(): void
    {
        if (isset($this->schema['product']['offers'])) {
            $this->product_data['is_in_stock'] = Str::contains($this->schema['product']['offers']['availability'], 'InStock', true);

            return;
        }
        $this->product_data['is_in_stock'] = $this->product_data['price'] > 0 || $this->product_data['used_price'] > 0;
    }

    public function get_shipping_price(): void
    {
        $general_selectors = [
            'span.f-deliveryInfo__price',
        ];

        $results = $this->dom->querySelectorAll(implode(',', $general_selectors));

        if (Str::contains($results[0]?->textContent, 'Gratuit', true)) {
            $this->product_data['shipping_price'] = 0;

            return;
        }

        $temp = Str::of($results[0]?->textContent)
            ->replace(',', '.')
            ->toString();

        $this->product_data['shipping_price'] = (float) GeneralHelper::get_numbers_only_with_dot($temp);
    }

    public function get_condition(): void
    {
        if (isset($this->schema['product']['offers'])) {
            $this->product_data['condition'] = Str::contains($this->schema['product']['offers']['itemCondition'], 'NewCondition', true) ? 'New' : 'Used';
        }
    }

    public function other_method_if_system_detected_as_bot(): void {}
}

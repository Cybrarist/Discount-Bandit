<?php

namespace App\Classes;

use App\Classes\Crawler\ChromiumCrawler;
use App\Classes\Crawler\SimpleCrawler;
use App\Helpers\GeneralHelper;
use App\Helpers\LinkHelper;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Product;
use App\Notifications\ProductDiscounted;
use App\Services\NotificationService;
use App\Services\SchemaParser;
use Dom\HTMLDocument;
use HeadlessChromium\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class CustomStoreTemplate
{
    protected array $schema;

    protected HTMLDocument $dom;

    protected string $current_product_url;

    protected array $product_data = [
        'name' => 'NA',
        'image' => '',
        'price' => 0.0,
        'used_price' => 0.0,
        'is_in_stock' => true,
        'is_official' => true,
        'total_reviews' => 0.0,
        'rating' => '',
        'seller' => '',
        'shipping_price' => 0.0,
        'condition' => 'new',
    ];

    // settings
    protected bool $chromium_crawler = false;

    protected array $chromium_options = [
        'page' => Page::NETWORK_IDLE,
        'timeout_ms' => 10000,
    ];

    protected array $extra_headers = [];

    protected string $user_agent = '';

    public function __construct(
        public Link $link
    ) {
        try {
            $this->link->loadMissing(['store', 'products' => fn ($query) => $query->withoutGlobalScopes()]);

            $this->current_product_url = self::prepare_url($this->link);

            $this->crawl_product();

        } catch (\Throwable $t) {
            Log::error("couldn't crawl the url for link {$this->link->id} with store {$this->link->store->name}");
            Log::error($t);
            $this->link->touch();
        }

        try {
            $this->prepare_dom_for_getting_product_information();
            $this->get_product_information();
            $this->get_product_pricing();
        } catch (\Throwable $t) {
            Log::error("couldn't parse data for link {$this->link->id} with store {$this->link->store->name}");
            Log::error($t);
            $this->link->touch();
        }

        try {
            $this->update_product_details();
        } catch (\Throwable $t) {
            Log::error("couldn't update data for link {$this->link->id} with store {$this->link->store->name}");
            Log::error($t);
            $this->link->touch();
        }
    }

    public static function prepare_url(Link $link): string
    {
        $base_url = "https://{$link->store->domain}/";

        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);

        return $base_url."{$link_base}?{$link_params}";
    }

    public function crawl_product(): void
    {
        if ($this->link->store->custom_settings['crawling_method'] == "chromium") {
            $this->dom = new ChromiumCrawler(
                url: $this->current_product_url,
                timeout_ms: $this->link->store->custom_settings['timeout'],
                page_event: $this->link->store->custom_settings['page_event'],
            )->dom;
        } else {
            $this->dom = new SimpleCrawler(
                url: $this->current_product_url,
                extra_headers: $this->extra_headers,
                user_agent: $this->user_agent,
            )->dom;
        }
        $this->dom->saveHTMLFile('response.html');

        $this->schema = new SchemaParser($this->dom)->schema;

    }

    // only update if the user didn't set the name or the image
    public function get_product_information(): void
    {
        $this->get_name();
        $this->get_image();
        $this->get_total_reviews();
        $this->get_rating();
        $this->get_seller();

    }

    // only update if the user didn't set the name or the image
    public function get_product_pricing(): void
    {
        $this->get_price();
        $this->get_used_price();
        $this->get_stock();
        $this->get_shipping_price();
        $this->get_condition();

        //        dd($this->schema, $this->product_data, $this->current_product_url);

    }

    public function update_product_details(): void
    {

        $products_for_link = $this->link->products->pluck('id')->implode(',');

        if (blank($products_for_link))
            return;

        // update products details that are missing.
        DB::statement("
            UPDATE products
            SET
                name = CASE WHEN name = '' OR name IS NULL THEN ? ELSE name END,
                image = CASE WHEN image = '' OR image IS NULL THEN ? ELSE image END
            WHERE id IN ($products_for_link)
        ", [
            $this->product_data['name'],
            $this->product_data['image'],
        ]);

        $this->link->loadMissing([
            'notification_settings' => fn ($query) => $query->withoutGlobalScopes(),
            'notification_settings.user.currency',
        ]);

        $new_temp_link = new Link([
            'price' => $this->product_data['price'],
            'used_price' => $this->product_data['used_price'],
            'is_in_stock' => $this->product_data['is_in_stock'],
            'shipping_price' => $this->product_data['shipping_price'],
            'condition' => $this->product_data['condition'],
            'total_reviews' => $this->product_data['total_reviews'],
            'rating' => $this->product_data['rating'],
            'seller' => $this->product_data['seller'],
            'is_official' => $this->product_data['is_official'],
        ]);

        $products_to_increment = [];

        foreach ($this->link->notification_settings as $notification_setting) {
            $current_product = $this->link->products->firstWhere('id', $notification_setting->product_id);
            $service = new NotificationService($this->link, $new_temp_link, $notification_setting, $current_product);
            if ($service->check()) {
                $notification_setting->user->notify(new ProductDiscounted(
                    product: $current_product,
                    store_name: $this->link->store->name,
                    link: $this->link,
                    new_link: $new_temp_link,
                    highest_price: $this->link->highest_price,
                    lowest_price: $this->link->lowest_price,
                    currency_code: $this->link->store->currency->code,
                    notification_reasons: $service->notification_reasons,
                    product_url: $this->current_product_url,
                ));

                $products_to_increment[] = $current_product->id;
            }

        }

        if (count($products_to_increment) > 0) {
            Product::withoutGlobalScopes()
                ->whereIn('id', $products_to_increment)
                ->increment('notifications_sent');
        }

        $this->link->update($this->product_data + [
            'highest_price' => ($this->product_data['price'] > $this->link->highest_price) ? $this->product_data['price'] : $this->link->highest_price,
            'lowest_price' => (($this->product_data['price'] &&
                    $this->product_data['price'] < $this->link->lowest_price) ||
                ! $this->link->lowest_price)
                ? $this->product_data['price']
                : $this->link->lowest_price,
        ]);

        $this->update_link_history();

    }

    // not required for all stores
    public function prepare_dom_for_getting_product_information()
    {
        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }
    }

    // required to be for all stores

    public function get_name()
    {
        if ($this->link->store->custom_settings['name_schema_key']) {
            $this->product_data['name'] = Arr::get($this->schema, $this->link->store->custom_settings['name_schema_key']);

            return;
        }

        if (! $this->link->store->custom_settings['name_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['name_selectors']);

        $attributes = ['content'];

        $this->get_results_for_key($results, $attributes, 'name');

    }

    public function get_image(): void
    {
        if ($this->link->store->custom_settings['image_schema_key']) {

            $this->product_data['image'] = Arr::get($this->schema, $this->link->store->custom_settings['image_schema_key']);

            GeneralHelper::append_domain_to_url_if_missing($this->product_data['image'], $this->link->store->domain);

            return;
        }

        if (! $this->link->store->custom_settings['image_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['image_selectors']);

        $attributes = ['content', 'src', 'href'];

        $this->get_results_for_key($results, $attributes, 'image');

        GeneralHelper::append_domain_to_url_if_missing($this->product_data['image'], $this->link->store->domain);
    }

    public function get_total_reviews()
    {

        if ($this->link->store->custom_settings['total_reviews_schema_key']) {
            $this->product_data['total_reviews'] = Arr::get($this->schema, $this->link->store->custom_settings['total_reviews_schema_key']);

            return;
        }

        if (! $this->link->store->custom_settings['total_reviews_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['total_reviews_selectors']);

        $attributes = ['content', 'src', 'href'];

        $this->get_results_for_key($results, $attributes, 'total_reviews');
    }

    public function get_rating()
    {

        if ($this->link->store->custom_settings['rating_schema_key']) {
            $this->product_data['rating'] = Arr::get($this->schema, $this->link->store->custom_settings['rating_schema_key']);

            return;
        }

        if (! $this->link->store->custom_settings['rating_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['rating_selectors']);

        $attributes = ['content'];

        $this->get_results_for_key($results, $attributes, 'rating');

        if (! $this->product_data['rating']) {
            $this->product_data['rating'] = 0;
        }

    }

    public function get_seller()
    {

        if ($this->link->store->custom_settings['seller_schema_key']) {
            $this->product_data['seller'] = Arr::get($this->schema, $this->link->store->custom_settings['seller_schema_key']);

            return;
        }

        if (! $this->link->store->custom_settings['seller_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['seller_selectors']);

        $attributes = ['content'];

        $this->get_results_for_key($results, $attributes, 'seller');
    }

    //
    public function get_price()
    {

        $price_available = Arr::get($this->schema, $this->link->store->custom_settings['price_schema_key']);
        if ($this->link->store->custom_settings['price_schema_key'] && $price_available) {
            $this->product_data['price'] = $price_available;

            return;
        }

        if (filled($this->link->store->custom_settings['price_selectors'])) {
            $results = $this->dom->querySelectorAll($this->link->store->custom_settings['price_selectors']);
            $attributes = ['content'];

            $this->get_results_for_key($results, $attributes, 'price');

            $this->product_data['price'] = (float) GeneralHelper::get_numbers_with_normalized_format(
                GeneralHelper::get_numbers_only_with_dot_and_comma($this->product_data['price'])
            );

        }

        if (! $this->product_data['price'] && $this->link->store->custom_settings['price_schema_key'])
            $this->product_data['price'] = $this->search_for_custom_keys_and_get_values($this->link->store->custom_settings['price_schema_key']);

        $this->product_data['price'] = (float) GeneralHelper::get_numbers_with_normalized_format(
            GeneralHelper::get_numbers_only_with_dot_and_comma($this->product_data['price'])
        );

    }

    //
    public function get_used_price() {}

    public function get_stock(): void
    {

        if ($this->link->store->custom_settings['stock_schema_key']) {
            $this->product_data['is_in_stock'] = Str::contains(Arr::get($this->schema, $this->link->store->custom_settings['stock_schema_key']), 'instock', true);

            return;
        }

        if (! $this->link->store->custom_settings['stock_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['stock_selectors']);

        $result_exists = $results->length > 0;

        // if the user didn't pass any value to compare against and searching only for existence
        if (! array_key_exists('stock_value_to_search', $this->link->store->custom_settings)
            || ! $this->link->store->custom_settings['stock_value_to_search']) {
            $this->product_data['is_in_stock'] = (! array_key_exists('in_or_out_of_stock_selector_type', $this->link->store->custom_settings) ||
                $this->link->store->custom_settings['in_or_out_of_stock_selector_type'])
                ? $result_exists
                : ! $result_exists;
        } else {
            // check if value is correct or not

            $string_is_correct = Str::is($this->link->store->custom_settings['stock_value_to_search'], trim($results[0]->textContent), true);
            $searching_for_in_stock = $this->link->store->custom_settings['in_or_out_of_stock_selector_type'];

            // did this as it's easier to read and understand.
            if ($string_is_correct && $searching_for_in_stock) {
                $this->product_data['is_in_stock'] = true;
            } elseif ($string_is_correct && ! $searching_for_in_stock) {
                $this->product_data['is_in_stock'] = false;
            } elseif (! $string_is_correct && $searching_for_in_stock) {
                $this->product_data['is_in_stock'] = false;
            } elseif (! $string_is_correct && ! $searching_for_in_stock) {
                $this->product_data['is_in_stock'] = true;
            }
        }

    }

    public function get_shipping_price() {}

    public function get_condition(): void
    {
        if ($this->link->store->custom_settings['condition_schema_key']) {
            $this->product_data['condition'] = Str::contains(Arr::get($this->schema, $this->link->store->custom_settings['condition_schema_key']), 'new', true) ? 'New' : 'Used';

            return;
        }

        if (! $this->link->store->custom_settings['condition_selectors']) {
            return;
        }

        $results = $this->dom->querySelectorAll($this->link->store->custom_settings['condition_selectors']);

        $attributes = ['content'];

        $this->get_results_for_key($results, $attributes, 'condition');

    }

    public function other_method_if_system_detected_as_bot() {}

    private function get_results_for_key($results, $attributes, $key)
    {
        foreach ($results as $result) {
            if ($result->textContent) {
                $this->product_data[$key] = $result->textContent;

                return;
            } else {
                foreach ($attributes as $attribute) {
                    if ($result->getAttribute($attribute)) {
                        if ($key == 'condition') {
                            $this->product_data[$key] = Str::contains($result->getAttribute($attribute), 'new', true) ? 'New' : 'Used';
                        }

                        $this->product_data[$key] = $result->getAttribute($attribute);

                        return;
                    }
                }
            }
        }

    }

    public function update_link_history()
    {
        if (! $this->product_data['price'])
            return;

        $history = LinkHistory::firstOrCreate([
            'date' => today(),
            'link_id' => $this->link->id,
        ], [
            'price' => $this->product_data['price'],
            'used_price' => $this->product_data['used_price'],
        ]);

        if ($history->price > $this->product_data['price'])
            $history->price = $this->product_data['price'];
        if ($history->used_price > $this->product_data['used_price'])
            $history->used_price = $this->product_data['used_price'];

        $history->save();
    }


    public function search_for_custom_keys_and_get_values(string $key, array $current_schema = [])
    {
        if (blank($key) || blank($this->schema)) return null;

        $key_parts = explode('.', $key);

        $remaining_key_parts = $key_parts;

        $shallow_schema = $current_schema ?: $this->schema;

        // go through each key part and search the parth for it until we get a result
        foreach ($key_parts as &$key_part) {

            $remaining_key_parts = array_slice($remaining_key_parts, 1);

            // check for case [sku=value]
            if (preg_match('/^\[[^=]+=[^]]+\]$/', $key_part)) {

                // check the schema key we want to access to compare its value to the value of get param of schema_key_value
                [$schema_key, $schema_key_value] = Str::of($key_part)
                    ->between('[', ']')
                    ->explode('=');

                $value_to_search = Uri::of($this->current_product_url)
                    ->query()
                    ->get($schema_key_value);

                // if they are not the same, skip the current record, otherwise continue to the next key.
                if (strtolower($current_schema[$schema_key]) != strtolower($value_to_search))
                    return null;

                continue;
            }

            if ($key_part == '*') {
                // go through every record and return the first path that returns a value
                foreach (data_get($shallow_schema, '*') as $single_record) {
                    $value = $this->search_for_custom_keys_and_get_values(implode('.', $remaining_key_parts), $single_record);

                    if (filled($value))
                        return $value;
                }
            }

            if (! isset($shallow_schema[$key_part]))
                return null;

            // check if they key exists to go through it, if we reach to a point where there isn't a child, return the result back
            $shallow_schema = $shallow_schema[$key_part];
        }

        return $shallow_schema;
    }

}

<?php

namespace App\Classes;

use App\Classes\Crawler\ChromiumCrawler;
use App\Classes\Crawler\SimpleCrawler;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Product;
use App\Notifications\ProductDiscounted;
use App\Services\NotificationService;
use App\Services\SchemaParser;
use Dom\HTMLDocument;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class StoreTemplate
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

            $this->current_product_url = static::prepare_url($this->link);

            $this->before_crawl();
            $this->crawl_product();
            $this->other_method_if_system_detected_as_bot();
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

    public function crawl_product(): void
    {
        if ($this->chromium_crawler) {
            $this->dom = new ChromiumCrawler(
                url: $this->current_product_url,
                timeout_ms: $this->chromium_options['timeout_ms'],
                page_event: $this->chromium_options['page'],
                extra_headers: $this->extra_headers,
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
    }

    public function update_product_details(): void
    {

        // update product details that are missing.
        DB::statement("
            UPDATE products
            SET
                name = CASE WHEN name = '' OR name IS NULL THEN ? ELSE name END,
                image = CASE WHEN image = '' OR image IS NULL THEN ? ELSE image END
            WHERE id IN ({$this->link->products->pluck('id')->implode(',')})
        ", [
            $this->product_data['name'],
            $this->product_data['image'],
        ]);

        $this->link->loadMissing([
            'notification_settings' => fn ($query) => $query->withoutGlobalScopes(),
            'notification_settings.user',
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
                    product_id: $current_product->id,
                    product_name: $current_product->name,
                    product_image: $current_product->image,
                    store_name: $this->link->store->name,
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

    abstract public static function prepare_url(Link $link, array $extra = []): string;

    // not required for all stores
    public function prepare_dom_for_getting_product_information() {}

    public function before_crawl() {}

    // required to be for all stores

    abstract public function get_name(): void;

    abstract public function get_image(): void;

    abstract public function get_total_reviews(): void;

    abstract public function get_rating(): void;

    abstract public function get_seller(): void;

    //
    abstract public function get_price(): void;

    //
    abstract public function get_used_price(): void;

    abstract public function get_stock(): void;

    abstract public function get_shipping_price(): void;

    abstract public function get_condition(): void;

    abstract public function other_method_if_system_detected_as_bot(): void;
}

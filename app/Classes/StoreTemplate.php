<?php

namespace App\Classes;

use App\Classes\Crawler\ChromiumCrawler;
use App\Classes\Crawler\SimpleCrawler;
use App\Models\ProductLink;
use App\Notifications\ProductDiscounted;
use App\Services\NotificationService;
use App\Services\SchemaParser;
use Dom\HTMLDocument;
use HeadlessChromium\Page;

use function Laravel\Prompts\error;

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
        public ProductLink $product_link
    ) {

        try {
            $this->product_link->loadMissing(['store', 'product' => fn($query) => $query->withoutGlobalScopes()]);

            $this->current_product_url = static::prepare_url($this->product_link);

            $this->before_crawl();
            $this->crawl_product();
            $this->other_method_if_system_detected_as_bot();
        } catch (\Throwable $t) {
            dd("store template", $t);
            error("couldn't crawl the url for product {$this->product_link->product->name} with store {$this->product_link->store->name}");
        }

        $this->prepare_dom_for_getting_product_information();
        $this->get_product_information();
        $this->get_product_pricing();

        $this->update_product_details();

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
        // we're doing this so each user tracks his own highest and lowest price
        $product_links = ProductLink::withoutGlobalScopes()
            ->with(['product' => fn($query) => $query->withoutGlobalScopes(), 'notification_settings', 'user'])
            ->where([
                'store_id' => $this->product_link->store_id,
                'key' => $this->product_link->key,
            ])->get();

        // go through every link, get each notification settings and check it against the new data
        foreach ($product_links as $product_link) {

            $new_product_link = new ProductLink([
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

            if (! $product_link->product->name) {
                $product_link->product->name = $this->product_data['name'];
            }

            if (! $product_link->product->image) {
                $product_link->product->image = $this->product_data['image'];
            }

            $product_link->product->save();

            $notifications_sent = 0;


            foreach ($product_link->notification_settings as $notification_setting) {
                $service = new NotificationService($product_link, $new_product_link, $notification_setting);
                if ($service->check()) {
                    $product_link->user->notify(new ProductDiscounted(
                        product_id: $product_link->product_id,
                        product_name: $product_link->product->name,
                        product_image: $product_link->product->image,
                        store_name: $product_link->store->name,
                        new_product_link: $new_product_link,
                        highest_price: $product_link->highest_price,
                        lowest_price: $product_link->lowest_price,
                        currency_code: $product_link->store->currency->code,
                        notification_reasons: $service->notification_reasons,
                        product_url: $this->current_product_url,
                    ));
                    $notifications_sent++;
                }
            }

            if ($notifications_sent) {
                $product_link->product()->withoutGlobalScopes()->increment('notifications_sent', $notifications_sent);
            }

            $product_link->update($this->product_data + [
                'highest_price' => ($this->product_data['price'] > $product_link->highest_price) ? $this->product_data['price'] : $product_link->highest_price,
                'lowest_price' => (($this->product_data['price'] &&
                        $this->product_data['price'] < $product_link->lowest_price) ||
                    ! $product_link->lowest_price)
                    ? $this->product_data['price']
                    : $product_link->lowest_price,
            ]);
        }
    }

    abstract public static function prepare_url(ProductLink $product_link, array $extra = []): string;

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

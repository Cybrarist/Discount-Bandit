<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\CurrencyHelper;
use App\Helpers\StoreHelper;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\RssFeedItem;
use App\Models\Store;
use App\Models\User;
use App\Notifications\ProductDiscounted;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Filament\Notifications\Notification;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

abstract class StoreTemplate
{
    protected ProductStore $current_record;

    const array USER_AGENTS = [
        //        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
        //         'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/127.0.2651.105',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.3',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.3',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux i686; rv:130.0) Gecko/20100101 Firefox/130.0',

    ];

    const array ARGOS_AGENTS = [
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25  Version/5.0.4 Safari/533.20.27",
    ];

    const string OTHER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:88.0) Gecko/20100101 Firefox/88.0";

    const string NOON_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0";

    protected string $product_url;

    protected string $name = "NA";

    protected string $image = "";

    protected float $price = 0;

    protected float $price_used = 0;

    protected bool $in_stock = true;

    protected int $no_of_rates = 0;

    protected string $rating = "-1";

    protected string $seller = "";

    protected float $shipping_price = 0;

    protected string $condition = "new";

    // todo change to array
    private string $ntfy_tags = "";

    protected ?DOMDocument $document = null;

    protected ?SimpleXMLElement $xml;

    public function __construct(int $product_store_id)
    {

        self::get_record($product_store_id);

        // get the product url
        $this->product_url = $this->prepare_url(
            domain: $this->current_record->store->domain,
            product: $this->current_record->key,
            store: $this->current_record->store,
        );

        try {
            $this->crawler();
            $this->prepare_sections_to_crawl();
            $this->get_product_information();
        } catch (Exception $exception) {
            $this->log_error(part: "Crawling", exception: $exception->getMessage());
        }
    }

    protected function get_record($product_store_id): void
    {
        $this->current_record = ProductStore::with([
            "product",
            "store",
        ])->find($product_store_id);
    }

    abstract public static function prepare_url($domain, $product, ?Store $store = null): string;

    /**
     * Define the crawler, either chrome based or GET request.
     */
    abstract public function crawler(): void;

    abstract public function is_system_detected_as_robot(): bool;

    /**
     * get the product page and prepare the dom and xpath.
     *
     * @throws ConnectionException
     */
    public function crawl_url(array $extra_headers = []): void
    {
        $response = self::get_website($this->product_url, $extra_headers);
        self::prepare_dom($response, $this->document, $this->xml);
    }

    public function crawl_url_chrome(array $extra_headers = []): void
    {
        $response = self::get_website_chrome($this->product_url, $extra_headers);
        self::prepare_dom($response, $this->document, $this->xml);
    }

    /**
     * Prepare the parts of page that needs to be crawled or add
     * extra steps to crawl the products.
     */
    abstract public function prepare_sections_to_crawl(): void;

    /**
     * Check if product has name or not
     * then get all the data related the product.
     *
     * if the product couldn't be crawled, then it'll be tapped with default values
     * so the system doesn't crawl it again in the next iteration
     */
    protected function get_product_information(): void
    {

        $update_basic_product = [];

        if (! $this->current_record->product->name || $this->current_record->product->name == "NA") {
            $this->get_name();
            $update_basic_product["name"] = $this->name;
        }

        if (! $this->current_record->product->image) {
            $this->get_image();
            $update_basic_product["image"] = $this->image;
        }

        if (! empty($update_basic_product)) {
            Product::where("id", $this->current_record->product_id)
                ->update($update_basic_product);
        }

        $this->get_price();
        $this->get_used_price();
        $this->get_stock();
        $this->get_no_of_rates();
        $this->get_rate();
        $this->get_seller();
        $this->get_shipping_price();
        $this->get_condition();

        if ($this->price) {
            // update the current record
            $this->current_record->update([
                'price' => $this->price,
                'used_price' => $this->price_used,
                'highest_price' => ($this->price > $this->current_record->highest_price) ? $this->price : $this->current_record->highest_price,
                'lowest_price' => (($this->price && $this->price < $this->current_record->lowest_price) || ! $this->current_record->lowest_price) ? $this->price : $this->current_record->lowest_price,
                'number_of_rates' => $this->no_of_rates,
                'seller' => $this->seller,
                'rate' => $this->rating,
                'shipping_price' => $this->shipping_price,
                'condition' => $this->condition,
                'in_stock' => $this->in_stock,
                'notifications_sent' => ($this->check_notification()) ? ++$this->current_record->notifications_sent : $this->current_record->notifications_sent,
            ]);

            self::record_price_history(
                product_id: $this->current_record->product_id,
                store_id: $this->current_record->store_id,
                price: $this->price,
                used_price: $this->price_used
            );

        } else {
            Log::info("New Price: (not updated) ".$this->price);
            Log::info("Product:".$this->current_record);
            $this->current_record->update(['updated_at' => now()]);
        }
    }

    /**
     * Check if the product matches the criteria the user wants
     * along with adding tags to the notification so the user
     * knows why he's been notified
     */
    public function check_notification(): bool
    {
        if ($this->notification_snoozed()) {
            return false;
        }

        if ($this->stock_available()) {
            $this->ntfy_tags .= ",Stocks Available";
            $this->notify();

            return true;
        }

        if (config('settings.notify_any_change') && $this->price_crawled_and_different_from_database()) {
            $this->ntfy_tags .= ",Any Change";
            $this->notify();

            return true;
        }

        if (! $this->price_crawled_and_different_from_database()) {
            return false;
        }

        if ($this->is_official_seller()) {
            $this->ntfy_tags .= ", Official Only";
        } elseif (! $this->current_record->product->only_official) {
            $this->ntfy_tags .= ", Not Official";
        } else {
            return false;
        }

        if (self::is_price_lowest_within(
            product_id: $this->current_record->product_id,
            store_id: $this->current_record->store_id,
            days: $this->current_record->product->lowest_within,
            price: $this->price
        )) {
            $this->ntfy_tags .= ", Lowest Within {$this->current_record->product->lowest_within} Days";
            $this->notify();

            return true;
        }

        if ($this->max_notification_reached()) {
            return false;
        }

        if ($this->price_reached_desired()) {
            $this->ntfy_tags .= ",Price Reached";
            $this->notify();

            return true;
        }

        if ($this->price_reached_dropped_percentage()) {
            $this->ntfy_tags .= ",Price Percentage Dropped";
            $this->notify();

            return true;
        }

        return false;
    }

    /**
     * validated different criteria of the product
     */
    public function notification_snoozed(): bool
    {
        return $this->current_record->product->snoozed_until && Carbon::create($this->current_record->product->snoozed_until)->isFuture();
    }

    public function stock_available(): bool
    {
        // check if the stock option is enabled, also the previous crawl was out of stock  and the current is in stock
        return $this->current_record->product->stock && ! $this->current_record->in_stock && $this->in_stock && $this->price;
    }

    public function price_crawled_and_different_from_database(): bool
    {
        // check that we have the crawled price, and that is different from the database.
        Log::info("crawled price: {$this->price} \n current price {$this->current_record->price}");
        Log::info($this->price && ($this->price != $this->current_record->price));

        return $this->price && ($this->price != $this->current_record->price);
    }

    public function is_official_seller(): bool
    {
        $class_called = explode("\\", get_called_class());

        return $this->current_record->product->only_official &&
            Str::contains($this->seller, end($class_called), true);
    }

    public static function is_price_lowest_within($product_id = null, $store_id = null, $days = null, $price = 0): bool
    {
        if (! $days || ! $price) {
            return false;
        }

        $lowest_price_in_database = PriceHistory::whereDate('date', '>=', Carbon::today()->subDays($days))
            ->where([
                "product_id" => $product_id,
                "store_id" => $store_id,
            ])
            ->min("price");

        return $price * 100 <= $lowest_price_in_database && $lowest_price_in_database != 0;
    }

    public function price_reached_desired(): bool
    {
        return ($this->current_record->add_shipping) ? $this->shipping_price + $this->price <= $this->current_record->notify_price : $this->price <= $this->current_record->notify_price;
    }

    public function price_reached_dropped_percentage(): bool
    {
        return $this->price &&
            $this->current_record->notify_percentage &&
            $this->current_record->price &&
            (($this->current_record->price - $this->price) / $this->current_record->price) * 100 >= $this->current_record->notify_percentage;
    }

    public function max_notification_reached(): bool
    {
        return $this->current_record->product->max_notifications &&
            $this->current_record->notifications_sent > $this->current_record->product->max_notifications;
    }

    /**
     * Send notification to the user.
     * and add the product to the rss feed
     */
    public function notify(): void
    {
        try {
            $user = User::first();

            $user->notify(
                new ProductDiscounted(
                    product_id: $this->current_record->product_id,
                    product_name: $this->current_record->product->name ?? $this->name,
                    store_name: $this->current_record->store->name,
                    price: $this->price,
                    highest_price: $this->current_record->highest_price,
                    lowest_price: $this->current_record->lowest_price,
                    product_url: $this->prepare_url(
                        domain: $this->current_record->store->domain,
                        product: $this->current_record->key,
                        store: $this->current_record->store,
                    ),
                    image: $this->current_record->product->image ?? $this->image,
                    currency: CurrencyHelper::get_currencies($this->current_record->store->currency_id),
                    tags: $this->ntfy_tags));

            if (config('settings.rss_feed')) {
                RssFeedItem::create([
                    "data" => [
                        'title' => "For Just $this->price -  Discount For ".Str::words($this->current_record->product->name),
                        'summary' => "Your product ".$this->current_record->product->name.", is at discount with price  ".$this->current_record->store->currency->code." $this->price",
                        'updated' => now()->toDateTimeString(),
                        'product_id' => $this->current_record->product_id,
                        'image' => $this->image ?? $this->current_record->product->image,
                        'name' => "Discount Bandit",
                    ],
                ]);
            }
        } catch (Exception $exception) {
            $this->log_error("Send Notification", $exception->getMessage());
        }

    }

    /**
     * methods to be implemented in each store to get the product information.
     */
    abstract public function get_name();

    abstract public function get_image();

    abstract public function get_price();

    abstract public function get_used_price();

    abstract public function get_stock();

    abstract public function get_no_of_rates();

    abstract public function get_rate();

    abstract public function get_seller();

    abstract public function get_shipping_price();

    abstract public function get_condition();

    public static function insert_other_store($domain, $product_id, $extra_data = []): void
    {

        try {
            $store_id = Store::where('domain', $domain)->first()->id;

            ProductStore::updateOrCreate([
                "product_id" => $product_id,
                "store_id" => $store_id,
            ],
                $extra_data
            );

            StoreHelper::clear_caches_related_to_stores();

        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title("Oops, Couldn't link that store")
                ->body("This store doesn't exist in the database, please check the url")
                ->persistent()
                ->send();

            Log::error("linking product with error \n $e");
        }

    }

    /**
     * Get the page source for specific website/page.
     */
    public static function get_website(string $url, array $data = [], array $extra_headers = []): Response
    {

        // todo move the the extra headers to single stores files
        $extra_headers = match (true) {
            Str::contains($url, "argos.co.uk", true) => [
                "Accept-Encoding" => "gzip, deflate, br, zstd",
            ],
            Str::contains($url, "emaxme.com", true) => [
                "x-context-request" => '{"applicationId":101,"tenantId":"5DF1363059675161A85F576D"}',
            ],
            default => $extra_headers
        };

        //        $creq = curl_init();
        //
        //
        //        curl_setopt_array($creq, array(
        //            CURLOPT_URL => $url,
        //            CURLOPT_RETURNTRANSFER => true,
        //            CURLOPT_POST => true,
        //            CURLOPT_ENCODING => '',
        //            CURLINFO_HEADER_OUT => true,
        //            CURLOPT_POSTFIELDS => $data,
        //            CURLOPT_FOLLOWLOCATION => false
        //        ));
        //
        //        $output = curl_exec($creq);

        //        dd($output);

        return Http::withUserAgent(self::get_random_user_agent())
            ->timeout(10)
            ->withHeaders(
                array_merge([
                    'Accept' => '*/*',
                    'DNT' => 1,
                    'Sec-Fetch-User' => '1',
                    'Connection' => 'closed',
                    "Accept-Encoding" => "gzip, deflate",
                ], $extra_headers)
            )
            ->get($url, $data);

        // todo remove later if noon is working fine for a while.

        //        $extra_headers=[
        //            "Connection"=> "keep-alive",
        //            "Cache-Control"=> "no-cache",
        //            "Accept"=> "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
        //            "Accept-Language"=> "en-US,en;q=0.5",
        //            "Priority"=> "u=1",
        //        ];

    }

    /**
     * get the page source using chromium.
     */
    public static function get_website_chrome(string $url, array $extra_headers = []): string
    {
        $browser = app()->isProduction() ? 'chromium' : null;

        $browser_factory = new BrowserFactory($browser);

        $options = [
//                        'keepAlive' => true,
            'connectionDelay' => 1,
            'headless' => false,
            'noSandbox' => true,
            "headers" => $extra_headers,
            'userAgent' => self::get_random_user_agent($url),
            'customFlags' => ['--lang=en-US', '--disable-blink-features=AutomationControlled', '--deny-permission-prompts=true'],
            'disableNotifications' => true,
        ];

        if (! $options['userAgent']) {
            unset($options['userAgent']);
        }

        $browser = $browser_factory
            ->createBrowser($options);

        $page = $browser->createPage();

        try {
            $page_event = match (true) {
                Str::contains($url, ["mediamarket", "eprice"], true) => Page::DOM_CONTENT_LOADED,
                Str::contains($url, ["emax"], true) => Page::INTERACTIVE_TIME,
                Str::contains($url, ["homedepot"], true) => Page::LOAD,

                default => Page::NETWORK_IDLE
            };

            $timeout = match (true) {
                Str::contains($url, ["bestbuy", "canadiantire"], true) => 20000,
                Str::contains($url, ["homedepot"], true) => 30000,
                default => 10000
            };

            try {
                $page->navigate($url)
                    ->waitForNavigation($page_event, $timeout);

                //todo change in v4
                if (Str::contains($url, "homedepot.ca", true)) {

                    $page->waitUntilContainsElement('.hdca-modal__content');

                    $page->mouse()
                        ->move(10, 10)
                        ->click();

                    $page->waitUntilContainsElement('.hdca-product__description-pricing-price-value');
                }

            } catch (Exception $e) {
                Log::error("couldn't crawl $url");
            }

            return $page->getHtml();

        } catch (OperationTimedOut $e) {
            return $page->getHtml();
            // too long to load
        } catch (Exception $exception) {
            Log::error("Crawling using chrome");
            Log::error($exception->getMessage());
        }

        return "";
    }

    public static function prepare_dom($response, &$document, &$xml): void
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);

        $document->loadHTML($response);
        $xml = simplexml_import_dom($document);
    }

    /**
     * if variation is included, then add all of them.
     *
     * P.S updateOrCreate since ProductStore extends Pivot instead of Model
     * didn't check upsert or to change the type to model
     * //todo productstore type.
     *
     * @param  array  $settings  the available parameters that are shared.
     */
    public static function insert_variation(array $variations, Store $store, array $settings): void
    {
        try {
            foreach ($variations as $single_variation) {

                $data_to_update = [
                    'snoozed_until' => $settings['snoozed_until'],
                    'max_notifications' => $settings['max_notifications'],
                    'status' => $settings['status'],
                    'only_official' => $settings['only_official'],
                    'stock' => $settings['stock'],
                    'lowest_within' => $settings['lowest_within'],
                    'favourite' => $settings['favourite'],
                ];

                $product_store_exists = ProductStore::where([
                    "key" => $single_variation,
                    "store_id" => $store->id,
                ])->first();

                if ($product_store_exists) {
                    Product::where("id", $product_store_exists->id)->update($data_to_update);
                } else {
                    $product = Product::create($data_to_update);
                    $product->product_stores()->create([
                        "key" => $single_variation,
                        "store_id" => $store->id,
                        "notify_price" => $settings['notify_price'],
                        "notify_percentage" => $settings['notify_percentage'],
                    ]);
                }

            }
        } catch (Exception $exception) {
            Notification::make()
                ->warning()
                ->title("Something Wrong Happened")
                ->body("can you please check your logs and share it with the developer".$exception->getMessage())
                ->persistent()
                ->send();

        }
    }

    /**
     * Record the price history for product in specific store.
     */
    public static function record_price_history(int $product_id, int $store_id, float $price = 0, float $used_price = 0): void
    {

        if (! $price && ! $used_price) {
            return;
        }

        $to_update = [
            'price' => $price,
            'used_price' => $used_price,
        ];

        try {
            $history = PriceHistory::firstOrCreate([
                'product_id' => $product_id,
                'store_id' => $store_id,
                'date' => today()->toDateString(),
            ], $to_update);

            // make sure each value is less than the stored value.
            foreach ($to_update as $key => $price) {
                ($price > 0 && $price <= $history->{$key}) ?: Arr::forget($to_update, $key);
            }
            $history->update($to_update);
        } catch (Exception $exception) {
            Log::error("Couldn't update the price history");
            Log::error($exception->getMessage());
        }
    }

    /*
     * record the error with the part responsible.
     */
    public function log_error(string $part, ?string $exception = null): void
    {
        Context::add("product", $this->product_url);
        Log::error("Couldn't get the $part \n Full error message:\n $exception");
    }

    public static function get_random_user_agent($url = ""): string
    {
        return match (true) {
            Str::contains($url, ["costco.", "currys.c"], true) => self::OTHER_AGENT,
            Str::contains($url, "noon.com", true) => self::NOON_AGENT,
            Str::contains($url, "argos.co.uk", true) => Arr::random(self::ARGOS_AGENTS),
            Str::contains($url, "walmart", true) => Str::random(),
            Str::contains($url, "homedepot", true) => "",

            default => Arr::random(self::USER_AGENTS)
        };
    }

    public function get_product_schema(string $script_type): array
    {
        $scripts = $this->xml->xpath("//script[@type='$script_type']");

        foreach ($scripts as $single_script) {
            if (Str::contains(Str::remove(" ", $single_script->__toString()), '"@type":"Product"', true)) {
                return json_decode($single_script->__toString(), true);
            }
        }

        return [];
    }

    public function get_meta_items(): array
    {
        return $this->xml->xpath("//meta");
    }
}

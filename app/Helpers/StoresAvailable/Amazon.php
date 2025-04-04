<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use App\Models\Currency;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class Amazon extends StoreTemplate
{
    const string MAIN_URL = "https://www.store/dp/product_id";

    const string OTHER_BUYING_OPTIONS = "https://www.store/gp/product/ajax?ref=dp_aod_NEW_mbc&asin=product_id&m=&qid=&smid=&&sourcecustomerorglistid=&sourcecustomerorglistitemid=&sr=&pc=dp&experienceId=aodAjaxMain";

    private $center_column;

    private $right_column;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    // define crawler
    public function crawler(): void
    {
        //        if ($this->current_record->store_id==4)
        //            parent::crawl_url_chrome();
        //        else
        parent::crawl_url(extra_headers: [
            'Accept' => 'application/json',
            'DNT' => 1,
            "Cache-Control" => "no-cache",
            'Sec-Fetch-User' => '1',
            "Accept-Language" => "en-US,en;q=0.5",
            'Connection' => 'keep-alive',
            "Accept-Encoding" => "gzip, deflate",
        ],
        );
    }

    public function prepare_sections_to_crawl(): void
    {
        if (! $this->is_system_detected_as_robot()) {
            try {
                // get the center column to get the related data for it
                $this->center_column = $this->xml->xpath("//div[@id='centerCol']")[0];
                // get the right column to get the seller and other data
                $this->right_column = $this->xml->xpath("//div[@id='desktop_buybox']")[0];

                return;
            } catch (Throwable $exception) {
                $this->log_error("Page Structure", $exception->getMessage());
            }

        } else {
            Log::warning("System was detected as bot, but we're continuing to try to get the price at least");
            try {
                $this->get_other_options();

            } catch (Throwable $exception) {
                $this->log_error("Page Structure Second", $exception->getMessage());
            }
        }

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $title = explode(":", $this->document->getElementsByTagName("title")->item(0)->textContent);

            $this->name = (count($title) > 1) ? $title[0] : $title[1];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
        try {
            $this->name = trim($this->center_column->xpath("//span[@id='productname'][1]")[0]
                ->__toString());
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }

        try {
            $this->name = trim($this->document->getElementById("aod-asin-title-text")->textContent);
        } catch (Throwable $exception) {
            $this->log_error("Product Name Third Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->document->getElementById("landingImage")->getAttribute("data-old-hires");

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }
        try {
            $this->image = $this->document->getElementById("aod-asin-image-id")->getAttribute("src");
        } catch (Throwable $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price = (float) Str::remove([Currency::find($this->current_record->store->currency_id)->code, ","], $this->center_column->xpath("(//span[contains(@class, 'apexPriceToPay')])[1]")[0]->span->__toString());

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

        // method 2 to return the price of the product
        try {
            $whole = GeneralHelper::get_numbers_only(
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-whole']")[0]
                    ->__toString()
            );

            $fraction = GeneralHelper::get_numbers_only(
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-fraction']")[0]
                    ->__toString()
            );

            $this->price = (float) "$whole.$fraction";

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price Second Method", $exception->getMessage());
        }

        // method 3 to return the price of the product only when bot is detected
        if (! $this->center_column) {
            try {

                $whole = GeneralHelper::get_numbers_only(
                    $this->xml
                        ->xpath("//span[@class='a-price-whole']")[0]
                        ->__toString()
                );

                $fraction = GeneralHelper::get_numbers_only(
                    $this->xml
                        ->xpath("//span[@class='a-price-fraction']")[0]
                        ->__toString()
                );

                $this->price = (float) "$whole.$fraction";
            } catch (Throwable $exception) {
                $this->log_error("Price Third Method", $exception->getMessage());
            }
        }
    }

    public function get_used_price(): void
    {
        // todo implemnet used price for the second crawl, needs to get translation of "used" for different languages

        // method 1 to return the price of the product
        try {
            $prices_in_the_page = json_decode($this->right_column->xpath("//div[contains(@class,'twister-plus-buying-options-price-data')]")[0]->__toString());

            foreach ($prices_in_the_page->{'desktop_buybox_group_1'} as $single_price) {
                if ($single_price->{"buyingOptionType"} == "USED") {
                    $this->price_used = $single_price->{'priceAmount'};
                }
            }
        } catch (Throwable  $exception) {
            $this->log_error("First Method Used Price", $exception->getMessage());
        }
    }

    public function get_stock(): void
    {
        try {
            $availability_string = Str::squish($this->document->getElementById("availability")->textContent);

            $this->in_stock = Str::contains($availability_string, "in stock", true) && Str::length($availability_string) < 10;

        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }
        try {
            $this->in_stock = $this->price > 0;

        } catch (Throwable $exception) {
            $this->log_error("Stock Availability Second Method", $exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $ratings = $this->center_column->xpath("//span[@id='acrCustomerReviewText']")[0]->__toString();
            $this->no_of_rates = (int) GeneralHelper::get_numbers_only_with_dot($ratings);
        } catch (Throwable $exception) {
            $this->log_error("First Method No. Of Rates", $exception->getMessage());
        }
        try {
            $this->no_of_rates = (int) GeneralHelper::get_numbers_only_with_dot($this->document->getElementById("aod-asin-reviews-block")->textContent);
        } catch (Throwable $exception) {
            $this->log_error("Second Method No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            // check if the store is amazon poland or not
            ($this->current_record->domain == "amazon.pl") ? $exploding = 'z' : $exploding = 'out';

            $this->rating = explode(" $exploding",
                $this->center_column->xpath("//div[@id='averageCustomerReviews']//span[@id='acrPopover']//span[@class='a-icon-alt']")[0]->__toString(),
                2)[0];
        } catch (Throwable $exception) {
            $this->log_error("The Rate", $exception->getMessage());
        }
    }

    public function get_seller(): void
    {

        try {
            $this->seller = $this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//div[@class='offer-display-feature-text']//span")[0]
                ->__toString();

            throw_if(! $this->seller, new Exception);

            return;
        } catch (Throwable $exception) {
            $this->log_error("The Seller First Method", $exception->getMessage());
        }

        try {
            $this->seller = $this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//a[@id='sellerProfileTriggerId']")[0]
                ->__toString();

            throw_if(! $this->seller, new Exception);

            return;
        } catch (Throwable $exception) {
            $this->log_error("The Seller Second method", $exception->getMessage());
        }

        // seller method for subscribe and save items
        try {
            $this->seller = $this
                ->right_column
                ->xpath("//div[@id='shipsFromSoldByMessage_feature_div']//span")[0]
                ->__toString();

            // trim the spaces
            $this->seller = trim($this->seller);
            $this->seller = explode('by ', $this->seller)[1] ?? $this->seller;

            throw_if(! $this->seller, new Exception);

            return;
        } catch (Throwable $exception) {
            $this->log_error("The Seller Third Method", $exception->getMessage());
            $this->seller = "";
        }

        // seller method for subscribe and save items
        try {
            $this->seller = Str::remove([" ", "soldby"], $this->document->getElementById("aod-offer-soldBy")->textContent, false);
        } catch (Throwable $exception) {
            $this->log_error("The Seller Third Method", $exception->getMessage());
            $this->seller = "";
        }
    }

    public function get_shipping_price(): void
    {
        try {
            $shipping_price = $this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $shipping_price = Str::replace(",", ".", $shipping_price);
            $this->shipping_price = (float) GeneralHelper::get_numbers_only_with_dot($shipping_price);
        } catch (Throwable $exception) {
            $this->log_error("Shipping Price", $exception->getMessage());
        }
    }

    // TODO: Implement get_condition() method.
    public function get_condition() {}

    public static function get_variations($url): array
    {
        $response = parent::get_website($url);

        parent::prepare_dom($response, $document, $xml);
        try {
            $array_script = $document->getElementById("twister_feature_div")->getElementsByTagName("script");
            $array_script = $array_script->item($array_script->count() - 1)->nodeValue;
            $array_script = explode('"dimensionValuesDisplayData"', $array_script)[1];
            $array_script = explode("\n", $array_script)[0];
            $final_string = preg_replace('/\s+[\{\}\:]/', '', $array_script);
            $array_of_keys_values = explode("],", $final_string);

            foreach ($array_of_keys_values as $single) {
                $key_value = explode(":[", Str::replace(['"', ']},'], " ", $single));
                $options[Str::replace(" ", "", $key_value[0])] = $key_value[1];
            }

            return $options ?? [];

        } catch (Throwable) {
            Notification::make()
                ->danger()
                ->title("Error")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }

        return [];
    }

    public static function prepare_url($domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain, $product],
            self::MAIN_URL);
    }

    public function is_system_detected_as_robot(): bool
    {
        return count($this->xml->xpath('//input[@id="captchacharacters"]')) ||
            count($this->xml->xpath('//label[@for="captchacharacters"]'));
    }

    private function get_other_options(): void
    {
        $temp_url = Str::replace(
            ["store", "product_id"],
            [$this->current_record->store->domain, $this->current_record->key],
            self::OTHER_BUYING_OPTIONS);

        $creq = curl_init();

        curl_setopt_array($creq, [
            CURLOPT_URL => $temp_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_ENCODING => '',
            CURLINFO_HEADER_OUT => true,
            CURLOPT_POSTFIELDS => [],
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $output = curl_exec($creq);
        self::prepare_dom($output, $this->document, $this->xml);
    }
}

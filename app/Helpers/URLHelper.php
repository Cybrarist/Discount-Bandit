<?php

namespace App\Helpers;

use App\Models\Currency;
use App\Models\Store;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class URLHelper
{
    public string $final_url;

    public string $domain;

    public string $top_host;

    private string $path;

    public string $product_unique_key = "";

    public ?Store $store = null;

    private array $parsed_url = [];

    public function __construct(private string $url)
    {
        try {
            // parse the url
            $this->parsed_url = parse_url($url);

            $this->domain = Str::of($this->parsed_url['host'])->lower()->remove(['www.', 'uae.']);

            $this->store = Store::whereDomain($this->domain)->firstOrFail();

            // check the store exists
            $remove_ref_if_exists = explode("/ref", $this->parsed_url['path'] ?? "");
            $this->path = $remove_ref_if_exists[0] ?? "";
            $this->final_url = "https://$this->domain$this->path";
            $this->top_host = explode('.', $this->parsed_url["host"])[0];

            self::get_key();
        } catch (Exception $exception) {
            Notification::make()
                ->danger()
                ->title("Wrong Store")
                ->body("Couldn't get the store")
                ->persistent()
                ->send();

            return;
        }

    }

    public function get_key(): void
    {
        $function_to_be_called = "self::get_".explode(".", $this->domain)[0]."_key";
        $this->product_unique_key = call_user_func($function_to_be_called);
    }

    // todo move all of the following to the classes as static method
    // methods to collect unique products keys
    public function get_argos_key(): string
    {
        return Str::remove("/", Str::squish(Arr::last(explode("/", $this->path))));
    }

    public function get_ajio_key(): string
    {

        $paths = explode("/p/", $this->path);

        return (count($paths) == 1) ? $paths[0] : $paths[1];
    }

    public function get_amazon_key(): string
    {
        $this->path = Str::replace("/gp/product/", "/dp/", $this->path, false);

        $dp_temp = explode("/dp/", $this->path);

        if (count($dp_temp) <= 1) {
            return "";
        }

        $temp = $dp_temp[1];

        $check_slashes_after_dp = explode("/", $temp);
        if ($check_slashes_after_dp) {
            $temp = $check_slashes_after_dp[0];
        }

        return Str::remove("/", Str::squish($temp));
    }

    public function get_bestbuy_key(): string
    {
        $temp = explode("/", $this->path);

        // todo if same after finihsing implementing ca then remove.
        return match ($this->domain) {
            'bestbuy.com' => explode(".", Arr::last($temp))[0],
            'bestbuy.ca' => Arr::last($temp),
        };
    }

    public function get_canadiantire_key(): string
    {
        $paths = explode("/pdp", $this->path);
        $sections = explode("-", $paths[1]);

        return Str::remove(".html", Arr::last($sections));
    }

    public function get_costco_key(): string
    {
        return match ($this->domain) {
            "costco.com","costco.ca" => Str::replace([".", "html"], "", explode(".product", $this->path)[1]),
            "costco.com.mx","costco.co.uk","costco.co.kr","costco.com.tw","costco.co.jp","costco.com.au","costco.is" => explode("/p/", $this->path)[1],
        };
    }

    public function get_currys_key(): string
    {

        $paths = explode("/", $this->path);
        $sections = explode("-", $paths[2]);

        return Str::remove(".html", Arr::last($sections));
    }

    public function get_diy_id(): string
    {

        $this->path = Str::remove(["/departments/", "_BQ.prd"], $this->path);
        $is_two_parts = explode("/", $this->path);

        if (count($is_two_parts) > 2) {
            throw new \Exception("wrong url");
        } else {
            return (count($is_two_parts) > 1) ? $is_two_parts[1] : $is_two_parts[0];
        }
    }

    public function get_ebay_key()
    {
        return explode("/itm/", $this->path)[1];
    }

    public function get_emaxme_key(): string
    {
        return Str::remove(["/", '.html'], $this->path);
    }

    public function get_eprice_key(): string
    {
        return "d-" . explode("/d-", $this->path)[1];
    }

    public function get_flipkart_key(): string
    {
        return Str::after($this->path, 'itm');
    }

    public function get_mediamarkt_key(): string
    {
        $temp = explode("-", $this->path);
        $product_key = explode(".html", end($temp))[0];

        return $product_key;
    }

    public function get_microless_key(): array|string
    {
        $subdomain = explode(".", $this->parsed_url['host'])[0];

        $this->store = match ($subdomain) {
            'uae' => Store::where('name', 'Microless UAE')->first(),
            default => Store::where('name', 'Microless Global')->first(),
        };

        return str_replace("/", "", explode("product/", $this->path)[1]);
    }

    public function get_myntra_key(): string
    {
        $temp = explode("/", $this->path);

        $product_key = $temp[count($temp) - 2];

        return $product_key;
    }

    public function get_nexths_key(): string
    {
        return explode("sku/", $this->path)[1];
    }

    public function get_noon_key(): string
    {

        $paths = explode("/", $this->path);

        // update the store for noon, since they use path instead of custom domain per country
        $this->store = match (Str::lower(explode("-", $paths[1])[0])) {
            "uae" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "AED")->first()->id)->first(),
            "egypt" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "EGP")->first()->id)->first(),
            "saudi" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "SAR")->first()->id)->first(),
        };

        return Str::lower($paths[count($paths) - 3]);

        throw new \Exception("wrong formula");
    }

    public function get_newegg_key(): string
    {

        $country = explode("/global/", $this->path);

        $stores_without_global = [
            'newegg.com' => "$",
            'newegg.ca' => "$",
        ];

        if (array_key_exists($this->domain, $stores_without_global) && count($country) < 2) {
            $this->store = Store::where('domain', $this->domain)
                ->where('currency_id', Currency::where("code", "$")->first()->id)
                ->first();
        } else {

            $without_global_path = explode("/global/", $this->path)[1];
            $global_path = explode("/", $without_global_path)[0];
            $country_shortcut = explode("-", $global_path)[0];

            $this->store = match ($country_shortcut) {
                "ar" => Store::where('name', "Newegg Argentina")->first(),
                "au" => Store::where('name', "Newegg Australia")->first(),
                "bh" => Store::where('name', "Newegg Bahrain")->first(),
                "hk" => Store::where('name', "Newegg Hong Kong")->first(),
                "il" => Store::where('name', "Newegg Occupied Palestine")->first(),
                "jp" => Store::where('name', "Newegg Japan")->first(),
                "kw" => Store::where('name', "Newegg Kuwait")->first(),
                "mx" => Store::where('name', "Newegg Mexico")->first(),
                "nz" => Store::where('name', "Newegg New Zealand")->first(),
                "om" => Store::where('name', "Newegg Oman")->first(),
                "ph" => Store::where('name', "Newegg Philippines")->first(),
                "qa" => Store::where('name', "Newegg Qatar")->first(),
                "sa" => Store::where('name', "Newegg Saudi Arabia")->first(),
                "sg" => Store::where('name', "Newegg Singapore")->first(),
                "kr" => Store::where('name', "Newegg South Korea")->first(),
                "ae" => Store::where('name', "Newegg UAE")->first(),
                "uk" => Store::where('name', "Newegg UK")->first(),
            };
        }

        return explode("/p/", $this->path)[1];
    }

    public function get_nykaa_key(): string
    {

        $paths = explode("/p/", $this->path);

        return (count($paths) == 1) ? $paths[0] : $paths[1];
    }

    public function get_princessauto_key(): string
    {
        $temp = explode("/product/", $this->path)[1];

        return Str::remove("/", Str::squish($temp));
    }

    public function get_snapdeal_key(): string
    {
        $temp = explode("/", $this->path);

        return Arr::last($temp);
    }

    public function get_target_key()
    {
        if (Str::contains($this->url, "preselect", true)) {
            return "A-".explode("#", explode("preselect=", $this->url)[1])[0];
        }

        $paths = explode("/-/", $this->path);

        return $paths[1];
    }

    public function get_tatacliq_key()
    {

        $paths = explode("/p-mp", $this->path);

        return $paths[1];
    }

    public function get_walmart_key(): string
    {
        //        return Str::remove("/" , Str::squish(  Arr::last(explode("/" , $this->path))) );
        return explode('/ip/', $this->path)[1];
    }
}

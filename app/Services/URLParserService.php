<?php

namespace App\Services;

use App\Exceptions\CouldntParseProductKeyOrURLException;
use App\Models\Currency;
use App\Helpers\CacheHelper;
use App\Helpers\UserAgentHelper;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Mockery\Exception;

class URLParserService
{
    private const int MAX_SHORT_URL_REDIRECTS = 5;

    public string $domain;

    public string $top_host;

    public string $product_key = '';

    public ?Store $store = null;

    private Uri $parsed_url;

    public function setup(?string $url = null): void
    {
        if (! $url)
            return;

        if (Str::doesntcontain($url, '://'))
            $url = "https://".$url;

        $url = self::expand_short_url($url);

        $this->parsed_url = Uri::of($url);

        $this->domain = Str::of($this->parsed_url->host())
            ->chopStart(["www.", "uae.", "global."])
            ->toString();

        $this->parse_product_key_or_url();

        $this->store = Store::firstWhere('domain', $this->domain);

        throw_if(! $this->store, new Exception('store not found'));

        $this->remove_disallowed_params();

        $this->top_host = explode('.', $this->parsed_url->host())[0];

    }


    /**
     * prepare the short url
     */
    public static function is_short_url(?string $url): bool
    {
        $host = self::normalize_host($url);

        return filled($host) && in_array($host, CacheHelper::get_short_url_hosts(), true);
    }


    public static function normalize_host(?string $url): string
    {
        if (blank($url)) {
            return '';
        }

        $url = trim($url);
        $host = parse_url(Str::contains($url, '://') ? $url : "https://{$url}", PHP_URL_HOST);

        return Str::lower(Str::chopStart((string) $host, 'www.'));
    }

    /**
     * Follow the redirects of a short URL until it lands on a non short host, without requesting the store itself.
     */
    public static function expand_short_url(string $url): string
    {
        $expanded_url = self::expand_short_urls([$url])[$url];

        throw_if(blank($expanded_url), new Exception("Couldn't resolve the short URL"));

        return $expanded_url;
    }

    /**
     * Expand many short URLs at once, each redirect hop is requested in parallel for all URLs.
     * URLs that aren't short are returned as they are, short URLs that couldn't be resolved are null.
     *
     * @param  list<string>  $urls
     * @return array<string, ?string> expanded URL keyed by the original URL
     */
    public static function expand_short_urls(array $urls): array
    {
        $expanded_urls = collect($urls)
            ->mapWithKeys(fn (string $url): array => [$url => Str::contains(trim($url), '://') ? trim($url) : 'https://'.trim($url)])
            ->all();

        for ($redirect = 0; $redirect < self::MAX_SHORT_URL_REDIRECTS; $redirect++) {
            $short_urls = array_filter($expanded_urls, fn (?string $url): bool => self::is_short_url($url));

            if (blank($short_urls)) {
                break;
            }

            $responses = Http::pool(fn (Pool $pool): array => collect($short_urls)
                ->map(fn (string $url, string $original_url) => $pool->as($original_url)
                    ->withoutRedirecting()
                    ->withUserAgent(UserAgentHelper::get_random_user_agent())
                    ->timeout(10)
                    ->get($url))
                ->values()
                ->all());

            foreach ($short_urls as $original_url => $url) {
                $response = $responses[$original_url] ?? null;
                $location = $response instanceof Response ? $response->header('Location') : null;

                $expanded_urls[$original_url] = match (true) {
                    blank($location) => null,
                    Str::startsWith($location, '/') => parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).$location,
                    default => $location,
                };
            }
        }

        return collect($expanded_urls)
            ->map(fn (?string $url, string $original_url): ?string => match (true) {
                ! self::is_short_url($original_url) => trim($original_url),
                blank($url), self::is_short_url($url) => null,
                default => $url,
            })
            ->all();
    }

    private function parse_product_key_or_url(): void
    {

        $method = "get_".match (true) {
            Str::contains($this->domain, "aliexpress") => 'aliexpress',
            default => explode(".", $this->domain)[0],
        }."_key";

        if (method_exists($this, $method)) {
            $this->product_key = self::$method();
        } else {
            $this->domain = Str::of($this->parsed_url->host())
                ->remove(["www."])
                ->toString();
            $this->product_key = $this->parsed_url->path();

            $this->product_key .= "?" . http_build_query($this->parsed_url->query()
                    ->toArray());
        }


    }

    private function remove_disallowed_params(): void
    {
        [
            $base_key,
            $params
        ] = array_pad(explode('?', $this->product_key, 2), 2, '');

        parse_str($params, $query);

        if (!$this->store->are_params_allowed) {
            $query = [];
        }
        elseif (filled($this->store->allowed_params)) {
            $query = Arr::only($query, $this->store->allowed_params);
        }

        $this->product_key = filled($query) ? $base_key . '?' . http_build_query($query) : $base_key;
    }

    // predefined stores
    public function get_aliexpress_key(): string
    {
        $this->domain = "aliexpress.com";

        return Str::of($this->parsed_url->path())
            ->remove(".html")
            ->explode('/')
            ->last();
    }

    public function get_argos_key(): string
    {
        return Str::remove("/", Str::squish(Arr::last(explode("/", $this->parsed_url->path()))));
    }

    public function get_ajio_key(): string
    {

        $paths = explode("/p/", $this->parsed_url->path());

        return (count($paths) == 1) ? $paths[0] : $paths[1];
    }

    public function get_amazon_key(): string
    {
        // prepended the path, just to make sure the path doesn't end with dp/
        $temp = Str::of($this->parsed_url->path())
            ->prepend("/")
            ->replace("/gp/product/", "/dp/", false)
            ->explode("/dp/");

        throw_if(count($temp) < 2, CouldntParseProductKeyOrURLException::class);

        return $temp[1];
    }

    public function get_bestbuy_key(): string
    {
        $temp = explode("/", $this->parsed_url->path());

        // todo if same after finishing implementing ca then remove.
        return match ($this->domain) {
            'bestbuy.com' => explode(".", Arr::last($temp))[0],
            'bestbuy.ca' => Arr::last($temp),
        };
    }

    public function get_canadiantire_key(): string
    {
        $temp = Str::of($this->parsed_url->path())
            ->explode("-")
            ->last();

        throw_if(! str_contains($temp, ".html"), CouldntParseProductKeyOrURLException::class);

        return str_replace(".html", "", $temp);
    }

    public function get_costco_key(): string
    {
        return match ($this->domain) {
            "costco.com" => Str::of($this->parsed_url->path())->afterLast('/'),
            "costco.ca" => Str::replace([".", "html"], "", explode(".product", $this->parsed_url->path())[1]),
            "costco.com.mx", "costco.co.uk", "costco.co.kr", "costco.com.tw", "costco.co.jp", "costco.com.au", "costco.is" => explode("/p/", $this->parsed_url->path())[1],
        };
    }

    public function get_currys_key(): string
    {

        $this->domain = "currys.co.uk";

        $paths = Str::of($this->parsed_url->path())
            ->explode("/")
            ->last();

        $sections = Str::of($paths)
            ->explode("-")
            ->last();

        return Str::remove(".html", $sections);
    }

    public function get_diy_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->remove(["/departments/", "_BQ.prd"])
            ->explode("/")
            ->last();
    }

    public function get_ebay_key()
    {
        return Str::of($this->parsed_url->path())
            ->explode('/itm/')
            ->last();
    }

    public function get_emaxme_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->remove(["/", "/html"])
            ->toString();
    }

    public function get_eprice_key(): string
    {
        return "d-".Str::of($this->parsed_url->path())
            ->explode("/d-")
            ->last();
    }

    public function get_flipkart_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->explode('/itm')
            ->last();
    }

    public function get_fnac_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->explode("/")[1];
    }

    public function get_homedepot_key(): string
    {

        $paths = match ($this->domain) {
            'homedepot.ca' => explode('/product/', $this->parsed_url->path()),
            'homedepot.com' => explode('/p/', $this->parsed_url->path()),
        };

        return Str::of($paths[0])
            ->explode("/")
            ->last();
    }

    public function get_mediamarkt_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->remove([".html"])
            ->explode("-")
            ->last();
    }

    public function get_microless_key(): array|string
    {
        $subdomain = explode(".", $this->parsed_url->host())[0];

        $this->store = match ($subdomain) {
            'uae' => Store::where('name', 'Microless UAE')->first(),
            default => Store::where('name', 'Microless Global')->first(),
        };

        return str_replace("/", "", explode("product/", $this->parsed_url->path())[1]);
    }

    public function get_myntra_key(): string
    {

        return Str::of($this->parsed_url->path())
            ->remove(["/buy"])
            ->explode('/')
            ->last();
    }

    public function get_nexths_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->explode("sku/")
            ->last();
    }

    public function get_noon_key(): string
    {

        $temp = Str::of($this->parsed_url->path())
            ->explode("/");

        // update the store for noon, since they use path instead of custom domain per country
        $this->store = match (Str::lower(explode("-", $temp[0])[0])) {
            "uae" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "AED")->first()->id)->first(),
            "egypt" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "EGP")->first()->id)->first(),
            "saudi" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "SAR")->first()->id)->first(),
            "kuwait" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "KWD")->first()->id)->first(),
            "bahrain" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "BHD")->first()->id)->first(),
            "qatar" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "QAR")->first()->id)->first(),
            "oman" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code", "OMR")->first()->id)->first(),
        };

        return Str::lower($temp[count($temp) - 2]);
    }

    public function get_newegg_key(): string
    {

        if (! Str::contains($this->parsed_url->getUri()->toString(), '/global/', true)) {
            $this->store = Store::where('domain', $this->domain)
                ->where('currency_id', Currency::where("code", "USD")->first()->id)
                ->first();
        } else {

            $country = Str::of($this->parsed_url->path())
                ->explode("/")[1];

            $country = explode("-", $country)[0];

            $this->store = match ($country) {
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

        return Str::of($this->parsed_url->path())
            ->explode("/")
            ->last();
    }

    public function get_nykaa_key(): string
    {

        return Str::of($this->parsed_url->path())
            ->explode('/')
            ->last();
    }

    public function get_otakume_key()
    {
        return Str::of($this->parsed_url->path())
            ->explode("/")
            ->last();
    }

    public function get_princessauto_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->remove(".html")
            ->explode("/")
            ->last();
    }

    // todo removed as website blocked in my country. need help with implementation
    //    public function get_snapdeal_key(): string
    //    {
    //        $temp = explode("/", $this->path);
    //
    //        return Arr::last($temp);
    //    }

    public function get_target_key()
    {

        $parts = Str::of($this->parsed_url->path())
            ->explode('/')
            ->last();

        return explode("A-", $parts)[1];

    }

    public function get_tatacliq_key()
    {
        return Str::of($this->parsed_url->path())
            ->explode("/p-mp")
            ->last();

    }

    public function get_walmart_key(): string
    {
        return Str::of($this->parsed_url->path())
            ->explode("/")
            ->last();
    }
}

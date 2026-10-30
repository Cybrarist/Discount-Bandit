<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetExchangePriceTodayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the exchange rate for today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rates = match (config('settings.exchange_rate_provider')) {
            'allratestoday' => $this->allRatesTodayRates(),
            default => $this->exchangeRateApiRates(),
        };

        if ($rates === null) {
            return;
        }

        $rates = Arr::only($rates, Currency::all()->pluck('code')->toArray());

        foreach ($rates as $code => $rate) {
            \App\Models\Currency::updateOrCreate([
                "code" => $code,
            ], [
                "rate" => $rate,
            ]);
        }
    }

    private function exchangeRateApiRates(): ?array
    {
        $response = Http::get("https://v6.exchangerate-api.com/v6/".
            config('settings.exchange_rate_api_key').
            "/latest/USD")->json();

        if (! isset($response['conversion_rates'])) {
            Log::error("Couldn't get the currencies");
            Log::info($response);

            return null;
        }

        return $response["conversion_rates"];
    }

    private function allRatesTodayRates(): ?array
    {
        $response = Http::withToken(config('settings.exchange_rate_api_key'))
            ->get("https://allratestoday.com/api/v1/rates", ["source" => "USD"])
            ->json();

        if (! is_array($response) || ! array_is_list($response)) {
            Log::error("Couldn't get the currencies");
            Log::info($response);

            return null;
        }

        // Rows look like {"source": "USD", "target": "EUR", "rate": 0.89, "time": "..."}.
        return array_merge(['USD' => 1], Arr::pluck($response, 'rate', 'target'));
    }
}

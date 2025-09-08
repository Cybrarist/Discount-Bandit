<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GetExchangePriceTodayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get("https://v6.exchangerate-api.com/v6/".
            config('settings.exchange_rate_api_key').
            "/latest/USD")->json();

        $rates = $response["conversion_rates"];

        $rates = Arr::only($rates, Currency::all()->pluck('code')->toArray());

        foreach ($rates as $code => $rate) {
            \App\Models\Currency::updateOrCreate([
                "code" => $code,
            ], [
                "rate" => $rate,
            ]);
        }
    }
}

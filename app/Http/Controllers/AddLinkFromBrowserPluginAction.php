<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Link;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Services\URLParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddLinkFromBrowserPluginAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, URLParserService $service)
    {

        $validated = $request->validate([
            'current_url' => ['required', 'string', 'url'],
            'name' => ['nullable', 'string', 'max:255'],
            'price_desired' => ['nullable', 'numeric'],
            'percentage_drop' => ['nullable', 'numeric'],
            'lowest_within' => ['nullable', 'numeric'],
            'extra_costs_amount' => ['nullable', 'numeric'],
            'extra_costs_percentage' => ['nullable', 'numeric'],
            'is_in_stock' => ['nullable', 'boolean'],
            'is_official' => ['nullable', 'boolean'],
            'is_shipping_included' => ['nullable', 'boolean'],
            'any_price_change' => ['nullable', 'boolean'],
        ]);

        $service->setup($request->current_url);

        throw_if(! $service->store?->id, "Store Not Found");

        throw_if(
            Auth::user()->role == RoleEnum::User->value &&
            Auth::user()->other_settings['max_links'] &&
            Auth::user()->links()->count() > Auth::user()->other_settings['max_links'],
            "You have reached the maximum number of links"
        );

        DB::transaction(function () use ($validated, $service) {
            $product = Product::firstOrCreate([
                'name' => $validated['name'],
                'user_id' => Auth::user()->id,
            ]);

            $link = Link::firstOrCreate([
                'key' => $service->product_key,
                'store_id' => $service->store->id,
            ]);

            DB::table('link_product')
                ->updateOrInsert([
                    'link_id' => $link->id,
                    'product_id' => $product->id,
                ], [
                    'user_id' => Auth::user()->id,
                ]);

            Arr::forget($validated, [
                'current_url',
                'name',
            ]);

            $temp = array_filter($validated, fn ($value) => filled($value) && $value);

            if (count($temp) == 0) {
                return;
            }

            NotificationSetting::create([
                'price_desired' => $validated['price_desired'],
                'percentage_drop' => $validated['percentage_drop'],
                'price_lowest_in_x_days' => $validated['lowest_within'],
                'is_in_stock' => $validated['is_in_stock'],
                'any_price_change' => $validated['any_price_change'],
                'is_official' => $validated['is_official'],
                'user_id' => Auth::user()->id,
                'extra_costs_amount' => $validated['extra_costs_amount'],
                'extra_costs_percentage' => $validated['extra_costs_percentage'],
                'is_shipping_included' => $validated['is_shipping_included'],
                'link_id' => $link->id,
                'product_id' => $product->id,
            ]);
        });

        return response()->json([
            'message' => 'Link Added Successfully',
        ]);

    }
}

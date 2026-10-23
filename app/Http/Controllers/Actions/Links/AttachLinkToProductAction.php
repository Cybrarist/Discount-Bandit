<?php

namespace App\Http\Controllers\Actions\Links;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AttachLinkToProductAction extends Controller
{
    /**
     * Notification setting fields accepted when attaching a link.
     *
     * @var list<string>
     */
    public const array NOTIFICATION_SETTING_FIELDS = [
        'price_desired',
        'percentage_drop',
        'price_lowest_in_x_days',
        'is_in_stock',
        'is_shipping_included',
        'any_price_change',
        'is_official',
        'extra_costs_amount',
        'extra_costs_percentage',
        'description',
    ];

    /**
     * Find or create the link, attach it to the product for the current user and create its notification setting.
     *
     * @param  array{store_id: int, key: string, price_desired?: ?float, percentage_drop?: ?float, price_lowest_in_x_days?: ?int, is_in_stock?: bool, is_shipping_included?: bool, any_price_change?: bool, is_official?: bool, extra_costs_amount?: ?float, extra_costs_percentage?: ?float, description?: ?string}  $data
     */
    public function __invoke(Product $product, array $data): Link
    {
        $link = Link::updateOrCreate(Arr::only($data, [
            'key',
            'store_id',
        ]));

        $link->products()
            ->syncWithoutDetaching([$product->id => [
                'user_id' => Auth::id(),
            ]]);

        if ($link->image && ! $product->image) {
            $product->image = $link->image;
        }

        if ($link->name && ! $product->name) {
            $product->name = $link->name;
        }

        if ($product->isDirty()) {
            $product->save();
        }

        $link->notification_settings()
            ->create([
                ...Arr::only($data, self::NOTIFICATION_SETTING_FIELDS),
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
            ]);

        return $link;
    }
}

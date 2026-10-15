<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Scopes\LinkScope;
use App\Observers\LinkObserver;
use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy(LinkScope::class)]
#[ObservedBy(LinkObserver::class)]
class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'image',
        'price',
        'used_price',
        'highest_price',
        'lowest_price',
        'shipping_price',
        'rating',
        'total_reviews',
        'seller',
        'condition',
        'is_official',
        'is_in_stock',
        'store_id',
    ];

    protected function casts(): array
    {
        return [
            'price'          => MoneyCast::class,
            'used_price'     => MoneyCast::class,
            'highest_price'  => MoneyCast::class,
            'lowest_price'   => MoneyCast::class,
            'shipping_price' => MoneyCast::class,
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withTimestamps()
            ->withPivot([
                'user_id',
            ]);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function notification_settings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function link_histories(): HasMany
    {
        return $this->hasMany(LinkHistory::class);
    }

    #[Scope]
    protected function orderByPrice(Builder $query): void
    {
        $query->orderByRaw('links.price = 0');

        if (! config('settings.exchange_rate_api_key')) {
            $query->orderBy('links.price');

            return;
        }

        $query->orderByRaw('links.price * 1.0 / (select coalesce(nullif(currencies.rate, 0), 1) from stores join currencies on currencies.id = stores.currency_id where stores.id = links.store_id)');
    }
}

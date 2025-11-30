<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Scopes\LinkScope;
use App\Observers\LinkObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy(LinkScope::class)]
#[ObservedBy(LinkObserver::class)]
class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
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
            'price' => MoneyCast::class,
            'used_price' => MoneyCast::class,
            'highest_price' => MoneyCast::class,
            'lowest_price' => MoneyCast::class,
            'shipping_price' => MoneyCast::class,
        ];
    }


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withTimestamps()
            ->withPivot([
                'user_id'
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
}

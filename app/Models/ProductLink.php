<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy(UserOwnedScope::class)]
class ProductLink extends Model
{
    /** @use HasFactory<\Database\Factories\ProductLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'price',
        'used_price',
        'highest_price',
        'lowest_price',
        'shipping_price',
        'is_in_stock',
        'rating',
        'total_reviews',
        'seller',
        'condition',
        'is_official',
        'store_id',
        'product_id',
        'user_id',
        'key',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function notification_settings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

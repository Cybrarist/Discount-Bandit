<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy(UserOwnedScope::class)]
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'status',
        'user_id',
        'is_favourite',
        'is_in_stock',
        'snoozed_until',
        'max_notifications_daily',
        'lowest_price',
        'highest_price',
        'notifications_sent',

    ];

    protected function casts(): array
    {
        return [
            'snoozed_until' => 'date',
            'status' => ProductStatusEnum::class,
        ];
    }

    public function product_links(): HasMany
    {
        return $this->hasMany(ProductLink::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'remove_link_if_out_of_stock_for_x_days',

    ];

    protected function casts(): array
    {
        return [
            'snoozed_until' => 'date',
            'status' => ProductStatusEnum::class,
        ];
    }

    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class)
            ->withTimestamps()
            ->withPivot([
                'user_id',
            ]);
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

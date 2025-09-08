<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'price_desired',
        'percentage_drop',
        'price_lowest_in_x_days',
        'is_in_stock',
        'any_price_change',
        'is_official',
        'user_id',
        'other_costs_amount',
        'other_costs_percentage',
        'description',
        'is_shipping_included',
        'product_link_id',
    ];

    protected function casts(): array
    {
        return [
            'price_desired' => MoneyCast::class,
            'percentage_drop' => MoneyCast::class,
            'other_costs_amount' => MoneyCast::class,
            'other_costs_percentage' => MoneyCast::class,
            'is_in_stock' => 'boolean',
            'any_price_change' => 'boolean',
            'is_official' => 'boolean',
            'is_shipping_included' => 'boolean',
        ];
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product_store(): BelongsTo
    {
        return $this->belongsTo(ProductStore::class);
    }
}

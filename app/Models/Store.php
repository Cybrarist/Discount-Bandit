<?php

namespace App\Models;

use App\Enums\CrawlingMethodEnum;
use App\Observers\StoreObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(StoreObserver::class)]
class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'image',
        'slug',
        'referral',
        'status',
        'user_id',
        'currency_id',
        'are_params_allowed',
        'allowed_params',
        'custom_settings',
        'custom_settings.crawling_method',
        'custom_settings.crawling_chrome.timeout',
        'custom_settings.crawling_chrome.page_event',
        'custom_settings.extra_headers',
        'custom_settings.user_agents',
        'custom_settings.name_selectors',
        'custom_settings.name_schema_key',
        'custom_settings.image_selectors',
        'custom_settings.image_schema_key',
        'custom_settings.total_reviews_selectors',
        'custom_settings.total_reviews_schema_key',
        'custom_settings.rating_selectors',
        'custom_settings.rating_schema_key',
        'custom_settings.price_selectors',
        'custom_settings.price_schema_key',
        'custom_settings.seller_selectors',
        'custom_settings.seller_schema_key',
        'custom_settings.used_price_selectors',
        'custom_settings.used_schema_key',
        'custom_settings.shipping_price_selectors',
        'custom_settings.shipping_schema_key',
        'custom_settings.stock_selectors',
        'custom_settings.stock_schema_key',
        'custom_settings.condition_selectors',
        'custom_settings.condition_schema_key',
    ];

    protected function casts(): array
    {
        return [
            'custom_settings' => 'json',
            'custom_settings.crawling_method' => CrawlingMethodEnum::class,
            'allowed_params' => 'array',
            'are_params_allowed' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }
}

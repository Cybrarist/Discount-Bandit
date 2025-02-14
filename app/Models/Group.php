<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use App\Observers\GroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


#[ObservedBy(GroupObserver::class)]
class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "notify_price",
        "currency_id",
        "status",
        "snoozed_until",
        "max_notifications",
        "notifications_sent",
        "lowest_within",
        "current_price",
        "lowest_price",
        "highest_price",
        "notify_percentage",
    ];

    protected function casts(): array
    {
        return [
            "snoozed_until" => "date:Y-m-d",
            "notify_price" => Money::class,
            "current_price" => Money::class,
            "lowest_price" => Money::class,
            "highest_price" => Money::class,
            "status" => StatusEnum::class,
        ];
    }


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot("key");
    }
}

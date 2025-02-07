<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPriceHistory extends Model
{
    /** @use HasFactory<\Database\Factories\GroupPriceHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'price',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => Money::class,
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

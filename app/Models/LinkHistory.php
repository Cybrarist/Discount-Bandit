<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkHistory extends Model
{
    /** @use HasFactory<\Database\Factories\LinkHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'link_id',
        'price',
        'date',
        'used_price',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'used_price' => MoneyCast::class,
            'date' => 'date',
        ];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}

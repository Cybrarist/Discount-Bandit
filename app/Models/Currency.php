<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'rate',
    ];

    //    protected function casts(): array
    //    {
    //        return [
    //            'rate'=>MoneyCast::class
    //        ];
    //    }

    protected function getCurrencySymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }

    protected function getCodeNameAttribute(): string
    {
        return $this->code.' - '.$this->name;
    }
}

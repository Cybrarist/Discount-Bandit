<?php

namespace App\Http\Filters;

use App\Services\URLParserService;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;
use Throwable;

class FiltersLinkByUrl implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $service = new URLParserService;
        try {
            $service->setup($value);
            $query->where('key', $service->product_key);
        } catch (Throwable) {
            $query->whereRaw('0 = 1');
        }
    }
}

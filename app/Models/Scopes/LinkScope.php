<?php

namespace App\Models\Scopes;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LinkScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {

        if (Auth::check() && Auth::user()->role == RoleEnum::Admin)
            return;

        $builder->whereExists(function ($query) {
            $query->selectRaw(1)
                ->from('link_product')
                ->whereColumn('link_product.link_id', 'links.id')
                ->where('link_product.user_id', Auth::id());
        });
    }
}

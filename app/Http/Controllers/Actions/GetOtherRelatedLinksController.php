<?php

namespace App\Http\Controllers\Actions;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetOtherRelatedLinksController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Link $link)
    {
        $product_have_the_link = DB::table('link_product')
            ->where('link_id', $link->id)
            ->pluck('product_id');

        $links = Product::withoutGlobalScopes()
            ->where('products.user_id', Auth::id())
            ->whereIn('products.id', $product_have_the_link)
            ->join('link_product', 'products.id', '=', 'link_product.product_id')
            ->pluck('link_product.link_id');

        return Link::whereIn('id', $links)
            ->whereNot('id', $link->id)
            ->with([
                'store',
                'store.currency',
            ])
            ->get()
            ->map(function ($link) {
                $link->url = LinkHelper::get_url($link);

                return $link;
            });

    }
}

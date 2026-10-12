<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Filters\FiltersLinkByUrl;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Services\URLParserService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class LinkController extends Controller
{
    /**
     * Get available links for the user as pagination
     */
    #[QueryParameter(name: 'filter[name]', description: 'search for links with name', type: 'string', default: '', example: 'bat')]
    #[QueryParameter(name: 'filter[seller]', description: 'search for links with seller', type: 'string', default: '', example: 'man')]
    #[QueryParameter(name: 'filter[is_favourite]', description: 'search for favourite links', type: 'bool', default: '', example: false)]
    #[QueryParameter(name: 'filter[is_official]', description: 'search for links sold by official sellers only', type: 'bool', default: '', example: false)]
    #[QueryParameter(name: 'filter[is_in_stock]', description: 'search for links sold that are in stock', type: 'bool', default: '', example: false)]
    #[QueryParameter(name: 'filter[store_id]', description: 'search for links with specific stores', type: 'array', default: [], example: [1])]

    #[QueryParameter(name: 'sort', description: 'sort grades', type: 'string', default: 'id', example: '-id')]
    #[QueryParameter(name: 'include', description: 'include relations', type: 'array', default: [], example: ['store', 'products', 'notification_settings', 'link_histories'])]
    public function index(Request $request)
    {
        $links = QueryBuilder::for(Link::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('is_favourite'),
                AllowedFilter::exact('is_official'),
                AllowedFilter::exact('is_in_stock'),
                AllowedFilter::partial('seller'),
                AllowedFilter::exact('store_id'),

                AllowedFilter::custom('key', new FiltersLinkByUrl),
            )
            ->allowedSorts('id', 'price', 'lowest_price', 'highest_price', 'created_at', 'store_id')
            ->allowedIncludes('store', 'products', 'notification_settings', 'link_histories')
            ->defaultSort('-id')
            ->paginate(20)
            ->appends($request->query());

        return LinkResource::collection($links);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLinkRequest $request, URLParserService $service)
    {
        $validated = $request->validated();

        $service->setup($validated['url']);

        abort_if(! $service->store?->id, Response::HTTP_NOT_FOUND, "Store Not Found");

        abort_if(Auth::user()->role == RoleEnum::User->value &&
            Auth::user()->other_settings['max_links'] &&
            Auth::user()->links()->count() > Auth::user()->other_settings['max_links'],
            Response::HTTP_NOT_ACCEPTABLE,
            "You have reached the maximum number of links"
        );

        try {
            DB::transaction(function () use ($validated, $service) {

                if (array_key_exists('product_id', $validated) && $validated['product_id']) {
                    $product = Auth::user()->products()->find($validated['product_id']);
                } else {
                    $product = Auth::user()->products()->create(
                        array_key_exists('product_name', $validated) && $validated['product_name']
                            ? ['name' => $validated['product_name']]
                            : []
                    );
                }

                $link = $product->links()->firstOrCreate([
                    'key' => $service->product_key,
                    'store_id' => $service->store->id,
                ]);

                DB::table('link_product')
                    ->updateOrInsert([
                        'link_id' => $link->id,
                        'product_id' => $product->id,
                    ], [
                        'user_id' => Auth::user()->id,
                    ]);

                Arr::forget($validated, [
                    'url', 'product_name', 'product_id',
                ]);

                $temp = array_filter($validated, fn ($value) => filled($value) && $value);

                if (count($temp) == 0) {
                    return;
                }

                $link->notification_settings()->create([
                    ...$validated,
                    'user_id' => Auth::user()->id,
                    'product_id' => $product->id,
                ]);
            });

        } catch (\Exception $e) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        return response()->noContent();
    }

    /**
     * Display the specified resource.
     */
    public function show(Link $link)
    {
        $link->load([
            'store', 'products', 'notification_settings',
            'link_histories',
        ]);

        return LinkResource::make($link);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLinkRequest $request, Link $link)
    {
        // link is never updated
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link)
    {
        //
    }
}

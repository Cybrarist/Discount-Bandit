<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreController extends Controller
{
    /**
     * Get available stores as pagination.
     */
    #[QueryParameter(name: 'filter[name]', description: 'search for stores by name', type: 'string', default: '', example: 'amazon')]
    #[QueryParameter(name: 'filter[domain]', description: 'search for stores by domain', type: 'string', default: '', example: 'amazon.com')]
    #[QueryParameter(name: 'filter[status]', description: 'filter stores by status', type: 'string', default: '', example: 'active')]
    #[QueryParameter(name: 'sort', description: 'sort stores', type: 'string', default: 'name', example: '-name')]
    #[QueryParameter(name: 'include', description: 'include relations', type: 'array', default: [], example: ['currency', 'links'])]
    public function index(Request $request)
    {
        $stores = QueryBuilder::for(Store::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('domain'),
                AllowedFilter::exact('status'),
            )
            ->allowedSorts('id', 'name', 'status', 'created_at')
            ->allowedIncludes('currency', 'links')
            ->defaultSort('name')
            ->paginate(20)
            ->appends($request->query());

        return StoreResource::collection($stores);
    }

    /**
     * Store a newly created store.
     */
    public function store(StoreStoreRequest $request)
    {
        Gate::authorize('create', Store::class);

        $store = Store::create($request->validated());

        return StoreResource::make($store);
    }

    /**
     * Display the specified store.
     */
    public function show(Store $store)
    {
        $store->load('currency', 'links');

        return StoreResource::make($store);
    }

    /**
     * Update the specified store.
     */
    public function update(UpdateStoreRequest $request, Store $store)
    {
        Gate::authorize('update', $store);

        $store->update($request->validated());

        return StoreResource::make($store);
    }

    /**
     * Remove the specified store.
     */
    public function destroy(Store $store)
    {
        Gate::authorize('delete', $store);

        $store->delete();

        return response()->noContent();
    }
}

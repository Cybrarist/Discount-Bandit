<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\URLParserService;
use Illuminate\Http\Request;

class GetLinkUsingUrlController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, URLParserService $service)
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'string'],
        ]);

        $service->setup($validated['url']);

        return Link::with(['store', 'store.currency'])
            ->firstWhere([
                'store_id' => $service->store?->id,
                'key' => $service->product_key,
            ]);

    }
}

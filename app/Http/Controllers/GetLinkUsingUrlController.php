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

        $link = Link::with(['store', 'store.currency'])
            ->firstWhere([
                'store_id' => $service->store?->id,
                'key' => $service->product_key,
            ]);

        if (! $link)
            return response()->json(['message' => 'Link not found'], 404);

        $link->url = route('filament.admin.resources.links.edit', $link);

        return $link;
    }
}

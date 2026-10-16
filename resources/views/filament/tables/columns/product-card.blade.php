@php
    use App\Enums\ProductStatusEnum;
    use App\Helpers\LinkHelper;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;

    $record = $getRecord();
    $editUrl = route('filament.admin.resources.products.edit', ['record' => $record]);

    $user = auth()->user();
    $userRate = $user->currency_id && $user->currency?->rate ? $user->currency->rate : 1;
    $userCode = $user->currency_id && $user->currency?->rate ? $user->currency->code : null;

    $links = $record->links;
    $visibleLinks = $links->reject(fn ($link) => $link->has_price_update_since);

    $convert = fn ($link, $amount) => $amount * $userRate / ($link->store->currency->rate ?: 1);
    $currencyOf = fn ($link) => $userCode ?? $link->store->currency->code;

    $pricedLinks = $links->filter(fn ($link) => $link->price > 0);
    $bestLink = $pricedLinks->first(fn ($link) => $link->is_in_stock) ?? $pricedLinks->first();

    $isOutOfStock = $pricedLinks->isNotEmpty() && $pricedLinks->doesntContain(fn ($link) => $link->is_in_stock);
    $isAllTimeLow = $bestLink && ! $isOutOfStock && $bestLink->lowest_price > 0 && $bestLink->price <= $bestLink->lowest_price;

    $bestPrice = $bestLink ? $convert($bestLink, $bestLink->price) : null;
    $previousPrice = $bestLink && $bestLink->highest_price > $bestLink->price ? $convert($bestLink, $bestLink->highest_price) : null;
    $discountPercentage = $previousPrice ? round(($previousPrice - $bestPrice) / $previousPrice * 100) : null;
    $status = $record->status;
@endphp

<div class="fi-product-card group flex h-full w-full flex-col">
    {{-- Image --}}
    <a href="{{ $editUrl }}" class="relative block aspect-[16/10] w-full overflow-hidden bg-gray-100 dark:bg-white/5">
        @if ($record->image)
            <img
                src="{{ $record->image }}"
                alt="{{ $record->name }}"
                loading="lazy"
                @class([
                    'size-full object-contain p-4 transition-transform duration-500 group-hover:scale-105',
                    'opacity-60 grayscale' => $isOutOfStock,
                ])
            />
        @else
            <div class="flex size-full items-center justify-center text-gray-300 dark:text-gray-600">
                <x-filament::icon :icon="Heroicon::OutlinedPhoto" class="size-12" />
            </div>
        @endif

        <div class="absolute bottom-3 start-3 flex flex-wrap gap-1.5">
            @if ($isOutOfStock)
                <x-filament::badge color="gray">Out of Stock</x-filament::badge>
            @elseif ($isAllTimeLow)
                <x-filament::badge color="primary">All-time Low</x-filament::badge>
            @endif

            @if ($status && $status !== ProductStatusEnum::Active)
                <x-filament::badge :color="$status->getColor()">{{ $status->getLabel() }}</x-filament::badge>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        {{-- Title --}}
        <div class="mb-2 flex items-start justify-between gap-2">
            <a href="{{ $editUrl }}" title="{{ $record->name }}" class="line-clamp-2 text-lg font-bold leading-tight text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400">
                {{ $record->name ?? 'Fetching....' }}
            </a>

            <button
                type="button"
                title="{{ $record->is_favourite ? 'Remove from favourites' : 'Add to favourites' }}"
                wire:click="toggle_favourite(@js((string) $record->getKey()))"
                class="shrink-0 text-primary-500 transition hover:scale-110"
            >
                <x-filament::icon :icon="$record->is_favourite ? Heroicon::Star : Heroicon::OutlinedStar" class="size-5" />
            </button>
        </div>

        {{-- Best price --}}
        <div class="mb-4 flex items-baseline gap-2">
            @if ($bestLink)
                <span @class([
                    'text-2xl font-black',
                    'text-gray-400 dark:text-gray-500' => $isOutOfStock,
                    'text-primary-600 dark:text-primary-400' => ! $isOutOfStock && $discountPercentage,
                    'text-gray-950 dark:text-white' => ! $isOutOfStock && ! $discountPercentage,
                ])>{{ Number::currency($bestPrice, $currencyOf($bestLink)) }}</span>

                @if ($isOutOfStock)
                    <span class="text-xs font-medium text-danger-600 dark:text-danger-400">Unavailable</span>
                @elseif ($previousPrice)
                    <span class="text-sm text-gray-400 line-through dark:text-gray-500">{{ Number::currency($previousPrice, $currencyOf($bestLink)) }}</span>
                    <span class="ms-auto rounded bg-success-100 px-1.5 py-0.5 text-xs font-bold text-success-700 dark:bg-success-500/20 dark:text-success-400">-{{ $discountPercentage }}%</span>
                @endif
            @else
                <span class="text-sm text-gray-400 dark:text-gray-500">No price yet</span>
            @endif
        </div>

        {{-- Tracked links, cheapest first, scrolls after 3 --}}
        <div class="mt-auto flex flex-col gap-2 border-t border-gray-100 pt-2 dark:border-white/10">
            <p class="mb-1 text-xs font-bold uppercase tracking-widest text-gray-400">Tracked Links ({{ $links->count() }})</p>

            <div
                x-data="{
                    maxHeight: null,
                    measure() {
                        const rows = this.$el.children
                        this.maxHeight = rows.length > 3
                            ? rows[2].getBoundingClientRect().bottom - this.$el.getBoundingClientRect().top
                            : null
                    },
                }"
                x-init="$nextTick(() => measure())"
                x-on:resize.window.debounce.150ms="measure()"
                x-bind:style="maxHeight ? { maxHeight: `${maxHeight}px` } : {}"
                class="flex flex-col gap-1 overflow-y-auto pe-1"
            >
                @forelse ($visibleLinks as $link)
                    @php
                        $code = $currencyOf($link);
                        $isCheapest = $bestLink && $link->is($bestLink) && ! $isOutOfStock;
                    @endphp

                    <a
                        href="{{ LinkHelper::get_url($link) }}"
                        target="_blank"
                        class="group/link block rounded-lg border border-transparent p-2 transition hover:border-gray-200 hover:bg-gray-50 dark:hover:border-white/10 dark:hover:bg-white/5"
                    >
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="flex min-w-0 items-center gap-2">
                                @if ($link->store->image)
                                    <img src="{{ Storage::disk('store')->url($link->store->image) }}" alt="" class="size-6 shrink-0 rounded bg-white object-contain ring-1 ring-gray-950/5" />
                                @else
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded bg-gray-100 text-[9px] font-bold text-gray-700 ring-1 ring-gray-950/5 dark:bg-white/10 dark:text-gray-200">
                                        {{ Str::upper(Str::substr($link->store->name, 0, 3)) }}
                                    </span>
                                @endif

                                <span class="truncate text-sm font-bold text-gray-700 transition group-hover/link:text-primary-600 dark:text-gray-300 dark:group-hover/link:text-primary-400">
                                    {{ $link->store->name }} ({{ $code }})
                                </span>
                            </div>

                            <x-filament::icon :icon="Heroicon::ArrowUpRight" class="size-4 shrink-0 text-gray-400 transition group-hover/link:text-primary-500" />
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-400 dark:text-gray-500">Low</span>
                                <span class="font-bold text-success-600 dark:text-success-400">{{ $link->lowest_price > 0 ? Number::currency($convert($link, $link->lowest_price), $code) : '—' }}</span>
                            </div>

                            <div class="flex flex-col border-s border-gray-100 ps-2 dark:border-white/10">
                                <span class="font-medium text-gray-400 dark:text-gray-500">High</span>
                                <span class="font-bold text-danger-600 dark:text-danger-400">{{ $link->highest_price > 0 ? Number::currency($convert($link, $link->highest_price), $code) : '—' }}</span>
                            </div>

                            <div class="flex flex-col border-s border-gray-100 ps-2 text-end dark:border-white/10">
                                <span class="font-medium text-gray-400 dark:text-gray-500">Current</span>

                                @if ($link->price <= 0)
                                    <span class="font-bold text-gray-400">—</span>
                                @elseif (! $link->is_in_stock)
                                    <span class="pt-0.5 font-bold text-gray-400">Out of Stock</span>
                                @else
                                    <span @class([
                                        'text-sm font-bold',
                                        'text-primary-600 dark:text-primary-400' => $isCheapest,
                                        'text-gray-700 dark:text-gray-300' => ! $isCheapest,
                                    ])>{{ Number::currency($convert($link, $link->price), $code) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="py-2 text-xs text-gray-400">No links tracked yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Collapsible price history chart, loaded only when opened --}}
        <x-product-price-history-chart
            class="mt-3"
            :product-key="$record->getKey()"
            :product-name="$record->name"
        />
    </div>
</div>

@php
    use App\Helpers\LinkHelper;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;

    $record = $getRecord();
    $editUrl = route('filament.admin.resources.links.edit', ['record' => $record]);

    $user = auth()->user();
    $userRate = $user->currency_id && $user->currency?->rate ? $user->currency->rate : 1;
    $currencyCode = $user->currency_id && $user->currency?->rate ? $user->currency->code : $record->store->currency->code;

    $convert = fn ($amount) => $amount * $userRate / ($record->store->currency->rate ?: 1);

    $hasPrice = $record->price > 0;
    $isOutOfStock = $hasPrice && ! $record->is_in_stock;
    $isAllTimeLow = $hasPrice && ! $isOutOfStock && $record->lowest_price > 0 && $record->price <= $record->lowest_price;

    $currentPrice = $hasPrice ? $convert($record->price) : null;
    $previousPrice = $hasPrice && $record->highest_price > $record->price ? $convert($record->highest_price) : null;
    $discountPercentage = $previousPrice ? round(($previousPrice - $currentPrice) / $previousPrice * 100) : null;
@endphp

<div class="fi-link-card group flex h-full w-full flex-col">
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
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        {{-- Title --}}
        <a href="{{ $editUrl }}" title="{{ $record->name }}" class="mb-2 line-clamp-2 text-lg font-bold leading-tight text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400">
            {{ $record->name ?? 'Fetching....' }}
        </a>

        {{-- Store, opens the product page on the store --}}
        <a
            href="{{ LinkHelper::get_url($record) }}"
            target="_blank"
            class="group/store mb-3 flex min-w-0 items-center gap-2"
        >
            @if ($record->store->image)
                <img src="{{ Storage::disk('store')->url($record->store->image) }}" alt="" class="size-6 shrink-0 rounded bg-white object-contain ring-1 ring-gray-950/5" />
            @else
                <span class="flex size-6 shrink-0 items-center justify-center rounded bg-gray-100 text-[9px] font-bold text-gray-700 ring-1 ring-gray-950/5 dark:bg-white/10 dark:text-gray-200">
                    {{ Str::upper(Str::substr($record->store->name, 0, 3)) }}
                </span>
            @endif

            <span class="truncate text-sm font-bold text-gray-700 transition group-hover/store:text-primary-600 dark:text-gray-300 dark:group-hover/store:text-primary-400">
                {{ $record->store->name }} ({{ $currencyCode }})
            </span>

            <x-filament::icon :icon="Heroicon::ArrowUpRight" class="size-4 shrink-0 text-gray-400 transition group-hover/store:text-primary-500" />
        </a>

        {{-- Current price --}}
        <div class="mb-4 flex items-baseline gap-2">
            @if ($hasPrice)
                <span @class([
                    'text-2xl font-black',
                    'text-gray-400 dark:text-gray-500' => $isOutOfStock,
                    'text-primary-600 dark:text-primary-400' => ! $isOutOfStock && $discountPercentage,
                    'text-gray-950 dark:text-white' => ! $isOutOfStock && ! $discountPercentage,
                ])>{{ Number::currency($currentPrice, $currencyCode) }}</span>

                @if ($isOutOfStock)
                    <span class="text-xs font-medium text-danger-600 dark:text-danger-400">Unavailable</span>
                @elseif ($previousPrice)
                    <span class="text-sm text-gray-400 line-through dark:text-gray-500">{{ Number::currency($previousPrice, $currencyCode) }}</span>
                    <span class="ms-auto rounded bg-success-100 px-1.5 py-0.5 text-xs font-bold text-success-700 dark:bg-success-500/20 dark:text-success-400">-{{ $discountPercentage }}%</span>
                @endif
            @else
                <span class="text-sm text-gray-400 dark:text-gray-500">No price yet</span>
            @endif
        </div>

        {{-- Collapsible price history chart, loaded only when opened --}}
        <x-link-price-history-chart
            class="mt-auto"
            :link-key="$record->getKey()"
            :link-name="$record->name"
        />
    </div>
</div>

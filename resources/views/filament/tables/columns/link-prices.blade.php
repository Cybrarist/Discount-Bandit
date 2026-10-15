@php
    use Illuminate\Support\Number;
    use App\Helpers\LinkHelper;

    $user     = auth()->user();
    $userRate = $user->currency_id && $user->currency?->rate ? $user->currency->rate : 1;
    $userCode =  $user->currency_id && $user->currency?->rate ?$user->currency->code : null;
@endphp

<div class="flex flex-col w-full space-y-4">

    <div class="grid grid-cols-3 md:grid-cols-4 w-full gap-x-4   dark:border-white/10 pb-1 text-xs font-semibold">
        <div class="hidden md:block!">Store</div>
        <div>Highest</div>
        <div>Current</div>
        <div>Best</div>
    </div>

    @php
        $days  = $column->getTable()->getLivewire()->tableFilters['no_price_update']['days'] ?? null;
        $since = $days ? now()->subDays((int) $days)->toDateString() : null;

        $visibleLinks = $since
            ? $getState()->filter(fn ($link) => $link->link_histories->where('date', '>=', $since)->isEmpty())
            : $getState();
    @endphp

    {{-- One row per link (ordered in ProductsTable), scrolls after 5 rows. --}}
    <div
        x-data="{
            maxHeight: null,
            measure() {
                const rows = this.$el.children
                this.maxHeight = rows.length > 5
                    ? rows[4].getBoundingClientRect().bottom - this.$el.getBoundingClientRect().top
                    : null
            },
        }"
        x-init="$nextTick(() => measure())"
        x-on:resize.window.debounce.150ms="measure()"
        x-bind:style="maxHeight ? { maxHeight: `${maxHeight}px` } : {}"
        class="flex flex-col gap-4 overflow-y-auto pe-2"
    >
    @foreach ($visibleLinks ?? [] as $link)
        @php
            $storeRate = $link->store->currency->rate ?: 1;
            $factor  = $userRate / $storeRate;

            $code      = $userCode ?? $link->store->currency->code;
            $highest   = $link->highest_price * $factor;
            $current   = $link->price * $factor;
            $lowest    = $link->lowest_price * $factor;

        @endphp

        <div class="grid grid-cols-3 md:grid-cols-4 gap-4 md:gap-2 text-xs md:text-sm hover:opacity-80 ">
            <div class="col-span-full md:col-span-1">
                <a href="{{ LinkHelper::get_url($link) }}"
                   target="_blank"
                   class="underline text-primary-400 hover:text-primary-300">
                    {{ $link->store->name }} ({{$code}})
                </a>
            </div>
            <div class="text-danger-500">{{ Number::format($highest) }}</div>
            <div>{{ Number::format($current) }}</div>
            <div class="text-success-500">{{ Number::format($lowest) }}</div>
        </div>
    @endforeach
    </div>
</div>

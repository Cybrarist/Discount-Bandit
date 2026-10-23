{{--
    Collapsible price history chart for a product card, with a fullscreen lightbox. Nothing is mounted or queried
    until the user opens one of them; the data then comes from the renderless ListProducts::price_history_series().
    wire:ignore keeps the loaded chart when Livewire re-renders the table (e.g. after toggling favourite).
    The lightbox isn't teleported: the apexcharts Alpine component needs $wire, which only resolves inside the
    Livewire component's DOM. A fixed overlay still covers the viewport since no card ancestor is transformed.
--}}
@props([
    'productKey',
    'productName' => null,
])

@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Icons\Heroicon;

    $chartId = 'product-price-history-chart-'.$productKey;

    $chartOptions = [
        'chart' => [
            'type' => 'area',
            'height' => 200,
            'fontFamily' => 'inherit',
            'toolbar' => ['show' => false],
            'zoom' => ['enabled' => false],
        ],
        'xaxis' => ['type' => 'datetime'],
        'yaxis' => ['decimalsInFloat' => 2],
        'stroke' => ['curve' => 'smooth', 'width' => 2],
        'dataLabels' => ['enabled' => false],
        'legend' => ['fontSize' => '11px'],
        'tooltip' => [
            'shared' => true,
            'intersect' => false,
            'x' => ['format' => 'yyyy-MM-dd'],
        ],
    ];
@endphp

<div
    wire:ignore
    x-data="{
        isOpen: false,
        isFullscreen: false,
        isLoading: false,
        hasFailed: false,
        series: null,
        baseOptions: @js($chartOptions),
        async load() {
            if (this.series !== null || this.isLoading) {
                return
            }

            this.isLoading = true
            this.hasFailed = false

            try {
                this.series = await this.$wire.price_history_series(@js((int) $productKey))
            } catch (error) {
                this.hasFailed = true
            } finally {
                this.isLoading = false
            }
        },
        toggle() {
            this.isOpen = ! this.isOpen

            if (this.isOpen) {
                this.load()
            }
        },
        open_fullscreen() {
            this.isFullscreen = true
            this.load()
        },
        chart_options(overrides = {}) {
            return {
                ...this.baseOptions,
                chart: { ...this.baseOptions.chart, ...overrides },
                series: JSON.parse(JSON.stringify(this.series)),
            }
        },
        // wide 2.5:1 rectangle on desktop; close to square on phones, where 2.5:1 would squish the plot.
        // never shorter than 260px or taller than 70% of the window
        fullscreen_chart_height() {
            const isNarrow = window.innerWidth < 640
            const chartWidth = Math.min(window.innerWidth - (isNarrow ? 40 : 104), 1600)
            const height = chartWidth / (isNarrow ? 1.1 : 2.5)

            return Math.round(Math.min(Math.max(height, 260), window.innerHeight * 0.7))
        },
        get hasData() {
            return (this.series ?? []).some((line) => line.data.length > 0)
        },
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', isFullscreen)"
    x-on:keydown.escape.window="isFullscreen = false"
    {{ $attributes->class(['border-t border-gray-100 pt-2 dark:border-white/10']) }}
>
    <div class="flex items-center gap-1">
        <button
            type="button"
            x-on:click="toggle()"
            class="flex flex-1 items-center justify-between py-1 text-xs font-bold uppercase tracking-widest text-gray-400 transition hover:text-primary-600 dark:hover:text-primary-400"
        >
            <span>Price History</span>
            <x-filament::icon
                :icon="Heroicon::ChevronDown"
                class="size-4 transition-transform duration-200"
                x-bind:class="isOpen && 'rotate-180'"
            />
        </button>

        <button
            type="button"
            title="Open fullscreen"
            x-on:click="open_fullscreen()"
            class="rounded p-1 text-gray-400 transition hover:scale-110 hover:bg-gray-100 hover:text-primary-600 dark:hover:bg-white/5 dark:hover:text-primary-400"
        >
            <x-filament::icon :icon="Heroicon::ArrowsPointingOut" class="size-4" />
        </button>
    </div>

    {{-- Inline chart --}}
    <div x-cloak x-show="isOpen" x-transition.opacity class="pt-2">
        <div x-show="isLoading" class="flex h-[200px] items-center justify-center">
            <x-filament::loading-indicator class="size-6 text-gray-400" />
        </div>

        <p x-show="hasFailed" class="py-4 text-center text-xs text-danger-600 dark:text-danger-400">Couldn't load the price history.</p>

        <p x-show="series !== null && ! hasData" class="py-4 text-center text-xs text-gray-400">No price history in the last 6 months.</p>

        <template x-if="isOpen && hasData">
            <div class="w-full">
                <div
                    x-ignore
                    x-load
                    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('apexcharts') }}"
                    x-data="apexcharts({
                        options: chart_options(),
                        chartId: '#{{ $chartId }}',
                        theme: document.querySelector('html').matches('.dark') ? 'dark' : 'light',
                        extraJsOptions: {
                            yaxis: { labels: { formatter: (value) => value.toLocaleString('en-US') } },
                        },
                    })"
                ></div>

                <div id="{{ $chartId }}"></div>
            </div>
        </template>
    </div>

    {{-- Fullscreen lightbox --}}
    <div
        x-cloak
        x-show="isFullscreen"
        x-transition.opacity
        x-on:click.self="isFullscreen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 p-2 backdrop-blur-sm sm:p-8"
    >
        <div class="flex max-h-full w-full max-w-[1600px] flex-col rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-4 py-3 sm:px-5 dark:border-white/10">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Price History · last 6 months</p>
                    <h2 class="truncate text-lg font-bold text-gray-950 dark:text-white">{{ $productName ?? 'Product' }}</h2>
                </div>

                <button
                    type="button"
                    title="Close"
                    x-on:click="isFullscreen = false"
                    class="shrink-0 rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-200"
                >
                    <x-filament::icon :icon="Heroicon::XMark" class="size-5" />
                </button>
            </div>

            <div class="p-3 sm:p-5">
                <div x-show="isLoading" class="flex h-[40vh] items-center justify-center">
                    <x-filament::loading-indicator class="size-8 text-gray-400" />
                </div>

                <p x-show="hasFailed" class="py-16 text-center text-sm text-danger-600 dark:text-danger-400">Couldn't load the price history.</p>

                <p x-show="series !== null && ! hasData" class="py-16 text-center text-sm text-gray-400">No price history in the last 6 months.</p>

                {{-- created on open and removed on close, so it always fits the current window size --}}
                <template x-if="isFullscreen && hasData">
                    <div class="w-full">
                        <div
                            x-ignore
                            x-load
                            x-load-src="{{ FilamentAsset::getAlpineComponentSrc('apexcharts') }}"
                            x-data="apexcharts({
                                options: chart_options({
                                    height: fullscreen_chart_height(),
                                    toolbar: { show: true },
                                    zoom: { enabled: true },
                                }),
                                chartId: '#{{ $chartId }}-fullscreen',
                                theme: document.querySelector('html').matches('.dark') ? 'dark' : 'light',
                                extraJsOptions: {
                                    yaxis: { labels: { formatter: (value) => value.toLocaleString('en-US') } },
                                },
                            })"
                        ></div>

                        <div id="{{ $chartId }}-fullscreen"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@use(Boquizo\FilamentLogViewer\Utils\Icons)
@use(Filament\Support\Contracts\ScalableIcon)
@use(Filament\Support\Enums\IconSize)
@php
    $log = $log ?? null;
    $timezone = $timezone ?? Config::string('app.timezone', 'UTC');
    $entries = $log?->toModel() ?? [];
    $colors = Config::array('filament-log-viewer.colors.levels', []);
    $icons = collect(Config::array('filament-log-viewer.icons', []))
        ->map(fn (string|ScalableIcon $icon, string $key) => (string) Icons::get($key, IconSize::Small));
@endphp
@if ($log)
    <div class="space-y-4">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4">
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                        {{ __('filament-log-viewer::log.table.detail.file_path') }}
                    </dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100 break-all">
                        {{ $log->path() }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                        {{ __('filament-log-viewer::log.table.detail.log_entries') }}
                    </dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        {{ $log->entries()->count() }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                        {{ __('filament-log-viewer::log.table.detail.size') }}
                    </dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        {{ $log->size() }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                        {{ __('filament-log-viewer::log.table.detail.updated_at') }}
                    </dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        {{ $log->updatedAt() }} <span class="text-gray-400">({{ $timezone }})</span>
                    </dd>
                </div>
            </dl>
        </div>

        <div
            x-data="{
                search: '',
                scrollContainer: null,
                colors: {{ json_encode($colors) }},
                icons: {{ json_encode($icons) }},
                formatStack(stack) {
                    if (!stack) {
                        return '';
                    }
                    let safe = stack.replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');

                    return safe.replace(
                        /(.*vendor.*)/gm,
                        '<span class=\'opacity-40 text-gray-400\'>$1</span>',
                    );
                },
                get filteredEntries() {
                    const term = this.search.toLowerCase();
                    if (!term) {
                        return {{ json_encode($entries) }};
                    }
                    return {{ json_encode($entries) }}
                        .filter(e => {
                            const text = [
                                e.level ?? '',
                                e.datetime ?? '',
                                e.header ?? '',
                                this.formatStack(e.stack) ?? '',
                                e.context ?? ''
                            ]
                        .join(' ')
                        .toLowerCase();

                        return text.includes(term);
                    });
                },
                getColor(level) {
                    return this.colors[level] ?? this.colors['info'];
                },
                getIcon(level) {
                    return this.icons[level] ?? this.icons['info'];
                },
                scrollToTop() {
                    this.$refs.scrollContainer?.scrollTo({ top: 0, behavior: 'smooth' });
                },
                scrollToBottom() {
                    const el = this.$refs.scrollContainer;
                    if (el) {
                        el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                    }
                }
            }"
            class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
            <div class="flex items-center gap-2 p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex-1 relative">
                    <input
                        type="search"
                        x-model="search"
                        placeholder="{{ __('filament-log-viewer::log.table.modal.search_placeholder') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>
                <button
                    type="button"
                    @click="scrollToTop"
                    class="shrink-0 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    title="{{ __('filament-log-viewer::log.table.modal.go_to_top') }}"
                >
                    ↑ {{ __('filament-log-viewer::log.table.modal.top') }}
                </button>
                <button
                    type="button"
                    @click="scrollToBottom"
                    class="shrink-0 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    title="{{ __('filament-log-viewer::log.table.modal.go_to_bottom') }}"
                >
                    ↓ {{ __('filament-log-viewer::log.table.modal.bottom') }}
                </button>
            </div>
            <div
                x-ref="scrollContainer"
                class="max-h-[32rem] overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700"
            >
                <div
                    x-show="filteredEntries.length === 0"
                    class="p-8 text-center text-sm text-gray-500 dark:text-gray-400"
                    x-text="search ? '{{ __('filament-log-viewer::log.table.modal.no_matching_entries') }}' : '{{ __('filament-log-viewer::log.table.modal.no_entries') }}'">
                </div>
                <template x-for="(entry, index) in filteredEntries" :key="index">
                    <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded px-2 py-0.5 text-xs font-medium shrink-0"
                                        :style="`background-color: ${getColor(entry.level || 'info')}20; color: ${getColor(entry.level || 'info')}`"
                                    >
                                    <span x-html="getIcon(entry.level || 'info')"></span>
                                    <span x-text="(entry.level || 'info').toUpperCase()"></span>
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="entry.datetime || ''"></span>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-gray-100 break-words" x-text="entry.header || ''"></p>
                            <template x-if="entry.context">
                                <details>
                                    <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                        {{ __('filament-log-viewer::log.table.modal.context') }}
                                    </summary>
                                    <pre class="mt-2 p-3 text-xs bg-gray-100 dark:bg-gray-900 rounded max-h-64 overflow-auto font-mono whitespace-pre-wrap break-words" x-text="entry.context"></pre>
                                </details>
                            </template>
                            <template x-if="entry.stack">
                                <details>
                                    <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                        {{ __('filament-log-viewer::log.table.modal.stack_trace') }}
                                    </summary>
                                    <pre class="mt-2 p-3 text-xs bg-gray-100 dark:bg-gray-900 rounded max-h-64 overflow-auto font-mono whitespace-pre-wrap break-words" x-html="formatStack(entry.stack)"></pre>
                                </details>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('filament-log-viewer::log.table.detail.title') }}
    </p>
@endif

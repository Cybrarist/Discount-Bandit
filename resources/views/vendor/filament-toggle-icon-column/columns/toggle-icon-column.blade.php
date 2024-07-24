@php
    $state = $getState();
    $size = $getSize() ?? 'lg';
    $stateColor = $getStateColor();
    $stateIcon = $getStateIcon();
    $hoverColor = $getHoverColor();

    $iconSize ??= $size;

    $iconSize = match ($iconSize) {
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        'xl' => 'h-7 w-7',
        default => $iconSize,
    };

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        match ($stateColor) {
            'danger' => 'text-danger-500',
            'primary' => 'text-primary-500',
            'success' => 'text-success-500',
            'info' => 'text-info-500',
            'warning' => 'text-warning-500',
            'secondary' => 'text-gray-400 dark:text-gray-500',
            null => 'text-gray-700 dark:text-gray-200',
            default => $stateColor,
        },
        match ($hoverColor) {
            'danger' => 'hover:text-danger-600 dark:hover:text-danger-500',
            'primary' => 'hover:text-primary-600 dark:hover:text-primary-500',
            'success' => 'hover:text-success-600 dark:hover:text-success-500',
            'info' => 'hover:text-info-600 dark:hover:text-info-500',
            'warning' => 'hover:text-warning-600 dark:hover:text-warning-500',
            'secondary' => 'hover:text-gray-300 dark:hover:text-gray-600',
            null => 'hover:text-gray-700 dark:hover:text-gray-200',
            default => 'hover:'.$hoverColor,
        },
    ]);
@endphp

<div 
    wire:key="{{ $this->getId() }}.table.record.{{ $recordKey }}.column.{{ $getName() }}.toggle-column.{{ $state ? 'true' : 'false' }}"
>
    <div
        x-data="{
            error: undefined,
            state: @js((bool) $state),
            isLoading: false,
        }"
        wire:ignore
        {{ 
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['filament-toggle-icon-column'])
        }}
    >
        <button
            role="switch"
            aria-checked="false"
            x-bind:aria-checked="state.toString()"
            x-on:click="
                if (isLoading) {
                    return
                }

                state = ! state

                isLoading = true
                response = await $wire.updateTableColumnState(@js($getName()), @js($recordKey), state)
                error = response?.error ?? undefined

                if (error) {
                    state = ! state
                }

                isLoading = false
            "
            x-tooltip="error"
            x-bind:class="{
                'opacity-50 pointer-events-none': isLoading,
            }"
            @disabled($isDisabled())
            type="button"
            class="items-center justify-center inline-flex shrink-0 h-10 w-10 border-transparent cursor-pointer outline-none disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none"
        >
            <span
                {{
                    $attributes
                        ->merge($getExtraAttributes(), escape: false)
                        ->class([
                            "flex flex-wrap gap-1 filament-toggle-icon-column-size-{$size}",
                            '' => ! $isInline(),
                        ])
                }}
            >
                @if ($stateIcon)
                    <x-filament::icon
                        :icon="$stateIcon"
                        :size="$iconSize"
                        :class="$iconClasses . ' ' . $iconSize"
                    />                    
                @endif
            </span>
        </button>
    </div>
</div>

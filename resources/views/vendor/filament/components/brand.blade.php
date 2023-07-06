

<div style="justify-content: center;
display: flex;
align-items: center;
">
    <img src="/storage/bandit.png" alt="Logo" class="h-10 mr-2">
    @if (filled($brand = config('filament.brand')))
        <div
            @class([
                'filament-brand text-xl font-bold leading-5 tracking-tight',
                'dark:text-white' => config('filament.dark_mode'),
            ])
        >
            {{ $brand }}
        </div>
    @endif
</div>


<x-filament-panels::page>
    @if ($this->getTableRecords()->count() > 0)
        <div class="w-full">
            <div class="flex flex-col 2xl:flex-row gap-6">
                <div class="2xl:w-2/3 w-full">
                    @livewire(\Boquizo\FilamentLogViewer\Widgets\IconsWidget::class)
                </div>
            </div>
        </div>
    @endif
    <div class="w-full mt-2">
        {{ $this->table }}
    </div>
</x-filament-panels::page>

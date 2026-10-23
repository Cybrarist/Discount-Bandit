<div
    x-data="{
        storageKey: 'products.grid_columns',
        columns: $wire.entangle('grid_columns').live,
        init() {
            let savedColumns = null

            try {
                savedColumns = Number(localStorage.getItem(this.storageKey)) || null
            } catch (error) {}

            if (savedColumns && savedColumns !== this.columns) {
                this.columns = savedColumns
            }
        },
        pick(value) {
            this.columns = value ? Number(value) : null

            try {
                this.columns
                    ? localStorage.setItem(this.storageKey, this.columns)
                    : localStorage.removeItem(this.storageKey)
            } catch (error) {}
        },
    }"
    class="flex items-center gap-2"
>
    <label for="products-grid-columns" class="text-sm text-gray-500 dark:text-gray-400">Columns</label>

    <x-filament::input.wrapper>
        <x-filament::input.select
            id="products-grid-columns"
            x-bind:value="columns ?? ''"
            x-on:change="pick($event.target.value)"
        >
            <option value="">Auto</option>
            @foreach (range(2, 6) as $columns)
                <option value="{{ $columns }}">{{ $columns }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>

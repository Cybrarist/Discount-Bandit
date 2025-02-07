<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Helpers\StoreHelper;
use App\Models\ProductStore;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected $listeners = ['refresh_products_relation' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('key')
                    ->string(),
            ]);
    }

    public function table(Table $table): Table
    {
        $all_records_prices = ProductStore::whereIn('store_id', StoreHelper::get_stores_with_same_currency($table->getLivewire()->ownerRecord->currency_id)->pluck('id')->toArray())
            ->whereIn('product_id', $table->getLivewire()->ownerRecord->products->pluck('id')->toArray())
            ->select(['product_id', DB::raw('MIN(price)/100 as min_price')])
            ->groupBy('product_id')
            ->pluck('min_price', 'product_id')
            ->toArray();

        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(
                fn (Model $record): string => route('filament.admin.resources.products.edit', ['record' => $record])
            )
            ->openRecordUrlInNewTab()
            ->defaultSort('key', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\TextColumn::make('name')->words(20)->searchable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Current')
                    ->formatStateUsing(function ($record) use ($all_records_prices) {
                        return Number::format($all_records_prices[$record->id] ?? 0, 2);
                    }),
                Tables\Columns\TextColumn::make('key')->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Delete selected'),
            ]);
    }
}

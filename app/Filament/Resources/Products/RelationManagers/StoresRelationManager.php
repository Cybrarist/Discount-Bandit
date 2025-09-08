<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\ProductStores\Schemas\ProductStoreForm;
use App\Helpers\ProductHelper;
use App\Models\NotificationSetting;
use App\Models\ProductStore;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return ProductStoreForm::configure($schema);
    }

    /**
     * @throws \Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->allowDuplicates()
            ->columns([
                TextColumn::make('name')
                    ->url(url: function ($record) {
                        return ProductHelper::get_url($record);
                    }, shouldOpenInNewTab: true)
                    ->color('primary'),

                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => Number::format($state, 2))
                    ->color(fn ($record) => (($record->price <= $record->notify_price) ? "success" : "danger")),

                TextColumn::make('used_price')
                    ->formatStateUsing(fn ($state) => Number::format($state, 2))
                    ->color(fn ($record) => (($record->used_price <= $record->notify_price) ? "success" : "danger")),

                TextColumn::make('highest_price')
                    ->formatStateUsing(fn ($state) => Number::format($state, 2))
                    ->color("danger"),

                TextColumn::make('lowest_price')
                    ->formatStateUsing(fn ($state) => Number::format($state, 2))
                    ->color("success"),

                TextColumn::make('notify_price')
                //                    ->prefix(fn ($record) => CurrencyHelper::get_currencies($record->currency_id))
                ,

                TextColumn::make('notify_percentage')
                    ->prefix('%'),

                TextColumn::make('shipping_price'),

                TextColumn::make('rate'),

                TextColumn::make('updated_at')->label("Updated At"),

                TextColumn::make('number_of_rates')->label('Total Ratings'),

                IconColumn::make('add_shipping')
                    ->boolean()
                    ->trueIcon("heroicon-o-check-circle")
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Add Shipping Price')
                    ->tooltip('notify price < price + shipping price'),

                TextColumn::make('seller'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordTitleAttribute('name')
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action, $schema) => [
                        $action->getRecordSelect()->hiddenJs(true),
                        ...ProductStoreForm::configure($schema)->getComponents(),
                    ])
                    ->mutateDataUsing(function ($data) {
                        $data['product_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->modalWidth(Width::FourExtraLarge)
                    ->action(function ($data) {

                        $product_store = ProductStore::updateOrCreate(
                            ['store_id' => $data['recordId'], 'key' => $data['key'], 'product_id' => $data['product_id']]
                        );

                        NotificationSetting::create([
                            'product_store_id' => $product_store->id,
                            'user_id' => Auth::user(),
                            ...Arr::only($data, [
                                'notify_price',
                                'notify_percentage',
                                'notify_in_stock'])
                        ]);
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
                EditAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}

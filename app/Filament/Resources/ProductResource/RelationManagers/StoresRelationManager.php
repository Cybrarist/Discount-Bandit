<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Enums\StatusEnum;
use App\Helpers\CurrencyHelper;
use App\Models\PriceHistory;
use App\Models\ProductStore;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';

    protected static bool $isLazy = false;

    protected $listeners =['refresh'=>'$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->allowDuplicates()
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->color("warning")
                    ->url( function ($record) {
                        $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$record->domain)[0]);
                        return call_user_func($final_class_name . '::prepare_url' , $record->domain, $record->key , $record );
                    } ,true),

                TextColumn::make('price')
                    ->formatStateUsing(fn($state)=> Number::format($state ,2))
                    ->prefix(fn ($record) => CurrencyHelper::get_currencies($record->currency_id))
                    ->color(fn($record)=> (($record->price <= $record->notify_price) ? "success" :"danger")),


                TextColumn::make('highest_price')
                    ->formatStateUsing(fn($state)=> Number::format($state ,2))
                    ->prefix(fn ($record) => CurrencyHelper::get_currencies($record->currency_id))
                    ->color("danger"),

                TextColumn::make('lowest_price')
                    ->formatStateUsing(fn($state)=> Number::format($state ,2))
                    ->prefix(fn ($record) => CurrencyHelper::get_currencies($record->currency_id))
                    ->color("success"),

                TextColumn::make('notify_price')
                    ->prefix(fn ($record) => CurrencyHelper::get_currencies($record->currency_id)),

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
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn(Tables\Actions\AttachAction $action): array=>[
                    $action->recordSelectOptionsQuery(function($query){
                        $available_stores=DB::table('product_store')
                                ->where('product_id', $this->ownerRecord->id)
                                ->select('store_id')
                                ->pluck('store_id');

                        return $query->whereIn('status', [
                            StatusEnum::Published,
                            StatusEnum::Silenced,
                        ])->whereNotIn('stores.id', $available_stores);
                    })->getRecordSelect()
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set){
                            $store=Store::find($state);

                            if ($store)
                            {
                                $set('currency', CurrencyHelper::get_currencies($store->currency_id));
//                                $set('price', $store->);
                            }
                        }),

                    TextInput::make('price')
                        ->disabled()
                        ->dehydrated(false)
                        ->label('Current Price'),

                    TextInput::make('notify_price')
                        ->numeric()
                        ->integer()
                        ->label('Notify when cheaper than'),

                    TextInput::make('currency')
                        ->dehydrated(false)
                        ->disabled()
                ])->preloadRecordSelect()
                    ->mutateFormDataUsing(function ($data){
                        $data['notify_price']*=100;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form(fn(Tables\Actions\EditAction $action): array=>[
                    Forms\Components\Checkbox::make('add_shipping')
                        ->label('Shipping'),

                        Forms\Components\TextInput::make('price')
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Current Price')
                            ->suffix(CurrencyHelper::get_currencies(currency_id: $action->getRecord()->currency_id)),

                        Forms\Components\TextInput::make('notify_price')
                            ->numeric()
                            ->step(0.01)
                            ->label('Notify when cheaper than')
                            ->suffix(CurrencyHelper::get_currencies(currency_id: $action->getRecord()->currency_id)),

                    ])->using(function ($record, array $data) {
                    $record->products()->updateExistingPivot($this->ownerRecord->id , [
                        'notify_price'=>$data['notify_price'] * 100,
                        'add_shipping'=>$data['add_shipping'],
                    ]);

                    return $record;
                }),
                Tables\Actions\DetachAction::make()->label("Remove")->after(function ($record){
                    if (ProductStore::where([
                        "store_id" => $record->store_id,
                        "product_id" => $record->product_id
                    ])->count() ==0)
                        PriceHistory::where([
                            "store_id" => $record->store_id,
                            "product_id" => $record->product_id,
                        ])->delete();


                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])

            ;
    }


}

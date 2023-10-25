<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Walmart;
use App\Enums\StatusEnum;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoresRelationManager extends RelationManager
{

    protected static string $relationship = 'stores';

    protected $listeners =['refresh_products_relation'=>'$refresh'];


    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }



    public function table(Table $table): Table
    {

        $table->allowDuplicates(true);
        return $table
            ->recordTitleAttribute('name')
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->color("warning")
                    ->url( function ($record) {
                        //todo make static function for the following
                        if ( MainStore::is_amazon($record->domain))
                            return Amazon::prepare_url($record->domain , $this->ownerRecord->asin ,$record->referral);
                        elseif ( MainStore::is_ebay($record->domain))
                            return Ebay::prepare_url($record->domain , $record->ebay_id ,$record->referral);
                        elseif ( MainStore::is_walmart($record->domain))
                            return Walmart::prepare_url($record->domain , $this->ownerRecord->walmart_ip ,$record->referral);
                    } ,true),

                Tables\Columns\TextColumn::make('price')
                    ->prefix(fn ($record) => get_currencies($record->currency_id))
                    ->color(fn($record)=> (($record->price <= $record->notify_price) ? "success" :"danger")),

                Tables\Columns\TextColumn::make('notify_price')
                    ->prefix(fn ($record) => get_currencies($record->currency_id)),

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
                Tables\Actions\AttachAction::make()->form(fn(Tables\Actions\AttachAction $action): array=>[
                    $action->recordSelectOptionsQuery(function($query){
                        $available_stores=\DB::table('product_store')
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
                            $service=Store::find($state);

                            if ($service)
                            {
                                $set('currency', get_currencies($service->currency_id));
                                $set('price', $service->price);
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
                            ->suffix(get_currencies($action->getRecord()->currency_id)),

                        Forms\Components\TextInput::make('notify_price')
                            ->numeric()
                            ->step(0.01)
                            ->label('Notify when cheaper than')
                            ->suffix(get_currencies($action->getRecord()->currency_id)),

                    ])->using(function ($record, array $data) {
                    $record->products()->updateExistingPivot($this->ownerRecord->id , [
                        'notify_price'=>$data['notify_price'] * 100,
                        'add_shipping'=>$data['add_shipping'],
                    ]);

                    return $record;
                }),
                Tables\Actions\DetachAction::make()->label("Remove"),
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

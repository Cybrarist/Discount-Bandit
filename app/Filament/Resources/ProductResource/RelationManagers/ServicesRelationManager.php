<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Enums\StatusEnum;
use App\Models\Product;
use App\Models\Service;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Pages\Actions\ViewAction;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use PhpParser\Node\Scalar\String_;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->url( function ($record) {
                    $to_return=$record->url . "/dp/" .  $record->pivot->pivotParent->ASIN;
                    if (env('ALLOW_REF'))
                        return  $to_return ."/ref=nosim?tag=" . $record->referral;
                    else
                        return $to_return;

                } ,true),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(function ($record) {
                        if ($record->price <= $record->notify_price)
                            $color_string="green";
                        else
                            $color_string="red";
                        return Str::of( "<span  style='color:$color_string'>" . $record->price / 100 . " " .  $record->currency->code . " </span>")->toHtmlString();
                }),
                Tables\Columns\TextColumn::make('shipping_price')->formatStateUsing(function ($state) {
                    return $state/100;
                }),
                Tables\Columns\TextColumn::make('notify_price')->formatStateUsing(function ($record) {
                    return $record->notify_price / 100 . " " .  $record->currency->code;
                }),
                Tables\Columns\TextColumn::make('rate'),
                Tables\Columns\TextColumn::make('pivot.updated_at')->label("Updated At"),
                Tables\Columns\TextColumn::make('number_of_rates')->label('Total Ratings'),
                Tables\Columns\IconColumn::make('is_prime')->boolean()->trueIcon('heroicon-o-badge-check')->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('seller'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn(Tables\Actions\AttachAction $action): array=>[

                    $action->recordSelectOptionsQuery(function($query){
                        return $query->whereIn('status', [
                            StatusEnum::Published,
                            StatusEnum::Silenced,

                        ]);
                    })->getRecordSelect()->reactive()
                        ->afterStateHydrated(function ($state, callable $set){
                        $service=Service::find($state);
                        if ($service)
                        {
                            $set('currency', $service->currency->code);
                            $set('price', $service->price );
                        }
                    })
                        ->afterStateUpdated(function ($state, callable $set){
                            $service=Service::find($state);
                            if ($service)
                            {
                                $set('currency', $service->currency->code);
                                $set('price', $service->price);
                            }
                        }),
                    Forms\Components\TextInput::make('price')->disabled()->dehydrated(false)->label('Current Price'),
                    Forms\Components\TextInput::make('notify_price')->numeric()->integer()->label('Notify when cheaper than'),
                    Forms\Components\TextInput::make('currency')->dehydrated(false)->disabled()
                ])->preloadRecordSelect()->mutateFormDataUsing(function ($data){
                    $data['notify_price']*=100;
                    return $data;
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form(fn(Tables\Actions\EditAction $action): array=>[
                    Forms\Components\TextInput::make('price')->disabled()->formatStateUsing(function ( $state){
                        return $state / 100;
                    }) ->dehydrated(false)->label('Current Price')->suffix($action->getRecord()->currency->code),
                    Forms\Components\TextInput::make('notify_price')->numeric()->formatStateUsing(function ($state){
                            return $state / 100;
                    }) ->step(0.01)->label('Notify when cheaper than')->suffix($action->getRecord()->currency->code),
                    ])->using(function ($record, array $data) {

                        $record->products()->updateExistingPivot($record->pivot_product_id , [
                            'notify_price'=>$data['notify_price'] * 100
                        ]);

                        return $record;
                }),
                Tables\Actions\DetachAction::make()->label('Remove Service'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


}

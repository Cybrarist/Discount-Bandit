<?php

namespace App\Filament\Resources\GroupListResource\RelationManagers;

use App\Models\GroupList;
use App\Models\Service;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $inverseRelationship='group_lists';

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
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->words(10)->url( function ($record) {
                    return route('filament.resources.products.view', $record->id);
                } ),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(function ($record){
                    $record->load('services.currency:id,code');
                    $record->load(['services'=>function($query) use ($record){
                        $query->where('services.id',$record->pivot->pivotParent->service_id );
                    }]);
                    return $record->services[0]->pivot->price /100 . " " .$record->services[0]->currency->code;
                })->label('Current Price'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->recordSelectOptionsQuery(function (Tables\Actions\AttachAction $action, $query) use ($table){
                    $parent_record=$action->getLivewire()->ownerRecord;
                    return $query->whereHas('services', function ($service_query ) use ($parent_record){
                        $service_query->where('services.id',$parent_record->service_id );
                    } );
                })->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->label('Remove'),
            ]);
    }
}

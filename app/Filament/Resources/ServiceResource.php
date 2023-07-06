<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Enums\StatusEnum;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationGroup="Products";

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),

                Forms\Components\TextInput::make('url')
                    ->url(),
//
                Forms\Components\FileUpload::make('image')
                    ->disk('service')
                    ->image()
                    ->preserveFilenames()
                    ->imagePreviewHeight(100),

                Forms\Components\Select::make('currency')
                    ->relationship('currency' , 'code'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->hint('This will effect ALL the products that are related to this service')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload(),

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('service')->height(50),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\SelectColumn::make('status')->options(StatusEnum::to_array()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(StatusEnum::to_array())->label('Status'),

            ])->filtersLayout(Tables\Filters\Layout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                FilamentExportBulkAction::make('export')
                    ->fileName('Products')
                    ->csvDelimiter(',')
                    ->withColumns([TextColumn::make('referral')])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}

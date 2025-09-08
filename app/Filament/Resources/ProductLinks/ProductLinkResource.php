<?php

namespace App\Filament\Resources\ProductLinks;

use App\Filament\Resources\ProductLinks\Pages\CreateProductLink;
use App\Filament\Resources\ProductLinks\Pages\EditProductLink;
use App\Filament\Resources\ProductLinks\Pages\ListProductLinks;
use App\Filament\Resources\ProductLinks\RelationManagers\NotificationSettingsRelationManager;
use App\Filament\Resources\ProductLinks\Schemas\ProductLinkForm;
use App\Filament\Resources\ProductLinks\Tables\ProductLinksTable;
use App\Models\ProductLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductLinkResource extends Resource
{
    protected static ?string $model = ProductLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort =2 ;

    public static function form(Schema $schema): Schema
    {
        return ProductLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductLinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NotificationSettingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductLinks::route('/'),
            'create' => CreateProductLink::route('/create'),
            'edit' => EditProductLink::route('/{record}/edit'),
        ];
    }
}

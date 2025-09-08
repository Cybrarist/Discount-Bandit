<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\ProductLinks\Schemas\ProductLinkForm;
use App\Filament\Resources\ProductLinks\Tables\ProductLinksTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'product_links';


    public function form(Schema $schema): Schema
    {
        return ProductLinkForm::configure($schema, $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return ProductLinksTable::configure($table, true);
    }
}

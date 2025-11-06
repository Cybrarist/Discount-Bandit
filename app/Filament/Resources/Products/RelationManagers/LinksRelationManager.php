<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\Links\Schemas\LinkForm;
use App\Filament\Resources\Links\Tables\LinksTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    public function form(Schema $schema): Schema
    {
        return LinkForm::configure($schema, $this->ownerRecord);

    }

    public function table(Table $table): Table
    {
        return LinksTable::configure($table, $this->ownerRecord);
    }
}

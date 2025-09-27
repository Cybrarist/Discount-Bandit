<?php

namespace App\Filament\Resources\Links\RelationManagers;

use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingForm;
use App\Filament\Resources\NotificationSettings\Tables\NotificationSettingsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NotificationSettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'notification_settings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(NotificationSettingForm::configure($schema, $this->ownerRecord)->getComponents());
    }


    public function table(Table $table): Table
    {
        return NotificationSettingsTable::configure($table);
    }
}

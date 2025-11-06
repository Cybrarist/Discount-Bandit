<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('name'),
                TextEntry::make('domain'),
                ImageEntry::make('image'),
                TextEntry::make('slug'),
                TextEntry::make('referral'),
                TextEntry::make('status'),
                TextEntry::make('currency_id')
                    ->numeric(),
            ]);
    }
}

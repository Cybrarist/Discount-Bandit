<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class Settings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static bool $shouldRegisterNavigation = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
        ]);

    }
}

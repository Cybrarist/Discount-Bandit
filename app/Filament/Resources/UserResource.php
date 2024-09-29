<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-c-users';

    protected static ?string $navigationGroup="Settings";

    protected static ?int $navigationSort=10;

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\TextInput::make("name")
                    ->autofocus(),

                Forms\Components\TextInput::make("email")
                    ->string()
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),

                Forms\Components\TextInput::make("password")
                    ->string()
                    ->nullable()
                    ->password(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TokensRelationManager::make()
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    /**
     * @throws \Exception
     */
    public static function configure(Schema $schema): Schema
    {
        $colors = array_combine(array_keys(Color::all()), array_keys(Color::all()));

        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                TextInput::make('password')
                    ->label(__('filament-panels::auth/pages/register.form.password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(fn ($operation) => $operation == 'create')
                    ->rule(Password::default())
                    ->showAllValidationMessages()
                    ->validationAttribute(__('filament-panels::auth/pages/register.form.password.validation_attribute')),

                Section::make('Notification Settings')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('notification_settings.ntfy_url')
                            ->url(),

                        TextInput::make('notification_settings.ntfy_auth_username'),

                        TextInput::make('notification_settings.ntfy_auth_password')
                            ->password()
                            ->revealable(),

                        TextInput::make('notification_settings.ntfy_auth_token'),

                        TextInput::make('notification_settings.telegram_bot_token'),
                        TextInput::make('notification_settings.telegram_channel_id')
                            ->hint('include the "-"'),

                        Toggle::make('notification_settings.enable_rss_feed'),
                        TextInput::make('rss_feed')
                            ->formatStateUsing(fn ($state) => config('app.url').'/feed/?feed_id='.$state)
                            ->copyable()
                            ->disabled(),

                    ]),

                Section::make('Customization Settings')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('customization_settings.enable_top_navigation'),

                    ]),

                Section::make('Other Settings')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('currency_id')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->label('Currency')
                            ->preload(),

                        TextInput::make('other_settings.max_links')
                            ->numeric()
                            ->hint('Maximum number of links per user')
                            ->disabled(fn () => Auth::user()->role == RoleEnum::User->value),
                    ]),

            ]);
    }
}

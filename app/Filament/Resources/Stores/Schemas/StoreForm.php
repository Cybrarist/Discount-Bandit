<?php

namespace App\Filament\Resources\Stores\Schemas;

use App\Enums\StoreStatusEnum;
use App\Http\Controllers\Actions\NewStoreSmartSetupAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Operation;
use HeadlessChromium\Page;
use Novadaemon\FilamentPrettyJson\Form\PrettyJsonField;

class StoreForm
{
    /**
     * @throws \Exception
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->image()
                    ->disk('store')
                    ->maxFiles(1)
                    ->previewable()
                    ->openable()
                    ->downloadable()
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('domain')
                    ->required(),

                TextInput::make('slug')
                    ->required(),

                TextInput::make('referral'),

                Select::make('status')
                    ->options(StoreStatusEnum::class)
                    ->default(StoreStatusEnum::Active->value)
                    ->required()
                    ->native(false),

                Select::make('currency_id')
                    ->relationship('currency', 'code')
                    ->label('Currency')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),

                Section::make('Store URL Settings')
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(fn ($operation) => $operation == Operation::Edit->value)
                    ->schema([
                        Toggle::make('are_params_allowed')
                            ->inline(false)
                            ->default(false),

                        Repeater::make('allowed_params')
                            ->label(null)
                            ->grid(3)
                            ->columnSpanFull()
                            ->simple(
                                TextInput::make('field')->required(),
                            )
                            ->distinct()
                            ->addActionAlignment(Alignment::Start),

                    ]),

                Section::make('Custom Store Settings')
                    ->columnSpanFull()
                    ->columns(3)
                    ->headerActions([
                        Action::make('Smart Fetch')
                            ->schema([
                                TextInput::make('url'),
                            ])->action(function ($data, Set $set, Get $get) {

                                if ($get('custom_settings.crawling_method') == "chromium") {
                                    $setup = new NewStoreSmartSetupAction($data['url'], $get('custom_settings.timeout'), $get('custom_settings.page_event'));
                                }

                                $set('custom_settings.crawling_method', $setup->settings['crawling_method']);

                                $set('domain', $setup->settings['domain']);
                                $set('slug', $setup->settings['slug']);
                                $set('schema', json_encode($setup->schema));

                                foreach ($setup->schema_keys as $schema_key => $paths) {
                                    if (! $get('custom_settings.'.$schema_key)) {
                                        $set('custom_settings.'.$schema_key, implode(',', $paths));
                                    }
                                }

                                foreach ($setup->selectors as $selector_key => $paths) {
                                    if (! $get('custom_settings.'.$selector_key)) {
                                        $set('custom_settings.'.$selector_key, implode(',', $paths));
                                    }
                                }

                            }),
                    ])
                    ->schema([

                        Select::make('custom_settings.crawling_method')
                            ->options([
                                'chromium' => 'Chromium',
                                //                                'http' => 'Simple HTTP',
                            ])
                            ->default('chromium')
                            ->preload()
                            ->native(false),

                        Section::make('Chromium Settings')
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                TextInput::make('custom_settings.timeout')
                                    ->hint('in milliseconds')
                                    ->default(5000),

                                Select::make('custom_settings.page_event')
                                    ->options([
                                        Page::DOM_CONTENT_LOADED => 'DOM Loaded',
                                        Page::FIRST_CONTENTFUL_PAINT => 'First Contentful Paint',
                                        Page::FIRST_IMAGE_PAINT => 'First Image Paint',
                                        Page::FIRST_MEANINGFUL_PAINT => 'First Meaningful Paint',
                                        Page::FIRST_PAINT => 'First Paint',
                                        Page::INIT => 'Init',
                                        Page::INTERACTIVE_TIME => 'Interactive Time',
                                        Page::LOAD => 'Load',
                                        Page::NETWORK_IDLE => 'Network Idle',
                                    ])
                                    ->hintAction(
                                        Action::make('check_docs')
                                            ->url('https://github.com/chrome-php/chrome?tab=readme-ov-file#page-api', true)
                                    )
                                    ->default(Page::NETWORK_IDLE)
                                    ->native(false)
                                    ->preload(),
                            ]),

                        PrettyJsonField::make('schema')
                            ->copyable()
                            ->columnSpanFull(),

                        Action::make('download')
                            ->label('Download HTML ')
                            ->action(function () {
                                return response()->download(public_path('custom.html'));
                            }),

                        Section::make('Schema Keys')
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                TextInput::make('custom_settings.name_schema_key'),
                                TextInput::make('custom_settings.image_schema_key'),
                                TextInput::make('custom_settings.total_reviews_schema_key'),
                                TextInput::make('custom_settings.rating_schema_key'),
                                TextInput::make('custom_settings.price_schema_key'),
                                TextInput::make('custom_settings.used_price_schema_key'),
                                TextInput::make('custom_settings.shipping_schema_key'),
                                TextInput::make('custom_settings.stock_schema_key'),
                                TextInput::make('custom_settings.condition_schema_key'),
                                TextInput::make('custom_settings.seller_schema_key'),
                            ]),

                        Section::make('CSS Selector Keys')
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                TextInput::make('custom_settings.name_selectors'),
                                TextInput::make('custom_settings.image_selectors'),
                                TextInput::make('custom_settings.total_reviews_selectors'),
                                TextInput::make('custom_settings.rating_selectors'),
                                TextInput::make('custom_settings.price_selectors'),
                                TextInput::make('custom_settings.used_price_selectors'),
                                TextInput::make('custom_settings.shipping_selectors'),
                                TextInput::make('custom_settings.stock_selectors'),
                                TextInput::make('custom_settings.condition_selectors'),
                                TextInput::make('custom_settings.seller_selectors'),
                            ]),

                    ]),
            ]);
    }
}

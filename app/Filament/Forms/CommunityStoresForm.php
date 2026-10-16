<?php

namespace App\Filament\Forms;

use App\Enums\RoleEnum;
use App\Helpers\StoreHelper;
use App\Jobs\AddStoreToDiscountJob;
use Daikazu\FilamentImageCheckboxGroup\Forms\Components\ImageCheckboxGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Community Stores Form Configuration
 *
 * This class provides a Filament form wizard that allows users to search and add community stores
 * to their discount collection. The wizard consists of two main steps:
 * 1. Search and select stores from the community store repository
 * 2. Select specific domains for the chosen stores
 *
 * The form integrates with GitHub-hosted community store data and dispatches background jobs
 * to process the selected stores and add them to the discount system.
 */
class CommunityStoresForm
{
    public static function allowed_to_access(): bool
    {
        return Auth::user()->role === RoleEnum::Admin;
    }

    public static function configure()
    {
        return Action::make('add_community_stores')
            ->icon(Heroicon::BuildingStorefront)
            ->authorize(fn () => self::allowed_to_access())
            ->schema([
                Wizard::make([

                    // Step 1: Store Search and Selection
                    // Allows users to search for stores by name or browse alphabetically
                    Step::make('Search Stores')
                        ->schema([

                            // Search input with 600ms debounce to prevent excessive API calls
                            // Updates results dynamically as user types
                            TextInput::make('search')
                                ->live(debounce: 600)
                                ->afterStateUpdated(function ($state, $set) {
                                    $set('letter', $state ? Str::upper(substr($state, 0, 1)) : -1);
                                })
                                ->label('Search for a store'),

                            // Generates A-Z buttons dynamically for quick filtering
                            ToggleButtons::make('letter')
                                ->label('Choose a letter')
                                ->live()
                                ->options(array_combine(range('A', 'Z'), range('A', 'Z')))
                                ->default(-1)
                                ->inline()
                                ->reactive(),

                            // Visual store selection using image checkboxes
                            // Dynamically loads stores based on search/letter filter and displays them with logos
                            // Responsive grid layout adjusts from 1 column on mobile to 4 columns on large screens
                            ImageCheckboxGroup::make('custom_stores')
                                ->options(function ($get, $set) {
                                    $stores = StoreHelper::search_community_stores($get('search'), $get('letter'));

                                    $final_files = [];

                                    foreach ($stores as $store)
                                        $final_files[$store['path']] = [
                                            'label' => $store['name'],
                                            'image' => $store['image'],
                                            'path' => $store['path'],
                                        ];

                                    return $final_files;
                                })
                                ->gridColumns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'lg' => 4,
                                    'xl' => 4,
                                    '2xl' => 4,
                                ])
                                ->minSelect(1),

                            // Hidden field to trigger domain fetch in the next step
                            // This prevents premature API calls before user confirms store selection
                            Hidden::make('domains_fetched')
                                ->default(false),

                        ])
                        ->afterValidation(function ($state, $set) {
                            // This runs when clicking "Next" after validation passes
                            // Marks that user has completed store selection and triggers domain fetching in next step
                            $set('domains_fetched', true);
                        }),

                    // Step 2: Domain Selection
                    // Some stores have multiple domains/websites; this step lets users choose specific ones
                    Step::make('Select Domains')
                        ->key('select-domains')
                        ->schema([
                            Hidden::make('stores_selected_domains'),

                            // Domain selection with visual checkboxes
                            // Fetches available domains for the stores selected in previous step
                            ImageCheckboxGroup::make('domains_selected')
                                ->options(function ($get, $set) {

                                    // Only fetch domains after user completes first step (prevents unnecessary API calls)
                                    // Returns empty array if user navigates back or hasn't completed step 1
                                    if (! $get('domains_fetched')) {
                                        return [];
                                    }

                                    $customStores = $get('custom_stores');

                                    $stores = StoreHelper::get_domains_for_stores($customStores);

                                    $set('stores_selected_domains', $stores);

                                    return $stores;
                                })
                                ->gridColumns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'lg' => 4,
                                    'xl' => 4,
                                    '2xl' => 4,
                                ])
                                ->minSelect(1),

                        ]),
                ]),
            ])
            ->action(function ($data) {
                // Process selected domains and dispatch background jobs
                // Each job adds a store to the discount system with its logo from GitHub

                foreach ($data['domains_selected'] as $domain) {
                    $current_domain = $data['stores_selected_domains'][$domain];

                    $image_link = config('settings.github_community_store_gist_base').$current_domain['store'].'/logo.png';
                    AddStoreToDiscountJob::dispatch($current_domain['path'], $image_link);
                }

                Notification::make()
                    ->title('Store are being added to discount system')
                    ->success()
                    ->send();
            });
    }
}

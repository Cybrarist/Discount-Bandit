<?php

namespace App\Providers\Filament;

use App\Filament\Resources\StoreResource;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use FilipFonal\FilamentLogManager\FilamentLogManager;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth("full")
            ->brandLogo("/storage/bandit.png")
            ->brandName("Discount Bandit")
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
//            ->pages([
//                Pages\Dashboard::class
//            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])->authMiddleware([
                Authenticate::class,
            ])->plugins([
                \Hasnayeen\Themes\ThemesPlugin::make(),
                SpotlightPlugin::make(),
                QuickCreatePlugin::make()->excludes([
                    StoreResource::class
                ]),
                BreezyCore::make()->myProfile()
                    ->enableTwoFactorAuthentication()
                    ->enableSanctumTokens(
                        permissions: ['get_product'] // optional, customize the permissions (default = ["create", "view", "update", "delete"])
                    ),
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backups::class),
                FilamentSpatieLaravelHealthPlugin::make(),
                FilamentLogManager::make(),
            ]);

    }
}

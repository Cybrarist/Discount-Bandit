<?php

namespace App\Providers\Filament;

use App\Filament\Resources\StoreResource;
use App\Http\Middleware\DisableAuthMiddleware;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use FilipFonal\FilamentLogManager\FilamentLogManager;
use GeoSot\FilamentEnvEditor\FilamentEnvEditorPlugin;
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
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->colors([
                'primary' => Color::{config('settings.theme_color')},
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugins([
                FilamentApexChartsPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make()
                    ->navigationGroup('Settings'),
                SpotlightPlugin::make(),
                EasyFooterPlugin::make()
                    ->withGithub()
                    ->withLogo(asset("storage/bandit.png"), "https://discount-bandit.cybrarist.com", "v3.4"),
                FilamentLogManager::make(),
                FilamentJobsMonitorPlugin::make(),
                QuickCreatePlugin::make()
                    ->excludes([
                        StoreResource::class,
                        QueueMonitorResource::class,
                    ]),
                FilamentEnvEditorPlugin::make()
                    ->navigationGroup('Settings'),
                BreezyCore::make()
                    ->myProfile()
                    ->enableTwoFactorAuthentication()
                    ->enableSanctumTokens(
                        permissions: ['get_product', 'create_product', 'delete_product'] // optional, customize the permissions (default = ["create", "view", "update", "delete"])
                    ),
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
                DisableAuthMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandLogo(logo: asset("storage/bandit.png"))
            ->brandName("Discount Bandit")
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->topNavigation(config('settings.top_nav'))
            ->topbar(config('settings.disable_top_bar'))
            ->breadcrumbs(config('settings.breadcrumbs'))
            ->spa(config('settings.spa'));
    }
}

<?php

namespace App\Providers\Filament;

use App\Enums\RoleEnum;
use App\Filament\Forms\ImportForm;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Stores\StoreResource;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Awcodes\QuickCreate\QuickCreatePlugin;
use Boquizo\FilamentLogViewer\FilamentLogViewerPlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->registration(Register::class)
            ->login(action: Login::class)
            ->userMenuItems([
                Action::make('settings')
                    ->url(fn (): string => route('filament.admin.resources.users.edit', ['record' => Auth::id()]))
                    ->icon('heroicon-o-cog-6-tooth'),

                ImportForm::configure(),

            ])
            ->brandName(name: config('app.name'))
            ->brandLogo(asset("storage/bandit.png"))
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::{config('settings.theme_color')},

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
//            ->pages([
//                Dashboard::class,
//            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop(
                fn () => ! Auth::user()?->customization_settings['enable_top_navigation']
            )
            ->breadcrumbs(false)
            ->assets([
                Css::make('custom-css', resource_path('css/custom.css')),
                Js::make('custom-script', resource_path('js/custom.js')),
            ])
            ->topNavigation(fn () => Auth::user()?->customization_settings['enable_top_navigation'])
            ->plugins([
                FilamentAuthenticationLogPlugin::make(),
                EasyFooterPlugin::make()
                    ->withGithub()
                    ->withLogo(asset("storage/bandit.png"), "https://discount-bandit.cybrarist.com", "v-4.0"),
                BreezyCore::make()
                    ->myProfile()
                    ->enableTwoFactorAuthentication()
                    ->enableSanctumTokens(permissions: ['all']),
                FilamentLogViewerPlugin::make()
                    ->navigationGroup('Settings')
                    ->authorize(fn () => Auth::user()->role == RoleEnum::Admin),
                ResizedColumnPlugin::make()
                    ->preserveOnDB(),
                QuickCreatePlugin::make()
                    ->includes([
                        ProductResource::class,
                        StoreResource::class,
                    ]),
                FilamentApexChartsPlugin::make(),
            ]);
    }
}

<?php

namespace App\Providers;

use App\Enums\RoleEnum;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewApiDocs', function () {
            return Auth::check() && Auth::user()->role == RoleEnum::Admin;
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationMode(PaginationMode::Cursor)
                ->paginationPageOptions([24, 48, 72, 96, 'all'])
                ->deferLoading();
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => new HtmlString('<script>document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0))</script>'),
        );

    }
}

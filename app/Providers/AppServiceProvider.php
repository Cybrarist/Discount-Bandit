<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
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
    public function boot(): void {

        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationPageOptions([24, 48, 72, 96, 'all'])
                ->deferLoading();
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => new HtmlString('<script>document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0))</script>'),
        );

    }
}

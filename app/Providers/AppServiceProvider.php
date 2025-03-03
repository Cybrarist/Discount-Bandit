<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //        URL::forceScheme('https');

        JsonResource::withoutWrapping();

        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationPageOptions([24, 48, 72, 96, 'all'])
                ->deferLoading();
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => new HtmlString('<script>document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0))</script>'),
        );

        Health::checks([
            CacheCheck::new(),
            DatabaseCheck::new(),
            EnvironmentCheck::new(),
            PingCheck::new()->url('https://google.com')->failureMessage("Couldn't access the internet"),
            ScheduleCheck::new(),
        ]);



    }
}

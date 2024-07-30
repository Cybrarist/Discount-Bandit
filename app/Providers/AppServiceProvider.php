<?php

namespace App\Providers;

use App\Models\User;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
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

        if (config('settings.disable_auth'))
            Auth::login(User::first());

        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationPageOptions([ 25, 50 , 100 , 150 ,200,'all'])
                ->deferLoading();
        });


        Health::checks([
            CacheCheck::new(),
            DatabaseCheck::new(),
            EnvironmentCheck::new(),
            PingCheck::new()->url('https://google.com')->failureMessage("Couldn't access the internet"),
            ScheduleCheck::new(),
        ]);
    }
}

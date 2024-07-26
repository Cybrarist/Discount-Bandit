<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Laravel\Prompts\Prompt;

class DiscountUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update discount bandit with the new stores and database changes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //add the new stores and apply new migrations

        Artisan::call("migrate --seed --force", [] , $this->output);

        \Laravel\Prompts\info("Please Update Your Crons Queues with the following information");
        \Laravel\Prompts\info("Stores crons:");
        \Laravel\Prompts\info("-----------------------------------------------");

        $stores=Store::all();

        foreach ($stores as $store)
            \Laravel\Prompts\info("*/6 * * * * php_path project_path/artisan queue:work --stop-when-empty --queue=$store->slug >> /dev/null 2>&1");

//        \Laravel\Prompts\info("Groups Crons:");
//        \Laravel\Prompts\info("-----------------------------------------------");
//        \Laravel\Prompts\info("*/11 * * * * php_path project_path/artisan queue:work --stop-when-empty --queue=groups >> /dev/null 2>&1");


        //clear caches
        Artisan::call("optimize:clear", [] , $this->output);
        Artisan::call("filament:clear-cached-components", [] , $this->output);
        Artisan::call("icons:clear", [] , $this->output);
        Artisan::call("queue:clear", [] , $this->output);
        Artisan::call("notification:clear", [] , $this->output);

        //cache stuff
        Artisan::call("optimize", [] , $this->output);
        Artisan::call("filament:cache-components", [] , $this->output);
        Artisan::call("icons:cache", [] , $this->output);
    }
}

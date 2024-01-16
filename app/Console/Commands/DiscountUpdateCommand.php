<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
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
    public function handle()
    {
        \Artisan::call("migrate --seed --force", [] , $this->output);

        \Laravel\Prompts\info("Please Update Your Crons Queues with the following information");
        \Laravel\Prompts\info("Stores Crons:");
        \Laravel\Prompts\info("-----------------------------------------------");

        $stores=Store::all();
        foreach ($stores as $store)
            \Laravel\Prompts\info("*/6 * * * * php_path project_path/artisan queue:work --stop-when-empty --queue=$store->slug >> /dev/null 2>&1");

        \Laravel\Prompts\info("Groups Crons:");
        \Laravel\Prompts\info("-----------------------------------------------");
        \Laravel\Prompts\info("*/11 * * * * php_path project_path/artisan queue:work --stop-when-empty --queue=groups >> /dev/null 2>&1");

    }
}

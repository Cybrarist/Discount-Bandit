<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    }
}

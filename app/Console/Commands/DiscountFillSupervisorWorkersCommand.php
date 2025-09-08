<?php

namespace App\Console\Commands;

use App\Enums\StoreStatusEnum;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DiscountFillSupervisorWorkersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:fill-supervisor-workers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add stores workers to the supervisor config file';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        try {

            File::copy(base_path('docker/base_supervisord.conf'), '/etc/supervisor/conf.d/supervisord.conf');

            Store::where("status", StoreStatusEnum::Active)->get()
                ->each(function ($store) {

                    Log::info($store->name." appended");

                    File::append('/etc/supervisor/conf.d/supervisord.conf',
                        "
[program:laravel-worker-{$store->id}]
process_name=%(program_name)s_%(process_num)02d
user=root
command=php artisan queue:work --queue={$store->slug} --max-time=300 --sleep=1 --tries=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/worker.log
stopwaitsecs=300
                    ");

                });

            Process::run("
                supervisorctl -c /etc/supervisor/conf.d/supervisord.conf reread &&
                supervisorctl -c /etc/supervisor/conf.d/supervisord.conf update &&
                supervisorctl -c /etc/supervisor/conf.d/supervisord.conf restart all
            ");
        } catch (\Throwable $throwable) {
            Log::warning("Couldn't update the supervisor");
        }

    }
}

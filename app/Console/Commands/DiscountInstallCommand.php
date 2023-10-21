<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class DiscountInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'install Discount Bandit Web app';

    /**
     * Execute the console command.
     */


    private function setup_cron_linux($path, $project){

        \Laravel\Prompts\info("Schedule Automation");
        \Laravel\Prompts\info("*/5 * * * * $path $project/artisan schedule:run >> /dev/null 2>&1\"");

        $stores=Store::all();
        foreach ($stores as $store)
            \Laravel\Prompts\info("*/6 * * * * $path $project/artisan queue:work --stop-when-empty --queue=$store->slug >> /dev/null 2>&1");


    }


    private function setup_cron_windows($path, $project){

        \Laravel\Prompts\info("Schedule Automation");
        \Laravel\Prompts\info("schtasks /create /sc minute /mo 5 /tn \"DiscountScheduleTask\" /tr \"$path $project\\artisan schedule:run\"");

        $stores=Store::all();
        foreach ($stores as $store)
            \Laravel\Prompts\info("schtasks /create /sc minute /mo 6 /tn \"CrawlJobFor$store->id\" /tr \"$path $project\\artisan queue:work --stop-when-empty --queue=$store->slug\"");

    }




    private function setup_terminal_linux($path , $project){

        \Laravel\Prompts\info("Schedule Automation");

        $final_string="$path $project/artisan schedule:work >> /dev/null 2>&1";

        $stores=Store::all();
        foreach ($stores as $store)
            $final_string.=" & $path $project/artisan queue:listen --queue=$store->slug >> /dev/null 2>&1";

        \Laravel\Prompts\info($final_string);
    }

    private function setup_terminal_windows($path , $project){

        \Laravel\Prompts\info("Schedule Automation");

        $final_string="start /B $path $project\\artisan schedule:work > nul 2>&1";

        $stores=Store::all();
        foreach ($stores as $store)
            $final_string.=" & start /B $path $project\\artisan queue:listen --queue=$store->slug > nul 2>&1";

        \Laravel\Prompts\info($final_string);
    }


    public function handle()
    {








        \File::copy(".env.example" , ".env");
        \File::append(".env" , "\n\n\n");

        $app_url=text(
            label:"What is the app url?",
            hint:"Default: http://localhost:8000"
        );
        (\Str::length($app_url))?: $app_url="http://localhost:8000";
        \File::append(".env" , "APP_URL=$app_url\n");


        $db_host=text(
            label:"What is the database host?  you can use IP or URL",
            hint:"Default: 127.0.0.1"
        );
        (\Str::length($db_host))?: $db_host="127.0.0.1";
        \File::append(".env" , "DB_HOST=$db_host\n");

        $db_port=text(
            label:"What is the database port? ",
            hint:"Default: 3306"
        );
        (\Str::length($db_port))?: $db_port="3306";

        \File::append(".env" , "DB_PORT=$db_port\n");


        $db_name=text(
            label:"What is the database name? ",
            hint:"Default: price-tracker"
        );
        (\Str::length($db_name))?: $db_name="price-tracker";

        \File::append(".env" , "DB_DATABASE=$db_name\n");

        $db_user=text(
            label:"What is the database user? ",
            hint:"Default: root"
        );
        (\Str::length($db_user))?: $db_user="root";

        \File::append(".env" , "DB_USERNAME=$db_user\n");

        $db_pass=password(
            label:"What is the database password? ",
            hint:"Default: password"
        );
        (\Str::length($db_pass))?: $db_pass="password";
        \File::append(".env" , "DB_PASSWORD=$db_pass\n");


        $ntfy_url=text(
            label: "What is the ntfy URL? ",
            placeholder: "example : https://ntfy.sh/random_value",
            required: true,
        );
        \File::append(".env" , "NTFY_LINK=$ntfy_url\n");


        \Artisan::call("key:generate --force", [], $this->getOutput());
        \Artisan::call("storage:link", [], $this->getOutput());
        \Artisan::call("config:cache",[], $this->getOutput());
        \Artisan::call("config:clear",[], $this->getOutput());
        \Artisan::call("cache:clear",[], $this->getOutput());
        \Artisan::call("optimize:clear",[], $this->getOutput());


        \Artisan::call("migrate:fresh --seed --force" ,[], $this->getOutput());

        $name=text(
            label:"What is the Name for the user? ",
            hint:"Default: First Last"
        );
        $email=text(
            label:"What is the email for the user? ",
            hint:"Default: test@test.com"
        );

        $password=password(
            label:"What is the password for the user? ",
            hint:"Default: password"
        );

        User::create([
            'name'=> (\Str::length($name) >1 ) ? $name : "Test",
            'email'=> (\Str::length($email) >1 ) ? $email : "test@test.com",
            'password'=> (\Str::length($password) >1 ) ? $password : "password",
        ]);
        \Laravel\Prompts\info("User Created Successfully");


        $how_to_run=select(
            label: "how are you planning to run the schedule and queues?",
            required: true,
            options: ["cron" , "terminal"],
            default: "cron"
        );
        $os=select(
            label: "what is your operating system",
            required: true,
            options: ["linux" , "windows" , "mac"],
            default: "linux"
        );

        $path=text(
            label: "what is the path to php",
            hint: ($os=="linux" || $os=="mac" ) ? "/path/to/php" : "C:\\path\\to\\php"
        );
        $project=text(
            label: "what is the path to the project folder",
            hint: ($os=="linux" || $os=="mac" ) ? "/var/www/discount" : "C:\\path\\to\\project"
        );

        if ($how_to_run=="cron" && ($os=="mac" || $os=="linux"))
            $this->setup_cron_linux($path , $project);
        elseif ($how_to_run=="terminal" && ($os=="mac" || $os=="linux"))
            $this->setup_terminal_linux($path , $project);
        elseif ($how_to_run=="cron" && $os=="windows")
            $this->setup_cron_windows($path, $os);
        elseif ($how_to_run=="terminal" && $os=="windows")
            $this->setup_terminal_windows($path, $os);
    }
}

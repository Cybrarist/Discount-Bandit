<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use Filament\Support\Colors\Color;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
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
    private function setup_cron_linux($path, $project): void
    {
        \Laravel\Prompts\info("Schedule Automation");
        \Laravel\Prompts\info("*/5 * * * * $path $project/artisan schedule:run >> /dev/null 2>&1\"");
        \Laravel\Prompts\info("*/1 * * * * $path $project/artisan queue:work --max-time=300 --sleep=1  --queue=groups >> /dev/null 2>&1");

        $stores = Store::all();
        foreach ($stores as $store) {
            \Laravel\Prompts\info("*/6 * * * * $path $project/artisan queue:work --max-time=300 --sleep=1  --tries=1 --queue=$store->slug >> /dev/null 2>&1");
        }
    }

    private function setup_cron_windows($path, $project): void
    {
        \Laravel\Prompts\info("Schedule Automation");
        \Laravel\Prompts\info("schtasks /create /sc minute /mo 5 /tn \"DiscountScheduleTask\" /tr \"$path $project\\artisan schedule:run\"");

        \Laravel\Prompts\info("schtasks /create /sc minute /mo 1 /tn \"CrawlJobForGroups\" /tr \"$path $project\\artisan queue:work --max-time=300 --sleep=1  --queue=groups\"");

        $stores = Store::all();
        foreach ($stores as $store) {
            \Laravel\Prompts\info("schtasks /create /sc minute /mo 6 /tn \"CrawlJobFor$store->id\" /tr \"$path $project\\artisan queue:work --max-time=300 --tries=1 --sleep=1  --queue=$store->slug\"");
        }

    }

    private function setup_terminal_linux($path, $project): void
    {

        \Laravel\Prompts\info("Schedule Automation");

        $final_string = "$path $project/artisan schedule:work >> /dev/null 2>&1 & $path $project/artisan queue:listen --queue=groups >> /dev/null 2>&1 ";

        $stores = Store::all();
        foreach ($stores as $store) {
            $final_string .= " & $path $project/artisan queue:listen --queue=$store->slug >> /dev/null 2>&1";
        }

        \Laravel\Prompts\info($final_string);
    }

    private function setup_terminal_windows($path, $project): void
    {

        \Laravel\Prompts\info("Schedule Automation");

        $final_string="start /B $path $project\\artisan schedule:work > nul 2>&1  & start /B $path $project\\artisan queue:listen --queue=groups > nul 2>&1";

        $stores = Store::all();
        foreach ($stores as $store) {
            $final_string .= " & start /B $path $project\\artisan queue:listen --queue=$store->slug > nul 2>&1";
        }

        \Laravel\Prompts\info($final_string);
    }

    public function handle(): void
    {

        File::copy(".env.example", ".env");
        File::append(".env", "\n\n\n");

        sleep(1);
        Artisan::call("config:clear", [], $this->getOutput());
        Artisan::call("key:generate --force", [], $this->getOutput());

        // APP URL
        $app_url = text(
            label: "What is the app url?",
            hint: "Default: http://localhost:8000"
        );
        (Str::length($app_url)) ?: $app_url = "http://localhost:8000";
        File::append(".env", "APP_URL=$app_url\n");

        // DATABASE
        $database_type = select(
            label: "Database Type",
            options: ["sqlite", "mysql"],
            default: "sqlite"
        );

        if ($database_type == "mysql") {
            $db_host = text(
                label: "What is the database host?  you can use IP or URL",
                hint: "Default: 127.0.0.1"
            );
            (Str::length($db_host)) ?: $db_host = "127.0.0.1";
            File::append(".env", "DB_HOST=$db_host\n");

            $db_port = text(
                label: "What is the database port? ",
                hint: "Default: 3306"
            );
            (Str::length($db_port)) ?: $db_port = "3306";
            File::append(".env", "DB_PORT=$db_port\n");

            $db_name = text(
                label: "What is the database name? ",
                hint: "Default: price-tracker"
            );
            (Str::length($db_name)) ?: $db_name = "price-tracker";
            File::append(".env", "DB_DATABASE=$db_name\n");

            $db_user = text(
                label: "What is the database user? ",
                hint: "Default: root"
            );
            (Str::length($db_user)) ?: $db_user = "root";
            File::append(".env", "DB_USERNAME=$db_user\n");

            $db_pass = password(
                label: "What is the database password? ",
                hint: "Default: password"
            );
            (Str::length($db_pass)) ?: $db_pass = "password";
            File::append(".env", "DB_PASSWORD=\"$db_pass\"\n");

        } elseif ($database_type != "sqlite") {
            $this->error("Database type not supported");
            exit;
        }

        File::append(".env", "DB_CONNECTION=$database_type\n");

        Artisan::call("migrate:fresh --seed --force", [], $this->getOutput());

        Artisan::call("optimize:clear", [], $this->output);
        Artisan::call("icons:clear", [], $this->output);
        Artisan::call("config:clear", [], $this->output);

        $name = text(
            label: "What is the Name for the user? ",
            hint: "Default: First Last"
        );
        $email = text(
            label: "What is the email for the user? ",
            hint: "Default: test@test.com"
        );
        $password = password(
            label: "What is the password for the user? ",
            hint: "Default: password"
        );

        User::truncate();
        User::create([
            'name' => (Str::length($name) > 1) ? $name : "Test",
            'email' => (Str::length($email) > 1) ? $email : "test@test.com",
            'password' => (Str::length($password) > 1) ? $password : "password",
        ]);
        \Laravel\Prompts\info("User Created Successfully");

        $ntfy_channel = text(
            label: "What is the ntfy channel? ",
            placeholder: "example : https://ntfy.sh/random_value only random_value is needed.",
            required: true,
        );
        File::append(".env", "NTFY_CHANNEL_ID=\"$ntfy_channel\"\n");

        $telegram_bot_token = text(
            label: "What is Telegram bot token?",
            placeholder: "Not required",
            required: false,
        );
        File::append(".env", "TELEGRAM_BOT_TOKEN=\"$telegram_bot_token\"\n");

        $telegram_channel_id = text(
            label: "What is Telegram channel ID?",
            placeholder: "Not required",
            required: false,
        );
        File::append(".env", "TELEGRAM_CHANNEL_ID=\"$telegram_channel_id\"\n");

        Artisan::call("config:clear", [], $this->output);
        \Laravel\Prompts\info("Testing Notification");
        Artisan::call("discount:test-notify", [], $this->getOutput());

        $timezone = text(
            label: "What is your timezone",
            placeholder: "UTC",
            required: true,
        );
        File::append(".env", "APP_TIMEZONE=$timezone\n");

        $rss_feed = confirm(
            label: 'Do you want to enable RSS Feed',
            default: true,
            yes: 'Yes',
            no: 'No',
            hint: 'the feed will be accessed on /feed'
        );
        File::append(".env", "RSS_FEED=$rss_feed\n");

        $every_change_notify = confirm(
            label: 'Do you want to be notified on EVERY SINGLE CHANGE on product price',
            default: false,
            yes: 'Yes',
            no: 'No',
            hint: 'if you are using ntfy, be careful of hourly/daily quota',
        );
        File::append(".env", "NOTIFY_ON_ANY_PRICE_CHANGE=$every_change_notify\n");

        $top_navigation = confirm(
            label: 'Do you want to switch to top panel instead of side panel navigation',
            default: false,
            yes: 'Yes',
            no: 'No',
        );
        File::append(".env", "TOP_NAVIGATION=$top_navigation\n");

        $disable_top_bar = confirm(
            label: 'Do you want to disable top bar completely',
            default: false,
            yes: 'Yes',
            no: 'No',
        );
        File::append(".env", "DISABLE_TOP_BAR=$disable_top_bar\n");

        $breadcrumbs = confirm(
            label: 'Would you like to enable breadcrumbs',
            default: false,
            yes: 'Yes',
            no: 'No',
        );
        File::append(".env", "BREADCRUMBS=$breadcrumbs\n");

        $spa_mode = confirm(
            label: 'Would you like to enable SPA mode',
            default: true,
            yes: 'Yes',
            no: 'No',
            hint: 'This will make the website feels like an app instead of reloading the whole page'
        );
        File::append(".env", "SPA=$spa_mode\n");

        $disable_auth = confirm(
            label: 'Would you like to access the website without authentication',
            default: true,
            yes: 'Yes',
            no: 'No',
        );
        File::append(".env", "DISABLE_AUTH=$disable_auth\n");

        $main_theme_color = select(
            label: 'Choose your preferred theme color',
            options: array_keys(Color::all()),
            default: 'amber',
            hint: "this can be changed later on"
        );
        File::append(".env", "THEME_COLOR=".Str::headline($main_theme_color)."\n");

        $this->info("Spinning up the website");

        Artisan::call("optimize", [], $this->output);
        Artisan::call("filament:cache-components", [], $this->output);
        Artisan::call("icons:cache", [], $this->output);

        Artisan::call("vendor:publish --tag=\"filament-breezy-views\"", [], $this->getOutput());

        $how_to_run = select(
            label: "how are you planning to run the schedule and queues?",
            options: ["cron", "terminal"],
            default: "cron",
            required: true
        );
        $os = select(
            label: "what is your operating system",
            options: ["linux", "windows", "mac"],
            default: "linux",
            required: true
        );

        $path = text(
            label: "what is the path to php",
            hint: ($os == "linux" || $os == "mac") ? "/path/to/php" : "C:\\path\\to\\php"
        );
        $project = text(
            label: "what is the path to the project folder",
            hint: ($os == "linux" || $os == "mac") ? "/var/www/discount" : "C:\\path\\to\\project"
        );

        if ($how_to_run == "cron" && ($os == "mac" || $os == "linux")) {
            $this->setup_cron_linux($path, $project);
        } elseif ($how_to_run == "terminal" && ($os == "mac" || $os == "linux")) {
            $this->setup_terminal_linux($path, $project);
        } elseif ($how_to_run == "cron" && $os == "windows") {
            $this->setup_cron_windows($path, $os);
        } elseif ($how_to_run == "terminal" && $os == "windows") {
            $this->setup_terminal_windows($path, $os);
        }

    }
}

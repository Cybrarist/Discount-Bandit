<?php

use App\Enums\RoleEnum;
use App\Models\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('notification_settings')->nullable();
            $table->text('customization_settings')->nullable();
            $table->text('other_settings')->nullable();
            $table->string('rss_feed')->nullable();

            $table->string('role')->default(RoleEnum::User->value);
            $table->foreignIdFor(Currency::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};

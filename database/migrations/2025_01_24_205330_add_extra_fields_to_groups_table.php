<?php

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
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedInteger("current_price")->nullable();
            $table->unsignedInteger("highest_price")->nullable();
            $table->unsignedInteger("lowest_price")->nullable();
            $table->unsignedTinyInteger('notify_percentage')->nullable()->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn("current_price");
            $table->dropColumn("highest_price");
            $table->dropColumn("lowest_price");
            $table->dropColumn("notify_percentage");
        });
    }
};

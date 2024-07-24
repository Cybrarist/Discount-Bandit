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
        Schema::table('product_store', function (Blueprint $table) {
            $table->string("key" , 100)->nullable();
            $table->unsignedInteger('used_price')->nullable();
            $table->unsignedInteger("highest_price")->nullable();
            $table->unsignedInteger("lowest_price")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_store', function (Blueprint $table) {
            $table->dropColumn("key");
            $table->dropColumn("used_price");
            $table->dropColumn("highest_price");
            $table->dropColumn("lowest_price");
        });
    }
};

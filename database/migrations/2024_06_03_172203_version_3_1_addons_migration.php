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
        Schema::table('price_histories', function (Blueprint $table) {
            $table->unsignedInteger('used_price');
        });

        Schema::table('product_store', function (Blueprint $table) {
            $table->unsignedInteger("highest_price")->nullable();
            $table->unsignedInteger("lowest_price")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

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

        //remove referral from the system
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn("referral");
        });

        //drop asin and switch to keys, and update strings to be nullable
        Schema::table('products', function (Blueprint $table) {
            $table->string('name' , 2000)->change()->nullable();
            $table->string('image' , 2000)->change()->nullable();

        });

        //make product nullable since the unique key will be in product store instead of product table to be able
        //to scale vertically + removal of ebay id to use the key column
        Schema::table('product_store', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Product::class)->nullable()->change();
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

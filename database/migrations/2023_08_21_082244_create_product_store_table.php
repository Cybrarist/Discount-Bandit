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
        Schema::create('product_store', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('notify_price')->nullable();
            $table->string('rate')->default("0");
            $table->unsignedInteger('number_of_rates')->default(0);
            $table->string('seller')->nullable();
            $table->text('coupons')->nullable();
            $table->text('special_offers')->nullable();
            $table->boolean('in_stock')->default(false);
            $table->boolean('lowest_30')->default(false);
            $table->boolean('top_deal')->default(false);
            $table->unsignedInteger('shipping_price')->nullable();
            $table->boolean('add_shipping')->default(false);
            $table->string('condition', 50)->default('new');
            $table->unsignedTinyInteger('notifications_sent')->default(0);


            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Store::class)->constrained()->cascadeOnDelete();
            $table->string('ebay_id')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_store');
    }
};

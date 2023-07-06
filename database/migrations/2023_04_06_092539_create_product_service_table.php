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
        Schema::create('product_service', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Service::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('notify_price')->nullable();
            $table->string('rate')->default("0");
            $table->unsignedInteger('number_of_rates')->default(0);
            $table->string('seller')->nullable();
            $table->text('coupons')->nullable();
            $table->unsignedTinyInteger('shipping_price')->nullable();
            $table->text('special_offers')->nullable();
            $table->boolean('is_prime')->default(false);
            $table->boolean('in_stock')->default(false);
            $table->boolean('lowest_30')->default(false);
            $table->boolean('top_deal')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_service');
    }
};

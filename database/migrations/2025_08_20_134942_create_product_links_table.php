<?php

use App\Enums\ProductConditionEnum;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
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
        Schema::create('product_links', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('key');

            $table->string('name')->nullable();
            $table->string('image')->nullable();

            // data
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('used_price')->default(0);
            $table->unsignedInteger('highest_price')->default(0);
            $table->unsignedInteger('lowest_price')->default(0);
            $table->unsignedInteger('shipping_price')->default(0);
            $table->string('rating')->default('0');
            $table->unsignedInteger('total_reviews')->default(0);
            $table->string('seller')->nullable();
            $table->string('condition')->default(ProductConditionEnum::New->value);
            $table->boolean('is_official')->default(false);
            $table->boolean('is_in_stock')->default(false);

            $table->foreignIdFor(Store::class)->constrained();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();

            $table->unique(['key', 'store_id', 'product_id'], 'key_store_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_links');
    }
};

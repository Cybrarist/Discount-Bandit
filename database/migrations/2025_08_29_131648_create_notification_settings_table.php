<?php

use App\Models\Link;
use App\Models\Product;
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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('price_desired')->nullable();
            $table->unsignedSmallInteger('percentage_drop')->nullable();
            $table->unsignedInteger('extra_costs_amount')->default(0);
            $table->unsignedSmallInteger('extra_costs_percentage')->default(0);
            $table->unsignedSmallInteger('price_lowest_in_x_days')->nullable();
            $table->boolean('is_in_stock')->default(false);
            $table->boolean('any_price_change')->default(false);
            $table->boolean('is_official')->default(false);
            $table->boolean('is_shipping_included')->default(false);

            $table->text('description')->nullable();

            $table->foreignIdFor(Link::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};

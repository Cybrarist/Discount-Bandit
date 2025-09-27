<?php

use App\Enums\ProductStatusEnum;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->text('name')->nullable();
            $table->text('image')->nullable();

            // price information
            $table->unsignedInteger('lowest_price')->nullable();
            $table->unsignedInteger('highest_price')->nullable();

            // notification information
            $table->date('snoozed_until')->nullable();
            $table->unsignedTinyInteger('max_notifications_daily')->nullable();
            $table->unsignedTinyInteger('notifications_sent')->default(0);
            $table->unsignedTinyInteger('remove_link_if_out_of_stock_for_x_days')->nullable();

            // other information
            $table->boolean('is_favourite')->default(false);

            $table->string('status')->default(ProductStatusEnum::Active->value);

            $table->foreignIdFor(User::class)->constrained();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

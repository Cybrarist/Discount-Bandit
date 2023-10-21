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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('asin')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->char('status', 1)->default(\App\Enums\StatusEnum::Published->value);

            $table->boolean('favourite')->default(false);
            $table->boolean("stock")->default(false);
            $table->date('snoozed_until')->nullable();
            $table->unsignedTinyInteger('max_notifications')->nullable();
            $table->unsignedSmallInteger('lowest_within')->nullable();
            $table->boolean('only_official')->default(false);

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

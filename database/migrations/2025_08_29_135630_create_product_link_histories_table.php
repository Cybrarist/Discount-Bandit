<?php

use App\Models\ProductLink;
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
        Schema::create('product_link_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(ProductLink::class)->constrained();
            $table->unsignedInteger('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_link_histories');
    }
};

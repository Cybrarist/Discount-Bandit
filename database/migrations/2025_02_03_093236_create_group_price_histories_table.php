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
        Schema::create('group_price_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date');
            $table->unsignedInteger('price');

            $table->foreignIdFor(\App\Models\Group::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_price_histories');
    }
};

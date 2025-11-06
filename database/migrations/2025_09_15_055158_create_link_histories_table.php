<?php

use App\Models\Link;
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
        Schema::create('link_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->date('date');
            $table->unsignedInteger('price');
            $table->unsignedInteger('used_price');

            $table->foreignIdFor(Link::class)->constrained();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_histories');
    }
};

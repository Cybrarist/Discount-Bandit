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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');
            $table->string('domain');
            $table->string('image');
            $table->string('slug');
            $table->string('referral')->nullable();
            $table->char('status', 1)->default(\App\Enums\StatusEnum::Published->value);
            $table->boolean('tabs')->default(false);

            $table->foreignIdFor(\App\Models\Currency::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};

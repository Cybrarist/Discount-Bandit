<?php

use App\Enums\StoreStatusEnum;
use App\Models\Currency;
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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name');
            $table->string('domain');
            $table->string('image');
            $table->string('slug');
            $table->string('referral')->nullable();
            $table->string('status')->default(StoreStatusEnum::Disabled->value);

            $table->text('custom_settings')->nullable();

            $table->foreignIdFor(Currency::class)->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
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

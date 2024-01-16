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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("name" );
            $table->unsignedInteger("notify_price");
            $table->unsignedInteger("currency_id");

            $table->char('status', 1)->default(\App\Enums\StatusEnum::Published->value);
            $table->date('snoozed_until')->nullable();

            $table->unsignedTinyInteger('max_notifications')->nullable();
            $table->unsignedTinyInteger('notifications_sent')->default(0);
            $table->unsignedSmallInteger('lowest_within')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

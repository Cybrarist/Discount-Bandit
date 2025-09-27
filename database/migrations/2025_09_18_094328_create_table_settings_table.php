<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('table_settings')) {
            Schema::create('table_settings', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->string('resource');
                $table->json('styles')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_settings');
    }
};

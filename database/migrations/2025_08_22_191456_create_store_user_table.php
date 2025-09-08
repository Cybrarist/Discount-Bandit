<?php

use App\Models\Store;
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
        Schema::create('store_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Store::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();

            $table->boolean('is_tabs')->default(false);
            $table->boolean('is_active')->default(false);
            $table->text('default_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_user');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sport_id')->constrained()->onDelete('cascade');
            $table->string('address');
            $table->string('area');          // Jakarta Selatan, Bandung, dll
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('open_hours')->nullable();   // "06:00 - 22:00"
            $table->text('description')->nullable();
            $table->decimal('price_estimate', 10, 0)->nullable(); // estimated hourly price in IDR
            $table->string('contact')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};

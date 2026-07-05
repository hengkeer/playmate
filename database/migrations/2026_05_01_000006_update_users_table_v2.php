<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove old flat fields
            $table->dropColumn(['skill_level', 'preferred_start_time', 'preferred_end_time', 'location']);

            // Add new enriched profile fields
            $table->string('photo_url')->nullable();
            $table->text('bio')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('age_range', 20)->nullable();          // "18-25", "26-35", "36-50"
            $table->string('home_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('play_style', 20)->nullable();         // casual | competitive
            $table->timestamp('last_active_at')->nullable();
            $table->integer('total_matches_joined')->default(0);
            $table->integer('total_events_joined')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'photo_url', 'bio', 'gender', 'age_range',
                'home_address', 'latitude', 'longitude',
                'play_style', 'last_active_at',
                'total_matches_joined', 'total_events_joined',
            ]);

            // Restore old flat fields
            $table->integer('skill_level')->nullable();
            $table->time('preferred_start_time')->nullable();
            $table->time('preferred_end_time')->nullable();
            $table->string('location')->nullable();
        });
    }
};

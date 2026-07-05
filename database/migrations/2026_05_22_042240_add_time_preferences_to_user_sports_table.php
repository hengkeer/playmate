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
        Schema::table('user_sports', function (Blueprint $table) {
            // Preferred playing time (per sport)
            $table->time('preferred_start_time')->nullable()->after('play_style');
            $table->time('preferred_end_time')->nullable()->after('preferred_start_time');
            // Preferred days: JSON array of day numbers (0=Sunday, 6=Saturday)
            $table->json('preferred_days')->nullable()->after('preferred_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sports', function (Blueprint $table) {
            $table->dropColumn(['preferred_start_time', 'preferred_end_time', 'preferred_days']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sport_id')->constrained()->onDelete('cascade');
            $table->string('skill_value')->nullable();   // NTRP 2.0–5.0 for Tennis, level name for others
            $table->integer('skill_number')->nullable();  // numeric: 1–10 or NTRP*10
            $table->string('play_style')->nullable();    // casual | competitive
            $table->timestamps();

            $table->unique(['user_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sports');
    }
};

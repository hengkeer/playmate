<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Tennis, Badminton, Padel
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // emoji or icon class
            $table->string('description')->nullable();
            $table->text('skill_levels')->nullable(); // JSON: [{level: 1, name: 'Beginner', description: ''}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sports');
    }
};

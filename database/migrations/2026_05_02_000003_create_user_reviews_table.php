<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned(); // 1–5
            $table->text('comment')->nullable();
            $table->string('source_type', 50)->nullable(); // 'connection' or 'event'
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            // Prevent duplicate reviews: one reviewer can only review the same user once per source
            $table->unique(['reviewer_id', 'reviewed_user_id', 'source_type'], 'unique_review');
            $table->index(['reviewed_user_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reviews');
    }
};

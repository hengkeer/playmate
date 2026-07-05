<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connection_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('connections')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->enum('type', ['text', 'image', 'file'])->default('text');
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();

            $table->index(['connection_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connection_messages');
    }
};

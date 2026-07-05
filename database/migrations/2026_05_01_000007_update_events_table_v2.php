<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('sport_id')->nullable()->after('host_id')->constrained()->nullOnDelete();
            $table->text('description')->nullable()->after('title');
            $table->string('venue_name')->nullable()->after('description');
            $table->decimal('latitude', 10, 8)->nullable()->after('venue_name');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('max_slots')->default(4)->after('end_time');
            $table->decimal('price', 10, 0)->nullable()->after('max_slots');
            $table->text('payment_info')->nullable()->after('price');
            $table->enum('visibility', ['public', 'private'])->default('public')->after('payment_info');
            $table->enum('match_type', ['singles', 'doubles'])->default('singles')->after('visibility');
            $table->boolean('approval_required')->default(false)->after('match_type');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming')->after('approval_required');

            // Drop old location column (replaced by venue_name + lat/lng)
            $table->dropColumn('location');
        });

        // Update event_participants table
        Schema::table('event_participants', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'waiting'])->default('approved')->after('user_id');
            $table->integer('slot_number')->nullable()->after('status');
            $table->timestamp('joined_at')->nullable()->after('slot_number');
        });
    }

    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn(['status', 'slot_number', 'joined_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('location')->after('end_time');

            $table->dropColumn([
                'sport_id', 'description', 'venue_name',
                'latitude', 'longitude', 'max_slots',
                'price', 'payment_info', 'visibility',
                'match_type', 'approval_required', 'status',
            ]);
        });
    }
};

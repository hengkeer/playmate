<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'host_id', 'sport_id', 'title', 'description',
        'venue_name', 'latitude', 'longitude',
        'start_time', 'end_time',
        'max_slots', 'price', 'payment_info',
        'visibility', 'match_type',
        'approval_required', 'status', 'is_challenge',
    ];

    protected function casts(): array
    {
        return [
            'host_id'           => 'integer',
            'sport_id'          => 'integer',
            'start_time'        => 'datetime',
            'end_time'          => 'datetime',
            'latitude'          => 'float',
            'longitude'         => 'float',
            'max_slots'         => 'integer',
            'price'             => 'float',
            'approval_required' => 'boolean',
            'is_challenge'      => 'boolean',
        ];
    }

    // ─── Relationships ──��─────────────────────────────────────────────────────

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function approvedParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class)->where('status', 'approved');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status', 'slot_number', 'joined_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EventMessage::class)->orderBy('created_at', 'asc');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isFull(): bool
    {
        return $this->approvedParticipants()->count() >= $this->max_slots;
    }

    public function availableSlots(): int
    {
        return max(0, $this->max_slots - $this->approvedParticipants()->count());
    }

    public function hasUser(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function isUserApproved(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming' && $this->start_time->isFuture();
    }

    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    public function isHostedBy(User $user): bool
    {
        return $this->host_id === $user->id;
    }

    /**
     * Check if user can access the group chat.
     */
    public function canAccessChat(User $user): bool
    {
        return $this->isUserApproved($user) || $this->isHostedBy($user);
    }
}
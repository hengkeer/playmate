<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipant extends Model
{
    protected $fillable = ['event_id', 'user_id', 'status', 'is_invite', 'slot_number', 'joined_at'];

    protected function casts(): array
    {
        return [
            'joined_at'  => 'datetime',
            'slot_number' => 'integer',
            'is_invite'  => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isChallengeInvite(): bool
    {
        return $this->is_invite && $this->status === 'pending';
    }
}
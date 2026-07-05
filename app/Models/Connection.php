<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connection extends Model
{
    protected $fillable = ['requester_id', 'receiver_id', 'status'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConnectionMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): BelongsTo
    {
        return $this->hasOne(ConnectionMessage::class)->latestOfMany();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Get the other user in a connection given the current user.
     */
    public function getOtherUser(User $user): User
    {
        return $this->requester_id === $user->id
            ? $this->receiver
            : $this->requester;
    }

    /**
     * Check if a given user is part of this connection.
     */
    public function involvesUser(User $user): bool
    {
        return $this->requester_id === $user->id || $this->receiver_id === $user->id;
    }
}

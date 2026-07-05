<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionMessage extends Model
{
    protected $fillable = [
        'connection_id', 'sender_id', 'message', 'type', 'file_url', 'file_name',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(Connection::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->sender_id === $user->id;
    }
}

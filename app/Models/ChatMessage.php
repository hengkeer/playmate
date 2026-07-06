<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = ['session_id', 'role', 'content', 'tokens_used'];

    protected function casts(): array
    {
        return [
            'session_id'  => 'integer',
            'tokens_used' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    protected $table = 'game_matches';

    protected $fillable = ['user_id', 'opponent_id', 'score', 'scheduled_time'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'scheduled_time' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }
}

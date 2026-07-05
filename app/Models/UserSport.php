<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSport extends Model
{
    protected $table = 'user_sports';

    protected $fillable = [
        'user_id',
        'sport_id',
        'skill_value',
        'skill_number',
        'play_style',
        'preferred_start_time',
        'preferred_end_time',
        'preferred_days',
    ];

    protected function casts(): array
    {
        return [
            'skill_number'  => 'integer',
            'preferred_days' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Human-readable preferred playing time, e.g. "Mon, Wed, Fri · 08:00–11:00".
     * Returns an empty string when no availability is set.
     */
    public function preferredTimeLabel(): string
    {
        $start = $this->preferred_start_time ? substr($this->preferred_start_time, 0, 5) : null;
        $end   = $this->preferred_end_time ? substr($this->preferred_end_time, 0, 5) : null;

        $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $days = collect(is_array($this->preferred_days) ? $this->preferred_days : [])
            ->map(fn ($d) => $labels[(int) $d] ?? null)
            ->filter()
            ->values()
            ->all();

        $parts = [];
        if (!empty($days))     $parts[] = implode(', ', $days);
        if ($start && $end)    $parts[] = $start . '–' . $end;

        return implode(' · ', $parts);
    }
}

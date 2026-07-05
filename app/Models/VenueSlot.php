<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueSlot extends Model
{
    protected $fillable = [
        'venue_id', 'day_of_week', 'start_time', 'end_time', 'price', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week'  => 'integer',
            'price'        => 'float',
            'is_available' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function getDayNameAttribute(): string
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$this->day_of_week] ?? '?';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name', 'sport_id', 'address', 'area',
        'latitude', 'longitude', 'open_hours', 'description',
        'price_estimate', 'contact', 'image_url', 'images', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'       => 'float',
            'longitude'      => 'float',
            'price_estimate' => 'float',
            'is_active'      => 'boolean',
            'images'         => 'array',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(VenueSlot::class);
    }

    /**
     * Calculate distance in km from a given lat/lng using Haversine formula.
     */
    public function distanceFrom(float $lat, float $lng): float
    {
        $R = 6371; // Earth radius in km
        $dLat = deg2rad($this->latitude - $lat);
        $dLng = deg2rad($this->longitude - $lng);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat)) * cos(deg2rad($this->latitude))
            * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}

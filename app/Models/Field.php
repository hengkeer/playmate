<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $fillable = ['name', 'location'];

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}

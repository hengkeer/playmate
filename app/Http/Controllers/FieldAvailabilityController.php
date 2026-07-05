<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;

class FieldAvailabilityController extends Controller
{
    public function index()
    {
        $fields = Field::with('timeSlots')->get();

        return view('field-availability', compact('fields'));
    }
}

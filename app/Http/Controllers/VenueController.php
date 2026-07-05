<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $sportId = $request->get('sport_id');
        $area    = $request->get('area');

        $query = Venue::where('is_active', true)->with(['sport']);

        if ($sportId) {
            $query->where('sport_id', $sportId);
        }
        if ($area) {
            $query->where('area', 'like', "%{$area}%");
        }

        $venues = $query->orderBy('name')->paginate(9)->withQueryString();
        $sports = Sport::all();

        return view('venues.index', compact('venues', 'sports'));
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $venues = Venue::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('area', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
            })
            ->with('sport')
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'area', 'address', 'latitude', 'longitude', 'sport_id']);

        return response()->json($venues->map(fn($v) => [
            'id'        => $v->id,
            'name'      => $v->name,
            'area'      => $v->area,
            'address'   => $v->address,
            'sport'     => $v->sport?->name,
            'latitude'  => $v->latitude,
            'longitude' => $v->longitude,
        ]));
    }

    public function show(Venue $venue)
    {
        $venue->load(['sport']);

        return view('venues.show', compact('venue'));
    }
}

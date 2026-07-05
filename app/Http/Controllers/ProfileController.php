<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\UserSport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('userSports.sport');
        $sports = Sport::all();

        return view('profile', compact('user', 'sports'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'photo'         => 'nullable|image|max:2048', // max 2MB
            'bio'           => 'nullable|string|max:500',
            'gender'        => 'nullable|in:male,female,other',
            'age_range'     => 'nullable|string|max:20',
            'home_address' => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'play_style'   => 'nullable|in:casual,competitive',
            'sports'        => 'nullable|array',
            'sports.*.sport_id' => 'nullable|exists:sports,id',
            'sports.*.skill_number' => 'nullable|integer|min:1', // raw sport value; Sport::normalizeSkill() clamps + normalizes
            'sports.*.play_style'   => 'nullable|in:casual,competitive',
            'sports.*.preferred_start_time' => 'nullable|date_format:H:i',
            'sports.*.preferred_end_time'   => 'nullable|date_format:H:i',
            'sports.*.preferred_days'        => 'nullable|array',
            'sports.*.preferred_days.*'     => 'integer|between:0,6',
        ]);

        $update = $request->only([
            'name', 'bio', 'gender', 'age_range',
            'home_address', 'latitude', 'longitude', 'play_style',
        ]);

        // Handle avatar upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('avatars', 'public');
            $update['photo_url'] = Storage::url($path);
        }

        $user->update($update);

        // Preload all sports once to avoid repeated queries
        $sportsById = Sport::all()->keyBy('id');

        // Update sports — skill_number is sport-specific raw value; normalize to 1–10
        if ($request->has('sports')) {
            $user->userSports()->delete();
            foreach ($request->input('sports') as $sportData) {
                $rawValue = filter_var($sportData['skill_number'] ?? null, FILTER_VALIDATE_INT);
                if (!isset($sportData['sport_id']) || $rawValue === false) {
                    continue;
                }
                $sport = $sportsById->get((int) $sportData['sport_id']);
                $normalized = $sport ? $sport->normalizeSkill($rawValue) : max(1, min(10, $rawValue));

                // Preferred days: JSON array of day numbers
                $preferredDays = null;
                if (!empty($sportData['preferred_days']) && is_array($sportData['preferred_days'])) {
                    $preferredDays = json_encode(array_map('intval', $sportData['preferred_days']));
                }

                UserSport::create([
                    'user_id'                  => $user->id,
                    'sport_id'                 => (int) $sportData['sport_id'],
                    'skill_number'             => $normalized,
                    'skill_value'              => $sport ? $sport->getSkillLabel($rawValue) : "Level $rawValue",
                    'play_style'               => $sportData['play_style'] ?? null,
                    'preferred_start_time'     => $sportData['preferred_start_time'] ?? null,
                    'preferred_end_time'       => $sportData['preferred_end_time'] ?? null,
                    'preferred_days'          => $preferredDays,
                ]);
            }
        }

        $user->update(['last_active_at' => now()]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}

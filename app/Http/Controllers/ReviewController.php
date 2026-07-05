<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Event;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show review form for a user.
     * source_type: 'connection' or 'event'
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'source_type' => 'required|in:connection,event',
            'source_id'   => 'nullable|integer',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);
        $currentUser = Auth::user();

        if ($targetUser->id === $currentUser->id) {
            return back()->withErrors(['user_id' => 'You cannot review yourself.']);
        }

        // Check if already reviewed
        $already = UserReview::where('reviewer_id', $currentUser->id)
            ->where('reviewed_user_id', $targetUser->id)
            ->where('source_type', $validated['source_type'])
            ->exists();

        if ($already) {
            return back()->with('info', 'You have already reviewed this player.');
        }

        return view('reviews.create', [
            'targetUser'  => $targetUser,
            'sourceType'  => $validated['source_type'],
            'sourceId'    => $validated['source_id'] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewed_user_id' => 'required|exists:users,id',
            'source_type'     => 'required|in:connection,event',
            'source_id'       => 'nullable|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:500',
        ]);

        $currentUser = Auth::user();
        $targetId = (int) $validated['reviewed_user_id'];

        if ($targetId === $currentUser->id) {
            return back()->withErrors(['rating' => 'You cannot review yourself.']);
        }

        // Prevent duplicate
        $exists = UserReview::where('reviewer_id', $currentUser->id)
            ->where('reviewed_user_id', $targetId)
            ->where('source_type', $validated['source_type'])
            ->exists();

        if ($exists) {
            return back()->with('info', 'You have already reviewed this player for this connection/event.');
        }

        UserReview::create([
            'reviewer_id'      => $currentUser->id,
            'reviewed_user_id'=> $targetId,
            'rating'          => $validated['rating'],
            'comment'         => $validated['comment'] ?? null,
            'source_type'     => $validated['source_type'],
            'source_id'       => $validated['source_id'] ?? null,
        ]);

        return redirect()
            ->route('player.profile', $targetId)
            ->with('success', 'Review submitted! Thanks for the feedback.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Sport;
use App\Services\MatchmakingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchmakingController extends Controller
{
    public function __construct(private MatchmakingService $matchmakingService)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has completed profile (at least one sport)
        if ($user->userSports()->count() === 0) {
            return redirect()->route('profile.index')->with('error', 'Please complete your profile and add at least one sport before using Matchmaking.');
        }
        // Build filters from request
        $filters = $request->only([
            'sport_id',
            'play_style',
            'start_time',
            'end_time',
            'radius_km',
            'gender',
            'age_range',
            'min_score',
        ]);

        // Parse preferred_days from request (array of day numbers)
        if ($request->has('preferred_days')) {
            $days = $request->input('preferred_days', []);
            $days = is_array($days) ? array_map('intval', $days) : [];
            $filters['preferred_days'] = $days;
        }

        // Exclude connected users filter
        if ($request->has('exclude_connected')) {
            $filters['exclude_connected'] = (bool) $request->input('exclude_connected');
        }

        // Only run the matchmaking engine after an explicit search — the results
        // section stays empty (prompt state) on the initial page load.
        $searched = $request->boolean('searched');
        $recommendations = $searched
            ? $this->matchmakingService->getRecommendations($user, $filters, 30)
            : [];
        $sports = Sport::all();

        // ── Match Reveal Logic ──
        // Show the Challenger Discovered overlay only when the top match meets ALL criteria:
        //   - Score ≥ 0.75 (strong match)
        //   - Both users play the same sport
        //   - At least one strong factor or mutual connection
        $topMatch = !empty($recommendations) ? $recommendations[0] : null;
        $showReveal = false;

        if ($topMatch && $request->boolean('searched')) {
            $components = $topMatch['components'] ?? [];
            $sportScore = $components['sport']['raw'] ?? 0;
            $hasMutual  = ($topMatch['mutual_connections_count'] ?? 0) > 0;

            // When filtering by a non-primary sport, the theoretical max total score is lower
            // (sport component caps at 0.75 instead of 1.0), so we use a lower threshold.
            $filteredSportId = !empty($filters['sport_id']) ? (int) $filters['sport_id'] : null;
            $userPrimarySport = $user->userSports->sortBy('id')->first()?->sport_id;
            $isNonPrimaryFilter = $filteredSportId && $filteredSportId !== $userPrimarySport;

            $scoreThreshold       = $isNonPrimaryFilter ? 0.55 : 0.75;
            $sportScoreThreshold  = 0.75;
            $strongFactorCount    = collect($components)->filter(fn($c) => $c['raw'] >= 0.7)->count();
            $strongFactorRequired = $isNonPrimaryFilter ? 1 : 2;

            $showReveal = $topMatch['score'] >= $scoreThreshold
                && $sportScore >= $sportScoreThreshold
                && ($strongFactorCount >= $strongFactorRequired || $hasMutual);
        }

        // Paginate results (simple offset pagination for in-memory scored results)
        // Exclude topMatch from the grid when reveal is shown
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 12;
        $offset = $showReveal ? ($page - 1) * $perPage + 1 : ($page - 1) * $perPage;
        $gridResults = collect($recommendations)->slice($offset, $perPage)->values()->all();
        $total = count($recommendations) - ($showReveal ? 1 : 0);
        $hasMore = ($page * $perPage) < $total;
        $hasPrev = $page > 1;

        // ── Connection states (existing code) ──
        $connectionStates = [];
        $idsToCheck = $showReveal
            ? array_merge([$topMatch['user']->id], collect($gridResults)->pluck('user.id')->toArray())
            : collect($gridResults)->pluck('user.id')->toArray();

        if (!empty($idsToCheck)) {
            $connections = Connection::with('requester', 'receiver')
                ->where(function ($q) use ($idsToCheck, $user) {
                    $q->where(function ($sub) use ($idsToCheck, $user) {
                        $sub->whereIn('receiver_id', $idsToCheck)->where('requester_id', $user->id);
                    })->orWhere(function ($sub) use ($idsToCheck, $user) {
                        $sub->whereIn('requester_id', $idsToCheck)->where('receiver_id', $user->id);
                    });
                })
                ->get();

            foreach ($idsToCheck as $oppId) {
                $conn = $connections->first(fn ($c) =>
                    ($c->requester_id === $user->id && $c->receiver_id === $oppId)
                    || ($c->receiver_id === $user->id && $c->requester_id === $oppId)
                );
                $connectionStates[$oppId] = $conn;
            }
        }

        return view('matchmaking', compact(
            'recommendations',
            'gridResults',
            'sports',
            'filters',
            'connectionStates',
            'page',
            'hasMore',
            'hasPrev',
            'total',
            'topMatch',
            'showReveal',
            'searched',
        ));
    }
}
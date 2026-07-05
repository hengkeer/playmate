<?php

namespace App\Services;

use App\Models\User;
use App\Models\Connection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rule-based matchmaking scoring service (v3 — July 2026).
 *
 * Scoring weights:
 *   - same sport        : 30%
 *   - skill similarity  : 30%
 *   - time overlap      : 20%
 *   - distance          : 15%
 *   - recent activity   :  5%
 *
 * Final score = sum of all weighted components (0.0 – 1.0).
 * Each recommendation includes a full `components` breakdown for UI display.
 *
 * Optimisations (v3):
 *   - sportId and prefA (searcher) pre-computed once before the scoring loop.
 *   - getBadgesFromPrefs() receives hasTimeOverlap flag from scoring to avoid
 *     redundant hasTimeOverlapInternal() call per candidate.
 */
class MatchmakingService
{
    private const WEIGHT_SPORT    = 0.30;
    private const WEIGHT_SKILL    = 0.30;
    private const WEIGHT_TIME     = 0.20;
    private const WEIGHT_DISTANCE = 0.15;
    private const WEIGHT_ACTIVITY = 0.05;

    /**
     * Main entry: get ranked recommendations for a user with search filters.
     *
     * Returns array of [
     *   user                    => User,
     *   score                   => float (0.00–1.00),
     *   components              => [...],
     *   badges                  => [...],
     *   distance_km             => float|null,
     *   mutual_connections_count => int,
     * ]
     */
    public function getRecommendations(User $user, array $filters = [], int $limit = 30): array
    {
        $query = User::query()
            ->where('id', '!=', $user->id)
            ->with(['userSports.sport']);

        // ── Sport filter ──
        if (!empty($filters['sport_id'])) {
            $query->whereHas('userSports', fn($q) => $q->where('sport_id', $filters['sport_id']));
        }

        // ── Play style filter ──
        if (!empty($filters['play_style'])) {
            $query->whereHas('userSports', fn($q) => $q->where('play_style', $filters['play_style']));
        }

        // ── Gender filter ──
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // ── Age range filter ──
        if (!empty($filters['age_range'])) {
            $query->where('age_range', $filters['age_range']);
        }

        // Note: radius filter applied in PHP — SQLite lacks trig functions.
        $radiusKm = isset($filters['radius_km']) ? (float) $filters['radius_km'] : null;

        $opponents = $query->get();

        // ── Exclude already-connected users (optional) ──
        $excludeConnectedIds = [];
        if (!empty($filters['exclude_connected'])) {
            $excludeConnectedIds = Connection::where('status', 'accepted')
                ->where(fn($q) => $q->where('requester_id', $user->id)->orWhere('receiver_id', $user->id))
                ->get()
                ->map(fn($c) => $c->requester_id === $user->id ? $c->receiver_id : $c->requester_id)
                ->flip()
                ->toArray();
        }

        // ── Pre-compute distances once per opponent (avoid triple Haversine) ──
        $distances = [];
        if ($user->latitude && $user->longitude) {
            foreach ($opponents as $opp) {
                $distances[$opp->id] = $user->distanceFromUser($opp);
            }
        }

        // ── Preload mutual connection counts in one batch query ──
        $mutualCounts = $this->getMutualConnectionCounts($user, $opponents);

        // ── Pre-compute searcher's sportId and time preference once (shared across all candidates) ──
        $searcherSportId = $filters['sport_id'] ?? $this->primarySportId($user);
        $searcherPrefA   = $this->getTimePreference($user, $searcherSportId);

        // ── Minimum score threshold (default 0.25) ──
        $minScore = (float) ($filters['min_score'] ?? 0.25);

        $results = $opponents
            ->filter(function (User $opponent) use ($excludeConnectedIds, $radiusKm, $distances) {
                if (!empty($excludeConnectedIds) && isset($excludeConnectedIds[$opponent->id])) {
                    return false;
                }
                if ($radiusKm !== null && isset($distances[$opponent->id])) {
                    if ($distances[$opponent->id] > $radiusKm) {
                        return false;
                    }
                }
                return true;
            })
            ->map(function (User $opponent) use ($user, $filters, $mutualCounts, $distances, $searcherSportId, $searcherPrefA) {
                // Pass pre-computed distance, sportId, and prefA — no redundant recomputation per candidate
                $distanceKm = $distances[$opponent->id] ?? null;

                $result = $this->calculateScoreWithComponents($user, $opponent, $filters, $distanceKm, $searcherSportId, $searcherPrefA);
                $result['mutual_connections_count'] = $mutualCounts[$opponent->id] ?? 0;

                return $result;
            })
            ->filter(fn($r) => $r['score'] >= $minScore)
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        return $results->all();
    }

    /**
     * Calculate score + per-component breakdown.
     * Accepts pre-computed $distanceKm, $searcherSportId, and $searcherPrefA
     * to avoid redundant Haversine and preference lookups per candidate.
     *
     * @return array{user: User, score: float, components: array, badges: array, distance_km: float|null}
     */
    public function calculateScoreWithComponents(
        User $userA,
        User $userB,
        array $filters = [],
        ?float $distanceKm = null,
        ?int $searcherSportId = null,
        ?array $searcherPrefA = null
    ): array {
        // Use pre-computed values when available (avoids recomputing per candidate in bulk scoring)
        $sportId    = $searcherSportId ?? ($filters['sport_id'] ?? $this->primarySportId($userA));
        $distanceKm = $distanceKm ?? $userA->distanceFromUser($userB);

        // ── Per-component raw scores (0.0–1.0) ──
        $sportRaw = $this->calculateSportScore($userA, $userB, $sportId);
        $skillRaw = $this->calculateSkillScore($userA, $userB, $sportId);

        // Use pre-computed prefA when available; always fetch prefB fresh (it's per-candidate)
        $prefA         = $searcherPrefA ?? $this->getTimePreference($userA, $sportId);
        $prefB         = $this->getTimePreference($userB, $sportId);
        [$timeRaw, $hasTimeOverlap] = $this->calculateTimeScoreFromPrefs($prefA, $prefB, $filters);

        $distRaw     = $this->calculateDistanceScore($distanceKm);
        $activityRaw = $this->calculateActivityScore($userB);

        // ── Weighted total ──
        $total = ($sportRaw    * self::WEIGHT_SPORT)
               + ($skillRaw   * self::WEIGHT_SKILL)
               + ($timeRaw    * self::WEIGHT_TIME)
               + ($distRaw    * self::WEIGHT_DISTANCE)
               + ($activityRaw * self::WEIGHT_ACTIVITY);

        // ── Per-component breakdown for UI ──
        $components = [
            'sport'    => ['raw' => round($sportRaw, 3),    'weight' => self::WEIGHT_SPORT,    'score' => round($sportRaw * self::WEIGHT_SPORT, 3)],
            'skill'    => ['raw' => round($skillRaw, 3),    'weight' => self::WEIGHT_SKILL,    'score' => round($skillRaw * self::WEIGHT_SKILL, 3)],
            'time'     => ['raw' => round($timeRaw, 3),     'weight' => self::WEIGHT_TIME,     'score' => round($timeRaw * self::WEIGHT_TIME, 3)],
            'distance' => ['raw' => round($distRaw, 3),     'weight' => self::WEIGHT_DISTANCE, 'score' => round($distRaw * self::WEIGHT_DISTANCE, 3)],
            'activity' => ['raw' => round($activityRaw, 3), 'weight' => self::WEIGHT_ACTIVITY, 'score' => round($activityRaw * self::WEIGHT_ACTIVITY, 3)],
        ];

        return [
            'user'        => $userB,
            'score'       => round($total, 2),
            'components'  => $components,
            // Pass hasTimeOverlap flag — avoids redundant hasTimeOverlapInternal() call in getBadges
            'badges'      => $this->getBadgesFromPrefs($userA, $userB, $sportId, $prefA, $prefB, $distanceKm, $hasTimeOverlap),
            'distance_km' => $distanceKm,
        ];
    }

    /**
     * Sport match score:
     *   1.0 = both share primary/filtered sport
     *   0.75 = primary of one matches secondary of other
     *   0.5  = non-primary sport overlap
     *   0.0  = no common sport
     */
    public function calculateSportScore(User $userA, User $userB, ?int $sportId): float
    {
        if ($sportId) {
            // Opponent always has the filtered sport (guaranteed by whereHas in getRecommendations).
            // Score based on primary vs secondary placement — punish if neither user has it as primary.
            $bHas = $this->sportIdExistsForUser($userB, $sportId);
            if (!$bHas) return 0.0;

            $aPrimary = $this->primarySportId($userA);
            $bPrimary = $this->primarySportId($userB);

            // Both have it as primary → perfect sport match
            if ($aPrimary === $sportId && $bPrimary === $sportId) return 1.0;
            // Searcher's primary matches opponent's secondary or vice-versa → good
            if ($aPrimary === $sportId || $bPrimary === $sportId) return 0.75;
            // Searcher has the sport as secondary too → acceptable
            if ($this->sportIdExistsForUser($userA, $sportId)) return 0.5;
            // Searcher explicitly filtered this sport but doesn't play it → still show but lower score
            return 0.35;
        }

        $aSports = $this->getUserSportIds($userA);
        $bSports = $this->getUserSportIds($userB);
        $common  = array_intersect($aSports, $bSports);

        if (empty($common)) return 0.0;

        $aPrimary = $this->primarySportId($userA);
        $bPrimary = $this->primarySportId($userB);

        if ($aPrimary && $bPrimary && $aPrimary === $bPrimary) return 1.0;
        if ($aPrimary && in_array($aPrimary, $bSports)) return 0.75;
        if ($bPrimary && in_array($bPrimary, $aSports)) return 0.75;

        return 0.5;
    }

    /**
     * Skill similarity score using band-based decay (normalized to 1–10 scale).
     *
     *   diff 0 → 1.0  (identical)
     *   diff 1 → 0.95
     *   diff 2 → 0.80
     *   diff 3 → 0.60
     *   diff 4 → 0.35
     *   diff ≥5 → 0.15 (decays further by 0.05 per step)
     */
    public function calculateSkillScore(User $userA, User $userB, ?int $sportId): float
    {
        if (!$sportId) return 0.5;

        $sportA = $this->getUserSportForSport($userA, $sportId);
        $sportB = $this->getUserSportForSport($userB, $sportId);

        if (!$sportA?->skill_number || !$sportB?->skill_number) return 0.5;

        $diff = abs($sportA->skill_number - $sportB->skill_number);

        return match(true) {
            $diff === 0 => 1.0,
            $diff === 1 => 0.95,
            $diff === 2 => 0.80,
            $diff === 3 => 0.60,
            $diff === 4 => 0.35,
            default     => max(0.0, 0.15 - ($diff - 5) * 0.05),
        };
    }

    /**
     * Time overlap score computed from already-fetched preference arrays.
     * Called internally to avoid re-fetching when prefs are already known.
     *
     * Returns [score, hasOverlap] where:
     *   score      : 1.0 = full overlap, 0.5 = partial, 0.0 = no overlap
     *   hasOverlap : bool passed to getBadgesFromPrefs to skip redundant check
     *
     * @return array{float, bool}
     */
    private function calculateTimeScoreFromPrefs(array $prefA, array $prefB, array $filters = []): array
    {
        $startA = $filters['start_time'] ?? $prefA['start'];
        $endA   = $filters['end_time']   ?? $prefA['end'];
        $startB = $prefB['start'];
        $endB   = $prefB['end'];

        if (!$startA && !$endA && !$startB && !$endB) return [0.5, false];
        if ((!$startA || !$endA) && (!$startB || !$endB)) return [0.5, false];

        // Day overlap
        $daysA    = $filters['preferred_days'] ?? $prefA['days'] ?? [];
        $daysB    = $prefB['days'] ?? [];
        $dayScore = 0.5;
        if (!empty($daysA) && !empty($daysB)) {
            $dayScore = !empty(array_intersect($daysA, $daysB)) ? 1.0 : 0.0;
        }

        // Time window overlap
        if ($startA && $endA && $startB && $endB) {
            $sA = $this->toCarbonTime($startA);
            $eA = $this->toCarbonTime($endA);
            $sB = $this->toCarbonTime($startB);
            $eB = $this->toCarbonTime($endB);

            if ($sA < $eB && $sB < $eA) {
                $overlapMins = max(0, $this->toCarbonTime(max($startA, $startB))
                    ->diffInMinutes($this->toCarbonTime(min($endA, $endB))));
                $minDuration = min($sA->diffInMinutes($eA), $sB->diffInMinutes($eB));

                $timeScore = $minDuration > 0 ? $overlapMins / $minDuration : 1.0;

                return [round(($timeScore + $dayScore) / 2, 3), true];
            }
        } elseif ((!$startB || !$endB) && $startA && $endA) {
            return [round(0.5 + ($dayScore * 0.5), 3), false];
        } elseif ((!$startA || !$endA) && $startB && $endB) {
            return [round(0.5 + ($dayScore * 0.5), 3), false];
        }

        return [0.0, false];
    }

    /**
     * Distance score using continuous exponential decay exp(-d/10).
     *   0 km  → 1.0
     *   5 km  → 0.61
     *   10 km → 0.37
     *   20 km → 0.14
     *   50 km → ~0.01
     */
    public function calculateDistanceScore(?float $distanceKm): float
    {
        if ($distanceKm === null) return 0.5;
        return round(max(0.0, exp(-$distanceKm / 10)), 3);
    }

    /**
     * Activity score — 5 tiers based on last_active_at.
     */
    public function calculateActivityScore(User $user): float
    {
        if (!$user->last_active_at) return 0.0;

        $days = $user->last_active_at->diffInDays(now());

        return match(true) {
            $days <= 3  => 1.0,
            $days <= 7  => 0.75,
            $days <= 30 => 0.5,
            $days <= 90 => 0.25,
            default     => 0.0,
        };
    }

    /**
     * Internal badge builder — uses pre-fetched prefs, distance, and hasTimeOverlap flag.
     * The flag is passed from calculateTimeScoreFromPrefs to avoid a redundant
     * hasTimeOverlapInternal() call that would recompute the same Carbon comparisons.
     */
    private function getBadgesFromPrefs(
        User $searcher,
        User $opponent,
        ?int $sportId,
        array $prefA,
        array $prefB,
        ?float $distanceKm,
        bool $hasTimeOverlap = false
    ): array {
        $badges = [];

        // Skill badge
        if ($sportId) {
            $skillA = $this->getUserSportForSport($searcher, $sportId)?->skill_number;
            $skillB = $this->getUserSportForSport($opponent, $sportId)?->skill_number;
            if ($skillA !== null && $skillB !== null) {
                $diff = abs($skillA - $skillB);
                if ($diff === 0)      $badges[] = 'Same Level';
                elseif ($diff <= 1)  $badges[] = 'Similar Skill';
            }
        }

        // Distance badge — uses pre-computed value
        if ($distanceKm !== null) {
            if ($distanceKm <= 5)       $badges[] = 'Nearby';
            elseif ($distanceKm <= 15)  $badges[] = 'Close';
        }

        // Activity badge
        if ($opponent->isRecentlyActive(7))       $badges[] = 'In Form';
        elseif ($opponent->isRecentlyActive(30))  $badges[] = 'Active';

        // Schedule badge — uses flag from calculateTimeScoreFromPrefs, no extra Carbon call
        if ($hasTimeOverlap) {
            $badges[] = 'Schedule Match';
        }

        return $badges;
    }

    /**
     * Mutual connection count between the current user and each opponent.
     * Batch query + adjacency list — O(1) per opponent, one DB round-trip.
     */
    public function getMutualConnectionCounts(User $user, $opponents): array
    {
        if ($opponents->isEmpty()) return [];

        $oppIds     = $opponents->pluck('id')->toArray();
        $allUserIds = array_unique(array_merge([$user->id], $oppIds));

        $allConnections = Connection::where('status', 'accepted')
            ->where(function ($q) use ($allUserIds) {
                $q->whereIn('requester_id', $allUserIds)
                  ->orWhereIn('receiver_id', $allUserIds);
            })
            ->get();

        // Build adjacency list
        $adj = array_fill_keys($allUserIds, []);

        foreach ($allConnections as $conn) {
            $adj[$conn->requester_id][$conn->receiver_id] = true;
            $adj[$conn->receiver_id][$conn->requester_id] = true;
        }

        $userConns = $adj[$user->id] ?? [];

        $counts = [];
        foreach ($oppIds as $oppId) {
            $counts[$oppId] = count(array_intersect_key($userConns, $adj[$oppId] ?? []));
        }

        return $counts;
    }

    // ─────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────

    private function hasTimeOverlapInternal(array $prefA, array $prefB): bool
    {
        if (!$prefA['start'] || !$prefA['end'] || !$prefB['start'] || !$prefB['end']) {
            return false;
        }
        return $this->toCarbonTime($prefA['start']) < $this->toCarbonTime($prefB['end'])
            && $this->toCarbonTime($prefB['start']) < $this->toCarbonTime($prefA['end']);
    }

    private function getTimePreference(User $user, ?int $sportId): array
    {
        $us = $sportId
            ? $this->getUserSportForSport($user, $sportId)
            : $this->getFirstUserSport($user);

        if (!$us) return ['start' => null, 'end' => null, 'days' => []];

        $days = [];
        if ($us->preferred_days) {
            $raw = is_string($us->preferred_days) ? json_decode($us->preferred_days, true) : $us->preferred_days;
            if (is_array($raw)) $days = array_map('intval', $raw);
        }

        return [
            'start' => $us->preferred_start_time,
            'end'   => $us->preferred_end_time,
            'days'  => $days,
        ];
    }

    private function primarySportId(User $user): ?int
    {
        if ($user->relationLoaded('userSports')) {
            return $user->userSports->sortBy('created_at')->first()?->sport_id;
        }
        return $user->userSports()->orderBy('created_at')->first()?->sport_id;
    }

    private function toCarbonTime(string|\DateTimeInterface|Carbon|null $time): Carbon
    {
        if (!$time) return Carbon::now();
        if ($time instanceof Carbon) return $time->copy()->setDate(2000, 1, 1);
        if ($time instanceof \DateTimeInterface) return Carbon::instance($time)->setDate(2000, 1, 1);
        return Carbon::createFromFormat('H:i', substr($time, 0, 5))->setDate(2000, 1, 1);
    }

    private function getUserSportIds(User $user): array
    {
        if ($user->relationLoaded('userSports')) {
            return $user->userSports->pluck('sport_id')->toArray();
        }
        return $user->userSports()->pluck('sport_id')->toArray();
    }

    private function sportIdExistsForUser(User $user, int $sportId): bool
    {
        if ($user->relationLoaded('userSports')) {
            return $user->userSports->contains('sport_id', $sportId);
        }
        return $user->userSports()->where('sport_id', $sportId)->exists();
    }

    private function getUserSportForSport(User $user, int $sportId): ?\App\Models\UserSport
    {
        if ($user->relationLoaded('userSports')) {
            return $user->userSports->firstWhere('sport_id', $sportId);
        }
        return $user->userSports()->where('sport_id', $sportId)->first();
    }

    private function getFirstUserSport(User $user): ?\App\Models\UserSport
    {
        if ($user->relationLoaded('userSports')) {
            return $user->userSports->sortBy('created_at')->first();
        }
        return $user->userSports()->orderBy('created_at')->first();
    }
}

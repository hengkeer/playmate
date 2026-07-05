# Matchmaking Algorithm — 5-Component Scoring Engine v2

> PlayMate — AI Matchmaking Reference
> File: `app/Services/MatchmakingService.php`
> Last updated: May 2026

---

## 1. Scoring Weights

| Component | Weight | Question Answered |
|---|---|---|
| Sport | 30% | Do both users play the same sport? |
| Skill | 30% | Are they at similar skill levels? |
| Time | 20% | Do their preferred times overlap? |
| Distance | 15% | How close are they geographically? |
| Activity | 5% | Is the user recently active? |

---

## 2. Per-Component Scoring

### 2.1 Sport Score (weight: 0.30)

**v1 (old):** Binary — 1.0 if same sport, 0.0 otherwise.
**v2:** Partial scores based on primary vs. secondary sport overlap (no filter).
**v3 (current):** Two modes depending on whether `sport_id` filter is active.

**Mode A — no sport filter** (free search):

| Condition | Score |
|---|---|
| Both same primary sport | 1.0 |
| One user's primary = other's secondary | 0.75 |
| Same sport but neither primary | 0.5 |
| No common sport | 0.0 |

**Mode B — sport filter active** (`sport_id` set, opponents guaranteed to have it via `whereHas`):

| Condition | Score |
|---|---|
| Both have filtered sport as primary | 1.0 |
| Either has filtered sport as primary | 0.75 |
| Searcher has it as secondary | 0.5 |
| Searcher doesn't play it at all | 0.35 |

> **Why not 0.0 when searcher doesn't play it?** The explicit `sport_id` filter expresses intent — the user is looking for that sport. Returning 0.0 would make ranking dependent only on distance/activity (remaining 40%), producing nonsensical ordering.

### 2.2 Skill Score (weight: 0.30)

**v1 (old):** Linear decay `1 - (diff / 10)`
**v2 (new):** Band-based smooth decay

| Skill Difference | Score |
|---|---|
| 0 | 1.00 |
| 1 | 0.95 |
| 2 | 0.80 |
| 3 | 0.60 |
| 4 | 0.35 |
| ≥5 | 0.15 |

All skills normalized to 1–10 scale before comparison. See `docs/models.md` for normalization formula.

### 2.3 Time Score (weight: 0.20)

**v1 (old):** Binary `hasTimeOverlap()` — always returned `false` (column missing).
**v2 (new):** Nuanced 0.0–1.0 based on:
- **Time range overlap ratio** — what fraction of the overlapping time window exists
- **Day intersection** — how many preferred days overlap

**Symmetric fallback:** If only one user has time preferences, returns `0.5 + (dayScore * 0.5)`. Both directions now score fairly.

Reads from: `user_sports.preferred_start_time`, `user_sports.preferred_end_time`, `user_sports.preferred_days`.

### 2.4 Distance Score (weight: 0.15)

**v1 (old):** Discrete tier — 2km→high, 5km→medium, 10km→low, 20km→very low.
**v2 (new):** Continuous exponential decay: `exp(-d / 10)`

| Distance (km) | Score |
|---|---|
| 0 | 1.00 |
| 5 | 0.61 |
| 10 | 0.37 |
| 15 | 0.22 |
| 20 | 0.14 |
| 30 | 0.05 |

### 2.5 Activity Score (weight: 0.05)

**v1 (old):** 3 tiers — ≤3 days→1.0, ≤7→0.75, ≤30→0.5, >30→0.0.
**v2 (new):** 5 tiers using `last_active_at` column.

| Days Since Active | Score |
|---|---|
| ≤3 | 1.00 |
| ≤7 | 0.75 |
| ≤30 | 0.50 |
| ≤90 | 0.25 |
| >90 | 0.00 |

---

## 3. Filter Parameters

| Filter | Type | Default | Notes |
|---|---|---|---|
| `sport_id` | int | null | Filter by specific sport |
| `play_style` | string | null | 'casual' or 'competitive' |
| `gender` | string | null | 'male', 'female', 'other' |
| `age_range` | string | null | '18-25', '26-35', '36-50', '51+' |
| `radius_km` | float | null | Haversine distance filter (applied in PHP, not SQL) |
| `start_time` | time | null | Preferred playing time from |
| `end_time` | time | null | Preferred playing time to |
| `preferred_days` | array | [] | Day numbers 0=Sun…6=Sat |
| `min_score` | float | 0.25 | Minimum match quality threshold |
| `exclude_connected` | bool | false | Hide users with accepted connections |

**⚠️ Radius filter in PHP, not SQL.**
SQLite lacks trigonometric functions (`acos/cos/sin/radians`) required for Haversine. Distance filter applied in `MatchmakingService` via `User::distanceFromUser()` after fetching candidates.

---

## 4. Skill Systems per Sport

| Sport | Scale | Labels |
|---|---|---|
| Tennis | 1–12 | 1.0 Beginner → 1.5 → 2.0 → ... → 5.5 → 6.0+ → 7.0 Elite |
| Badminton | 1–8 | Beginner → Lower Intermediate → Intermediate → Upper Intermediate → Advanced → Competitive → Tournament → Elite |
| Padel | 1–7 | Beginner → Casual → Improving → Intermediate → Advanced → Competitive → Elite |

### Normalization Formula

```
(value - 1) / (maxValue - 1) * 9 + 1  →  round  →  clamp to 1–10
```

| Sport | Raw → Normalized |
|---|---|
| Tennis | 1→1, 6→5, 7→6, 12→10 |
| Badminton | 1→1, 4→4, 8→10 |
| Padel | 1→1, 4→5, 7→10 |

---

## 5. Public API

```php
// Main entry point
getRecommendations(User $user, array $filters = [], int $limit = 30): array
// Returns: [{
//   user: User,
//   score: float (0.00–1.00),
//   components: {sport: {raw, weight, score}, skill: {...}, time, distance, activity},
//   badges: array,
//   distance_km: float|null,
//   mutual_connections_count: int
// }]

// Score breakdown (used for UI)
calculateScoreWithComponents(User $a, User $b, array $filters = [], ?float $distanceKm = null): array

// Backwards compatible
calculateScore(User $a, User $b, array $filters = []): float

// Individual components
calculateSportScore(User $a, User $b, ?int $sportId): float
calculateSkillScore(User $a, User $b, ?int $sportId): float
calculateTimeScore(User $a, User $b, array $filters = []): float
calculateDistanceScore(User $user, ?float $distanceKm): float
calculateActivityScore(User $user): float

// Badges
getBadges(User $searcher, User $opponent, ?int $sportId = null): array
// Returns: ['⚡ Same Level'] | ['⚡ Similar Skill'] | ['📍 Nearby'] | ['📍 Close']
//          | ['🔥 In Form'] | ['🟡 Active'] | ['⏰ Same Time']

// Batch mutual connections (performance critical)
getMutualConnectionCounts(User $user, $opponents): array
```

---

## 6. Performance Optimizations (N+1 Fixes)

**Problem:** Original implementation had ~218 DB queries per page load.

### 6.1 `getMutualConnectionCounts()` — Batch Query + Adjacency List
- **Before:** O(n) — one query per opponent
- **After:** Single batch query for all users + in-memory adjacency list
- **Impact:** 30 opponents: 31 queries → 1 query

### 6.2 `calculateSportScore()` / `calculateSkillScore()` — Preloaded Collections
- **Before:** `$user->userSports()->where()->first()` per call (ignores eager load)
- **After:** Helper methods check `relationLoaded()` and use in-memory collection
- **Impact:** 120 queries → 0 queries (uses preload)

### 6.3 `getTimePreference()` — Collection-Aware
- **Before:** `$user->userSports()->where()->first()` per call
- **After:** Uses `getUserSportForSport()` helper that respects preloaded data
- **Impact:** Eliminates per-user time preference queries

### 6.4 `primarySportId()` — Collection-Aware
- **Before:** Always queries `userSports()->orderBy()->first()`
- **After:** Uses preloaded collection when available
- **Impact:** Reduces redundant queries in badge generation

**Total reduction:** ~218 queries → ~5 queries per page load (96% reduction).

---

## 7. Score Bar UI Fix

**Bug:** Hardcoded divisor `0.30` (WEIGHT_SPORT) for all components.
**Example:** Distance (weight 0.15, score 0.03) showed 10% instead of 20%.

**Fix:**
```blade
width: {{ $comp['weight'] > 0 ? round($comp['score'] / $comp['weight'] * 100) : 0 }}%
```

---

## 8. Criteria Match Summary

Auto-generated per card:
- **Green box:** "✓ Strong match: Sport, Skill, Distance" (raw score ≥ 0.7)
- **Amber box:** "⚠ Weak: Time, Activity" (raw score < 0.3)

---

## 9. Challenger Discovered Overlay

Triggered when top match meets ALL criteria. Thresholds differ based on whether the filtered sport is the searcher's primary sport:

| Criterion | Primary sport filter | Non-primary sport filter |
|---|---|---|
| Total score | ≥ 0.75 | ≥ 0.55 |
| Sport score (`components['sport']['raw']`) | ≥ 0.75 | ≥ 0.75 |
| Strong factors (component raw ≥ 0.7) | ≥ 2 or mutual | ≥ 1 or mutual |

**Why lower thresholds for non-primary?** When filtering a sport that is not the searcher's primary, the sport score caps at 0.75 (instead of 1.0), which reduces the theoretical total score ceiling by ~0.075. Separate thresholds ensure the overlay fires fairly regardless of which sport is searched.

`userPrimarySport` = `$user->userSports->sortBy('id')->first()?->sport_id` (first sport added = primary).

**Challenge flow:**
```
⚔️ Challenge! button → /events/challenge/{user}
  → event create form with opponent pre-filled
  → opponent_id hidden field
  → on store, opponent auto-added as pending participant
```

**Grid adjustment:** When reveal is shown, top match is excluded from paginated `gridResults`.

---

## 10. Filter Presets

- **Storage:** Browser `localStorage` (`playmate_match_presets`)
- **UI:** "💾 Save Current Filters" button + preset chips with delete option
- **Use case:** User frequently searches "Tennis Players Jakarta 4.0–5.0" → save as preset

---

## See also

- [docs/architecture.md](architecture.md) — AI assistant integration
- [docs/features.md](features.md) § AI Assistant — UX details
- `php artisan rag:build` — regenerate RAG index from this file
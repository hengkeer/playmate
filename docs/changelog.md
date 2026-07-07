# Changelog — Bug Fixes & Feature Updates

> PlayMate — All resolved bugs, fixes, and feature additions
> Last updated: June 2026

---

## July 2026 — Matchmaking Challenge Flow & Event Privacy

### Matchmaking: Direct Challenge Button
**Feature:** Added a direct `CHALLENGE` button on the recommended opponents cards in the matchmaking grid, matching the action available in the match reveal overlay.

### Event System: Challenge Privacy & Deletion on Decline
**Feature:** Matchmaking challenges now create private, isolated events.
- Added `is_challenge` boolean to `events` table (default `false`).
- When an event is created via the challenge flow (`opponent_id` is present), it automatically sets `is_challenge = true` and `visibility = private`.
- **Public Feed:** `EventController@index` explicitly excludes events where `is_challenge = true`.
- **Lifecycle Cleanup:** In `MyEventsController@declineChallenge`, if the opponent declines an invite and the event is a challenge event, the entire event is deleted automatically, preventing empty "zombie" events.

### AI Chatbot: General Info RAG Fix
**Fix:** Added `docs/general-info.md` detailing what PlayMate is, and adjusted the `ChatbotService` prompt to relax restrictions on general questions. The chatbot can now successfully answer "Apa yang Anda ketahui tentang Playmate".

---

## June 2026 — Week 4 (continued): Profile Sports, Matchmaking Filter Fixes

### Profile — Sports Editor: Multiple Sports Now Addable

**Bug:** `@forelse/@empty` split meant users with ≥1 sport only saw their existing rows — no empty slot to add a second sport. Users with 1 sport (e.g. Tennis) could not add Badminton.

**Fix:** Replaced `@forelse/@empty` with two explicit loops:
1. Loop existing `userSports` rows (pre-filled)
2. Loop empty rows to fill up to 3 total (`$emptyCount = 3 - existing count`)

| State | Before | After |
|---|---|---|
| 0 sports | 3 empty rows | 3 empty rows |
| 1 sport | 1 filled row, no empty | 1 filled + 2 empty |
| 2 sports | 2 filled rows, no empty | 2 filled + 1 empty |
| 3 sports | 3 filled rows | 3 filled rows |

Empty slot placeholder text: "Select sport..." (first slot) / "Add another sport (optional)" (subsequent).

---

### Matchmaking — Non-Primary Sport Filter Fixes

#### Sport Score: Non-Primary Filter No Longer Returns 0.0

**Bug:** `calculateSportScore()` returned `0.0` if the searcher didn't have the filtered sport as any of their sports. When searching Padel as a Tennis-primary user, all Padel opponents received sport score = 0.0, making ranking based purely on distance/activity.

**Fix:** When `sport_id` filter is set, sport score now reflects primary/secondary placement:

| Condition | Score |
|---|---|
| Both have filtered sport as primary | 1.0 |
| Either has filtered sport as primary | 0.75 |
| Searcher has it as secondary | 0.5 |
| Searcher doesn't play it at all | 0.35 |

#### Card Display: Filtered Sport Shown First

**Bug:** `$opp->sports->take(3)` displayed sports in default order (primary first). A Tennis-primary/Padel-secondary player appeared to be a "Tennis player" when found via Padel filter.

**Fix:** When `$filters['sport_id']` is set, the matching sport is sorted to the front and highlighted in orange. Other sports remain grey.

#### Reveal Overlay: Threshold Adjusted for Non-Primary Sport

**Bug:** `$sportScore >= 1.0` threshold was never reachable for non-primary sport filters (max 0.75). Also total score threshold of 0.75 was unreachable since sport component is capped at 0.75 × 0.30 = 0.225 instead of 0.30.

**Fix:** Detect whether the filtered sport differs from the searcher's primary sport, and apply lower thresholds:

| Threshold | Primary sport filter | Non-primary sport filter |
|---|---|---|
| Total score | >= 0.75 | >= 0.55 |
| Sport score | >= 0.75 | >= 0.75 |
| Strong factors | >= 2 or mutual | >= 1 or mutual |

`userPrimarySport` detected via `$user->userSports->sortBy('id')->first()?->sport_id`.

---

## June 2026 — Week 4: Data Fixes, UX Polish & Emoji Cleanup

### Venue Data Corrections (DB)

**Sport type swap fixed:** Venues 1–10 (Tennis names) had `sport_id=2` (Badminton); venues 11–15 (Badminton names) had `sport_id=1` (Tennis). Root cause: seeder assigned IDs in wrong order.

| Fix | Detail |
|---|---|
| Venues 1–10 | `sport_id` corrected to `1` (Tennis) |
| Venues 11–15 | `sport_id` corrected to `2` (Badminton) |
| Venues 16–19 | Already correct (Padel) |

**Addresses fixed:** 5 venues had empty `address`, several had duplicate city suffix, one had typo.

| ID | Venue | Fix |
|---|---|---|
| 4 | GOAT Tennis Arena | Address filled: Jl. Pondok Betung Raya No. 9A, Pondok Aren |
| 7 | Maison Tennis Playcourt | Address filled: Jl. Taman Makam Bahagia No.164, Parigi |
| 9 | Armor Tennis Outdoor | Address filled: Jl. Mutiara 3 No. 105, Serpong Utara |
| 10 | RR Tennis Court | Address filled: Modern Hill, Pondok Cabe Udik, Pamulang |
| 17 | Gas Padel Court | Address filled: Jl. Aria Putra No. 76, Ciputat |
| 5,6,8,12–16,18,19 | Multiple | Removed duplicate "Kota Tangerang Selatan Banten" suffix from address string |
| 15 | Hall Badminton Merpati | Fixed typo: "Kota tangerang sekarang" → "Kota Tangerang Selatan" |

Venue count in `seeded-data.md` updated: 9 → 19 (10 tennis, 5 badminton, 4 padel).

---

### "Host an Event Here" → Pre-fill Fix

**Bug:** Clicking "Host an Event Here" on `venues/show` passed `?venue_id=X` but the create form ignored it — venue name was blank, map stayed at default Jakarta coords, sport was unset.

**Fix:**

| File | Change |
|---|---|
| `EventController::create()` | Resolves `?venue_id` via `Venue::find()`, passes `$venue` to view |
| `events/create.blade.php` | Passes `$venue->name`, `$venue->latitude`, `$venue->longitude` as props to `<x-venue-picker>` |
| `events/create.blade.php` | Sport dropdown auto-selects `$venue->sport_id` when coming from venue page |
| `venue-picker` component | Added `selectedLat` + `selectedLng` props; `x-init` dispatches `playmate:venue-selected` so map pans immediately on page load |

---

### Upcoming Events — Landing Page Fix & Redesign

**Bug:** Query used `event_date` column (does not exist); correct column is `start_time`. Section was always empty.

**Redesign:** Removed photo placeholder, added sport badge, proper date block, no emojis.

| Change | Detail |
|---|---|
| Column fix | `where('event_date', ...)` → `where('start_time', '>=', now())` |
| Eager load | Added `->with('sport')` |
| Card header | Date block (month label + day number) + sport badge (right) |
| Card body | Title, venue name, 2-line description |
| Card footer | Players count (`N / max`) + "View Event →" |
| Empty state | "No upcoming events right now" + "Host the First One" button |

---

### Events Index — Hide Past Events

**Change:** `EventController::index()` previously showed events from 90 days in the past (`subDays(90)`) to allow the page to appear non-empty with old seeded data. Now filters to upcoming only.

| Before | After |
|---|---|
| `where('start_time', '>=', now()->subDays(90))` + upper bound | `where('start_time', '>=', now())` |

---

### Profile — Skill Level Dropdown Fix

**Bug 1:** In existing sport rows, the sport `<select>` had no `onchange` — changing sport did not update skill level options.

**Bug 2:** In empty (new) sport rows, skill levels used an `<optgroup>` hack — `<optgroup label="...">` elements were shown as non-selectable headers but could still be clicked in some browsers. Options from other sports remained visible even when "disabled".

**Bug 3:** Placeholder "Level" option was selectable (no `disabled` attribute).

**Fix:** Replaced optgroup approach with JS-driven dynamic rebuild.

| Change | Detail |
|---|---|
| `SPORT_LEVELS` JS object | All sports' skill levels embedded as JSON via `@json($sportsList->mapWithKeys(...))` |
| `rebuildSkillLevels(sportSelect)` | Clears and rebuilds the skill level `<select>` from `SPORT_LEVELS[sportId]` on every sport change |
| Both row types | `onchange="rebuildSkillLevels(this)"` added to sport selects in both existing and empty rows |
| Placeholder | `<option value="" disabled>Level</option>` — not selectable |
| Existing rows | Pre-select by `skill_number` value match (not `skill_value` string) |

---

### Emoji Cleanup — All Blade Views

All decorative/alay emojis removed from Blade templates. Functional characters (`★` for ratings/CTA, `✓`/`✕` for approve/decline) retained.

| File | Removed |
|---|---|
| `profile.blade.php` | `$sport->icon` (🎾/🏸/🟡) in sport select options |
| `connections/index.blade.php` | 🤝 empty-state, `$sport->icon` in sport list |
| `events/create.blade.php` | ⚔ in "Challenging" label and "Send Challenge" button |
| `host-game.blade.php` | ⚔ in "Challenging" label and "Send Challenge" button |
| `my-events-host-requests.blade.php` | 🎉 empty-state, `$sport->icon` in sport tags |
| `reviews/create.blade.php` | `$sport->icon` in sport list |

`sport->icon` fallbacks (`'🎾'`) replaced with `$us->sport->name` or removed.

---

## June 2026 — Week 2 (continued): Event Forms, Venue Coordinates & Autocomplete

### Dead Code Removed

| Item | Detail |
|---|---|
| `app/Http/Controllers/GameController.php` | Deleted — no routes, no views, no references |
| `app/Models/Game.php` | Deleted — `games` table exists but model was unused |
| `GameMatch` model | **Kept** — still referenced by `User::gameMatches()` hasMany |

### Venue Coordinates Fixed

19 venues in DB; only 2 had `latitude`/`longitude`. 17 venues showed NULL → Leaflet map on detail page was hidden for all of them.

| Root cause | Scraper output `venues.json` lacked lat/lng for 17 venues; `import:venues` command stored NULL |
|---|---|
| Fix | Coordinates set manually via Tinker based on each venue's area/address (all Kota Tangerang Selatan) |
| Command added | `php artisan venue:geocode` — geocodes NULL-coordinate venues via Nominatim. `--all` flag to re-geocode everything |
| Result | 19/19 venues now have `latitude` + `longitude` |

### `events/create.blade.php` — Full Dark Theme Rewrite

Old `create.blade.php` used light/white theme (sky-600, slate-200, rounded-full, white/95 card) inconsistent with the rest of the app.

**Rewritten to match dark theme:**
- Uses `pm-card`, `pm-input`, `pm-btn-primary`, `pm-section-title`
- Error banner: `border-red-500/40 bg-red-500/5` dark style
- Approval toggle: custom dark CSS toggle (not bare checkbox)
- Challenger banner: same as `host-game.blade.php` (orange border, DIRECT CHALLENGE badge)
- `old()` values preserved on validation fail

### Venue Autocomplete Picker — New Component

New `<x-venue-picker>` Blade component (`resources/views/components/venue-picker.blade.php`) added to both event creation forms.

| Detail | Value |
|---|---|
| Endpoint | `GET /venues/search?q=` (new, no auth required) |
| Route | `venues.search` — registered **before** `venues/{venue}` to avoid route capture |
| Controller method | `VenueController::search()` — searches name/area/address, returns JSON max 8 |
| Debounce | 250ms |
| On select | Fills `venue_name`, dispatches `CustomEvent('playmate:venue-selected', {lat, lng, name})` |
| On manual type | `venue_name` = typed text; no coordinates dispatched |
| Ownership | Owns `name="venue_name"` only — lat/lng owned by map-picker |

### Map Picker — Venue Event Integration

`map-picker.blade.php` updated to listen for `playmate:venue-selected`:

```js
window.addEventListener('playmate:venue-selected', (e) => {
    const { lat, lng, name } = e.detail;
    this.address = name;
    if (this.map && this.marker) {
        this.setPosition(lat, lng, false); // false = skip reverse geocode
    } else {
        this._pending = { lat, lng, name }; // apply after map renders
    }
});
```

- `setPosition(lat, lng, reverseGeocode)` — new `reverseGeocode` param. `false` when driven by venue-picker or Nominatim result. `true` when user clicks/drags map.
- `this._pending` — stores venue selection that arrives before Leaflet finishes loading. Applied inside `setTimeout(invalidateSize)` callback after `renderMap()`.
- Removed `window._playmate_map` / `window._playmate_marker` global refs (replaced by event-based communication).

### Field Ownership — No Duplicate Inputs

| Field | Owned by |
|---|---|
| `venue_name` | `<x-venue-picker>` |
| `latitude` | `<x-map-picker lat-id="latitude">` |
| `longitude` | `<x-map-picker lng-id="longitude">` |
| `map_address` | `<x-map-picker addr-id="map_address">` (helper field, not validated by controller) |

`addr-id="map_address"` (not `venue_name`) prevents map-picker from overwriting the venue autocomplete's `venue_name` hidden input.

---

## May 2026 — Week 3: Data Audit & Seed Refresh

### Data Problems Fixed

| Problem | Severity | Fix |
|---|---|---|
| `last_active_at` all within 0–25 days before May 4 | High | Regenerated with full 0–40 day spread → all 5 activity score tiers now covered |
| Zero time preferences in `user_sports` | High | Added `preferred_start_time`, `preferred_end_time`, `preferred_days` to primary sport of 20/32 core users |
| Skill value stored as generic "Level N" labels | Medium | All normalized via `Sport::normalizeSkill()` using sport-specific max values |
| Only 4 connections (3 pending, 1 accepted) | High | Expanded to 24 connections: 16 accepted (mutual clusters) + 8 pending |
| Only 2 reviews | High | Expanded to 21 reviews across major players (connection + event sourced) |
| 10 events, only 8 relevant to new users | Medium | Seeded 14 new events (19 total), 1–14 days ahead, with proper participant lists |
| `Court Terre Arena` venue missing | Low | Added via Tinker command; referenced by Erlangga's event |
| Roger Federer `skill_value` empty, no time prefs | Medium | Updated to Tennis 3 (norm 4) with weekend morning preferences |
| Angga (id=34) has no sports or coords | Low | Preserved as-is (protected) — not deleted |

### New DataSeeder (`database/seeders/DataSeeder.php`)

Replaces old inline seeders in `DatabaseSeeder`. Idempotent — uses `updateOrCreate`.

| Feature | Detail |
|---|---|
| Protected users | Erlangga, Angga, Roger Federer never overwritten |
| Time preferences | Added to primary sport only; realistic Jakarta play times |
| Mutual connection clusters | 5 clusters trigger mutual badge on matchmaking cards |
| Distance decay coverage | 5 far-zone users (Bandung → Malang) demonstrate exp(-d/10) decay |
| Activity score variance | All 5 tiers: ≤3d / ≤7d / ≤30d / ≤90d / >90d |
| Challenge overlay trigger | Galih Ramadhan is top match for Rizky: score=0.91, sport=1.0, skill=1.0, mutual=1 → ⚔️ Challenger Discovered shown |
| Sports data | NTRP 6 (norm 5) for Rizky tennis → enables skill band scoring demonstration |
| Event participant realism | Host as approved slot 1, specific users per event (not random pool) |

### Database Stats After Refresh

| Table | Before | After |
|---|---|---|
| Connections | 4 | 24 |
| UserReviews | 2 | 21 |
| Events | 10 | 19 |
| EventParticipants | 32 | 72 |
| Venues | 8 | 9 |
| UserSports with time prefs | 0 | ~20 |

---

## May 2026 — Week 2: Matchmaking v2 Upgrade

### Performance: Query Reduction (~218 → ~5 queries, 96% reduction)

**Bug:** ~218 DB queries per matchmaking page load due to N+1 issues.

**Fixes applied:**

1. **`getMutualConnectionCounts()`** — O(n) → O(1) via batch query + in-memory adjacency list
2. **`calculateSportScore()` / `calculateSkillScore()`** — Now check `relationLoaded()` and use preloaded collections (120 queries → 0)
3. **`getTimePreference()`** — Collection-aware via `getUserSportForSport()` helper
4. **`primarySportId()`** — Uses preloaded collection when available

### Scoring Algorithm: v1 → v2

| Component | v1 Bug / Limitation | v2 Fix |
|---|---|---|
| Sport | Binary (0/1) | Partial scores: 0.0/0.5/0.75/1.0 based on primary vs secondary |
| Skill | Linear decay `1 - (diff/10)` | Band-based: diff 0→1.0, 1→0.95, 2→0.80, 3→0.60, 4→0.35, ≥5→0.15 |
| Time | Binary, always `false` (column missing) | Nuanced 0.0–1.0 based on overlap ratio + day intersection |
| Distance | Discrete 2/5/10/20km tiers | Continuous `exp(-d/10)` decay |
| Activity | 3 tiers | 5 tiers: ≤3→1.0, ≤7→0.75, ≤30→0.5, ≤90→0.25, >90→0.0 |
| Radius filter | In-memory after scoring (broken) | Applied in PHP via `User::distanceFromUser()` |
| Time score | Asymmetric (A→B vs B→A different) | Symmetric fallback `0.5 + (dayScore * 0.5)` |

### New Features

| Feature | Description |
|---|---|
| Minimum score threshold filter | `min_score` filter: 25% (All), 30% (Fair), 40% (Good), 50% (Strong) |
| Exclude connected users | `exclude_connected` checkbox hides accepted connections |
| Filter presets | localStorage `playmate_match_presets` — save/load filter combinations |
| Criteria match summary | Auto-generated green/amber box showing strong/weak factors |
| Last active display | `Carbon::diffForHumans()` — "Active 2 days ago" replaces generic badges |
| Challenger Discovered overlay | Fullscreen reveal when top match: score ≥ 0.75 + same sport + strong factors/mutual. Canvas confetti, Alpine.js, Challenge!/Profile/Skip buttons |
| Challenge flow | `⚔️ Challenge!` → pre-filled event form → auto-invite opponent as pending participant |

---

## May 2026 — Week 1: Core System Stabilization

| Fix | Description |
|---|---|
| `Sport::getSkillLevels()` auto-conversion | Detects and converts legacy `["Beginner",...]` and `[1,2,3]` formats at runtime |
| `Sport::getSkillLabel()` safe lookup | Uses `value` key scan, not array index |
| `Sport::normalizeSkill()` clamping | Out-of-range values clamped before normalization |
| Events browse page empty | `EventController::index` now queries past 90 days + future year |
| Connection state on matchmaking cards | Pre-loaded in controller — no N+1 per card |
| Avatar upload | Stored at `storage/app/public/avatars/`, shown across all views |
| Map tiles not rendering | Leaflet in `<head>` + `map.invalidateSize()` on init |
| Branding | "SportsMatch" → "PlayMate" in nav + logo |
| `acceptedConnections()` SQL | Uses static `Connection::where()` builder, not `hasMany` |
| Sports validation | `nullable` instead of `required` for `sports.*.sport_id` |

---

## Pre-May 2026

| Fix | Description |
|---|---|
| Raw lat/lng number inputs | Replaced with `<x-map-picker>` Blade component |
| Map picker component | Reusable `resources/views/components/map-picker.blade.php` with Leaflet |
| Filament Pinpoint vendor override | Map clipping, `x-cloak` conflict, z-index layering fixed |
| Legacy skill data | One-time Tinker migration script provided in `docs/database-schema.md` |

---

## June 2026 — Week 1: AI Assistant (RAG Chatbot)

### New Feature
| Component | Description |
|---|---|
| `chat_sessions` + `chat_messages` tables | Per-user chat history, cascade delete on user removal |
| `GeminiClient` service | Wraps Gemini 2.0 Flash (chat) + `gemini-embedding-001` (embeddings, 3072-dim) |
| `RagRetriever` service | Loads `storage/app/private/rag/chunks.json` + `embeddings.json`, returns top-K chunks by cosine similarity |
| `ChatbotService` | Orchestrates: embed query → retrieve top-5 → build prompt → call Gemini → return reply + tokens |
| `php artisan rag:build` | Splits `docs/*.md` by H2 (~1500 chars) → embeds → writes JSON index. Re-run after docs change |
| `ChatAssistantController` | 6 endpoints: `show` (page), `index` (sessions JSON), `store`, `messages`, `send`, `destroy`. All `auth` middleware |
| Floating widget | `@include('partials._chatbot-widget')` injected into `layouts/app.blade.php` — Alpine.js panel, sidebar session list + thread |
| `/chat` page | Sidebar session list + thread (ChatGPT-like) |
| Rate limit | `throttle:30,10` on POST message (30 reqs per 10 min per user) |

### Key Decisions
| Decision | Rationale |
|---|---|
| `gemini-embedding-001` (not `text-embedding-004`) | `text-embedding-004` was retired May 2025; old name now silently returns different dim, breaking cosine sim. `gemini-embedding-001` is the current stable ID (3072-dim) |
| RAG to file JSON, not vector DB | Corpus is ~50 docs, < 2000 lines. Cosine sim in PHP < 50ms. Avoids Postgres/MySQL FTS5 dependency |
| RAG to `docs/`, not `PROJECT-DOCS/` | `docs/` is the canonical reference (CLAUDE.md + models.md). `PROJECT-DOCS/` is a workspace scratchpad |
| Q&A only (no function calling) | Simplest viable scope; actions like "find partner" can be future v2 |
| Login-gated | Session/messages are personal; auth middleware is one line |
| No streaming in v1 | Synchronous POST → JSON reply. SSE addable later without breaking changes |
| Chat assistant distinct from `ChatController` | Suffix `Assistant` to avoid colliding with event group chat |

### Why a new model ID?
`gemini-embedding-001` and `text-embedding-004` produce **different** embeddings for the same text (different dim, different scale). Building an index on the old ID and querying with the new ID silently corrupts cosine similarity. Always pin the model string and rebuild.

---

## June 2026 — Week 3: Matchmaking Efficiency & Filament Admin Cleanup

### MatchmakingService — Performance Optimizations

| Fix | Detail |
|---|---|
| Triple `distanceFromUser()` eliminated | Distance computed once per opponent in filter step, stored in `$distances[id]` map, reused in map step and `getBadgesFromPrefs()`. Was called 3× → now 1× per opponent |
| Quadruple `getTimePreference()` eliminated | Prefs computed once per opponent in `calculateScoreWithComponents()`, passed to `calculateTimeScoreFromPrefs()` and `getBadgesFromPrefs()`. Was called 4× → now 2× (one per user) |
| `getBadgesFromPrefs()` extracted | Private method accepts pre-fetched prefs + distance. `getBadges()` public API unchanged for external callers |
| `calculateTimeScoreFromPrefs()` extracted | Private method for time scoring when prefs already fetched |
| 5 dead compat methods removed | `calculateSportScoreCompat`, `calculateSkillScoreCompat`, `calculateDistanceScoreCompat`, `calculateActivityScoreCompat`, `hasTimeOverlap` — all deleted |
| `getSkillForSport()` in User model | Added `relationLoaded()` guard — uses preloaded collection when available |

### Filament Admin — Cleanup & Enhancements

| Change | Detail |
|---|---|
| `GameMatches/` resource deleted | 0 DB rows, empty form schema, dead resource. Entire directory removed |
| `Fields/` resource deleted | Old court booking system, 8 DB rows, dead. Entire directory removed |
| `TimeSlots/` resource deleted | Old booking slots, 88 rows, dead. Entire directory removed |
| `EventParticipantsTable` — enhanced | Added `status` badge column (success/warning/danger colors), `slot_number` column, `joined_at` column, EditAction + DeleteBulkAction |
| `EventParticipantForm` — enhanced | Added `status` Select (pending/approved/rejected, default pending) and `slot_number` TextInput |
| `UsersTable` — role badge | Added `role` column with badge: admin=red, user=gray |
| `UserForm` — role field | Added `role` Select (User / Admin, default User). Password field made nullable with helper text |
| `role` column migration | `add_role_to_users_table` migration: `string('role')->default('user')` on users table |
| User model — `FilamentUser` | Implements `FilamentUser` interface, `canAccessPanel()` returns `$this->isAdmin()` |
| User model — `isAdmin()` | Helper method: `return $this->role === 'admin'` |
| Admin panel gated | Only users with `role=admin` can access Filament. All others get 403 |
| Demo admin users | `erlanggarafi38@gmail.com` + `rizky@example.com` set to `role=admin` via Tinker |

---

## June 2026 — Week 3: Bowl Series Restyle, Venue Simplification & Matchmaking Fixes

### Visual Restyle — Bowl Series Theme

| Change | Detail |
|---|---|
| Background color | `#0A0F1F` (navy) → `#0D0D0D` (pure black) across all layouts |
| Primary button color | Orange fill + dark text → **Red `#D62B2B`** (hover → orange `#F97316`) |
| Navbar layout | Logo-left → **logo center, menu split left/right** |
| Section labels | Plain orange text → `— LABEL —` format with side decorators |
| `pm-section-title` | Updated to flex with `::before`/`::after` line decorators |
| Footer | Minimal 1-liner → **4-column footer** with nav links + social icons |
| Grid gap background | `grid gap-px bg-white/10` → `grid gap-px` + `style="background:#0D0D0D"` (fixed gray leak on incomplete grid rows) across all views |
| Pagination | Container `bg-white/10` removed; each button has its own border/background |

**Files changed:** `layouts/app.blade.php`, `welcome.blade.php`, `resources/css/app.css`, `matchmaking.blade.php`, `my-events.blade.php`, `connections/index.blade.php`, `events/show.blade.php`, `profile.blade.php`, `players/profile.blade.php`, `venues/show.blade.php`

### Welcome Page — Content Preserved, Visual Restyle

- Hero: "Find Your Next Match." + slogan + stats (32 Members / 03 Sports / 08 Venues) retained
- 3 sport tiles retained: Tennis (NTRP 1.0–7.0), Padel (Doubles, levels 1–7), Badminton (Singles & Doubles)
- Added **Upcoming Events** section (live data from DB)
- Added **How It Works** 3-step section
- CTA "Ready to Join?" retained
- Navbar: centered logo, split nav, red CTA button

### Matchmaking — Popup & Badge Fixes

| Fix | Detail |
|---|---|
| Confetti removed | Entire canvas confetti JS (~100 lines) removed — not aligned with premium theme |
| Emoji stripped from badges | `MatchmakingService::getBadges()` — `⚡📍🔥🟡⏰` removed; plain text: `Same Level`, `Similar Skill`, `Nearby`, `Close`, `In Form`, `Active`, `Schedule Match` |
| `⏳ Pending` badge | Replaced with plain "Request Pending" |
| Popup only on search | `x-init` now gated by `@if(request()->boolean('searched'))` — popup no longer fires on fresh page load |
| Saved presets | Wrapped in `@if(request()->has('searched'))` — chips hidden until first search |
| Popup redesign | Score ring (96px border box), skill bar 2px, component bars 2px, badges plain text, no glow ring, clean 2-button footer |
| Popup animation | CSS keyframe `pm-card-in`: `translateY(28px) scale(0.97)` → overshoot `-3px / scale(1.005)` → settle. `cubic-bezier(0.22,1,0.36,1)` 550ms. Backdrop separate 350ms fade |

### Venue Pages — Simplified to Directory

**Decision:** Venue pages are now a read-only **Venue Directory** for reference when hosting events. Booking and court availability are out of scope (no real-time API, no venue partnership agreements).

| Change | Detail |
|---|---|
| `VenueController` | Removed `slots` from all eager loads |
| `venues/index.blade.php` | Rewritten — filter by sport/area, card grid showing name, sport, address, hours, price estimate |
| `venues/show.blade.php` | Rewritten — removed entire "Availability Schedule" section + `VenueSlot` rendering |
| Price estimate | Still shown but labeled "Estimated Rate · contact venue to confirm" |
| Leaflet map | Added to detail page — dark tile (CartoDB), orange marker, read-only. Renders only if `latitude` & `longitude` set |
| "Host Event Here" | Retained — links to `events.create?venue_id=X` |
| Notice box | Added — clarifies venue is directory only; direct booking via venue contact |
| Data source | Venue data will be scraped from **ayo.co.id** |

---

## June 2026 — Week 2: Bug Fixes & UI Improvements

### Bug Fixes

| Fix | Description |
|---|---|
| `partials._animejs-init` not found | `dashboard.blade.php` and `matchmaking.blade.php` referenced a non-existent partial. Fixed to `partials._animejs-init-base` |
| Chatbot JS config closures broken | PHP closures (`fn($id) => url(...)`) in `@js()` do not serialize to JS functions. Replaced `messagesUrl`, `sendUrl`, `destroyUrl` with a single `baseUrl` string and template literals |
| Chatbot delete session always failed | `deleteSession()` JS did not check HTTP response status. Fixed with proper `r.ok` check |
| Gemini chat model 404 | `gemini-2.0-flash` daily free-tier quota exhausted by RAG build. Switched to `gemini-2.5-flash` |

### UI / UX

| Change | Description |
|---|---|
| Landing hero background | Replaced static `landing-hero.jpg` with `background.mp4` video (autoplay, muted, loop). CSS z-index: video (0) → gradient overlay (1) → content (2) |
| Chat assistant UI language | All UI text in chatbot widget and full-page chat changed from Indonesian to English ("KIRIM" → "SEND", "Tanpa judul" → "Untitled", "mengetik" → "typing...", etc.) |
| Chatbot error messages | All error strings in `_chatbot-scripts.blade.php` and `ChatAssistantController` changed to English |

### Model Update

| Item | Before | After |
|---|---|---|
| `GEMINI_CHAT_MODEL` | `gemini-2.0-flash` | `gemini-2.5-flash` |
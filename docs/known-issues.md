# Known Issues & Technical Debt

> PlayMate — Issues Tracker
> Last updated: June 2026

---

## 1. Known Issues

| Issue | Severity | Workaround | Status |
|---|---|---|---|
| Filament Pinpoint search dropdown blank in dark mode | Medium | Use light mode or click/drag map marker for location | **Pending fix** — CSS contrast in vendor override |
| Chat file uploads unvalidated for type/size | Low | No validation yet — should add mime/size validation | Not started |
| No pagination on matchmaking results | Low | All matched users loaded at once | Not started |
| `Field` + `TimeSlot` are legacy | Low | Kept for `FieldAvailabilityController` | Not started |
| RAG index `storage/app/private/rag/` not in git | Low | Run `php artisan rag:build` after `git pull` if `docs/` changed | Documented |
| Gemini API key required to run bot | Low | Set `GEMINI_API_KEY` in `.env`; bot endpoints return 502 if missing | Documented |
| Venue Nominatim geocoding fails for Indonesian venue names | Low | Use `php artisan venue:geocode` — will skip venues Nominatim can't find. Set coordinates manually via Tinker or Filament admin. | Workaround available |
| Embedding model drift | High | Always pin `gemini-embedding-001`. Old `text-embedding-004` returns different dim → corrupts cosine similarity | Resolved June 2026 |

---

## 2. Resolved Issues (June 2026)

| Issue | Fixed By | Detail |
|---|---|---|
| 17/19 venues had NULL latitude/longitude — map not shown | Manual Tinker + `venue:geocode` command | Coordinates set based on address/area. All 19 venues now have lat/lng |
| `events/create.blade.php` used light/white theme | Full rewrite June 2026 | Now uses `pm-card`, `pm-input`, `pm-btn-primary` — consistent with dark theme |
| Map not updating when venue selected from autocomplete | `CustomEvent('playmate:venue-selected')` + `_pending` buffer | Map-picker now listens for venue selection event; handles race condition if Leaflet still loading |
| Duplicate `name="latitude"` from two components | Field ownership split | `venue-picker` owns `venue_name` only; `map-picker` owns `latitude` + `longitude`. `addr-id="map_address"` avoids clash |
| `GameController` + `Game` model dead code | Deleted June 2026 | No routes, views, or references. `GameMatch` kept for `User::gameMatches()` |

## 3. Historical Resolved Issues

| Issue | Fixed By | Detail |
|---|---|---|
| Filament Pinpoint map clipped when search dropdown opens | May 2026 vendor override | `overflow:clip` → `overflow:hidden` + `map.invalidateSize()` calls + `z-index: 0` on leaflet container |
| Filament Pinpoint `x-cloak` conflict | May 2026 vendor override | Removed `x-cloak`, added `x-transition` classes, added `autocomplete="off"` |
| `acceptedConnections()` SQL error | Static `Connection::where()` builder | `hasMany` with `where()` prepends invalid `user_id` constraint — must use static builder |
| `sports.*.sport_id` validation rejected empty rows | `nullable` validation rule | Changed from `required` to `nullable` in `ProfileController` |
| Raw lat/lng number inputs in user forms | Map picker component | Replaced with `<x-map-picker>` Blade component |
| Map tiles not rendering | Leaflet in `<head>` + `invalidateSize()` | Leaflet CSS/JS moved before Alpine; `map.invalidateSize()` called on init |
| Branding "SportsMatch" in nav | Updated in `layouts/app.blade.php` | Now "PlayMate" + "PM" logo |
| `foreach() must be of type array\|object` on `/profile` | `Sport::getSkillLevels()` always returns array | Never accesses `$this->skill_levels` directly in `getSkillLabel()` |
| Legacy `skill_levels` format causing crashes | Auto-conversion in `getSkillLevels()` | Detects and converts legacy string/numeric arrays at runtime |
| Events browse page showing no events | Event date range fix | `EventController::index` now queries `start_time >= now()->subDays(90)` to `now()->addYear()` |
| Matchmaking "Connect" button shown for already-connected users | Connection state pre-loading | `MatchmakingController::index` pre-loads all states in single query — no N+1 |
| Asymmetric time score scoring | Symmetric fallback | Both users without/with preferences now score `0.5 + (dayScore * 0.5)` |
| Score bar wrong percentage | Correct formula | `round($comp['score'] / $comp['weight'] * 100)` instead of hardcoded `0.30` divisor |
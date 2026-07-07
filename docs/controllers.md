# Controllers

> PlayMate — Controller Reference
> Last updated: June 2026

---

## 1. MatchmakingController

**File:** `app/Http/Controllers/MatchmakingController.php`

**Method:** `index()`

Preloads all data for performance (no N+1 queries):
- `userSports.sport` — user's sports with skill levels
- All recommended opponents with `userSports.sport` + `userSports` eager loaded
- `reviewsReceived` for rated players
- Batch connection states via single query
- `getMutualConnectionCounts()` — single query for all opponents

**Passes to view:** `topMatch`, `showReveal`, `gridResults`, `connectionStates`

**Filters supported:** sport_id, play_style, gender, age_range, start_time, end_time, radius_km, preferred_days, min_score, exclude_connected

**Connection state-aware UI:** Pre-loads all connection states in one query; view renders:
- `Connected` → "Chat" button + ✓ badge
- `Pending sent` → ⏳ Pending badge
- `Pending received` → Accept / Decline buttons
- `Declined/blocked` → "Connect" button
- No connection → "Connect" button

**Overlay trigger:** `showReveal = $topMatch && $topMatch['score'] >= 0.75 && $topMatch['components']['sport']['raw'] == 1.0 && ($strongFactors >= 2 || $topMatch['mutual_connections_count'] >= 1)`

---

## 2. EventController

**File:** `app/Http/Controllers/EventController.php`

| Method | Description |
|---|---|
| `index(createFilters)` | Shows events from `now()->subDays(90)` to `now()->addYear()` (fixed: was showing none after seeder dates passed). **Excludes events with `is_challenge = true`.** |
| `create()` | Show event creation form |
| `store()` | Validate + create event. If `opponent_id` provided → auto-add opponent as pending participant, sets `is_challenge=true` and `visibility=private`. |
| `show()` | Event detail + participant list |
| `join()` | Join event (auto-approved if `approval_required=false`) |
| `leave()` | Leave event (cancels own participation) |
| `cancel()` | Host cancels event |
| `challenge(User $user)` | Pre-fills event creation form with selected opponent; passes `opponent_id` for auto-invite on store |

---

## 3. ConnectionController

**File:** `app/Http/Controllers/ConnectionController.php`

| Method | Description |
|---|---|
| `index()` | Incoming / Sent / Active connections tabs |
| `store()` | Send connection request + duplicate check |
| `update(Connection $connection)` | Accept / Decline / Block (receiver-only actions) |
| `playerProfile(User $user)` | Public profile page — loads `userSports.sport`, `reviewsReceived.reviewer`, `joinedEvents.sport`. Shows smart CTA based on connection state |
| `chat(Connection $connection)` | Private DM thread |
| `sendMessage(Connection $connection)` | Text + file upload → `storage/app/public/chat-files/` |

**Connection states:**
```php
// Sent by current user (requester_id = auth id, receiver_id = target)
pending_sent    → awaiting acceptance
// Received by current user (receiver_id = auth id, requester_id = target)
pending_received→ can accept/decline
accepted        → connected, can chat
declined/blocked→ can send new request
none            → can connect
```

---

## 4. MyEventsController

**File:** `app/Http/Controllers/MyEventsController.php`

| Method | Description |
|---|---|
| `index()` | Upcoming / Hosted / Waiting / Pending / Past / Cancelled tabs + `pendingRequestsCount` badge |
| `hostRequests()` | List all pending join requests across user's hosted events |
| `approve(EventParticipant $participant)` | Sets status to `approved`, assigns next `slot_number`, posts system message to event chat. **403 if not host.** Disabled if event is full |
| `reject(EventParticipant $participant)` | Sets status to `rejected`, posts system message to event chat. **403 if not host** |
| `acceptChallenge(EventParticipant)` | Opponent accepts a challenge invite. Sets status to `approved`. |
| `declineChallenge(EventParticipant)` | Opponent declines a challenge invite. Sets status to `rejected`. **If event is `is_challenge=true`, deletes the event entirely.** |

---

## 5. ChatController

**File:** `app/Http/Controllers/ChatController.php`

| Method | Auth Rule |
|---|---|
| `show(Event $event)` | `canAccessChat($authUser)` — approved participant OR host only |
| `send(Event $event)` | Text/image/file upload to `storage/app/public/chat-files/` |
| `destroy(EventMessage $message)` | Owner only (`isOwnedBy($authUser)`) |

---

## 6. ProfileController

**File:** `app/Http/Controllers/ProfileController.php`

| Method | Description |
|---|---|
| `index()` | Shows profile + sports + time preferences + `acceptedConnections` |
| `update()` | Avatar upload to `storage/app/public/avatars/` + profile fields sync + **deletes and recreates** `user_sports` with sports sync + per-sport time preferences. Uses `nullable` for `sports.*.sport_id` validation to allow empty rows |

---

## 7. ReviewController

**File:** `app/Http/Controllers/ReviewController.php`

| Method | Description |
|---|---|
| `create()` | Show rating form + duplicate check (checks `UserReview` by `reviewer_id`, `reviewed_user_id`, `source_type`) |
| `store()` | Save review (1–5 star rating, comment, source tracking). **Duplicate prevention:** unique constraint on `(reviewer_id, reviewed_user_id, source_type)` |

---

## 8. VenueController

**File:** `app/Http/Controllers/VenueController.php`

| Method | Description |
|---|---|
| `index(filters)` | Venue directory cards filtered by `sport_id` and `area`. Paginated (9/page). |
| `search(Request $request)` | **JSON** — Autocomplete endpoint for venue picker. Query param `?q=`. Searches `name`, `area`, `address`. Returns max 8 results with `id`, `name`, `area`, `address`, `sport`, `latitude`, `longitude`. No auth required. |
| `show(Venue $venue)` | Full venue detail: info cards, gallery, Leaflet map (if lat/lng set), CTAs |

**Autocomplete endpoint:** Used by `<x-venue-picker>` component on event creation and challenge forms. Returns JSON for Alpine.js dropdown.

---

## 9. DashboardController

**File:** `app/Http/Controllers/DashboardController.php`

| Method | Description |
|---|---|
| `index()` | Landing dashboard with 4 feature cards |

---

## 10. ~~GameController~~ (Deleted — June 2026)

`GameController` and `app/Models/Game.php` were **deleted** as dead code. No routes, no views, no active references. `GameMatch` model retained (used by `User::gameMatches()` hasMany).

---

## 11. FieldAvailabilityController (Legacy)

Not listed in main routes — serves `GET /field-availability` for legacy `Field` + `TimeSlot` tables.

---

## 12. ChatAssistantController (AI Assistant — June 2026)

**File:** `app/Http/Controllers/ChatAssistantController.php`

Powers the RAG-backed PlayMate Assistant. **All endpoints behind `auth` middleware.**

| Method | Description |
|---|---|
| `show(ChatSession)` | Full-page chat view (`resources/views/chat/show.blade.php`) |
| `index()` | JSON: list of auth user's sessions for sidebar |
| `store()` | Create new empty session, return JSON `{id, title, ...}` |
| `messages(ChatSession)` | JSON: all messages of session (asc by created_at) |
| `send(ChatSession, ChatbotService)` | Persist user message → call `ChatbotService::ask()` → return JSON `{user, assistant, session}`. On `GeminiException` → 502 |
| `destroy(ChatSession)` | Delete session (cascades messages) |

**Owner authorization:** inline check `$session->user_id !== auth()->id() → abort(403)` *before* any service call (avoid timing leak).

**Distinguished from `ChatController`** (event group chat) — name uses `Assistant` suffix to avoid collision.
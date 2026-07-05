# Views & Templates

> PlayMate — Blade View Reference
> Last updated: June 2026

---

## 1. Layout

### `layouts/app.blade.php`
**Theme:** Pure black `#0D0D0D` background, Oswald font, orange `#F97316` accent, red `#D62B2B` primary button.

Role-aware navigation — **Bowl Series layout (logo center, menu split left/right):**
- **Guest:** Sign In (left) · PLAYMATE logo (center) · Join Now ★ CTA (right)
- **Authenticated:** Dashboard/Match/Events (left) · logo (center) · Venues/Network/Schedule/Profile/Logout (right)

**Leaflet loaded in `<head>`** (not deferred) — before Alpine so `window.L` is available globally:
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
```

---

## 2. Landing & Auth

| View | Route | Description |
|---|---|---|
| `welcome.blade.php` | GET `/` | Landing: full-screen video hero ("Find Your Next Match."), stats, 3 sport tiles (Tennis/Padel/Badminton), How It Works, Upcoming Events (live DB), CTA, full footer |
| `auth/login.blade.php` | GET `/login` | Email + password |
| `auth/register.blade.php` | GET `/register` | Name + email + password + play_style |

---

## 3. Dashboard & Matchmaking

| View | Route | Description |
|---|---|---|
| `dashboard.blade.php` | GET `/dashboard` | 4 feature cards |
| `matchmaking.blade.php` | GET `/matchmaking` | Top Match popup + scored opponent grid |

**Matchmaking view features:**
- **Top Match popup** — Alpine.js modal, only fires when `request()->boolean('searched')` is true (not on fresh page load). Score ≥ 0.75 + same sport + ≥2 strong factors or mutual connection
- Popup design: score ring (96px border box), skill bar 2px, per-component breakdown bars, plain-text badges, no confetti, CSS keyframe animation (`pm-card-in` cubic-bezier 550ms)
- Score breakdown panel (expandable) per card
- Per-component score bars: `width: {{ $comp['weight'] > 0 ? round($comp['score'] / $comp['weight'] * 100) : 0 }}%`
- **Badges (plain text, no emoji):** `Same Level`, `Similar Skill`, `Nearby`, `Close`, `In Form`, `Active`, `Schedule Match`
- Connection state-aware buttons (Chat/Connect/Request Pending/Accept/Decline)
- Filter presets (localStorage: `playmate_match_presets`) — **only shown after search**
- Last active display: `Carbon::diffForHumans()`
- Criteria match summary (Strong Match / Weak Factors boxes)
- **⚠️ Always use `$sport->getSkillLevels()` not `$sport->skill_levels`**

---

## 4. Profile

| View | Route | Description |
|---|---|---|
| `profile.blade.php` | GET `/profile` | Avatar upload, all profile fields, sports editor with per-sport time preferences (from/to + day selector), My Connections section |

**Sports editor:**
- 3 rows with sport picker + level + style per row
- New sport rows show all sports grouped in `<optgroup>`
- `skill_value` auto-filled from `sport->getSkillLevels()` on save
- **Per-sport time preferences:** time picker (from/to) + Su–Sa CSS toggle day selector
- Stores `preferred_start_time`, `preferred_end_time`, `preferred_days` in `user_sports`
- **⚠️ Uses `$sport->getSkillLevels()` NOT direct property access**

**Map picker usage:**
```blade
<x-map-picker
    lat-id="latitude" lng-id="longitude" addr-id="address"
    :lat-val="$user->latitude" :lng-val="$user->longitude"
    :addr-val="$user->home_address" label="Your Location" :height="280"
/>
```
`addr-id="address"` → matches hidden field name in profile form. `home_address` handled by map picker's hidden field — no separate visible text input needed.

---

## 5. Events

| View | Route | Description |
|---|---|---|
| `events/index.blade.php` | GET `/events` | Event grid with sport + date filter |
| `events/create.blade.php` | GET `/events/create` | Full dark-theme event form with venue autocomplete + map picker |
| `events/show.blade.php` | GET `/events/{event}` | Detail + join/leave/cancel + participant list + chat button |
| `my-events.blade.php` | GET `/my-events` | Tabs: Upcoming / Hosted / Waiting / Pending / Past / Cancelled. Amber badge with pending request count |
| `my-events-host-requests.blade.php` | GET `/my-events/host-requests` | Pending join request cards: avatar, tags, sports, rating, Approve/Decline |
| `chat.blade.php` | GET `/events/{event}/chat` | Event group chat: message bubbles, text/image/file input |
| `host-game.blade.php` | GET `/events/challenge/{user}` | Dark-theme challenge form. Pre-filled with opponent info, posts to `events.store`. Shows "DIRECT CHALLENGE" banner when opponent set. |

**Events/create — venue + map setup:**
```blade
{{-- Venue autocomplete (owns name="venue_name") --}}
<x-venue-picker
    :selected-name="old('venue_name', $venue?->name ?? '')"
    :selected-lat="old('venue_name') ? null : ($venue?->latitude)"
    :selected-lng="old('venue_name') ? null : ($venue?->longitude)"
/>

{{-- Map picker (owns name="latitude" + name="longitude") --}}
<x-map-picker lat-id="latitude" lng-id="longitude" addr-id="map_address"
    label="Event Location" :height="260" />
```
`addr-id="map_address"` avoids clashing with `venue_name` from venue-picker. The two components communicate via `CustomEvent('playmate:venue-selected')` — selecting a venue from autocomplete pans the map automatically.

**Pre-fill from venue page:** When navigating from `venues/show.blade.php` via "Host an Event Here" (`?venue_id=X`), `EventController::create()` resolves the venue and passes `$venue` to the view. The venue-picker pre-fills name + coordinates, the map pans to the venue, and the sport dropdown auto-selects the venue's sport.

---

## 6. Venues

**Scope:** Read-only Venue Directory. No booking, no court availability. Data source: scraped from ayo.co.id.

| View | Route | Description |
|---|---|---|
| `venues/index.blade.php` | GET `/venues` | Filter by sport/area. Card grid: name, sport, address, hours, price estimate |
| `venues/show.blade.php` | GET `/venues/{venue}` | Venue info, 3-column info cards, Leaflet dark-tile map (if lat/lng set), "Host an Event Here" CTA |

**`venues/show.blade.php` map:**
- Rendered only when `$venue->latitude && $venue->longitude`
- Tile: `https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png` (no API key)
- Orange `#F97316` circle marker via `L.divIcon`
- `scrollWheelZoom: false` to avoid accidental zoom
- Script in `@push('scripts')` — safe because Leaflet already loaded in `<head>`

**Notice box** on detail page clarifies: directory only, contact venue for booking.

---

## 7. Connections & Private Chat

| View | Route | Description |
|---|---|---|
| `connections/index.blade.php` | GET `/connections` | Tabs: Incoming (Accept/Decline), Sent (pending), My Connections (Chat/View/Review) |
| `connections/chat.blade.php` | GET `/connections/{connection}/chat` | Private DM thread: message bubbles, text/file input, auto-scroll |

---

## 8. Players / Public Profile

| View | Route | Description |
|---|---|---|
| `players/profile.blade.php` | GET `/players/{user}` | Public player profile |

**Content:**
- Gradient header: avatar, name, location, tags, star rating badge
- 3-column stats: Events Joined / Events Hosted / Average Rating
- Smart CTA based on connection state:
  - No connection → "Connect"
  - Pending sent → ⏳ Pending badge
  - Pending received → Accept / Decline
  - Accepted → "Chat" + "Connected" badge + "Leave Review"
- Sports grid with skill bars (`skill_number × 10%` width)
- Reviews section with rating summary + reviewer cards
- Recent events list: sport icon, title, date, venue, status badge

---

## 9. Reviews

| View | Route | Description |
|---|---|---|
| `reviews/create.blade.php` | GET `/reviews/create` | Interactive 1–5 star rating + comment textarea |

---

## 10. Component: Map Picker

**File:** `resources/views/components/map-picker.blade.php`

Uses plain JS `mapPicker()` function (not Alpine inline `x-data`) — Leaflet `L` global accessible from regular `<script>` block.

**Props:**

| Prop | Default | Description |
|---|---|---|
| `latId` | `latitude` | Hidden lat field ID + form name |
| `lngId` | `longitude` | Hidden lng field ID + form name |
| `addrId` | `address` | Hidden address field ID + form name |
| `latVal` | `null` | Pre-filled latitude |
| `lngVal` | `null` | Pre-filled longitude |
| `addrVal` | `null` | Pre-filled address string |
| `label` | `Location` | Label text |
| `height` | `300` | Map height in px |
| `defaultLat` | `-6.2088` | Fallback: Jakarta |
| `defaultLng` | `106.8456` | Fallback: Jakarta |
| `defaultZoom` | `13` | Initial zoom |

**Features:** Draggable marker, Nominatim address search (Indonesia-focused: `countrycodes=id`), click-to-set, reverse geocoding, "Use My Location" button, `map.invalidateSize()` after init.

**Venue-picker integration:** Listens for `window.CustomEvent('playmate:venue-selected', {detail: {lat, lng, name}})`. When received, calls `setPosition(lat, lng, false)` (skipping reverse-geocode) and updates `address`. If map is not yet rendered (Leaflet still loading), stores in `this._pending` and applies after `renderMap()` + `invalidateSize()`.

**`setPosition(lat, lng, reverseGeocode)`:** `reverseGeocode=true` when user clicks/drags map. `false` when driven by venue-picker or Nominatim search result.

**All coordinates stored in hidden form fields** — never exposed as raw numbers.

---

## 11. Component: Venue Picker

**File:** `resources/views/components/venue-picker.blade.php`

Alpine.js typeahead for selecting a venue from the database. Used in `events/create.blade.php` and `host-game.blade.php`.

**Props:**

| Prop | Default | Description |
|---|---|---|
| `selectedName` | `''` | Pre-filled venue name (e.g. `old('venue_name', $venue?->name)`) |
| `selectedLat` | `null` | Pre-filled latitude — triggers map pan on page load via `x-init` |
| `selectedLng` | `null` | Pre-filled longitude — triggers map pan on page load via `x-init` |

**Owns:** `<input type="hidden" name="venue_name">` only. Does **not** own `latitude`/`longitude` — those belong to map-picker.

**Behavior:**
- Debounce 250ms → `GET /venues/search?q=...` → dropdown (max 8 results)
- Each result shows sport badge + name + area/address + pin icon (if coords available)
- On select: sets `query` = venue name, dispatches `CustomEvent('playmate:venue-selected', {lat, lng, name})` on `window`
- On manual type: `hasCoords` reset to false; venue_name reflects typed text
- Clear (✕) button resets query and coords status
- Confirmation label "✓ Koordinat tersimpan" shown when venue with coords selected
- When `selectedLat`/`selectedLng` provided, dispatches `playmate:venue-selected` on `x-init` to auto-pan map on page load

---

## 11. AI Assistant (June 2026)

| View | Route / Included from | Description |
|---|---|---|
| `chat/show.blade.php` | GET `/chat` | Full-page chat: sidebar of sessions + main thread |
| `partials/_chatbot-widget.blade.php` | `layouts/app.blade.php` (auth only) | Thin wrapper — just calls `@include('partials._chatbot-panel')` |
| `partials/_chatbot-panel.blade.php` | `_chatbot-widget` + `chat/show` | Shared markup: floating widget mode OR full-page mode via `$fullPage` flag |
| `partials/_chatbot-scripts.blade.php` | `layouts/app.blade.php` `<head>` | Alpine.js `playmateChat(cfg)` function — session CRUD + send logic |

**`partials/_chatbot-panel.blade.php`:**
- Dual-mode: renders as floating bottom-right widget (default) or full-height panel (`$fullPage = true`)
- Sidebar: session list with "Untitled" fallback, delete button per session
- Thread: message bubbles (user = orange, assistant = dark), "typing..." indicator
- Input: text field + SEND button, error banner below
- All UI text in English

**`partials/_chatbot-scripts.blade.php`:**
- `playmateChat(cfg)` Alpine component function
- `cfg.baseUrl` = `/chat/sessions` — all endpoints built via template literal: `${cfg.baseUrl}/${id}/messages`
- `newSession()`, `loadSession(id)`, `deleteSession(id)`, `send()`

**`chat/show.blade.php`:**
- Two-column layout: session list (left) + thread (right)
- Sets `$fullPage = true` → panel fills container, no floating button
- Markdown-ish: assistant replies render with `whitespace-pre-wrap` (no Markdown lib in v1).
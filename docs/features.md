# Features

> PlayMate — Feature Explanations
> Last updated: July 2026

---

## 1. Multi-Sport System

- 3 sports: **Tennis** (NTRP 1.0–7.0, 12 levels), **Badminton** (8 levels), **Padel** (7 levels)
- Each sport has its own skill level naming system
- `user_sports` pivot: multiple sports per user, each with independent skill level
- Skill values normalized to `skill_number` (1–10) via `Sport::normalizeSkill()` before storage and matchmaking

---

## 2. Advanced User Profiles

- Fields: bio, avatar photo, gender, age_range, GPS coordinates, play_style
- **Avatar upload:** Stored at `storage/app/public/avatars/`, shown in profile/connections/chat
- **Multiple sports** per user with per-sport:
  - Skill level (sport-specific label + normalized number)
  - Play style override
  - Preferred start/end time (from/to time pickers)
  - Preferred days (Su–Sa CSS toggle buttons → stored as `[0,1,2,3,4,5,6]`)
- Last active timestamp for activity scoring in matchmaking

---

## 3. 5-Component AI Matchmaking v2

See `docs/matchmaking-algorithm.md` for full details.

**Summary:**
- Sport (30%) + Skill (30%) + Time (20%) + Distance (15%) + Activity (5%)
- Band-based skill decay, continuous `exp(-d/10)` distance decay, 5-tier activity
- Per-component score breakdown for UI
- Mutual connections count displayed per card
- Connection state-aware buttons (no N+1 queries — pre-loaded in controller)
- Minimum score threshold filter (25–50%)
- Exclude connected users filter
- **Top Match popup** for strong matches (score ≥ 0.75, same sport, ≥2 strong factors or mutual) — only shown after user clicks Search, not on page load. No confetti — clean score ring + component bars

---

## 4. Event Hosting System

- Host creates event: sport, venue, GPS, max_slots, price, visibility, match type
- **Auto-join host:** Host automatically added as first participant (status `approved`, slot 1)
- Join/leave/cancel with full participant list
- **Challenge flow:** `⚔️ Challenge!` dari matchmaking → `GET /events/challenge/{user}` → `host-game.blade.php` (dark-theme challenge form dengan opponent banner) → post ke `events.store` → lawan di-invite sebagai pending participant (`is_invite=true`, slot 2) → **notifikasi `challenge_invite` dikirim ke lawan**
- **Approval system:** Jika `approval_required=true`, join request masuk status `pending`; host approve/decline via `/my-events/host-requests`. **Notifikasi `join_request` dikirim ke host, notifikasi `join_approved`/`join_rejected` dikirim kembali ke pemain setelah host merespons.**
- **"Host a Game" is Challenge flow only.** `host-game.blade.php` adalah form challenge, bukan tipe game terpisah. Membuat record `Event` standar dengan `visibility=private`.

---

## 5. Group Chat (Per Event)

- Text, image, file support with file uploads to `storage/app/public/chat-files/`
- **Auth rule:** Only approved participants or host can access (`canAccessChat()`)
- Delete own messages only

---

## 6. My Events Dashboard

- **Tabs:** Upcoming, Hosted (dengan amber badge pending-request), Waiting, Pending, **Incoming Challenges** (badge terpisah), Past, Cancelled
- Hosted events menampilkan jumlah pending request yang menautkan ke halaman host-requests
- **Incoming Challenges tab:** Menampilkan challenge invite yang masuk ke user sebagai lawan (`is_invite=true`, `status=pending`). User bisa Accept atau Decline langsung dari tab ini.

---

## 7. Host Approval System

- `/my-events/host-requests` menampilkan semua pending join request dari event yang di-host user
- Setiap card: avatar, nama, tag gender/umur/play_style, olahraga + skill level, rating bintang
- **Approve:** Set status ke `approved`, assign `slot_number` berikutnya, posting system message ke event chat, **kirim notifikasi `join_approved` ke pemain**
- **Decline:** Set status ke `rejected`, posting system message ke event chat, **kirim notifikasi `join_rejected` ke pemain**
- **403** untuk non-host
- Tombol Approve **di-disable jika event sudah penuh**

---

## 7a. Challenge Approval (Opponent Flow)

Alur ketika seorang pemain di-challenge melalui fitur matchmaking:

1. Challenger membuat event via `host-game.blade.php` dengan `opponent_id` di form
2. `EventController@store` membuat `EventParticipant` dengan `is_invite=true`, `status=pending` untuk lawan
3. Notifikasi `challenge_invite` dikirim ke lawan
4. Lawan melihat challenge di tab **Incoming Challenges** pada halaman My Events
5. **Accept** (`POST /my-events/challenges/{participant}/accept`): status → `approved`, notifikasi `challenge_accepted` dikirim ke challenger
6. **Decline** (`POST /my-events/challenges/{participant}/decline`): status → `rejected`, notifikasi `challenge_declined` dikirim ke challenger

> Perbedaan kunci: `is_invite=false` = join request biasa (dikelola host); `is_invite=true` = challenge invite (dikelola lawan).

---

## 8. Venue Discovery

**Scope:** Read-only directory for reference when hosting events. Booking and court availability are intentionally out of scope.

- **Index:** Filter by sport and area/city. Card grid shows venue name, sport type, full address, operating hours, estimated price (as reference only)
- **Detail:** Venue info panel, 3-column info cards (Sport / Hours / Area), Leaflet map (dark tile, orange marker — only if lat/lng set), "Host an Event Here" CTA → `events.create?venue_id=X`
- **Price estimate:** Displayed with label "contact venue to confirm" — not a bookable rate
- **Notice box:** Clarifies directory purpose; directs users to contact venue for booking
- **No `VenueSlot` data rendered** on user-facing pages
- **Data source:** Venue data scraped from **ayo.co.id**. 19 venues, all in Kota Tangerang Selatan. All have `latitude`/`longitude` set.
- **Admin:** VenueResource in Filament uses Pinpoint map picker to set `latitude`/`longitude`
- **Geocoding command:** `php artisan venue:geocode` — uses OpenStreetMap Nominatim to geocode venues with NULL lat/lng. Skips venues that already have coordinates (override with `--all`).

## 8a. Venue Autocomplete (on Event Forms)

When creating an event or challenging a player, the **venue name field** is replaced by a live autocomplete picker:

- User types → debounce 250ms → `GET /venues/search?q=` → dropdown list (max 8)
- Each result: sport badge + venue name + area/address + 📍 pin if coordinates available
- **Selecting a venue** automatically:
  1. Fills `venue_name` hidden input
  2. Dispatches `CustomEvent('playmate:venue-selected')` → map-picker pans to venue coordinates
- Manual typing still allowed — user can enter any venue name not in the database
- Both `events/create.blade.php` and `host-game.blade.php` use `<x-venue-picker>`

---

## 9. Public Landing Page

- Sticky nav, hero section with stats, feature cards, sports section, footer CTA
- Role-aware nav: guests see Login/Get Started; auth sees full nav

---

## 10. Connection System

- **Send request:** Duplicate prevention via unique constraint on `(requester_id, receiver_id)`
- **Accept / Decline / Block:** Receiver-only actions via `PATCH /connections/{connection}`
- **Public `/players/{user}` profile** with Connect button
- **My Connections page:** Three tabs — Incoming (Accept/Decline), Sent (pending), My Connections (Chat/View/Review)

---

## 11. Private Chat (DMs)

- Per-connection message thread (text + image + file)
- Files uploaded to `storage/app/public/chat-files/`
- Auto-scroll to latest message, timestamps, message bubbles
- **File mime/size validation:** Not yet implemented (see known issues)

---

## 12. User Review System

- Interactive 1–5 star rating with JS
- Comment textarea
- **Source tracking:** `source_type` ('connection' or 'event') + `source_id` FK
- **Duplicate prevention:** Unique constraint on `(reviewer_id, reviewed_user_id, source_type)`
- Reviews displayed on public player profile (`players/profile.blade.php`)

---

## 13. Location System

**Package:** `fahiem/filament-pinpoint` v1.1.6 — Leaflet + OpenStreetMap (no API key).

**Filament Admin (VenueResource):** Uses `Pinpoint::make('location')` with address search (Nominatim), click/drag pin, reverse geocoding, "Use My Location" button.

**User-facing:** `<x-map-picker>` Blade component at `resources/views/components/map-picker.blade.php`. Used in `profile.blade.php` and `events/create.blade.php`.

**Vendor override:** `resources/views/vendor/filament-pinpoint/pinpoint-leaflet.blade.php` fixes map clipping, `x-cloak` conflict, z-index issues, and autocomplete.

---

## 14. Filament Admin Panel

See `docs/filament.md` for full details.

---

## 15. Seeded Demo Data

- Sports: 3 (Tennis, Badminton, Padel with proper skill levels)
- Venues: 8 (3 tennis, 3 badminton, 2 padel — Jakarta/Bekasi area)
- Users: 32 Indonesian profiles with GPS coordinates and varied skills
- Events: 8 mixed tennis/badminton/padel
- Connections, reviews, fields, time_slots seeded

**Demo login:** `rizky@example.com` / `password`

---

## 16a. Notification System (July 2026)

Sistem notifikasi in-app sederhana berbasis tabel `notifications` kustom (bukan Laravel's built-in polymorphic notifications).

**Tipe notifikasi yang didukung:**

| Type | Trigger | Penerima |
|---|---|---|
| `join_request` | Pemain join event dengan `approval_required=true` | Host |
| `join_approved` | Host approve join request | Pemain |
| `join_rejected` | Host reject join request | Pemain |
| `challenge_invite` | Event dibuat via challenge flow | Lawan (opponent) |
| `challenge_accepted` | Lawan menerima challenge | Challenger (host) |
| `challenge_declined` | Lawan menolak challenge | Challenger (host) |

**Implementasi:**
- Model: `Notification` — fillable: `user_id, type, title, body, action_url, read_at`
- `User::notifications()` — override dari Notifiable, menggunakan tabel kustom, ordered by `created_at desc`
- `User::unreadNotifications()` — scope `whereNull('read_at')`
- Bell dropdown ditampilkan di navbar melalui `layouts/app.blade.php`
- **Buka notifikasi:** `GET /notifications/{id}/open` → mark as read → redirect ke `action_url`
- **Tandai semua dibaca:** `POST /notifications/read-all`

---

## 17. AI Assistant (RAG Chatbot — June 2026)

Login-only RAG-backed chatbot, reachable from a floating launcher on every authenticated page or full-page at `/chat`.

- **Knowledge source:** chunks generated from `docs/*.md` by `php artisan rag:build` (split ~1500 chars by H2 heading). Stored at `storage/app/private/rag/chunks.json` + `embeddings.json`.
- **Embedding model:** `gemini-embedding-001` (3072-dim). **Stable model ID** — `text-embedding-004` was retired May 2025; rebuilding on old ID would silently produce a different dim and corrupt similarity. See `docs/known-issues.md`.
- **Retrieval:** Top-5 chunks by cosine similarity (computed in PHP). Each chunk: `{id, source, section, content, embedding[]}`.
- **Prompt budget:** Truncate to fit `gemini-2.0-flash`'s 1M-token context, but stay under ~3000 tokens total input for free-tier RPM safety.
- **Persistence:** `chat_sessions` (per-user) + `chat_messages` (role: `user` / `assistant`, with `tokens_used`).
- **Launch points:**
  - **Floating widget** on every authenticated page (`<x-chatbot-launcher />` in `layouts/app.blade.php`).
  - **Full page** at `/chat` (sidebar list + main thread, like ChatGPT).
- **Rate limit:** 30 messages / 10 min per user (`throttle:30,10` middleware).
- **Out of scope (v1):** Streaming responses (use simple POST → JSON reply; SSE can be added later), Q&A only (no function calling to other services).
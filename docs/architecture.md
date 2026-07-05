# Architecture

> PlayMate — Smart Sports Matchmaking Platform
> Last updated: July 2026

---

## 1. Project Overview

**Purpose:** Connect sports players for matches via rule-based AI matchmaking, event hosting with group chat, venue discovery, private messaging, and user ratings. (Similar to Reclub / Playtomic)

**Demo Login:**
- Email: `rizky@example.com`
- Password: `password`

---

## 2. Tech Stack

| Package | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Filament | ^5.5 |
| Filament Pinpoint | ^1.1 |
| Carbon | ^3 (bundled with Laravel) |
| Tailwind CSS | CDN (cdn.tailwindcss.com) |
| Database | MySQL 8.x |

---

## 3. Folder Structure

```
WEB-SKRIPSI/
├── app/
│   ├── Filament/Resources/
│   │   ├── EventMessage/
│   │   ├── EventParticipant/
│   │   ├── Events/
│   │   ├── Fields/            (legacy)
│   │   ├── GameMatches/        (legacy)
│   │   ├── Sport/
│   │   ├── TimeSlots/         (legacy)
│   │   ├── Users/
│   │   └── Venue/
│   ├── Http/Controllers/
│   │   ├── ChatController.php         # Event group chat
│   │   ├── ConnectionController.php   # Connections + private chat
│   │   ├── DashboardController.php
│   │   ├── EventController.php        # CRUD + join/leave/cancel + challenge invite
│   │   ├── MatchmakingController.php   # Find Opponent
│   │   ├── MyEventsController.php      # My Events + host approval + challenge accept/decline
│   │   ├── NotificationController.php  # Bell dropdown: open + read-all
│   │   ├── ProfileController.php       # Profile view + update + avatar
│   │   ├── ReviewController.php        # User reviews
│   │   ├── VenueController.php         # Venue index + show
│   │   ├── ChatAssistantController.php  # AI Assistant (RAG chatbot)
│   │   └── (GameController.php — legacy)
│   ├── Models/
│   │   ├── ChatSession.php, ChatMessage.php  # AI Assistant
│   │   ├── Connection.php, ConnectionMessage.php
│   │   ├── Event.php, EventMessage.php, EventParticipant.php
│   │   ├── Notification.php            # In-app notifications
│   │   ├── Field.php, Game.php, GameMatch.php (legacy)
│   │   ├── Sport.php, UserSport.php
│   │   ├── TimeSlot.php (legacy)
│   │   ├── User.php, UserReview.php
│   │   └── Venue.php, VenueSlot.php
│   └── Services/
│       ├── ChatbotService.php          # RAG orchestrator (June 2026)
│       ├── GeminiClient.php            # Gemini embeddings + chat wrapper
│       ├── RagRetriever.php            # cosine-similarity search
│       └── MatchmakingService.php      # 5-component AI scoring engine
├── database/
│   ├── migrations/                     # See docs/database-schema.md
│   └── seeders/DatabaseSeeder.php
└── resources/views/
    ├── auth/login, register
    ├── chat.blade.php
    ├── connections/{index,chat}.blade.php
    ├── chat/                         # AI Assistant (June 2026)
    │   ├── show.blade.php
    │   └── _widget-launcher.blade.php
    ├── dashboard.blade.php
    ├── events/{index,create,show}.blade.php
    ├── layouts/app.blade.php
    ├── matchmaking.blade.php
    ├── my-events.blade.php
    ├── players/profile.blade.php
    ├── profile.blade.php
    ├── reviews/create.blade.php
    └── venues/{index,show}.blade.php
```

---

## 4. Critical Anti-Regression Rules

⚠️ **Never access `$sport->skill_levels` directly in Blade.**
Always use `$sport->getSkillLevels()` instead. See `docs/models.md`.

⚠️ **Distance scoring in SQL is not used.**
Distance filter is applied in PHP via `User::distanceFromUser()` (Haversine formula) after fetching candidates from MySQL. See `docs/matchmaking-algorithm.md`.

⚠️ **`acceptedConnections()` must NOT use `hasMany`.**
`hasMany` prepends `user_id = $id` which breaks the OR logic. Use static `Connection::where()` builder. See `docs/models.md`.

⚠️ **Label lookups use `value` key, not array index.**
Always match by `{value: N, name: "..."}` key — not by numeric array position. See `docs/models.md` and `docs/matchmaking-algorithm.md`.

⚠️ **Radius filter runs in PHP, not SQL.**
Filter in `MatchmakingService` via `User::distanceFromUser()` after fetching candidates. See `docs/matchmaking-algorithm.md`.

⚠️ **Challenge invites vs. join requests are distinguished by `is_invite` flag.**
Only the challenged player can respond to challenge invites (`is_invite=true`). Host approval queue only shows `is_invite=false` entries. See `docs/features.md`.

⚠️ **N+1 prevention — always eager load relations.**
MatchmakingController preloads: `userSports.sport`, `userSports`, `joinedEvents.sport`, `reviewsReceived.reviewer`. Never query inside loops. See `docs/controllers.md`.

⚠️ **Sports editor validation: use `nullable` not `required`.**
`ProfileController` uses `nullable` for `sports.*.sport_id` to allow empty rows.

---

## 5. Coding Conventions

### Skill Levels
- Always access via `Sport::getSkillLevels()`, `Sport::getSkillLabel()`, `Sport::normalizeSkill()`
- Store `skill_value` (human-readable string) and `skill_number` (normalized 1–10 int)
- Normalize using: `(value - 1) / (maxValue - 1) * 9 + 1`, clamped to 1–10

### Avatar Upload
- Storage path: `storage/app/public/avatars/`
- Accessed via Laravel's `Storage` facade

### Chat File Uploads
- Storage path: `storage/app/public/chat-files/`
- No mime/size validation yet (see known issues)

### Map Picker Component
- Component: `resources/views/components/map-picker.blade.php`
- Uses Leaflet `L` global — Leaflet JS must load before Alpine in `<head>`
- Coordinate fields: hidden form inputs — never exposed as raw numbers

---

## 6. Pointer to Detailed Docs

| Topic | File |
|---|---|
| Models & relationships | `docs/models.md` |
| Database schema & migrations | `docs/database-schema.md` |
| Matchmaking algorithm (v2) | `docs/matchmaking-algorithm.md` |
| Controllers & methods | `docs/controllers.md` |
| Routes | `docs/routes.md` |
| Views & templates | `docs/views.md` |
| Feature explanations | `docs/features.md` |
| Filament admin panel | `docs/filament.md` |
| Known issues & technical debt | `docs/known-issues.md` |
| Feature changelog & bug history | `docs/changelog.md` |
| Seeded data reference | `docs/seeded-data.md` |
| Next steps & roadmap | `docs/roadmap.md` |
# Navigation Structure — PlayMate

> File: `docs/06-navigation-structure.md`
> Purpose: Describes all navigation paths, page hierarchy, and routing for each user role.

---

## 1. Guest (Unauthenticated)

```
/ (Landing Page)
├── GET /login        → Login Page
├── POST /login       → Auth Attempt → Dashboard (if success)
├── GET /register     → Registration Page
├── POST /register    → Create Account → Dashboard (if success)
│
├── GET /events           → Events Grid (public browse)
│   └── GET /events/{id} → Event Detail
│
├── GET /venues           → Venue List
│   └── GET /venues/{id} → Venue Detail + Schedule
│
└── GET /players/{id}     → Public Player Profile
    └── Click "Connect" → /login
```

---

## 2. Player (Authenticated User)

All Guest routes are accessible, plus:

### 2.1 Dashboard & Matchmaking

```
GET /dashboard     → Dashboard (upcoming events, stats, quick actions)
GET /matchmaking  → Matchmaking Page
│   ├── Filter: sport_id, skill, distance, time, min_score
│   ├── View opponent cards with AI score breakdown
│   ├── View mutual connections count
│   ├── Click "Challenge" → /events/challenge/{user} → Create Event (pre-filled)
│   └── Connection state-aware buttons (Connect/Chat/Pending/Accept/Decline)
```

### 2.2 Profile

```
GET /profile     → Own Profile Page
POST /profile   → Update Profile (avatar, bio, gender, GPS, sports)
```

### 2.3 Connections

```
GET /connections           → My Connections (3 tabs)
│   ├── Tab: Incoming  → Pending received (Accept/Decline)
│   ├── Tab: Sent      → Pending sent
│   └── Tab: My Connections → Accepted list (Chat/View/Review)
│
├── GET /players/{id}     → Public Player Profile
│   └── Click "Connect"  → POST /connections
│
├── PATCH /connections/{id} → Accept / Decline / Block
│
└── GET /connections/{id}/chat  → Private Chat Thread
    POST /connections/{id}/chat → Send message (text/image/file)
```

### 2.4 Events

```
GET /events           → Events Grid (browse)
GET /events/create    → Create Event Form
POST /events         → Store Event → Redirect to event detail

GET /events/{id}           → Event Detail + Participants
POST /events/{id}/join     → Join Event (auto-approve or pending)
DELETE /events/{id}/leave  → Leave Event
DELETE /events/{id}/cancel → Cancel Event (host only)

GET /events/{id}/chat      → Event Group Chat
POST /events/{id}/chat     → Send message
DELETE /events/{id}/chat/{msg} → Delete own message
```

### 2.5 My Events

```
GET /my-events               → My Events (6 tabs)
│   ├── Upcoming
│   ├── Hosted (with pending badge)
│   ├── Waiting
│   ├── Pending
│   ├── Past
│   └── Cancelled
│
GET /my-events/host-requests  → Pending Join Requests (host only)
POST /my-events/host-requests/{participant}/approve  → Approve
POST /my-events/host-requests/{participant}/reject   → Reject
```

### 2.6 Reviews

```
GET /reviews/create     → Write Review Form
POST /reviews           → Submit Review (rating 1–5, comment)
```

Reviews are also visible on:
- `/players/{id}` (public player profile)

### 2.7 Venues (same as Guest, plus)

```
GET /venues           → Venue List
GET /venues/{id}      → Venue Detail + Full Schedule Grid
```

### 2.8 AI Assistant (Chatbot)

```
GET /chat                              → Full-page chat (sidebar of sessions + thread)
GET /chat/sessions                     → JSON list of own sessions (sidebar)
POST /chat/sessions                    → Create new empty session
GET /chat/sessions/{session}/messages  → JSON messages for a session
POST /chat/sessions/{session}/messages → Send message → Gemini RAG reply
DELETE /chat/sessions/{session}        → Delete session (cascades messages)
```

Also reachable via a floating widget (`<x-chatbot-launcher />`) injected into every authenticated page, not just `/chat`. Rate-limited to 30 messages / 10 min per user. See `docs/features.md` §16.

---

## 3. Host (Player + Event Creation)

Host inherits all Player routes plus:

```
GET /events/create           → Create Event (additional fields)
│   ├── Set approval_required flag
│   ├── Set max_slots, price, visibility, match_type
│   └── If challenge flow → opponent pre-filled as pending participant

GET /my-events/host-requests  → Manage all pending join requests
POST .../approve             → Approve + assign slot + post to chat
POST .../reject              → Reject + post to chat

DELETE /events/{id}/cancel    → Host can cancel own events
```

---

## 4. Admin (Filament Panel)

```
GET /admin   → Filament Admin Dashboard (requires auth)
```

### Admin Resources (auto-discovered from `app/Filament/Resources/`)

| Resource | Path |
|---|---|
| UserResource | `/admin/users` |
| SportResource | `/admin/sports` |
| VenueResource | `/admin/venues` |
| EventResource | `/admin/events` |
| ConnectionResource | `/admin/connections` |
| ReviewResource | `/admin/reviews` |
| (others) | `/admin/...` |

Each resource supports: List, Create, Edit, Delete.

---

## 5. Navigation Summary Matrix

| Feature | Guest | Player | Host | Admin |
|---|---|---|---|---|
| Landing page | ✅ | ✅ | ✅ | ✅ |
| Register / Login | ✅ | ✅ | ✅ | ✅ |
| Browse Events | ✅ | ✅ | ✅ | — |
| Event Detail | ✅ | ✅ | ✅ | — |
| Browse Venues | ✅ | ✅ | ✅ | ✅ |
| Venue Detail | ✅ | ✅ | ✅ | ✅ |
| View Player Profile | ✅ | ✅ | ✅ | — |
| Matchmaking | — | ✅ | ✅ | — |
| My Connections | — | ✅ | ✅ | — |
| Create / Join / Leave Event | — | ✅ | ✅ | — |
| Create Event | — | ✅ | ✅ | — |
| Manage Host Requests | — | — | ✅ | — |
| Event Group Chat | — | ✅ | ✅ | — |
| Private Chat | — | ✅ | ✅ | — |
| Write Review | — | ✅ | ✅ | — |
| Manage Profile | — | ✅ | ✅ | — |
| AI Assistant (Chatbot) | — | ✅ | ✅ | — |
| Admin Panel | — | — | — | ✅ |

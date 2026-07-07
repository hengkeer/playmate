# Models & Relationships

> PlayMate — Models Reference
> Last updated: July 2026

---

## 1. Sport ⚠️ Critical Rule

```php
fillable: name, slug, icon, description, skill_levels (JSON array)
casts:   skill_levels → array
```

⚠️ **Never access `$sport->skill_levels` directly in Blade or PHP.**
Always use `$sport->getSkillLevels()` instead.

### Supported `skill_levels` Formats (auto-adapted at runtime)

| Format | Example | Conversion |
|---|---|---|
| New structured | `[{"value":1,"name":"1.0 Beginner"},...]` | Used as-is |
| Legacy string | `["Beginner","Intermediate",...]` | Auto-converted → `[{value:N,name:"Label"},...]` |
| Legacy numeric | `[1,2,3,...]` | Auto-converted → `[{value:N,name:"Level N"},...]` |

### Methods

```php
maxSkillValue(): int          // Tennis:12, Badminton:8, Padel:7
getSkillLevels(): array       // SAFE — always returns [{value, name}, ...]
getSkillLabel(int $rawValue): string  // Looks up label by 'value' key, NOT array index
getLabelFromNormalized(int $normalized): string  // Reverse lookup 1–10 → label
normalizeSkill(int $rawValue): int  // Clamp + normalize to 1–10 scale
convertLegacyStringArray(array $arr): array     // Protected
convertLegacyNumericArray(array $arr): array    // Protected
```

**Label lookup uses `value` key, not array index** — safe even if `skill_levels` array order changes.

### Skill Normalization

**Formula:** `(value - 1) / (maxValue - 1) * 9 + 1`, then round, then clamp to 1–10

| Sport | Scale | Endpoints normalized |
|---|---|---|
| Tennis | 1–12 | 1→1, 6→5, 7→6, 12→10 |
| Badminton | 1–8 | 1→1, 4→4, 8→10 |
| Padel | 1–7 | 1→1, 4→5, 7→10 |

**Clamping:** `raw < 1` → clamped to 1; `raw > max` → clamped to max; result clamped to 1–10.

---

## 2. UserSport (Pivot)

```php
fillable: user_id, sport_id, skill_value, skill_number, play_style,
          preferred_start_time, preferred_end_time, preferred_days (json)
casts:   skill_number → integer, preferred_days → array
unique:  (user_id, sport_id)

preferred_days format: [0,1,2,3,4,5,6]  // 0=Sun … 6=Sat
```

**On save (ProfileController):**
- `skill_value` = human-readable label string — looked up by `value` key (not array index)
- `skill_number` = normalized int 1–10 — guaranteed via `Sport::normalizeSkill()` with clamping
- `preferred_start_time` / `preferred_end_time` = `HH:MM:SS` format
- Deletes existing `user_sports` and recreates on update

---

## 3. User

```php
fillable: name, email, password, photo_url, bio, gender, age_range,
          home_address, latitude, longitude, play_style,
          last_active_at, total_events_joined
casts: photo_url, home_address, ...
```

### Relationships

```php
userSports()           → hasMany(UserSport::class)
sports()               → belongsToMany(Sport::class, 'user_sports')
                           → withPivot: skill_value, skill_number, play_style
hostedEvents()          → hasMany(Event::class, 'host_id')
eventParticipations()   → hasMany(EventParticipant::class)
joinedEvents()          → belongsToMany(Event::class, 'event_participants')
messages()             → hasMany(EventMessage::class)
matches()              → hasMany(GameMatch::class)   // legacy

sentConnections()      → hasMany(Connection::class, 'requester_id')
receivedConnections()  → hasMany(Connection::class, 'receiver_id')

// ⚠️ acceptedConnections() MUST use static Connection::where() builder
//    NOT hasMany — hasMany prepends user_id=$id which breaks OR logic
reviewsReceived()       → hasMany(UserReview::class, 'reviewed_user_id')
reviewsGiven()          → hasMany(UserReview::class, 'reviewer_id')

// Notifications (custom table — overrides Notifiable's polymorphic default)
notifications()         → hasMany(Notification::class) → orderByDesc('created_at')
unreadNotifications()   → notifications() → scope unread (whereNull read_at)
```

### Computed

```php
averageRating  → avg of reviewsReceived (float|null)
totalReviews   → count of reviewsReceived (int)
```

### Helpers

```php
avatarInitial(): string              // first letter of name, uppercase
getSkillForSport(int sportId): ?int  // returns skill_number
distanceFromUser(User $other): ?float // Haversine km
isRecentlyActive(int days=7): bool
```

---

## 4. Connection ⚠️

```php
fillable: requester_id, receiver_id, status
unique:  (requester_id, receiver_id)
status:  'pending' | 'accepted' | 'declined' | 'blocked'
indexes: (receiver_id, status), (requester_id, status)
```

### Relationships

```php
requester()   → belongsTo(User::class, 'requester_id')
receiver()    → belongsTo(User::class, 'receiver_id')
messages()    → hasMany(ConnectionMessage::class) → orderBy created_at asc
latestMessage() → hasOne(ConnectionMessage::class) → latestOfMany
```

### Helpers

```php
getOtherUser(User $user): User    // returns the other party
involvesUser(User $user): bool   // is this user in the connection?
isPending(): bool
isAccepted(): bool
isDeclined(): bool
isBlocked(): bool
```

⚠️ **CRITICAL:** `acceptedConnections()` in User model must use a static `Connection::where()` builder — NOT a `hasMany` relationship with a `where()` clause, because `hasMany` prepends `user_id = $id` which breaks the required OR logic.

**Correct pattern:**
```php
// In User model — use static builder
public static function acceptedConnections($id) {
    return Connection::where('status', 'accepted')
        ->where(fn($q) => $q->where('requester_id', $id)->orWhere('receiver_id', $id));
}
```

---

## 5. ConnectionMessage

```php
fillable: connection_id, sender_id, message, type, file_url, file_name
type:    'text' | 'image' | 'file'
index:   (connection_id, created_at)

relationship: sender() → belongsTo(User::class, 'sender_id')
helper: isOwnedBy(User $user): bool
```

---

## 6. UserReview

```php
fillable: reviewer_id, reviewed_user_id, rating, comment, source_type, source_id
casts:   rating → integer (1–5)
unique:  (reviewer_id, reviewed_user_id, source_type)
source_type: 'connection' | 'event' (nullable)

relationships:
  reviewer()      → belongsTo(User::class, 'reviewer_id')
  reviewedUser()  → belongsTo(User::class, 'reviewed_user_id')
helper: isOwnedBy(User $user): bool
```

---

## 7. Event

```php
fillable: host_id, sport_id, title, description, venue_name,
          latitude, longitude, start_time, end_time,
          max_slots, price, payment_info, visibility, match_type,
          approval_required, status, is_challenge
visibility:    'public' | 'private'
match_type:    'singles' | 'doubles'
approval_required: boolean (default false)
status:        'upcoming' | 'ongoing' | 'completed' | 'cancelled'
is_challenge:  boolean (default false)
```

### Relationships

```php
host()                → belongsTo(User::class, 'host_id')
sport()               → belongsTo(Sport::class)
participants()        → hasMany(EventParticipant::class)
approvedParticipants()→ hasMany(EventParticipant::class) → where status='approved'
users()               → belongsToMany(User::class, 'event_participants')
                          → withPivot: status, slot_number, joined_at
messages()            → hasMany(EventMessage::class) → orderBy created_at asc
```

### Helpers

```php
isFull(): bool
availableSlots(): int
hasUser(User): bool
isUserApproved(User): bool
isHostedBy(User): bool
canAccessChat(User): bool  // approved OR host
isUpcoming(): bool
isPast(): bool
```

---

## 8. EventParticipant

```php
fillable: event_id, user_id, status, is_invite, slot_number, joined_at
casts:   joined_at → datetime, slot_number → integer, is_invite → boolean
unique:  (event_id, user_id)
status:  'pending' | 'approved' | 'rejected' | 'waiting'
is_invite: false = join request (managed by host)
           true  = challenge invite (managed by opponent)

relationship: event() → belongsTo(Event::class)
             user()  → belongsTo(User::class)
helpers: isApproved(), isPending(), isWaiting(), isChallengeInvite()
```

> `isChallengeInvite()` returns `true` when `is_invite === true AND status === 'pending'`.
> This is the gate used in `MyEventsController` to separate challenge invites from join requests.

---

## 9. EventMessage

```php
fillable: event_id, user_id, content, type, file_url, file_name
type:    'text' | 'image' | 'file'

relationships: event() → belongsTo(Event::class)
               user()  → belongsTo(User::class)
helper: isOwnedBy(User): bool
```

---

## 10. Venue

```php
fillable: name, sport_id, address, area, latitude, longitude,
          open_hours, description, price_estimate, contact, image_url, is_active
relationship: sport() → belongsTo(Sport::class)
              slots() → hasMany(VenueSlot::class)
helper: distanceFrom(float lat, float lng): float  // Haversine km
```

---

## 11. VenueSlot

```php
fillable: venue_id, day_of_week (0=Sun–6=Sat), start_time, end_time, price, is_available
relationship: venue() → belongsTo(Venue::class)
helper: getDayNameAttribute(): string  // "Sun", "Mon", ...
```

---

## 12. Legacy Models

| Model | File | Notes |
|---|---|---|
| Field | `Field.php` | Legacy — kept for `FieldAvailabilityController` |
| TimeSlot | `TimeSlot.php` | Legacy |
| Game | `Game.php` | Legacy — parallel to events |
| GameMatch | `GameMatch.php` | Legacy — parallel to events |

---

## 13. ChatSession (AI Assistant — June 2026)

```php
fillable: user_id, title
title:    string nullable  // auto-set from first user message (str::limit 50)
indexes:  user_id, (user_id, updated_at)
cascade:  on user delete

relationships:
  user()     → belongsTo(User)
  messages() → hasMany(ChatMessage) orderBy created_at asc
```

---

## 14. ChatMessage (AI Assistant)

```php
fillable: session_id, role, content, tokens_used
role:    'user' | 'assistant'
tokens_used: unsignedInteger nullable  // from Gemini usageMetadata
index:   (session_id, created_at)
cascade: on session delete

relationship: session() → belongsTo(ChatSession)
```

---

## 15. Notification (July 2026)

```php
fillable: user_id, type, title, body, action_url, read_at
casts:   read_at → datetime
index:   (user_id, read_at)   // fast unread-count
cascade: on user delete

type values:
  'join_request'       // new join request → host
  'join_approved'      // host approved request → player
  'join_rejected'      // host rejected request → player
  'challenge_invite'   // challenger sent a challenge → opponent
  'challenge_accepted' // opponent accepted challenge → challenger
  'challenge_declined' // opponent declined challenge → challenger

relationship: user() → belongsTo(User::class)
scopes:  scopeUnread(Builder $q) → whereNull('read_at')
helpers: isUnread(): bool
         markAsRead(): void  // idempotent, only writes if currently unread
```
# Database Schema

> PlayMate — Complete Database Schema Reference
> Last updated: July 2026

---

## 1. Core Tables

### `sports`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | varchar | e.g. "Tennis" |
| slug | varchar | URL-safe identifier |
| icon | varchar nullable | Emoji or icon class |
| description | text nullable | |
| skill_levels | json | `[{value: int, name: string}, ...]` — **ALWAYS access via `Sport::getSkillLevels()`** |

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| email | varchar unique | |
| password | varchar | |
| email_verified_at | timestamp nullable | |
| photo_url | varchar nullable | Avatar image path — `storage/app/public/avatars/` |
| bio | text nullable | |
| gender | enum(male/female/other) nullable | |
| age_range | varchar(20) nullable | "18-25", "26-35", "36-50", "51+" |
| home_address | varchar nullable | |
| latitude | decimal(10,8) nullable | GPS coordinate |
| longitude | decimal(11,8) nullable | GPS coordinate |
| play_style | varchar(20) nullable | "casual" or "competitive" |
| last_active_at | timestamp nullable | Used for activity scoring in matchmaking |
| total_events_joined | int default 0 | |
| timestamps | | created_at, updated_at |

### `user_sports` (pivot)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | → `users.id` |
| sport_id | bigint FK | → `sports.id` |
| skill_value | varchar | Human-readable label, e.g. "3.5", "Upper Intermediate" |
| skill_number | int | Normalized 1–10 scale — **always store via `Sport::normalizeSkill()`** |
| play_style | varchar(20) nullable | Per-sport play style override |
| preferred_start_time | time nullable | Preferred playing time from (e.g. `09:00:00`) |
| preferred_end_time | time nullable | Preferred playing time to (e.g. `17:00:00`) |
| preferred_days | json nullable | Array of day numbers: `[0,1,2,3,4,5,6]` (0=Sun…6=Sat) |
| **Unique** | `(user_id, sport_id)` | One skill level per sport per user |

---

## 2. Event Tables

### `events`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| host_id | bigint FK | → `users.id` |
| sport_id | bigint FK nullable | → `sports.id` |
| title | varchar | |
| description | text nullable | |
| venue_name | varchar | |
| latitude | decimal(10,8) nullable | |
| longitude | decimal(11,8) nullable | |
| start_time | datetime | |
| end_time | datetime | |
| max_slots | int default 4 | |
| price | decimal nullable | |
| payment_info | text nullable | |
| visibility | enum(public/private) default public | |
| match_type | enum(singles/doubles) default singles | |
| approval_required | boolean default false | |
| status | enum(upcoming/ongoing/completed/cancelled) | |
| timestamps | | |

### `event_participants`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| event_id | bigint FK | → `events.id` |
| user_id | bigint FK | → `users.id` |
| status | enum default approved | 'pending'\|'approved'\|'rejected'\|'waiting' |
| is_invite | boolean default false | `true` = challenge invite sent by host; `false` = regular join request |
| slot_number | int nullable | Join order (1, 2, 3, ...) |
| joined_at | timestamp nullable | |
| **Unique** | `(event_id, user_id)` | |

### `event_messages`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| event_id | bigint FK | → `events.id` |
| user_id | bigint FK | → `users.id` |
| content | text | |
| type | enum(text/image/file) default text | |
| file_url | varchar nullable | `storage/app/public/chat-files/` |
| file_name | varchar nullable | |
| timestamps | | |

---

## 3. Social Tables

### `connections`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| requester_id | bigint FK | → `users.id` |
| receiver_id | bigint FK | → `users.id` |
| status | enum(pending/accepted/declined/blocked) default pending | |
| timestamps | | |
| **Unique** | `(requester_id, receiver_id)` | |
| **Index** | `(receiver_id, status)` | |
| **Index** | `(requester_id, status)` | |

### `connection_messages`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| connection_id | bigint FK | → `connections.id` |
| sender_id | bigint FK | → `users.id` |
| message | text | |
| type | enum(text/image/file) default text | |
| file_url | varchar nullable | |
| file_name | varchar nullable | |
| timestamps | | |
| **Index** | `(connection_id, created_at)` | |

### `user_reviews`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reviewer_id | bigint FK | → `users.id` |
| reviewed_user_id | bigint FK | → `users.id` |
| rating | tinyint unsigned | 1–5 |
| comment | text nullable | |
| source_type | varchar(50) nullable | 'connection' or 'event' |
| source_id | bigint nullable | FK to `connections` or `events` table |
| timestamps | | |
| **Unique** | `(reviewer_id, reviewed_user_id, source_type)` | |

---

## 4. Venue Tables

### `venues`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| sport_id | bigint FK | → `sports.id` |
| address | varchar | |
| area | varchar | |
| latitude | decimal(10,8) nullable | |
| longitude | decimal(11,8) nullable | |
| open_hours | varchar nullable | e.g. "06:00-22:00" |
| description | text nullable | |
| price_estimate | varchar nullable | |
| contact | varchar nullable | |
| image_url | varchar nullable | |
| is_active | boolean default true | |

### `venue_slots`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| venue_id | bigint FK | → `venues.id` |
| day_of_week | tinyint | 0=Sun, 1=Mon, ..., 6=Sat |
| start_time | time | |
| end_time | time | |
| price | decimal nullable | |
| is_available | boolean default true | |

---

## 5. Legacy Tables

| Table | Notes |
|---|---|
| `fields` | Legacy — 8 seeded records, kept for `FieldAvailabilityController` |
| `time_slots` | Legacy — 88 seeded records |
| `games` | Legacy — parallel to events system |
| `game_matches` | Legacy |

---

## 5b. Notification Table (July 2026)

### `notifications`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | → `users.id` ON DELETE CASCADE |
| type | varchar | `'join_request'` \| `'join_approved'` \| `'join_rejected'` \| `'challenge_invite'` \| `'challenge_accepted'` \| `'challenge_declined'` |
| title | varchar | Short heading for the bell dropdown |
| body | text nullable | Longer description text |
| action_url | varchar nullable | URL to redirect to when notification is opened |
| read_at | timestamp nullable | NULL = unread; set to `now()` on open or read-all |
| timestamps | | created_at, updated_at |
| **Index** | `(user_id, read_at)` | Fast unread-count query |

---

## 5c. AI Assistant Tables (June 2026)

### `chat_sessions`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | → `users.id` ON DELETE CASCADE |
| title | varchar nullable | Auto-set from first user message (Str::limit 50) |
| timestamps | | |
| **Index** | `user_id` | |
| **Index** | `(user_id, updated_at)` | Recent-first ordering |

### `chat_messages`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| session_id | bigint FK | → `chat_sessions.id` ON DELETE CASCADE |
| role | enum('user','assistant') | |
| content | text | |
| tokens_used | unsignedInteger nullable | From Gemini `usageMetadata.totalTokenCount` |
| timestamps | | |
| **Index** | `(session_id, created_at)` | Threaded fetch order |

---

## 6. Migration Files Reference

```
database/migrations/
├── ... (standard Laravel auth/users)
├── 2026_05_01_000001_create_sports_table.php
├── 2026_05_01_000002_create_user_sports_table.php
├── 2026_05_01_000003_create_venues_table.php
├── 2026_05_01_000004_create_venue_slots_table.php
├── 2026_05_01_000005_create_event_messages_table.php
├── 2026_05_01_000006_update_users_table_v2.php
├── 2026_05_01_000007_update_events_table_v2.php
├── 2026_05_02_000001_create_connections_table.php
├── 2026_05_02_000002_create_connection_messages_table.php
├── 2026_05_02_000003_create_user_reviews_table.php
├── 2026_05_22_042240_add_time_preferences_to_user_sports_table.php
├── 2026_06_03_000001_create_chat_sessions_table.php         # AI Assistant
├── 2026_06_03_000002_create_chat_messages_table.php         # AI Assistant
├── 2026_06_07_141205_add_role_to_users_table.php
├── 2026_06_07_145256_add_ayo_fields_to_venues_table.php
├── 2026_07_04_000001_add_is_invite_to_event_participants_table.php  # Challenge flow
└── 2026_07_04_000002_create_notifications_table.php                 # In-app notifications
```

---

## 7. Skill Level Migration (One-Time Maintenance)

⚠️ Run once via Tinker to convert legacy `skill_levels` data to new structured format:

```bash
php artisan tinker
```

```php
foreach (\App\Models\Sport::all() as $sport) {
    $raw = $sport->getRawOriginal('skill_levels');
    $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
    if (is_array($decoded) && !empty($decoded)) {
        $first = $decoded[0] ?? null;
        if (isset($first['value']) || isset($first['name'])) {
            echo "{$sport->name}: already structured — skipping\n";
            continue;
        }
    }
    $structured = $sport->getSkillLevels();
    if (!empty($structured)) {
        $sport->skill_levels = $structured;
        $sport->save();
        echo "{$sport->name}: migrated\n";
    }
}
```

**Converts:**
- `["Beginner","Intermediate",...]` → `[{value:1,name:"Beginner"},...]`
- `[1,2,3,4,5]` → `[{value:1,name:"Level 1"},...]`
- Already-structured data → skipped (idempotent)
# Seeded Data Reference

> PlayMate — Database Seeder Reference
> Run with: `php artisan db:seed`
> Last updated: May 2026 (v2 DataSeeder)

---

## 1. Seeded Entities

| Entity | Count | Notes |
|---|---|---|
| Sports | 3 | Tennis (NTRP 1-12, 12 levels), Badminton (8 levels), Padel (7 levels) |
| Venues | 19 | 10 tennis, 5 badminton, 4 padel — all Kota Tangerang Selatan |
| Users | 35 | 32 Indonesian personas + Erlangga + Angga + Roger Federer (protected) |
| UserSports | ~47 | Users with 1–3 sports + time preferences on primary sport |
| Events | 19 | All 3 sports, 1–14 days ahead, mix of singles/doubles + public/private |
| Connections | 24 | 16 accepted (mutual clusters) + 8 pending |
| UserReviews | 21 | Connection + event sourced, 3–5 star range |
| Fields (legacy) | 8 | |
| TimeSlots (legacy) | 88 | |

---

## 2. Sport Definitions

### Tennis
- **Scale:** 1–12 (NTRP)
- **Levels:** 1.0 Beginner → 1.5 → 2.0 → 2.5 → 3.0 → 3.5 → 4.0 → 4.5 → 5.0 → 5.5 → 6.0+ → 7.0 Elite
- **Normalization:** 1→1, 6→5, 7→6, 12→10

### Badminton
- **Scale:** 1–8
- **Levels:** Beginner → Lower Intermediate → Intermediate → Upper Intermediate → Advanced → Competitive → Tournament → Elite
- **Normalization:** 1→1, 4→4, 8→10

### Padel
- **Scale:** 1–7
- **Levels:** Beginner → Casual → Improving → Intermediate → Advanced → Competitive → Elite
- **Normalization:** 1→1, 4→5, 7→10

---

## 3. Demo Credentials

**⚠️ All 35 seeded users share the same password:** `password`

Use any account below with the same password to log in as that user.

---

### 3.1 Primary Demo User

| ID | Name | Email | Password | Primary Sport | Skill | Purpose |
|---|---|---|---|---|---|---|
| 1 | Rizky Pratama | `rizky@example.com` | `password` | Tennis | NTRP 3.5 (norm 5) | **Main demo account** — login here to see full matchmaking, reveal overlay, and all features |

> **Recommended workflow:** Log in as Rizky → visit `/matchmaking` → ⚔️ Challenger Discovered overlay triggers for Galih Ramadhan (score=0.91, same sport, same level, 1 mutual connection).

---

### 3.2 Core Matchmaking Demo Users

These users are used to demonstrate the 5-component matchmaking engine.

| ID | Name | Email | Primary Sport | Norm Skill | vs Rizky | Match Score | Demo Purpose |
|---|---|---|---|---|---|---|---|
| 11 | Galih Ramadhan | `galih@example.com` | Tennis | 5 | same level | **0.91** | ⚔️ **Reveal overlay trigger** — perfect sport/skill/time/distance, 1 mutual connection |
| 19 | Arif Rahman | `arif@example.com` | Tennis | 7 | +2 levels | 0.85 | Same sport, nearby, active today, connected |
| 23 | Bagus Pratama | `bagus@example.com` | Tennis | 6 | +1 level | 0.84 | Similar skill (0.95 band), 9.7km away → distance penalty demonstrated |
| 28 | Wawan Susanto | `wawan@example.com` | Tennis | 7 | +2 levels | 0.84 | Same sport, nearby, active, connected (mutual badge) |
| 5 | Budi Santoso | `budi@example.com` | Tennis | 5 | same level | 0.83 | Perfect skill match (diff=0 → 1.0), 14.7km away → strong distance penalty |
| 17 | Surya Darma | `surya@example.com` | Tennis | 6 | +1 level | 0.82 | Similar skill, weekday morning prefs (no overlap with Rizky) |
| 7 | Fajar Nugroho | `fajar@example.com` | Tennis | 8 | +3 levels | 0.81 | Elite player, very close (1.1km), skill diff=3 → 0.60 band |
| 9 | Hendra Wijaya | `hendra@example.com` | Tennis | 6 | +1 level | 0.81 | Perfect tennis skill match, Bekasi (17.8km), padel secondary |
| 25 | Yusuf Ibrahim | `yusuf@example.com` | Tennis | 8 | +3 levels | 0.81 | Elite tennis + padel, 1.6km away |
| 3 | Ahmad Fauzi | `ahmad@example.com` | Tennis | 7 | +2 levels | 0.80 | Elite tennis, weekday morning prefs |
| 33 | Erlangga Rafi | `erlanggarafi38@gmail.com` | Tennis | 4 | -1 level | 0.71 | Creator account (protected) |

---

### 3.3 Badminton Cluster

Badminton filter for Rizky (login as Rizky → filter by Badminton):

| ID | Name | Email | Primary Sport | Norm Skill | Match Score | Connection State | Demo Purpose |
|---|---|---|---|---|---|---|---|
| 2 | Dewi Anggraini | `dewi@example.com` | Badminton | 6 | **0.89** | Connected | Same sport, same level, Wed+Sat evenings, 3 mutual connections |
| 31 | Annisa Farida | `annisa@example.com` | Badminton | 7 | 0.85 | Connected | Same sport, 1 mutual, weekday evenings |
| 4 | Sinta Maharani | `sinta@example.com` | Badminton | 5 | 0.84 | Connected (11 mutual) | Same level, weekend mornings, far cluster |
| 12 | Putri Handayani | `putri@example.com` | Badminton | 5 | 0.83 | Connected (9 mutual) | Same level as Rizky's badminton, nearby |
| 29 | Hana Sabrina | `hana@example.com` | Badminton | 6 | 0.82 | Connected | Same level as Dewi's, connected cluster |
| 8 | Rina Wulandari | `rina@example.com` | Badminton | 6 | — | Connected | Same level, same evening slots as Dewi |

---

### 3.4 Event Hosts

Use these accounts to demo event creation, participant management, and host approval flow.

| ID | Name | Email | Sport | Hosted Events | Approval Required |
|---|---|---|---|---|---|
| 1 | Rizky Pratama | `rizky@example.com` | Tennis | Weekend Tennis Meetup | No |
| 2 | Dewi Anggraini | `dewi@example.com` | Badminton | Monday Night Badminton | No |
| 3 | Ahmad Fauzi | `ahmad@example.com` | Padel | Saturday Padel Tournament | No |
| 5 | Budi Santoso | `budi@example.com` | Tennis | Morning Tennis Practice | No |
| 10 | Anisa Nurfadilah | `anisa@example.com` | Badminton | Weekday Badminton League | **Yes** → demo host approval |
| 7 | Fajar Nugroho | `fajar@example.com` | Tennis | Elite Tennis Sparring | No |
| 13 | Dimas Aryo | `dimas@example.com` | Padel | Corporate Padel Challenge | **Yes** (private) |
| 8 | Rina Wulandari | `rina@example.com` | Badminton | Evening Badminton Social | No |
| 19 | Arif Rahman | `arif@example.com` | Tennis | Friday Night Tennis Doubles | No |
| 4 | Sinta Maharani | `sinta@example.com` | Badminton | Beginner Badminton Session | No |
| 17 | Surya Darma | `surya@example.com` | Tennis | Saturday Morning Tennis | No |
| 25 | Yusuf Ibrahim | `yusuf@example.com` | Padel | Weekend Padel Social | No |
| 29 | Hana Sabrina | `hana@example.com` | Badminton | Advanced Badminton Drill | **Yes** |
| 33 | Erlangga Rafi | `erlanggarafi38@gmail.com` | Tennis | Tennis Coaching Session | No |

---

### 3.5 Edge-Case / Far-Distance Users

Demonstrates distance decay `exp(-d/10)` and activity score variance.

| ID | Name | Email | Primary Sport | Location | Distance from Rizky | Activity Score | Demo Purpose |
|---|---|---|---|---|---|---|---|
| 16 | Vina Meilani | `vina@example.com` | Badminton | Bandung (~100km) | ~100km | 0.68 (≤7d) | Strong distance decay → low overall score |
| 20 | Nadia Zahra | `nadia@example.com` | Badminton | Surabaya (~700km) | ~700km | 0.5 (≤30d) | Very low distance score → near-zero contribution |
| 21 | Eko Prasetyo | `eko@example.com` | Padel | Semarang (~450km) | ~450km | 0.75 (≤7d) | Distance decay + padel filter match |
| 22 | Tika Ardianti | `tika@example.com` | Badminton | Yogyakarta (~550km) | ~550km | 0.5 (≤30d) | Far zone, same level as Dewi |
| 24 | Sari Dewi | `sari@example.com` | Badminton | Malang (~800km) | ~800km | 0.5 (≤30d) | Maximum distance decay |
| 14 | Lina Susilowati | `lina@example.com` | Badminton | Bogor (~50km) | ~50km | 0.5 (≤30d) | 25 days inactive → activity score 0.5 |

---

### 3.6 Protected / System Accounts

These accounts must not be deleted or overwritten.

| ID | Name | Email | Password | Sport | Skill | Purpose |
|---|---|---|---|---|---|---|
| 33 | Erlangga Rafi | `erlanggarafi38@gmail.com` | `password` | Tennis | NTRP 2.5 (norm 4) | Creator account — host of "Tennis Coaching Session" |
| 35 | Roger Federer | `rogerfederer@test.com` | `password` | Tennis | NTRP 2.0 (norm 3) | Demo account — connected to Erlangga, top match for Rizky (score=0.83) |
| 34 | Angga | `erlangga25846@gmail.com` | `password` | — | — | System account — no sports, no coords, preserved as-is |

---

### 3.7 Password Verification

All users are seeded with `Hash::make('password')` (Laravel's default bcrypt).

```php
// From DataSeeder.php line 855:
'password' => Hash::make('password'),

// Verifiable via tinker:
php artisan tinker --execute="echo \Illuminate\Support\Facades\Hash::check('password', \App\Models\User::find(1)->password) ? 'MATCH' : 'NO';"
// Output: MATCH
```

---

## 4. DataSeeder Strategy (v2)

The `DataSeeder` class (`database/seeders/DataSeeder.php`) replaces the old inline seeders.

### Design Goals

- **Realistic user personas** — every user has a bio, age range, play style, and realistic skill levels
- **Time preference diversity** — ~60% of users have time prefs (weekend mornings / weekday evenings / etc.)
- **Activity score coverage** — all 5 tiers represented (≤3d, ≤7d, ≤30d, ≤90d, inactive)
- **Mutual connection clusters** — 16 accepted connections in friend groups (triggers mutual badge on cards)
- **Challenger Discovered overlay** — Galih Ramadhan is top match for Rizky (score=0.91, same sport, skill=1.0, mutual=1 → reveal shown)
- **Distance decay demonstration** — Jakarta vs far-zone users (Bandung, Surabaya, Semarang, Yogya, Malang)
- **Sport filtering** — badminton filter shows Dewi cluster (mutual connections), padel filter shows Yusuf

### Protected Users (never deleted)

| Name | Email | Role |
|---|---|---|
| Erlangga Rafi | erlanggarafi38@gmail.com | Creator account |
| Angga | erlangga25846@gmail.com | Creator account |
| Roger Federer | rogerfederer@test.com | Demo/test account |

### Demo Login

```
Email:    rizky@example.com
Password: password
```

**Rizky's profile:** Tennis 6 (norm 5), Badminton 4 (norm 5), no time preferences.
Expected top match: **Galih Ramadhan** (score=0.91, same sport/skill level, time overlap, 1 mutual connection → ⚔️ Challenger Discovered overlay shown)

---

## 4. Event Dates

Events are seeded 1–14 days ahead of `Carbon::now()`. The browse page always shows events from `now()->subDays(90)` to `now()->addYear()`, so seeded events are always visible.

---

## 5. Connection Network

| Cluster | Members | Connection Type |
|---|---|---|
| Rizky cluster | Rizky ↔ Arif, Rizky ↔ Dewi | accepted |
| Tennis friend group | Arif ↔ Galih, Arif ↔ Wawan, Galih ↔ Wawan | accepted |
| Badminton group | Dewi ↔ Hana, Dewi ↔ Rina, Rina ↔ Hana | accepted |
| Elite tennis | Ahmad ↔ Surya, Ahmad ↔ Bagus | accepted |
| Padel cluster | Hendra ↔ Yusuf, Yusuf ↔ Rico, Fajar ↔ Rico | accepted |
| Far zone | Vina (Bandung) ↔ Tika (Yogya) | accepted |
| Erlangga–Roger | Roger ↔ Erlangga | accepted |
| Pending | Rizky → Galih, Rizky → Denny, Fajar → Rizky, Roger → Vina | pending |

---

## 6. Skill Levels Migration (One-Time)

If sports were seeded with legacy format, run the Tinker migration once:

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
- Already-structured data → skipped (idempotent, safe to re-run)

---

## 9. RAG Knowledge Base (June 2026)

The AI assistant's knowledge is built from `docs/*.md` (15 files, ~2200 lines total) and embedded offline by:

```bash
php artisan rag:build
```

Outputs to:
- `storage/app/private/rag/chunks.json` — chunk metadata (id, source, text, token estimate)
- `storage/app/private/rag/embeddings.json` — float vector per chunk, indexed by chunk id

Both files are gitignored (storage/app/private/ is allowlisted only for the folder). Re-run after updating `docs/`; the command is idempotent.
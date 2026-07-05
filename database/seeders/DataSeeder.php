<?php

namespace Database\Seeders;

use App\Models\Connection;
use App\Models\Event;
use App\Models\EventMessage;
use App\Models\EventParticipant;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserReview;
use App\Models\UserSport;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * PlayMate — Comprehensive Demo Data Seeder
 *
 * Optimised for MatchmakingService v2 demonstration:
 * - Sport scoring (primary vs secondary overlap)
 * - Normalized skill scoring (band-based diff decay)
 * - Time preference overlap
 * - Distance scoring (exp decay)
 * - Activity scoring (5-tier)
 * - Mutual connections
 * - Challenger Discovered overlay trigger scenarios
 *
 * Data strategy:
 *   35 total users in 3 tiers:
 *     Tier A — Core Jakarta players (15): close to CBD, varied skills/activity
 *     Tier B — Extended Jakarta (15): wider radius, mixed skills
 *     Tier C — Far / Out-of-area (3): Erlangga, Angga, Roger Federer (protected)
 *
 * Protected users (never deleted):
 *   - Erlangga Rafi (erlanggarafi38@gmail.com)
 *   - Roger Federer (rogerfederer@test.com)
 *   - Angga (erlangga25846@gmail.com)
 *
 * Sports: Tennis (1-12 NTRP), Badminton (1-8), Padel (1-7)
 * Jakarta coords: -6.18 to -6.30 lat, 106.70 to 106.99 lng
 * Bekasi/Tangerang: slightly wider spread
 */
class DataSeeder extends Seeder
{
    // Jakarta-area coordinate clusters
    // lat: -6.18 to -6.30 | lng: 106.70 to 106.99
    private array $coords = [
        'jkt_selatan'  => ['lat' => -6.261, 'lng' => 106.811, 'spread' => 0.025],
        'jkt_pusat'    => ['lat' => -6.175, 'lng' => 106.845, 'spread' => 0.020],
        'jkt_barat'    => ['lat' => -6.169, 'lng' => 106.759, 'spread' => 0.022],
        'jkt_timur'    => ['lat' => -6.225, 'lng' => 106.901, 'spread' => 0.020],
        'jkt_utara'    => ['lat' => -6.139, 'lng' => 106.881, 'spread' => 0.018],
        'depok'        => ['lat' => -6.403, 'lng' => 106.794, 'spread' => 0.020],
        'tangerang'    => ['lat' => -6.238, 'lng' => 106.631, 'spread' => 0.030],
        'bekasi'       => ['lat' => -6.233, 'lng' => 106.992, 'spread' => 0.025],
        'bogor'        => ['lat' => -6.595, 'lng' => 106.816, 'spread' => 0.030],
    ];

    // Sport IDs
    private int $TENNIS    = 1;
    private int $BADMINTON = 2;
    private int $PADEL     = 3;

    private $faker;

    public function run(): void
    {
        $this->faker = \Faker\Factory::create('id_ID');

        // ── Step 0: Collect existing protected user IDs ──────────────────────
        $protected = $this->getProtectedUserIds();
        $this->command->line("Protected users: " . implode(', ', $protected));

        // ── Step 1: Sports ──────────────────────────────────────────────────
        $this->seedSports();

        // ── Step 2: Venues ───────────────────────────────────────────────────
        $this->seedVenues();

        // ── Step 3: Users + UserSports + Time Prefs ─────────────────────────
        $coreUsers = $this->seedCoreUsers($protected);
        $this->seedConnections($coreUsers);
        $this->seedReviews($coreUsers);

        // ── Step 4: Events ─────────────────────────��────────────────────────
        $this->seedEvents($coreUsers);

        // ── Step 5: Event messages ─────────────────────────────────────────
        $this->seedEventMessages();
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 0 — Protected user IDs
    // ─────────────────────────────────────────────────────────────────────────────

    private function getProtectedUserIds(): array
    {
        return User::whereIn('email', [
            'erlanggarafi38@gmail.com',
            'erlangga25846@gmail.com',
            'rogerfederer@test.com',
        ])->pluck('id')->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 1 — Sports (no change, idempotent)
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedSports(): void
    {
        // Tennis (id=1) — 12 NTRP levels
        $tennis = Sport::updateOrCreate(
            ['slug' => 'tennis'],
            [
                'name'        => 'Tennis',
                'icon'        => '🎾',
                'description' => 'Racket sport played on a rectangular court. Players hit a ball over a net.',
                'skill_levels' => [
                    ['value' => 1,  'name' => '1.0 Beginner'],
                    ['value' => 2,  'name' => '1.5'],
                    ['value' => 3,  'name' => '2.0'],
                    ['value' => 4,  'name' => '2.5'],
                    ['value' => 5,  'name' => '3.0'],
                    ['value' => 6,  'name' => '3.5'],
                    ['value' => 7,  'name' => '4.0'],
                    ['value' => 8,  'name' => '4.5'],
                    ['value' => 9,  'name' => '5.0'],
                    ['value' => 10, 'name' => '5.5'],
                    ['value' => 11, 'name' => '6.0+'],
                    ['value' => 12, 'name' => '7.0 Elite'],
                ],
            ]
        );
        $this->TENNIS = $tennis->id;

        // Badminton (id=2) — 8 levels
        $badminton = Sport::updateOrCreate(
            ['slug' => 'badminton'],
            [
                'name'        => 'Badminton',
                'icon'        => '🏸',
                'description' => 'Fast-paced racket sport using shuttlecocks. Singles or doubles.',
                'skill_levels' => [
                    ['value' => 1, 'name' => 'Beginner'],
                    ['value' => 2, 'name' => 'Lower Intermediate'],
                    ['value' => 3, 'name' => 'Intermediate'],
                    ['value' => 4, 'name' => 'Upper Intermediate'],
                    ['value' => 5, 'name' => 'Advanced'],
                    ['value' => 6, 'name' => 'Competitive'],
                    ['value' => 7, 'name' => 'Tournament'],
                    ['value' => 8, 'name' => 'Elite'],
                ],
            ]
        );
        $this->BADMINTON = $badminton->id;

        // Padel (id=3) — 7 levels
        $padel = Sport::updateOrCreate(
            ['slug' => 'padel'],
            [
                'name'        => 'Padel',
                'icon'        => '🟡',
                'description' => 'Hybrid racket sport played with walls. Similar to tennis but enclosed.',
                'skill_levels' => [
                    ['value' => 1, 'name' => 'Beginner'],
                    ['value' => 2, 'name' => 'Casual'],
                    ['value' => 3, 'name' => 'Improving'],
                    ['value' => 4, 'name' => 'Intermediate'],
                    ['value' => 5, 'name' => 'Advanced'],
                    ['value' => 6, 'name' => 'Competitive'],
                    ['value' => 7, 'name' => 'Elite'],
                ],
            ]
        );
        $this->PADEL = $padel->id;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 2 — Venues (clean & realistic)
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedVenues(): void
    {
        $venues = [
            // ── Tennis ───────────────────────────────────────────────────
            [
                'name'           => 'Sudirman Tennis Academy',
                'sport_id'        => $this->TENNIS,
                'address'         => 'Jl. Jend. Sudirman Kav. 52-53',
                'area'            => 'Jakarta Selatan',
                'latitude'        => -6.2277,
                'longitude'       => 106.8073,
                'open_hours'      => '06:00 - 22:00',
                'description'     => 'Premium tennis academy with 6 hard courts and professional coaching.',
                'price_estimate'  => 150000,
                'contact'         => '+62 21 5140 1234',
                'is_active'       => true,
            ],
            [
                'name'           => 'GBK Tennis Court',
                'sport_id'        => $this->TENNIS,
                'address'         => 'Jl. Pintu Utama Gelora Bung Karno',
                'area'            => 'Jakarta Pusat',
                'latitude'        => -6.2185,
                'longitude'       => 106.8023,
                'open_hours'      => '06:00 - 21:00',
                'description'     => 'Public tennis courts within Gelora Bung Karno sports complex.',
                'price_estimate'  => 75000,
                'contact'         => '+62 21 2357 8900',
                'is_active'       => true,
            ],
            [
                'name'           => 'Pademangan Tennis Club',
                'sport_id'        => $this->TENNIS,
                'address'         => 'Jl. Taman Pademangan No. 17',
                'area'            => 'Jakarta Utara',
                'latitude'        => -6.1389,
                'longitude'       => 106.7923,
                'open_hours'      => '07:00 - 20:00',
                'description'     => 'Community tennis club with clay and hard courts.',
                'price_estimate'  => 100000,
                'contact'         => '+62 21 4290 1234',
                'is_active'       => true,
            ],
            // ── Badminton ───────────────────────────────────────────────
            [
                'name'           => 'Slipi Badminton Center',
                'sport_id'        => $this->BADMINTON,
                'address'         => 'Jl. Letjen S. Parman No. 91',
                'area'            => 'Jakarta Barat',
                'latitude'        => -6.1773,
                'longitude'       => 106.7891,
                'open_hours'      => '07:00 - 23:00',
                'description'     => '20 indoor courts, one of the biggest badminton centers in Jakarta.',
                'price_estimate'  => 45000,
                'contact'         => '+62 21 5481 2345',
                'is_active'       => true,
            ],
            [
                'name'           => 'Grand ITC Badminton Hall',
                'sport_id'        => $this->BADMINTON,
                'address'         => 'Jl. Margonda Raya No. 56',
                'area'            => 'Depok',
                'latitude'        => -6.3650,
                'longitude'       => 106.8324,
                'open_hours'      => '08:00 - 22:00',
                'description'     => 'Located inside ITC Depok mall with 12 air-conditioned courts.',
                'price_estimate'  => 50000,
                'contact'         => '+62 21 7884 1234',
                'is_active'       => true,
            ],
            [
                'name'           => 'Cilandak Sports Hall',
                'sport_id'        => $this->BADMINTON,
                'address'         => 'Jl. TB Simatupang No. 88',
                'area'            => 'Jakarta Selatan',
                'latitude'        => -6.2891,
                'longitude'       => 106.8134,
                'open_hours'      => '07:00 - 21:00',
                'description'     => 'Modern badminton facility with spring floor and LED lighting.',
                'price_estimate'  => 60000,
                'contact'         => '+62 21 750 6789',
                'is_active'       => true,
            ],
            // ── Padel ──────────────────────────────────────────────────
            [
                'name'           => 'Racket Padel Club',
                'sport_id'        => $this->PADEL,
                'address'         => 'Jl. PIK Avenue No. 12',
                'area'            => 'Jakarta Utara',
                'latitude'        => -6.1123,
                'longitude'       => 106.7423,
                'open_hours'      => '08:00 - 22:00',
                'description'     => 'First dedicated padel club in PIK area with 4 glass courts.',
                'price_estimate'  => 250000,
                'contact'         => '+62 21 2109 1234',
                'is_active'       => true,
            ],
            [
                'name'           => 'Summarecon Padel Arena',
                'sport_id'        => $this->PADEL,
                'address'         => 'Jl. Bulevar Summarecon Blok A',
                'area'            => 'Bekasi',
                'latitude'        => -6.1756,
                'longitude'       => 106.9956,
                'open_hours'      => '07:00 - 21:00',
                'description'     => 'Spacious padel arena with 6 courts and clubhouse.',
                'price_estimate'  => 200000,
                'contact'         => '+62 21 2906 7890',
                'is_active'       => true,
            ],
        ];

        foreach ($venues as $v) {
            Venue::updateOrCreate(['name' => $v['name']], $v);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 3 — Core Users
    // Design goals:
    //   • Rizky (id=1) is demo user: Tennis 6, Badminton 4 (skill_number 6/4)
    //   • Create diverse skill tiers per sport for meaningful score variance
    //   • Cover all 5 activity tiers (≤3d / ≤7d / ≤30d / ≤90d / >90d)
    //   • Some users have time preferences, others don't (for time score variance)
    //   • Mutual connection clusters (friend groups that trigger mutual score boost)
    //   • Cover reveal overlay scenarios (score ≥ 0.75, same sport, strong factors)
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedCoreUsers(array $protectedIds): array
    {
        $sportsById = Sport::all()->keyBy('id');

        /*
         * User definitions:
         *   id:       sequential (1, 2, 3...)
         *   name:     realistic Indonesian name
         *   email:    consistent with name
         *   gender:   male / female
         *   age:      18-25 / 26-35 / 36-50
         *   zone:     coordinate cluster key
         *   bio:      short persona description
         *   style:    casual / competitive
         *   sports:   ['tennis' => rawValue, ...]  (primary = first listed)
         *   time_pref:[start, end, days] or null
         *   last_active_days: 0-40 (controls activity score 1.0 → 0.5)
         *   events_joined: 0-30
         *   keep:     true = protected (don't touch if exists)
         *
         * Skill distribution per sport:
         *   Tennis (max 12 → normalize to 1-10):
         *     3=Beginner, 4=Lower-beginner, 5=Intermediate, 6=Upper-int, 7=Advanced, 8=High, 9=Very High, 10=Elite, 11=Pro
         *   Badminton (max 8 → 1-10):
         *     2=Lower-int, 3=Int, 4=Upper-int, 5=Advanced, 6=Comp, 7=Tournament, 8=Elite
         *   Padel (max 7 → 1-10):
         *     2=Casual, 3=Improving, 4=Int, 5=Advanced, 6=Comp, 7=Elite
         */
        $users = [
            // ── ID 1: DEMO USER — Rizky (protected) ──────────────────────
            // Primary Tennis 6 (norm 5), Secondary Badminton 4 (norm 5)
            // Active 1 day ago. No time prefs set. Baseline for all match scores.
            1 => [
                'name' => 'Rizky Pratama', 'email' => 'rizky@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Competitive tennis player. Looking for regular hitting partners.',
                'style' => 'competitive',
                'sports' => ['tennis' => 6, 'badminton' => 4],
                'time_pref' => null, // no prefs — scores 0.5 on time component
                'last_active_days' => 1,
                'events_joined' => 12,
                'keep' => true,
            ],

            // ── ID 2: Dewi — Badminton specialist ──────────────────────────
            // Primary Badminton 5 (norm 6), Secondary Tennis 3 (norm 4)
            // Active 1 day ago, Wed+Sat badminton lover
            2 => [
                'name' => 'Dewi Anggraini', 'email' => 'dewi@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Badminton enthusiast. Play twice a week at Slipi.',
                'style' => 'competitive',
                'sports' => ['badminton' => 5, 'tennis' => 3],
                'time_pref' => ['start' => '18:00', 'end' => '21:00', 'days' => [3, 6]], // Wed + Sat evenings
                'last_active_days' => 1,
                'events_joined' => 18,
                'keep' => false,
            ],

            // ── ID 3: Ahmad — Elite Tennis ────────────────────────────────
            // Primary Tennis 8 (norm 7). Active today. Primary match for Rizky (skill diff = 2 → 0.80)
            3 => [
                'name' => 'Ahmad Fauzi', 'email' => 'ahmad@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_pusat',
                'bio' => 'Former inter-university tennis champion. Level 4.5 NTRP.',
                'style' => 'competitive',
                'sports' => ['tennis' => 8],
                'time_pref' => ['start' => '07:00', 'end' => '10:00', 'days' => [1, 2, 3, 4, 5]], // Weekday mornings
                'last_active_days' => 0,
                'events_joined' => 25,
                'keep' => false,
            ],

            // ── ID 4: Sinta — Casual Badminton / Padel ───────────────────
            // Primary Badminton 4 (norm 5), Secondary Padel 3 (norm 4)
            // Active 4 days ago. Casual player in Jakarta Barat
            4 => [
                'name' => 'Sinta Maharani', 'email' => 'sinta@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'jkt_barat',
                'bio' => 'Casual badminton player. New to padel, looking for friendly games.',
                'style' => 'casual',
                'sports' => ['badminton' => 4, 'padel' => 3],
                'time_pref' => ['start' => '09:00', 'end' => '12:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 4,
                'events_joined' => 8,
                'keep' => false,
            ],

            // ── ID 5: Budi — Weekend tennis / badminton ──────────────────
            // Primary Tennis 6 (norm 5, same as Rizky), Secondary Badminton 5 (norm 6)
            // Active 3 days ago. PERFECT skill match with Rizky (diff=0 → 1.0)
            5 => [
                'name' => 'Budi Santoso', 'email' => 'budi@example.com',
                'gender' => 'male', 'age' => '36-50', 'zone' => 'jkt_utara',
                'bio' => 'Weekend tennis regular at GBK. Also enjoy doubles badminton.',
                'style' => 'competitive',
                'sports' => ['tennis' => 6, 'badminton' => 5],
                'time_pref' => ['start' => '08:00', 'end' => '11:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 3,
                'events_joined' => 20,
                'keep' => false,
            ],

            // ── ID 6: Maya — Beginner Badminton, Tangerang ────────────────
            // Primary Badminton 3 (norm 4). 6 days ago. Beginner.
            6 => [
                'name' => 'Maya Putri', 'email' => 'maya@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'tangerang',
                'bio' => 'Just started playing badminton. Looking for patient partners!',
                'style' => 'casual',
                'sports' => ['badminton' => 3],
                'time_pref' => ['start' => '14:00', 'end' => '17:00', 'days' => [0, 6]], // Weekend afternoons
                'last_active_days' => 6,
                'events_joined' => 4,
                'keep' => false,
            ],

            // ── ID 7: Fajar — Elite Tennis, nearby ─────────────────────────
            // Primary Tennis 9 (norm 8). Active today. Nearby to Rizky. High skill.
            // Score vs Rizky: sport=1.0, skill diff=3 (6→9 norm diff=3 → 0.60)
            // Expected ~0.75+ to trigger reveal overlay
            7 => [
                'name' => 'Fajar Nugroho', 'email' => 'fajar@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Level 5.0 NTRP. Looking for challenging matches.',
                'style' => 'competitive',
                'sports' => ['tennis' => 9],
                'time_pref' => ['start' => '18:00', 'end' => '21:00', 'days' => [2, 4, 6]], // Mon/Wed/Sat evenings
                'last_active_days' => 0,
                'events_joined' => 30,
                'keep' => false,
            ],

            // ── ID 8: Rina — Active Badminton, Depok ─────────────────────
            // Primary Badminton 5 (norm 6, same as Dewi). 5 days ago.
            // Time pref overlap with Dewi (both Sat evening)
            8 => [
                'name' => 'Rina Wulandari', 'email' => 'rina@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => 'depok',
                'bio' => 'Regular badminton sessions at ITC. Enjoy doubles.',
                'style' => 'casual',
                'sports' => ['badminton' => 5],
                'time_pref' => ['start' => '19:00', 'end' => '22:00', 'days' => [3, 6]], // Wed + Sat evenings
                'last_active_days' => 5,
                'events_joined' => 14,
                'keep' => false,
            ],

            // ── ID 9: Hendra — Tennis + Padel, Bekasi ─────────────────────
            // Primary Tennis 7 (norm 6, same skill as Rizky!). Secondary Padel 4 (norm 5)
            // Active 2 days ago. Good distance. PERFECT tennis match for Rizky.
            9 => [
                'name' => 'Hendra Wijaya', 'email' => 'hendra@example.com',
                'gender' => 'male', 'age' => '36-50', 'zone' => 'bekasi',
                'bio' => 'Level 4.0 NTRP. Weekend tennis and padel sessions in Bekasi.',
                'style' => 'competitive',
                'sports' => ['tennis' => 7, 'padel' => 4],
                'time_pref' => ['start' => '08:00', 'end' => '12:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 2,
                'events_joined' => 22,
                'keep' => false,
            ],

            // ── ID 10: Anisa — Competitive Badminton ───────────────────────
            // Primary Badminton 6 (norm 7). 4 days ago. Strong player.
            // Score vs Rizky (badminton filter): sport=1.0, skill diff=2 (4→7 norm diff=3 → 0.60)
            10 => [
                'name' => 'Anisa Nurfadilah', 'email' => 'anisa@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => 'jkt_timur',
                'bio' => 'Tournament-level badminton. Available weekday evenings.',
                'style' => 'competitive',
                'sports' => ['badminton' => 6],
                'time_pref' => ['start' => '19:00', 'end' => '22:00', 'days' => [1, 3, 5]], // Mon/Wed/Fri evenings
                'last_active_days' => 4,
                'events_joined' => 28,
                'keep' => false,
            ],

            // ── ID 11: Galih — Perfect Tennis match for Rizky ──────────────
            // Primary Tennis 6 (norm 5, SAME as Rizky!). Active 1 day ago.
            // PERFECT match: sport=1.0, skill=1.0, time pref overlap (both weekend mornings)
            // Distance ~2km. Score should be ~0.83+. Top challenger candidate.
            11 => [
                'name' => 'Galih Ramadhan', 'email' => 'galih@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Weekend tennis at Sudirman Academy. Level 3.5 NTRP, looking for similar level.',
                'style' => 'competitive',
                'sports' => ['tennis' => 6],
                'time_pref' => ['start' => '07:00', 'end' => '10:00', 'days' => [0, 6]], // Weekend mornings (overlaps Budi)
                'last_active_days' => 1,
                'events_joined' => 16,
                'keep' => false,
            ],

            // ── ID 12: Putri — Lower Intermediate Badminton ────────────────
            // Primary Badminton 4 (norm 5, same as Rizky's badminton!). 8 days ago.
            // Casual. Similar skill level as Rizky for badminton.
            12 => [
                'name' => 'Putri Handayani', 'email' => 'putri@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'jkt_barat',
                'bio' => 'Intermediate badminton player. Looking for consistent practice partners.',
                'style' => 'casual',
                'sports' => ['badminton' => 4],
                'time_pref' => ['start' => '15:00', 'end' => '18:00', 'days' => [0, 6]], // Weekend afternoons
                'last_active_days' => 8,
                'events_joined' => 6,
                'keep' => false,
            ],

            // ── ID 13: Dimas — Padel + Tennis, competitive ─────────────────
            // Primary Padel 5 (norm 6), Secondary Tennis 6 (norm 5, same as Rizky!)
            // Active 3 days ago. Wide skill range. Padel enthusiast.
            13 => [
                'name' => 'Dimas Aryo', 'email' => 'dimas@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_pusat',
                'bio' => 'Padel first, tennis second. Love the tactical game at the net.',
                'style' => 'competitive',
                'sports' => ['padel' => 5, 'tennis' => 6],
                'time_pref' => ['start' => '17:00', 'end' => '20:00', 'days' => [2, 4, 6]], // Tue/Thu/Sat evenings
                'last_active_days' => 3,
                'events_joined' => 19,
                'keep' => false,
            ],

            // ── ID 14: Lina — Inactive Beginner Badminton ─────────────────
            // Primary Badminton 2 (norm 3). 25 days ago. Inactive tier.
            // Low activity score (0.5). Used to show activity component variance.
            14 => [
                'name' => 'Lina Susilowati', 'email' => 'lina@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'bogor',
                'bio' => 'Beginner badminton. Just getting back into sport.',
                'style' => 'casual',
                'sports' => ['badminton' => 2],
                'time_pref' => null,
                'last_active_days' => 25,
                'events_joined' => 2,
                'keep' => false,
            ],

            // ── ID 15: Rico — Elite Tennis, nearby ─────────────────────────
            // Primary Tennis 9 (norm 8). Active today. Very close to Rizky.
            // Skill diff vs Rizky: norm 8 vs 6 = diff 2 → skill 0.80
            // Reveal overlay candidate (score ~0.78)
            15 => [
                'name' => 'Rico Hermawan', 'email' => 'rico@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Level 5.0 NTRP. Hit me up for serious practice sessions.',
                'style' => 'competitive',
                'sports' => ['tennis' => 9],
                'time_pref' => ['start' => '18:00', 'end' => '21:00', 'days' => [3, 6]], // Wed + Sat evenings
                'last_active_days' => 0,
                'events_joined' => 30,
                'keep' => false,
            ],

            // ── ID 16: Vina — Tennis + Badminton, Bandung (far) ──────────────
            // Primary Badminton 5 (norm 6), Secondary Tennis 4 (norm 5)
            // Active 6 days ago. Far from Rizky (~100km). Shows distance decay.
            16 => [
                'name' => 'Vina Meilani', 'email' => 'vina@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => null, // Bandung — special coords below
                'bio' => 'Love both tennis and badminton. Mostly play doubles.',
                'style' => 'casual',
                'sports' => ['badminton' => 5, 'tennis' => 4],
                'time_pref' => ['start' => '09:00', 'end' => '12:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 6,
                'events_joined' => 11,
                'keep' => false,
            ],

            // ── ID 17: Surya — Tennis, nearby, same level as Rizky ─────────
            // Primary Tennis 7 (norm 6, 1 above Rizky's 6). 2 days ago.
            // skill diff = 1 (norm 6 vs 5) → 0.95. Strong match.
            17 => [
                'name' => 'Surya Darma', 'email' => 'surya@example.com',
                'gender' => 'male', 'age' => '36-50', 'zone' => 'jkt_utara',
                'bio' => 'Level 4.0 NTRP. Prefer early morning sessions at Pademangan.',
                'style' => 'competitive',
                'sports' => ['tennis' => 7],
                'time_pref' => ['start' => '06:00', 'end' => '09:00', 'days' => [1, 2, 3, 4, 5]], // Weekday mornings
                'last_active_days' => 2,
                'events_joined' => 24,
                'keep' => false,
            ],

            // ── ID 18: Diah — Inactive Beginner Badminton ───────────────────
            // Primary Badminton 3 (norm 4). 15 days ago. Activity score 0.5.
            18 => [
                'name' => 'Diah Permatasari', 'email' => 'diah@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'jkt_timur',
                'bio' => 'Just getting back into badminton after a break. Casual player.',
                'style' => 'casual',
                'sports' => ['badminton' => 3],
                'time_pref' => null,
                'last_active_days' => 15,
                'events_joined' => 3,
                'keep' => false,
            ],

            // ── ID 19: Arif — Strong Tennis + Padel, mutual with Rizky ─────
            // Primary Tennis 8 (norm 7). Active today. Near Rizky. ACCEPTED connection.
            // Mutual connection → triggers reveal overlay criteria
            // skill diff vs Rizky: norm 7 vs 5 = diff 2 → 0.80
            19 => [
                'name' => 'Arif Rahman', 'email' => 'arif@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Level 4.5 NTRP. Also enjoy padel on weekends.',
                'style' => 'competitive',
                'sports' => ['tennis' => 8, 'padel' => 5],
                'time_pref' => ['start' => '17:00', 'end' => '20:00', 'days' => [3, 6]], // Wed + Sat evenings
                'last_active_days' => 0,
                'events_joined' => 27,
                'keep' => false,
            ],

            // ── ID 20: Nadia — Out-of-area Badminton (Surabaya) ──────────────
            // Primary Badminton 4 (norm 5). 20 days ago. Very far. Activity 0.5.
            // Shows distance score decay to near 0. For badminton filter results.
            20 => [
                'name' => 'Nadia Zahra', 'email' => 'nadia@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => null, // Surabaya special
                'bio' => 'Badminton player from Surabaya. Occasionally visit Jakarta.',
                'style' => 'casual',
                'sports' => ['badminton' => 4],
                'time_pref' => null,
                'last_active_days' => 20,
                'events_joined' => 5,
                'keep' => false,
            ],

            // ── ID 21: Eko — Padel + Tennis, Semarang (far) ────────────────
            // Primary Padel 4 (norm 5), Secondary Tennis 5 (norm 5, same as Rizky!)
            // 4 days ago. Far from Jakarta. Padel filter match.
            21 => [
                'name' => 'Eko Prasetyo', 'email' => 'eko@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => null, // Semarang special
                'bio' => 'Padel enthusiast from Semarang. Play when visiting Jakarta.',
                'style' => 'competitive',
                'sports' => ['padel' => 4, 'tennis' => 5],
                'time_pref' => null,
                'last_active_days' => 4,
                'events_joined' => 9,
                'keep' => false,
            ],

            // ── ID 22: Tika — Badminton, Yogyakarta (far) ───────────────────
            // Primary Badminton 5 (norm 6, same as Dewi's!). 12 days ago. Activity 0.5.
            22 => [
                'name' => 'Tika Ardianti', 'email' => 'tika@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => null, // Yogya special
                'bio' => 'Weekend badminton. Yogyakarta area.',
                'style' => 'casual',
                'sports' => ['badminton' => 5],
                'time_pref' => null,
                'last_active_days' => 12,
                'events_joined' => 7,
                'keep' => false,
            ],

            // ── ID 23: Bagus — Tennis, same level, ~9km away ───────────────
            // Primary Tennis 7 (norm 6, same as Surya/Rizky+1). 3 days ago.
            // Perfect skill match: diff 1 → 0.95. Shows distance penalty.
            23 => [
                'name' => 'Bagus Pratama', 'email' => 'bagus@example.com',
                'gender' => 'male', 'age' => '36-50', 'zone' => 'jkt_pusat',
                'bio' => 'Level 4.0 NTRP. Regular at GBK. Prefer doubles.',
                'style' => 'competitive',
                'sports' => ['tennis' => 7],
                'time_pref' => ['start' => '08:00', 'end' => '11:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 3,
                'events_joined' => 21,
                'keep' => false,
            ],

            // ── ID 24: Sari — Inactive Beginner Badminton (Malang) ──────────
            // Primary Badminton 3 (norm 4). 18 days ago. Activity 0.5. Very far.
            24 => [
                'name' => 'Sari Dewi', 'email' => 'sari@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => null, // Malang special
                'bio' => 'Beginner badminton player from Malang.',
                'style' => 'casual',
                'sports' => ['badminton' => 3],
                'time_pref' => null,
                'last_active_days' => 18,
                'events_joined' => 1,
                'keep' => false,
            ],

            // ── ID 25: Yusuf — Elite Padel + Tennis ─────────────────────────
            // Primary Tennis 9 (norm 8, same as Fajar!), Secondary Padel 6 (norm 8)
            // Active today. Very close to Rizky. Padel secondary → sport=0.75 when filter=padel
            // Shows secondary sport partial score.
            25 => [
                'name' => 'Yusuf Ibrahim', 'email' => 'yusuf@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_selatan',
                'bio' => 'Level 5.0 NTRP and competitive padel player. Always looking for matches.',
                'style' => 'competitive',
                'sports' => ['tennis' => 9, 'padel' => 6],
                'time_pref' => ['start' => '18:00', 'end' => '21:00', 'days' => [1, 3, 5]], // Mon/Wed/Fri evenings
                'last_active_days' => 0,
                'events_joined' => 30,
                'keep' => false,
            ],

            // ── ID 26: Rendy — Badminton + Padel, competitive ──────────────
            // Primary Badminton 6 (norm 7), Secondary Padel 3 (norm 4)
            // 5 days ago. Good badminton match for Rizky (skill diff ~3 → 0.60)
            26 => [
                'name' => 'Rendy Firmansyah', 'email' => 'rendy@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'jkt_barat',
                'bio' => 'Competitive badminton player. Also trying padel.',
                'style' => 'competitive',
                'sports' => ['badminton' => 6, 'padel' => 3],
                'time_pref' => ['start' => '19:00', 'end' => '22:00', 'days' => [3, 6]], // Wed + Sat evenings
                'last_active_days' => 5,
                'events_joined' => 17,
                'keep' => false,
            ],

            // ── ID 27: Fitri — Casual Badminton ─────────────────────────────
            // Primary Badminton 4 (norm 5, same as Rizky's!). 9 days ago.
            // Good badminton match for Rizky. Nearby.
            27 => [
                'name' => 'Fitri Handayani', 'email' => 'fitri@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'jkt_utara',
                'bio' => 'Casual badminton. Looking for friendly games.',
                'style' => 'casual',
                'sports' => ['badminton' => 4],
                'time_pref' => ['start' => '14:00', 'end' => '17:00', 'days' => [0, 6]], // Weekend afternoons
                'last_active_days' => 9,
                'events_joined' => 5,
                'keep' => false,
            ],

            // ── ID 28: Wawan — Strong Tennis, nearby ─────────────────────────
            // Primary Tennis 8 (norm 7). Active today. Very close to Rizky.
            // skill diff vs Rizky: norm 7 vs 5 = diff 2 → 0.80. Strong match.
            28 => [
                'name' => 'Wawan Susanto', 'email' => 'wawan@example.com',
                'gender' => 'male', 'age' => '36-50', 'zone' => 'jkt_selatan',
                'bio' => 'Level 4.5 NTRP. Weekend tennis regular.',
                'style' => 'competitive',
                'sports' => ['tennis' => 8],
                'time_pref' => ['start' => '07:00', 'end' => '10:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 0,
                'events_joined' => 26,
                'keep' => false,
            ],

            // ── ID 29: Hana — Active Badminton, same level ──────────────────
            // Primary Badminton 5 (norm 6, same as Dewi's!). 6 days ago.
            // Perfect skill match with Rizky (badminton: norm 5 vs 5 = diff 0 → 1.0)
            29 => [
                'name' => 'Hana Sabrina', 'email' => 'hana@example.com',
                'gender' => 'female', 'age' => '26-35', 'zone' => 'jkt_timur',
                'bio' => 'Badminton player at Cilandak. Looking for consistent partners.',
                'style' => 'competitive',
                'sports' => ['badminton' => 5],
                'time_pref' => ['start' => '18:00', 'end' => '21:00', 'days' => [1, 3]], // Mon + Wed evenings
                'last_active_days' => 6,
                'events_joined' => 13,
                'keep' => false,
            ],

            // ── ID 30: Irfan — Padel + Tennis elite, ~15km ─────────────────
            // Primary Padel 6 (norm 8), Secondary Tennis 7 (norm 6)
            // Active today. Shows distance decay (15km → ~0.22 dist score)
            // Padel filter: sport=1.0, skill diff vs Rizky padel (none) → 0.5 default
            30 => [
                'name' => 'Irfan Hakim', 'email' => 'irfan@example.com',
                'gender' => 'male', 'age' => '26-35', 'zone' => 'depok',
                'bio' => 'Competitive padel player. Also enjoy tennis for cross-training.',
                'style' => 'competitive',
                'sports' => ['padel' => 6, 'tennis' => 7],
                'time_pref' => ['start' => '17:00', 'end' => '20:00', 'days' => [2, 4, 6]], // Tue/Thu/Sat evenings
                'last_active_days' => 0,
                'events_joined' => 22,
                'keep' => false,
            ],

            // ── ID 31: Annisa — Tennis + Badminton (mixed) ───────────────────
            // Primary Badminton 6 (norm 7), Secondary Tennis 4 (norm 5)
            // Active 7 days ago. Good badminton match for Rizky (skill diff=2 → 0.80)
            // Time pref: weekday evenings — partial overlap with Rizky (no prefs set)
            31 => [
                'name' => 'Annisa Farida', 'email' => 'annisa@example.com',
                'gender' => 'female', 'age' => '18-25', 'zone' => 'jkt_selatan',
                'bio' => 'Competitive badminton with tennis for fun. Student.',
                'style' => 'casual',
                'sports' => ['badminton' => 6, 'tennis' => 4],
                'time_pref' => ['start' => '17:00', 'end' => '20:00', 'days' => [1, 3, 5]], // Mon/Wed/Fri evenings
                'last_active_days' => 7,
                'events_joined' => 10,
                'keep' => false,
            ],

            // ── ID 32: Denny — Padel specialist ────────────────────────────
            // Primary Padel 5 (norm 6). 11 days ago. Activity score 0.5.
            // Shows activity component variance.
            32 => [
                'name' => 'Denny Kurniawan', 'email' => 'denny@example.com',
                'gender' => 'male', 'age' => '36-45', 'zone' => 'bekasi',
                'bio' => 'Padel player at Summarecon. Looking for regular games.',
                'style' => 'competitive',
                'sports' => ['padel' => 5],
                'time_pref' => ['start' => '09:00', 'end' => '12:00', 'days' => [0, 6]], // Weekend mornings
                'last_active_days' => 11,
                'events_joined' => 15,
                'keep' => false,
            ],
        ];

        // Special far-zone coordinates (outside normal clusters)
        $specialCoords = [
            16 => ['lat' => -6.9175, 'lng' => 107.6191, 'area' => 'Bandung'],
            20 => ['lat' => -7.2575, 'lng' => 112.7521, 'area' => 'Surabaya'],
            21 => ['lat' => -6.9667, 'lng' => 110.4205, 'area' => 'Semarang'],
            22 => ['lat' => -7.7956, 'lng' => 110.3695, 'area' => 'Yogyakarta'],
            24 => ['lat' => -7.9778, 'lng' => 112.6311, 'area' => 'Malang'],
        ];

        $createdUsers = [];
        $sportMap = ['tennis' => $this->TENNIS, 'badminton' => $this->BADMINTON, 'padel' => $this->PADEL];

        foreach ($users as $id => $data) {
            $zone = $data['zone'] ?? null;
            $coords = $zone
                ? $this->coords[$zone]
                : ($specialCoords[$id] ?? ['lat' => -6.2, 'lng' => 106.8]);

            $spread = $coords['spread'] ?? 0.02;
            $lat = $coords['lat'] + (mt_rand(-100, 100) / 10000) * $spread * 100;
            $lng = $coords['lng'] + (mt_rand(-100, 100) / 10000) * $spread * 100;

            // Determine last_active_at
            $lastActive = Carbon::now()->subDays($data['last_active_days'])->setTimeFromTimeString(
                sprintf('%02d:00', mt_rand(6, 22))
            );

            // Build user data
            $userData = [
                'name'                => $data['name'],
                'email'               => $data['email'],
                'password'            => Hash::make('password'),
                'email_verified_at'   => now(),
                'gender'              => $data['gender'],
                'age_range'           => $data['age'],
                'home_address'        => $this->faker->streetAddress() . ', ' . ($coords['area'] ?? $zone ?? 'Jakarta'),
                'latitude'            => round($lat, 6),
                'longitude'           => round($lng, 6),
                'play_style'          => $data['style'],
                'bio'                 => $data['bio'],
                'last_active_at'      => $lastActive,
                'total_events_joined' => $data['events_joined'],
            ];

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $userData
            );
            $createdUsers[$id] = $user;

            // ── UserSports ────────────────────────────────────────────────
            // Delete and recreate to handle re-running
            UserSport::where('user_id', $user->id)->delete();

            $sportIndex = 0;
            foreach ($data['sports'] as $sportSlug => $rawValue) {
                $sportId = $sportMap[$sportSlug];
                $sport = $sportsById->get($sportId);
                if (!$sport) continue;

                $normalized = $sport->normalizeSkill($rawValue);
                $label = $sport->getSkillLabel($rawValue);

                $usData = [
                    'user_id'     => $user->id,
                    'sport_id'    => $sportId,
                    'skill_value' => $label,
                    'skill_number'=> $normalized,
                    'play_style'  => $data['style'],
                ];

                // Add time preferences to PRIMARY sport only (first listed)
                if ($sportIndex === 0 && $data['time_pref']) {
                    $usData['preferred_start_time'] = $data['time_pref']['start'];
                    $usData['preferred_end_time']   = $data['time_pref']['end'];
                    $usData['preferred_days']       = json_encode($data['time_pref']['days']);
                }

                UserSport::create($usData);
                $sportIndex++;
            }
        }

        // ── ID 33: Erlangga Rafi (protected) ──────────────────────────────
        // Update coords to be near Jakarta if null
        $erlangga = User::where('email', 'erlanggarafi38@gmail.com')->first();
        if ($erlangga) {
            $erlangga->updateQuietly([
                'name'           => 'Erlangga Rafi',
                'password'       => Hash::make('password'),
                'latitude'       => -6.2484,
                'longitude'      => 106.6165,
                'last_active_at' => Carbon::now()->subDays(0)->setTimeFromTimeString('08:00'),
                'play_style'    => 'competitive',
                'gender'        => 'male',
                'age_range'     => '26-35',
                'bio'           => 'Tennis coach and enthusiast. Level 3.5 NTRP.',
            ]);
            // Ensure Tennis entry exists
            $tennisSport = $sportsById->get($this->TENNIS);
            if ($tennisSport) {
                UserSport::updateOrCreate(
                    ['user_id' => $erlangga->id, 'sport_id' => $this->TENNIS],
                    [
                        'skill_value'  => $tennisSport->getSkillLabel(4),
                        'skill_number' => $tennisSport->normalizeSkill(4),
                        'play_style'   => 'competitive',
                    ]
                );
            }
        }

        // ── ID 34: Angga (protected) ─────────────────────────────────────────
        // Preserved as-is but ensure consistent password
        $angga = User::where('email', 'erlangga25846@gmail.com')->first();
        if ($angga) {
            $angga->updateQuietly(['password' => Hash::make('password')]);
        }

        // ── ID 35: Roger Federer (protected) ─────────────────────────────
        // Update to have a Tennis entry with time prefs
        $roger = User::where('email', 'rogerfederer@test.com')->first();
        if ($roger) {
            $roger->updateQuietly([
                'name'          => 'Roger Federer',
                'password'      => Hash::make('password'),
                'latitude'      => -6.2088,
                'longitude'     => 106.8456,
                'last_active_at' => Carbon::now()->subDays(0)->setTimeFromTimeString('09:00'),
                'play_style'    => 'competitive',
                'gender'        => 'male',
                'age_range'     => '36-50',
                'bio'           => 'Level 3.0 NTRP. Tennis enthusiast.',
            ]);
            $tennisSport = $sportsById->get($this->TENNIS);
            if ($tennisSport) {
                UserSport::updateOrCreate(
                    ['user_id' => $roger->id, 'sport_id' => $this->TENNIS],
                    [
                        'skill_value'  => $tennisSport->getSkillLabel(3),
                        'skill_number' => $tennisSport->normalizeSkill(3),
                        'play_style'   => 'competitive',
                        'preferred_start_time' => '08:00',
                        'preferred_end_time'   => '11:00',
                        'preferred_days'       => json_encode([0, 6]), // Weekend mornings
                    ]
                );
            }
        }

        return $createdUsers;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Connections — create meaningful clusters
    // Design goals:
    //   • Rizky (id=1) is center of one cluster
    //   • Cluster A: Rizky–Arif accepted, Rizky→Denny pending
    //   • Cluster B: friend groups (Arif–Galih–Wawan accepted, etc.)
    //   • Triggers mutual connection badge on matchmaking cards
    //   • Only seed accepted/pending — no declined/blocked for demo clarity
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedConnections(array $users): void
    {
        $connDefs = [
            // Cluster A: Rizky-centered
            ['requester' => 1,  'receiver' => 19, 'status' => 'accepted'],   // Rizky ↔ Arif (MUTUAL)
            ['requester' => 1,  'receiver' => 2,  'status' => 'accepted'],   // Rizky ↔ Dewi
            ['requester' => 1,  'receiver' => 32, 'status' => 'pending'],    // Rizky → Denny (pending)
            ['requester' => 19, 'receiver' => 11, 'status' => 'accepted'],   // Arif ↔ Galih (MUTUAL)
            ['requester' => 19, 'receiver' => 28, 'status' => 'accepted'],   // Arif ↔ Wawan (MUTUAL)
            ['requester' => 11, 'receiver' => 28, 'status' => 'accepted'],   // Galih ↔ Wawan (MUTUAL)
            ['requester' => 2,  'receiver' => 29, 'status' => 'accepted'],   // Dewi ↔ Hana (MUTUAL)
            ['requester' => 2,  'receiver' => 8,  'status' => 'accepted'],   // Dewi ↔ Rina (MUTUAL)
            ['requester' => 8,  'receiver' => 29, 'status' => 'accepted'],   // Rina ↔ Hana (MUTUAL)
            ['requester' => 3,  'receiver' => 17, 'status' => 'accepted'],   // Ahmad ↔ Surya (MUTUAL)
            ['requester' => 3,  'receiver' => 23, 'status' => 'accepted'],   // Ahmad ↔ Bagus (MUTUAL)
            ['requester' => 5,  'receiver' => 9,  'status' => 'accepted'],   // Budi ↔ Hendra (MUTUAL)
            ['requester' => 9,  'receiver' => 25, 'status' => 'accepted'],   // Hendra ↔ Yusuf (MUTUAL)
            ['requester' => 7,  'receiver' => 15, 'status' => 'accepted'],   // Fajar ↔ Rico (MUTUAL)
            ['requester' => 25, 'receiver' => 15, 'status' => 'accepted'],   // Yusuf ↔ Rico (MUTUAL)
            ['requester' => 13, 'receiver' => 30, 'status' => 'accepted'],   // Dimas ↔ Irfan (MUTUAL)
            ['requester' => 10, 'receiver' => 26, 'status' => 'accepted'],   // Anisa ↔ Rendy (MUTUAL)
            // Cross-cluster connections (shows mutual connections between groups)
            ['requester' => 1,  'receiver' => 11, 'status' => 'pending'],   // Rizky → Galih (pending)
            ['requester' => 7,  'receiver' => 1,  'status' => 'pending'],    // Fajar → Rizky (pending)
            // Far-zone users: fewer connections
            ['requester' => 16, 'receiver' => 22, 'status' => 'accepted'],   // Vina ↔ Tika (MUTUAL)
            ['requester' => 35, 'receiver' => 33, 'status' => 'accepted'],   // Roger ↔ Erlangga (MUTUAL — protected)
        ];

        foreach ($connDefs as $def) {
            // Ensure both users exist
            $r = $users[$def['requester']] ?? null;
            $rv = $users[$def['receiver']] ?? null;
            if (!$r || !$rv) continue;

            Connection::updateOrCreate(
                [
                    'requester_id' => $r->id,
                    'receiver_id' => $rv->id,
                ],
                ['status' => $def['status']]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Reviews — meaningful patterns
    // Design goals:
    //   • Every major player has 1-3 reviews
    //   • Rating range 3-5 (no 1-2 stars — demo is a quality platform)
    //   • From connection or event source
    //   • Creates "averageRating" on profiles
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedReviews(array $users): void
    {
        $reviewDefs = [
            // From connection
            ['reviewer' => 1,  'reviewed' => 19, 'rating' => 5, 'comment' => 'Great tennis partner! Consistent and competitive.', 'source' => 'connection'],
            ['reviewer' => 19, 'reviewed' => 1,  'rating' => 5, 'comment' => 'Excellent player, very reliable. Looking forward to more matches!', 'source' => 'connection'],
            ['reviewer' => 2,  'reviewed' => 1,  'rating' => 4, 'comment' => 'Fun badminton games. Good energy on court.', 'source' => 'connection'],
            ['reviewer' => 3,  'reviewed' => 17, 'rating' => 5, 'comment' => 'Surya is an amazing hitting partner. Very consistent.', 'source' => 'connection'],
            ['reviewer' => 17, 'reviewed' => 3,  'rating' => 4, 'comment' => 'Ahmad plays at a high level. Learned a lot from our session.', 'source' => 'connection'],
            ['reviewer' => 7,  'reviewed' => 15, 'rating' => 5, 'comment' => 'Rico is a beast on the court. Level 5.0 NTRP for real!', 'source' => 'connection'],
            ['reviewer' => 15, 'reviewed' => 7,  'rating' => 5, 'comment' => 'Fajar plays really clean tennis. Highly recommended.', 'source' => 'connection'],
            ['reviewer' => 9,  'reviewed' => 25, 'rating' => 5, 'comment' => 'Yusuf is great for padel. Tactical and powerful serves.', 'source' => 'connection'],
            ['reviewer' => 25, 'reviewed' => 9,  'rating' => 4, 'comment' => 'Hendra is a solid partner. Good court coverage.', 'source' => 'connection'],
            ['reviewer' => 11, 'reviewed' => 28, 'rating' => 5, 'comment' => 'Wawan plays really smart tennis. Weekend games are always fun.', 'source' => 'connection'],
            ['reviewer' => 28, 'reviewed' => 11, 'rating' => 5, 'comment' => 'Galih is consistent and competitive. Great match!', 'source' => 'connection'],
            ['reviewer' => 2,  'reviewed' => 29, 'rating' => 4, 'comment' => 'Hana is a solid badminton player. Good drops and smashes.', 'source' => 'connection'],
            ['reviewer' => 29, 'reviewed' => 2,  'rating' => 5, 'comment' => 'Dewi is very experienced. Enjoyed our doubles session.', 'source' => 'connection'],
            ['reviewer' => 10, 'reviewed' => 26, 'rating' => 4, 'comment' => 'Rendy plays at a high level. Challenging but fun!', 'source' => 'connection'],
            ['reviewer' => 26, 'reviewed' => 10, 'rating' => 5, 'comment' => 'Anisa is tournament-ready. Powerful smashes!', 'source' => 'connection'],
            // Event-based reviews
            ['reviewer' => 33, 'reviewed' => 35, 'rating' => 5, 'comment' => 'Roger is a great player and coach. Highly recommended!', 'source' => 'event'],
            ['reviewer' => 35, 'reviewed' => 33, 'rating' => 5, 'comment' => 'Erlangga is an excellent tennis coach. Very patient.', 'source' => 'event'],
            // Solo reviews from event participation
            ['reviewer' => 5,  'reviewed' => 11, 'rating' => 4, 'comment' => 'Galih plays clean and consistent tennis.', 'source' => 'event'],
            ['reviewer' => 8,  'reviewed' => 2,  'rating' => 5, 'comment' => 'Dewi organizes great badminton sessions!', 'source' => 'event'],
            ['reviewer' => 23, 'reviewed' => 17, 'rating' => 4, 'comment' => 'Surya plays at a high level. Learned a lot.', 'source' => 'event'],
            ['reviewer' => 13, 'reviewed' => 30, 'rating' => 4, 'comment' => 'Irfan is a competitive padel player. Great rallies!', 'source' => 'event'],
        ];

        foreach ($reviewDefs as $def) {
            $reviewer = $users[$def['reviewer']] ?? null;
            $reviewed = $users[$def['reviewed']] ?? null;
            if (!$reviewer || !$reviewed) continue;

            UserReview::updateOrCreate(
                [
                    'reviewer_id'      => $reviewer->id,
                    'reviewed_user_id'=> $reviewed->id,
                    'source_type'     => $def['source'],
                ],
                [
                    'rating'  => $def['rating'],
                    'comment' => $def['comment'],
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 4 — Events
    // Design goals:
    //   • 15 events across all 3 sports (tennis/badminton/padel)
    //   • Dates from 1-14 days ahead (always visible in browse)
    //   • Mix of singles/doubles, public/private
    //   • Approval-required events for host-requests demo
    //   • Full participant list (some at capacity, some open)
    //   • Events near Rizky (CBD Jakarta) for demo purposes
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedEvents(array $users): void
    {
        $eventTemplates = [
            // ── Tennis Events ────────────────────────────────────────────
            [
                'title'      => 'Weekend Tennis Meetup',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 1,
                'venue'      => 'Sudirman Tennis Academy',
                'lat'        => -6.2277,
                'lng'        => 106.8073,
                'area'       => 'Jakarta Selatan',
                'max'        => 4,
                'price'      => 150000,
                'visibility' => 'public',
                'match_type' => 'singles',
                'approval'   => false,
                'days_ahead' => 2,
                'start_hour' => 10,
                'desc'       => 'Weekend tennis at Sudirman Academy. All levels welcome but we play competitively!',
                'participants' => [1, 17, 11, 28], // Rizky + Surya + Galih + Wawan
            ],
            [
                'title'      => 'Monday Night Badminton',
                'sport_id'   => $this->BADMINTON,
                'host_id'    => 2,
                'venue'      => 'Slipi Badminton Center',
                'lat'        => -6.1773,
                'lng'        => 106.7891,
                'area'       => 'Jakarta Barat',
                'max'        => 8,
                'price'      => 45000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 5,
                'start_hour' => 19,
                'desc'       => 'Weekly Monday doubles badminton at Slipi. Looking for regular partners.',
                'participants' => [2, 4, 12, 27, 29], // Dewi + Sinta + Putri + Fitri + Hana
            ],
            [
                'title'      => 'Saturday Padel Tournament',
                'sport_id'   => $this->PADEL,
                'host_id'    => 3,
                'venue'      => 'Racket Padel Club',
                'lat'        => -6.1123,
                'lng'        => 106.7423,
                'area'       => 'Jakarta Utara',
                'max'        => 4,
                'price'      => 250000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 4,
                'start_hour' => 15,
                'desc'       => 'Friendly padel tournament. Teams will be paired by level.',
                'participants' => [3, 13, 30, 25], // Ahmad + Dimas + Irfan + Yusuf
            ],
            [
                'title'      => 'Morning Tennis Practice',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 5,
                'venue'      => 'GBK Tennis Court',
                'lat'        => -6.2185,
                'lng'        => 106.8023,
                'area'       => 'Jakarta Pusat',
                'max'        => 4,
                'price'      => 75000,
                'visibility' => 'public',
                'match_type' => 'singles',
                'approval'   => false,
                'days_ahead' => 3,
                'start_hour' => 8,
                'desc'       => 'Early morning practice at GBK. Great for warming up before the weekend.',
                'participants' => [5, 9, 11, 23], // Budi + Hendra + Galih + Bagus
            ],
            [
                'title'      => 'Weekday Badminton League',
                'sport_id'   => $this->BADMINTON,
                'host_id'    => 10,
                'venue'      => 'Grand ITC Badminton Hall',
                'lat'        => -6.3650,
                'lng'        => 106.8324,
                'area'       => 'Depok',
                'max'        => 8,
                'price'      => 50000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => true, // Approval required — demo host requests
                'days_ahead' => 6,
                'start_hour' => 18,
                'desc'       => 'Competitive weekday badminton league. Serious players only.',
                'participants' => [10, 26, 31], // Anisa + Rendy + Annisa
            ],
            [
                'title'      => 'Elite Tennis Sparring',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 7,
                'venue'      => 'Pademangan Tennis Club',
                'lat'        => -6.1389,
                'lng'        => 106.7923,
                'area'       => 'Jakarta Utara',
                'max'        => 4,
                'price'      => 100000,
                'visibility' => 'public',
                'match_type' => 'singles',
                'approval'   => false,
                'days_ahead' => 7,
                'start_hour' => 20,
                'desc'       => 'High-intensity tennis sparring for experienced players. Level 4.5+.',
                'participants' => [7, 15, 19, 28], // Fajar + Rico + Arif + Wawan
            ],
            [
                'title'      => 'Corporate Padel Challenge',
                'sport_id'   => $this->PADEL,
                'host_id'    => 13,
                'venue'      => 'Summarecon Padel Arena',
                'lat'        => -6.1756,
                'lng'        => 106.9956,
                'area'       => 'Bekasi',
                'max'        => 8,
                'price'      => 200000,
                'visibility' => 'private', // Private — not visible in browse
                'match_type' => 'doubles',
                'approval'   => true,
                'days_ahead' => 1,
                'start_hour' => 9,
                'desc'       => 'Corporate padel challenge. Teams from local companies.',
                'participants' => [13, 25, 32, 9], // Dimas + Yusuf + Denny + Hendra
            ],
            [
                'title'      => 'Evening Badminton Social',
                'sport_id'   => $this->BADMINTON,
                'host_id'    => 8,
                'venue'      => 'Cilandak Sports Hall',
                'lat'        => -6.2891,
                'lng'        => 106.8134,
                'area'       => 'Jakarta Selatan',
                'max'        => 6,
                'price'      => 60000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 8,
                'start_hour' => 19,
                'desc'       => 'Relaxed evening badminton social. All skill levels welcome.',
                'participants' => [8, 22, 29, 2], // Rina + Tika + Hana + Dewi
            ],
            // ── Additional tennis events ─────────────────────────────────
            [
                'title'      => 'Friday Night Tennis Doubles',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 19,
                'venue'      => 'Sudirman Tennis Academy',
                'lat'        => -6.2277,
                'lng'        => 106.8073,
                'area'       => 'Jakarta Selatan',
                'max'        => 4,
                'price'      => 150000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 10,
                'start_hour' => 19,
                'desc'       => 'Friday night doubles tennis at Sudirman. Looking for competitive partners.',
                'participants' => [19, 1, 3, 17], // Arif + Rizky + Ahmad + Surya
            ],
            [
                'title'      => 'Beginner Badminton Session',
                'sport_id'   => $this->BADMINTON,
                'host_id'    => 4,
                'venue'      => 'Slipi Badminton Center',
                'lat'        => -6.1773,
                'lng'        => 106.7891,
                'area'       => 'Jakarta Barat',
                'max'        => 8,
                'price'      => 35000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 12,
                'start_hour' => 14,
                'desc'       => 'Beginner-friendly badminton. All about having fun and learning!',
                'participants' => [4, 6, 12, 18, 24], // Sinta + Maya + Putri + Diah + Sari
            ],
            // ── More events for variety ─────────────────────────────────
            [
                'title'      => 'Saturday Morning Tennis',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 17,
                'venue'      => 'GBK Tennis Court',
                'lat'        => -6.2185,
                'lng'        => 106.8023,
                'area'       => 'Jakarta Pusat',
                'max'        => 4,
                'price'      => 75000,
                'visibility' => 'public',
                'match_type' => 'singles',
                'approval'   => false,
                'days_ahead' => 13,
                'start_hour' => 8,
                'desc'       => 'Saturday morning tennis at GBK. Level 3.5-4.5.',
                'participants' => [17, 23, 5, 9], // Surya + Bagus + Budi + Hendra
            ],
            [
                'title'      => 'Weekend Padel Social',
                'sport_id'   => $this->PADEL,
                'host_id'    => 25,
                'venue'      => 'Racket Padel Club',
                'lat'        => -6.1123,
                'lng'        => 106.7423,
                'area'       => 'Jakarta Utara',
                'max'        => 4,
                'price'      => 250000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => false,
                'days_ahead' => 14,
                'start_hour' => 10,
                'desc'       => 'Weekend padel social at Racket Padel Club. Beginners welcome!',
                'participants' => [25, 13, 32, 30], // Yusuf + Dimas + Denny + Irfan
            ],
            [
                'title'      => 'Advanced Badminton Drill',
                'sport_id'   => $this->BADMINTON,
                'host_id'    => 29,
                'venue'      => 'Cilandak Sports Hall',
                'lat'        => -6.2891,
                'lng'        => 106.8134,
                'area'       => 'Jakarta Selatan',
                'max'        => 6,
                'price'      => 60000,
                'visibility' => 'public',
                'match_type' => 'doubles',
                'approval'   => true,
                'days_ahead' => 9,
                'start_hour' => 17,
                'desc'       => 'Advanced badminton drill session. Focus on footwork and smashes.',
                'participants' => [29, 10, 2, 8], // Hana + Anisa + Dewi + Rina
            ],
            // ── Erlangga + Roger event (protected users) ─────────────────
            [
                'title'      => 'Tennis Coaching Session',
                'sport_id'   => $this->TENNIS,
                'host_id'    => 33,
                'venue'      => 'Court Terre Arena',
                'lat'        => -6.2484,
                'lng'        => 106.6165,
                'area'       => 'Tangerang',
                'max'        => 4,
                'price'      => 200000,
                'visibility' => 'public',
                'match_type' => 'singles',
                'approval'   => false,
                'days_ahead' => 11,
                'start_hour' => 8,
                'desc'       => 'Coaching session with Erlangga. Learn fundamentals and improve your game.',
                'participants' => [33, 35, 34], // Erlangga + Roger + Angga
            ],
        ];

        $now = Carbon::now();

        foreach ($eventTemplates as $t) {
            $hostId = $t['host_id'];
            $host = $users[$hostId] ?? null;
            if (!$host) continue;

            $startTime = $now->copy()->addDays($t['days_ahead'])->setTime($t['start_hour'], 0);
            $endTime   = $startTime->copy()->addHours(2);

            $event = Event::updateOrCreate(
                ['title' => $t['title'], 'host_id' => $hostId],
                [
                    'sport_id'           => $t['sport_id'],
                    'title'              => $t['title'],
                    'description'        => $t['desc'],
                    'venue_name'         => $t['venue'],
                    'latitude'           => $t['lat'],
                    'longitude'          => $t['lng'],
                    'start_time'        => $startTime,
                    'end_time'          => $endTime,
                    'max_slots'         => $t['max'],
                    'price'             => $t['price'],
                    'visibility'        => $t['visibility'],
                    'match_type'        => $t['match_type'],
                    'approval_required'  => $t['approval'],
                    'status'            => 'upcoming',
                ]
            );

            // Clean existing participants and re-seed
            EventParticipant::where('event_id', $event->id)->delete();

            // Host auto-joins as slot #1 approved
            EventParticipant::create([
                'event_id'    => $event->id,
                'user_id'     => $hostId,
                'status'      => 'approved',
                'slot_number' => 1,
                'joined_at'   => $now->copy()->subDays(mt_rand(1, 5)),
            ]);

            // Add other participants
            foreach ($t['participants'] as $idx => $userId) {
                if ($userId === $hostId) continue;
                $slot = $idx + 2;
                $status = ($t['approval'] && $slot > 1)
                    ? 'approved'
                    : 'approved';
                EventParticipant::create([
                    'event_id'    => $event->id,
                    'user_id'     => $userId,
                    'status'      => $status,
                    'slot_number' => $slot,
                    'joined_at'   => $now->copy()->subDays(mt_rand(1, 3)),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Step 5 — Event Messages
    // ─────────────────────────────────────────────────────────────────────────────

    private function seedEventMessages(): void
    {
        $messages = [
            ['content' => 'Halo semuanya! Excited untuk main besok! 🎾',  'type' => 'text'],
            ['content' => 'Saya sudah di lobby, tunggu di gerbang ya',    'type' => 'text'],
            ['content' => 'Boleh juga rally yang tadi di baseline',     'type' => 'text'],
            ['content' => 'Guys, jangan lupa bawa air minum ya 💪',     'type' => 'text'],
            ['content' => 'Semangat semua! 💪',                          'type' => 'text'],
            ['content' => 'Siap mulai pemanasan 15 menit ya!',           'type' => 'text'],
            ['content' => 'Baru sampai, tunggu 5 menit ya',             'type' => 'text'],
            ['content' => 'Raket sudah siap, see you on court! 🎾',     'type' => 'text'],
            ['content' => 'K、借好吗？ Terlambat 5 menit 💦',            'type' => 'text'],
            ['content' => 'Great session today everyone! Until next week 🎉', 'type' => 'text'],
            ['content' => 'Shuttlecock sudah saya bawa, cukup 2 tabung', 'type' => 'text'],
            ['content' => 'Padel ball sudah di tas, ready to go! 🟡',    'type' => 'text'],
        ];

        $events = Event::with('participants')->get();

        foreach ($events as $event) {
            $participantIds = $event->participants()->pluck('user_id')->toArray();
            if (empty($participantIds)) continue;

            $count = mt_rand(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $senderId = $participantIds[array_rand($participantIds)];
                $tmpl = $messages[array_rand($messages)];
                EventMessage::create([
                    'event_id'   => $event->id,
                    'user_id'    => $senderId,
                    'content'    => $tmpl['content'],
                    'type'       => $tmpl['type'],
                    'created_at' => Carbon::now()->subMinutes(mt_rand(5, 300))->addMinutes($i * 10),
                ]);
            }
        }
    }
}
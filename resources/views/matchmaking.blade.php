@extends('layouts.app')

@section('title', 'Find Opponent')

@section('content')
<div class="space-y-10" x-data="matchmakingFilters()">

    {{-- =========================================================
        HERO HEADER (Dashboard style)
    ========================================================= --}}
    <section class="pm-anim-load border border-white/10 bg-ink-800/40">
        <div class="grid lg:grid-cols-12 gap-0">
            <div class="lg:col-span-8 p-6 lg:p-14 border-b lg:border-b-0 lg:border-r border-white/10">
                <span class="pm-tag" data-animate="fade-down">Smart Matchmaking</span>
                <p class="font-display text-xs tracking-widest text-white/40 mt-6" data-animate="fade-up">SYSTEM ACTIVE</p>
                <h1 class="font-display text-4xl md:text-5xl uppercase mt-2" data-animate="fade-up">Find Your<br><span class="text-accent">Ideal Opponent</span></h1>
                <p class="text-white/60 mt-6 max-w-xl leading-relaxed" data-animate="fade-up">Precision-based player matching using sport type, skill proximity, availability, location radius, and activity signals.</p>
            </div>
            <div class="lg:col-span-4 p-6 lg:p-14 bg-accent/5" data-animate="fade-left">
                <p class="pm-section-title">Match Score Legend</p>
                <div class="space-y-3 mt-6 font-display text-xs tracking-widest">
                    <div class="flex items-center justify-between">
                        <span class="text-white/60">SAME SPORT</span><span class="text-accent">30%</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-white/60">SKILL MATCH</span><span class="text-accent">30%</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-white/60">TIME MATCH</span><span class="text-accent">20%</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-white/60">NEARBY</span><span class="text-accent">15%</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-white/60">ACTIVE</span><span class="text-accent">05%</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
        FILTER PANEL
    ========================================================= --}}
    <section class="border border-white/10 bg-ink-800/30 p-5 sm:p-8 lg:p-10" data-animate="fade-up">

        @php
            $activeFilterCount = collect($filters)
                ->filter(fn($v) => $v !== null && $v !== '' && $v !== false && $v !== [])
                ->count();
        @endphp

        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <p class="pm-section-title">Filters</p>
                    @if($activeFilterCount > 0)
                        <span class="font-display text-[0.6rem] tracking-widest text-accent uppercase border border-accent/40 bg-accent/10 px-2 py-0.5">
                            {{ $activeFilterCount }} Active
                        </span>
                    @endif
                </div>
                <h2 class="font-display text-2xl uppercase mt-2">Refine Your Search</h2>
            </div>
            <button type="button" @click="openPresetModal()"
                class="pm-btn-ghost text-[0.7rem] py-3 px-5">
                <span class="font-display tracking-widest text-accent">[+]</span> SAVE PRESET
            </button>
        </div>

        {{-- Saved presets — only visible after a search has been submitted --}}
        @if(request()->has('searched'))
        <div class="mb-6 flex flex-wrap gap-2">
            <template x-if="savedPresets.length > 0">
                <template x-for="(preset, idx) in savedPresets" :key="idx">
                    <div class="flex items-center gap-2 border border-white/10 bg-ink-900 px-3 py-1.5">
                        <button @click="loadPreset(preset)"
                            class="font-display text-[0.7rem] tracking-widest text-white/70 hover:text-accent"
                            x-text="preset.name">
                        </button>
                        <button @click="deletePreset(idx)"
                            class="text-xs text-white/40 hover:text-red-400">×</button>
                    </div>
                </template>
            </template>
        </div>
        @endif

        <form method="GET" class="grid gap-6 sm:grid-cols-3 xl:grid-cols-6" @submit="handleSubmit($event)">
            <input type="hidden" name="searched" value="1">

            <label class="space-y-2">
                <span class="pm-section-title">Sport</span>
                <select name="sport_id" class="pm-input">
                    <option value="">All Sports</option>
                    @foreach($sports as $sport)
                        <option value="{{ $sport->id }}" {{ ($filters['sport_id'] ?? '') == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Radius (km)</span>
                <input type="number" name="radius_km" value="{{ $filters['radius_km'] ?? '' }}" placeholder="e.g. 10" min="1" max="100" class="pm-input" />
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Start Time</span>
                <input type="time" name="start_time" value="{{ $filters['start_time'] ?? '' }}" class="pm-input" />
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">End Time</span>
                <input type="time" name="end_time" value="{{ $filters['end_time'] ?? '' }}" class="pm-input" />
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Play Style</span>
                <select name="play_style" class="pm-input">
                    <option value="">Any</option>
                    <option value="casual" {{ ($filters['play_style'] ?? '') === 'casual' ? 'selected' : '' }}>Casual</option>
                    <option value="competitive" {{ ($filters['play_style'] ?? '') === 'competitive' ? 'selected' : '' }}>Competitive</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Gender</span>
                <select name="gender" class="pm-input">
                    <option value="">Any</option>
                    <option value="male" {{ ($filters['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ ($filters['gender'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Age Range</span>
                <select name="age_range" class="pm-input">
                    <option value="">Any</option>
                    <option value="18-25" {{ ($filters['age_range'] ?? '') === '18-25' ? 'selected' : '' }}>18–25</option>
                    <option value="26-35" {{ ($filters['age_range'] ?? '') === '26-35' ? 'selected' : '' }}>26–35</option>
                    <option value="36-50" {{ ($filters['age_range'] ?? '') === '36-50' ? 'selected' : '' }}>36–50</option>
                    <option value="51+" {{ ($filters['age_range'] ?? '') === '51+' ? 'selected' : '' }}>51+</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="pm-section-title">Min Score</span>
                <select name="min_score" class="pm-input">
                    <option value="">Any (default 25%)</option>
                    <option value="0.5" {{ ($filters['min_score'] ?? '') == '0.5' ? 'selected' : '' }}>50%+ (Strong)</option>
                    <option value="0.4" {{ ($filters['min_score'] ?? '') == '0.4' ? 'selected' : '' }}>40%+ (Good)</option>
                    <option value="0.3" {{ ($filters['min_score'] ?? '') == '0.3' ? 'selected' : '' }}>30%+ (Fair)</option>
                    <option value="0.25" {{ ($filters['min_score'] ?? '') == '0.25' ? 'selected' : '' }}>25%+ (All)</option>
                </select>
            </label>

            <label class="flex items-end gap-2 pb-3 pt-6">
                <input type="checkbox" name="exclude_connected" value="1" {{ !empty($filters['exclude_connected']) ? 'checked' : '' }}
                    class="h-4 w-4 border-white/20 bg-ink-900 text-accent focus:ring-accent" />
                <span class="font-display text-[0.7rem] tracking-widest text-white/60">HIDE CONNECTED</span>
            </label>

            <div class="flex items-end gap-3 xl:col-span-2">
                <button type="submit" class="pm-btn-primary flex-1 text-[0.75rem] py-3 px-5 disabled:opacity-60"
                    :disabled="submitting">
                    <span x-show="!submitting">SEARCH</span>
                    <span x-show="submitting" style="display:none" class="inline-flex items-center gap-2">
                        <span class="inline-block h-3 w-3 border-2 border-ink-900/40 border-t-ink-900 rounded-full animate-spin"></span>
                        SEARCHING
                    </span>
                </button>
                <a href="{{ route('matchmaking') }}" class="pm-btn-ghost text-[0.7rem] py-3 px-5">RESET</a>
            </div>
        </form>
    </section>

    {{-- ============================================================
         CHALLENGER DISCOVERED — Match Reveal Overlay
         Only shown when showReveal = true (top match meets criteria)
         AND on the first page — never re-open while paginating.
    ============================================================ --}}
    @if($showReveal && $topMatch && $page === 1)
    @php
        $tm       = $topMatch;
        $tmUser   = $tm['user'];
        $tmScore  = $tm['score'];
        $tmScorePct = round($tmScore * 100);
        $tmComponents = $tm['components'] ?? [];
        $tmBadges = $tm['badges'] ?? [];
        $tmMutual = $tm['mutual_connections_count'] ?? 0;
        $tmDist   = isset($tm['distance_km']) ? round($tm['distance_km'], 1) : null;

        $tmSportLabel = '';
        $tmSkillNum   = 5;
        $tmSkillValue = null;
        $tmPrefTime   = '';
        if ($tmUser->sports->isNotEmpty()) {
            $firstSport   = $tmUser->sports->first();
            $tmSportLabel = $firstSport->name;
            $tmSkillNum   = $firstSport->pivot->skill_number ?? 5;
            $tmSkillValue = $firstSport->pivot->skill_value ?? null;
            if ($tmSkillValue) $tmSportLabel .= ' · ' . $tmSkillValue;

            $tmUs = $tmUser->userSports->firstWhere('sport_id', $firstSport->id);
            $tmPrefTime = $tmUs?->preferredTimeLabel() ?? '';
        }

        $reasons = [];
        if (($tmComponents['sport']['raw']    ?? 0) >= 1.0) $reasons[] = 'Same Sport';
        if (($tmComponents['skill']['raw']    ?? 0) >= 0.8) $reasons[] = 'Similar Skill';
        if (($tmComponents['distance']['raw'] ?? 0) >= 0.6) $reasons[] = 'Nearby';
        if (($tmComponents['time']['raw']     ?? 0) >= 0.7) $reasons[] = 'Schedule Overlap';
        if ($tmMutual > 0) $reasons[] = $tmMutual . ' Mutual ' . ($tmMutual > 1 ? 'Connections' : 'Connection');
        $reasons = array_slice($reasons, 0, 4);
    @endphp

    <div x-data="{ show: false, leaving: false, close() { this.leaving = true; setTimeout(() => { this.show = false; this.leaving = false; }, 300); } }"
         x-init="@if(request()->boolean('searched')) setTimeout(() => show = true, 80) @endif"
         x-show="show"
         x-effect="document.body.style.overflow = show ? 'hidden' : ''"
         @keydown.escape.window="show && close()"
         role="dialog" aria-modal="true" aria-labelledby="pm-reveal-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        {{-- Backdrop --}}
        <div class="pm-reveal-backdrop absolute inset-0" :class="{ 'pm-leaving': leaving }" style="background:rgba(0,0,0,0.88);backdrop-filter:blur(5px)" @click="close()"></div>

        {{-- Modal --}}
        <div class="pm-reveal-card relative z-10 w-full max-h-[90vh] overflow-y-auto" :class="{ 'pm-leaving': leaving }" style="max-width:520px">

            {{-- Outer border accent --}}
            <div style="border:1px solid rgba(249,115,22,0.35);background:rgb(var(--bg-surface));overflow:hidden;min-height:min-content">

                {{-- Top accent line --}}
                <div style="height:2px;background:#F97316;width:100%"></div>

                {{-- Close button --}}
                <button type="button" @click="close()" aria-label="Close"
                        style="position:absolute;top:0.75rem;right:0.75rem;z-index:2;width:34px;height:34px;display:flex;align-items:center;justify-content:center;font-family:'Oswald',sans-serif;font-size:1.4rem;line-height:1;color:rgb(var(--fg) / 0.4);background:none;border:1px solid transparent;cursor:pointer;transition:color 0.2s,border-color 0.2s"
                        onmouseover="this.style.color='#F97316';this.style.borderColor='rgba(249,115,22,0.35)'"
                        onmouseout="this.style.color='rgb(var(--fg) / 0.4)';this.style.borderColor='transparent'">&times;</button>

                {{-- Header label --}}
                <div style="padding:1.75rem 2rem 0;text-align:center">
                    <p style="font-family:'Oswald',sans-serif;font-size:0.68rem;font-weight:500;letter-spacing:0.28em;text-transform:uppercase;color:rgb(var(--fg) / 0.4);display:flex;align-items:center;justify-content:center;gap:0.6rem">
                        <span style="display:inline-block;width:40px;height:1px;background:rgb(var(--fg) / 0.2)"></span>
                        Top Match Found
                        <span style="display:inline-block;width:40px;height:1px;background:rgb(var(--fg) / 0.2)"></span>
                    </p>
                </div>

                {{-- Body --}}
                <div style="padding:1.5rem 2rem 2rem;text-align:center">

                    {{-- Score ring --}}
                    @php
                        $ringR = 46;
                        $ringC = 2 * M_PI * $ringR;
                        $ringOffset = $ringC * (1 - min(100, $tmScorePct) / 100);
                    @endphp
                    <div style="position:relative;width:112px;height:112px;margin:0 auto 1.5rem">
                        <svg width="112" height="112" viewBox="0 0 112 112" style="transform:rotate(-90deg)">
                            <circle cx="56" cy="56" r="{{ $ringR }}" fill="none" stroke="rgb(var(--fg) / 0.08)" stroke-width="4"></circle>
                            <circle cx="56" cy="56" r="{{ $ringR }}" fill="none" stroke="#F97316" stroke-width="4"
                                    stroke-linecap="round" stroke-dasharray="{{ $ringC }}"
                                    stroke-dashoffset="{{ $ringC }}"
                                    style="transition:stroke-dashoffset 1s cubic-bezier(0.22,1,0.36,1) 0.25s"
                                    x-effect="$el.style.strokeDashoffset = show ? '{{ $ringOffset }}' : '{{ $ringC }}'"></circle>
                        </svg>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                            <span style="font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;color:#F97316;line-height:1">{{ $tmScorePct }}</span>
                            <span style="font-family:'Oswald',sans-serif;font-size:0.6rem;font-weight:500;letter-spacing:0.2em;text-transform:uppercase;color:rgb(var(--fg) / 0.4)">MATCH</span>
                        </div>
                    </div>

                    {{-- Avatar --}}
                    <div style="display:flex;justify-content:center;margin-bottom:1.25rem">
                        @if($tmUser->avatar_url)
                            <img src="{{ Storage::url($tmUser->avatar_url) }}" alt="{{ $tmUser->name }}"
                                 style="width:72px;height:72px;border:1px solid rgb(var(--fg) / 0.15);object-fit:cover">
                        @else
                            <div style="width:72px;height:72px;border:1px solid rgb(var(--fg) / 0.15);background:rgb(var(--bg-base));display:flex;align-items:center;justify-content:center;font-family:'Oswald',sans-serif;font-size:2rem;font-weight:700;color:rgb(var(--fg))">
                                {{ strtoupper(substr($tmUser->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Name --}}
                    <h2 id="pm-reveal-title" style="font-family:'Oswald',sans-serif;font-size:1.9rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:rgb(var(--fg));line-height:1;margin-bottom:0.5rem">
                        {{ $tmUser->name }}
                    </h2>

                    {{-- Sport · skill --}}
                    @if($tmSportLabel)
                    <p style="font-family:'Oswald',sans-serif;font-size:0.78rem;letter-spacing:0.2em;text-transform:uppercase;color:#F97316;margin-bottom:0.4rem">
                        {{ $tmSportLabel }}
                    </p>
                    @endif

                    {{-- Location / distance --}}
                    <p style="font-family:'Inter',sans-serif;font-size:0.8rem;color:rgb(var(--fg) / 0.4);margin-bottom:{{ $tmPrefTime ? '0.75rem' : '1.5rem' }}">
                        {{ $tmUser->home_address ?? 'Location not set' }}
                        @if($tmDist) &mdash; {{ $tmDist }} km away @endif
                    </p>

                    {{-- Preferred playing time --}}
                    @if($tmPrefTime)
                    <p style="display:inline-flex;align-items:center;gap:0.45rem;font-family:'Oswald',sans-serif;font-size:0.68rem;letter-spacing:0.14em;text-transform:uppercase;color:rgb(var(--fg) / 0.6);border:1px solid rgb(var(--fg) / 0.12);padding:0.3rem 0.7rem;margin-bottom:1.5rem">
                        <span style="color:#F97316">◷</span> {{ $tmPrefTime }}
                    </p>
                    @endif

                    {{-- Skill bar --}}
                    @if($tmUser->sports->isNotEmpty())
                    <div style="margin-bottom:1.5rem">
                        <div style="display:flex;justify-content:space-between;font-family:'Oswald',sans-serif;font-size:0.65rem;letter-spacing:0.18em;text-transform:uppercase;color:rgb(var(--fg) / 0.4);margin-bottom:0.4rem">
                            <span>Skill Level</span>
                            <span style="color:#F97316">{{ $tmSkillValue ?? 'Level ' . $tmSkillNum }}</span>
                        </div>
                        <div style="height:6px;background:rgb(var(--fg) / 0.08)">
                            <div style="height:100%;background:#F97316;width:{{ $tmSkillNum * 10 }}%;transition:width 0.6s ease"></div>
                        </div>
                    </div>
                    @endif

                    {{-- Component score bars --}}
                    @if(!empty($tmComponents))
                    <div style="background:rgb(var(--bg-base));border:1px solid rgb(var(--border-base));padding:1rem 1.25rem;margin-bottom:1.5rem;text-align:left">
                        <p style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.22em;text-transform:uppercase;color:rgb(var(--fg) / 0.35);margin-bottom:0.75rem">Score Breakdown</p>
                        <div style="display:flex;flex-direction:column;gap:0.55rem">
                            @foreach($tmComponents as $key => $comp)
                            <div style="display:flex;align-items:center;gap:0.75rem">
                                <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.15em;text-transform:uppercase;color:rgb(var(--fg) / 0.4);min-width:4.5rem">{{ $key }}</span>
                                <div style="flex:1;height:6px;background:rgb(var(--fg) / 0.07)">
                                    <div style="height:100%;background:#F97316;width:{{ $comp['weight'] > 0 ? round($comp['score'] / $comp['weight'] * 100) : 0 }}%;transition:width 0.6s ease"></div>
                                </div>
                                <span style="font-family:'Oswald',sans-serif;font-size:0.65rem;letter-spacing:0.1em;color:#F97316;min-width:2.5rem;text-align:right">{{ round($comp['score'] * 100) }}%</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Badges + tags in one row --}}
                    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.5rem;margin-bottom:1.25rem">
                        @foreach($tmBadges as $badge)
                            <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;font-weight:500;letter-spacing:0.16em;text-transform:uppercase;color:#F97316;border:1px solid rgba(249,115,22,0.35);background:rgba(249,115,22,0.06);padding:0.25rem 0.65rem">{{ $badge }}</span>
                        @endforeach
                        @if($tmUser->play_style)
                            <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.16em;text-transform:uppercase;color:rgb(var(--fg) / 0.55);border:1px solid rgb(var(--fg) / 0.12);padding:0.25rem 0.65rem">{{ $tmUser->play_style }}</span>
                        @endif
                        @if($tmUser->gender)
                            <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.16em;text-transform:uppercase;color:rgb(var(--fg) / 0.55);border:1px solid rgb(var(--fg) / 0.12);padding:0.25rem 0.65rem">{{ $tmUser->gender }}</span>
                        @endif
                        @if($tmUser->age_range)
                            <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.16em;text-transform:uppercase;color:rgb(var(--fg) / 0.55);border:1px solid rgb(var(--fg) / 0.12);padding:0.25rem 0.65rem">{{ $tmUser->age_range }}</span>
                        @endif
                        @if($tmMutual > 0)
                            <span style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.16em;text-transform:uppercase;color:#F97316;border:1px solid rgba(249,115,22,0.35);background:rgba(249,115,22,0.06);padding:0.25rem 0.65rem">{{ $tmMutual }} Mutual</span>
                        @endif
                    </div>

                    {{-- Why this match --}}
                    @if(!empty($reasons))
                    <div style="border-left:2px solid #F97316;background:rgba(249,115,22,0.04);padding:0.75rem 1rem;text-align:left;margin-bottom:0">
                        <p style="font-family:'Oswald',sans-serif;font-size:0.62rem;letter-spacing:0.2em;text-transform:uppercase;color:rgba(249,115,22,0.7);margin-bottom:0.3rem">Why This Match</p>
                        <p style="font-family:'Inter',sans-serif;font-size:0.78rem;color:rgb(var(--fg) / 0.7)">{{ implode(' · ', $reasons) }}</p>
                    </div>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;border-top:1px solid rgb(var(--border-base))">
                    <a href="{{ route('events.challenge', $tmUser) }}"
                       style="display:flex;align-items:center;justify-content:center;padding:1rem;background:#D62B2B;font-family:'Oswald',sans-serif;font-size:0.78rem;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:#fff;transition:background 0.2s"
                       onmouseover="this.style.background='#F97316'" onmouseout="this.style.background='#D62B2B'">
                        Challenge
                    </a>
                    <a href="{{ route('player.profile', $tmUser) }}"
                       style="display:flex;align-items:center;justify-content:center;padding:1rem;background:rgb(var(--bg-surface));font-family:'Oswald',sans-serif;font-size:0.78rem;font-weight:500;letter-spacing:0.18em;text-transform:uppercase;color:rgb(var(--fg) / 0.75);border-left:1px solid rgb(var(--border-base));transition:color 0.2s"
                       onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgb(var(--fg) / 0.75)'">
                        View Profile
                    </a>
                </div>

                {{-- Skip --}}
                <div style="border-top:1px solid rgb(var(--border-base));padding:0.85rem;text-align:center">
                    <button @click="close()"
                            style="font-family:'Oswald',sans-serif;font-size:0.65rem;letter-spacing:0.2em;text-transform:uppercase;color:rgb(var(--fg) / 0.3);background:none;border:none;cursor:pointer;transition:color 0.2s"
                            onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgb(var(--fg) / 0.3)'">
                        Skip &mdash; See all {{ $total + 1 }} results
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- Results --}}
    <section id="results">
        <div class="mb-8 flex items-end justify-between" data-animate="fade-up">
            <div>
                <p class="pm-section-title">Results</p>
                <h2 class="font-display text-3xl uppercase mt-2">Recommended Opponents</h2>
            </div>
            @if($searched)
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase">{{ $total }} FOUND</span>
            @endif
        </div>

        @if(!$searched)
        {{-- Prompt state — no search performed yet --}}
        <div class="border border-white/10 bg-ink-800/30 p-16 text-center" data-animate="fade-up">
            <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center border border-accent/40 bg-accent/10 font-display text-2xl text-accent">?</div>
            <p class="font-display text-2xl uppercase text-white">Ready to find your match?</p>
            <p class="mt-3 text-sm text-white/50 max-w-md mx-auto">Set your filters above &mdash; sport, radius, availability &mdash; then hit search to run smart matchmaking.</p>
            <button type="button" @click="$root.querySelector('form').requestSubmit()"
                    class="pm-btn-primary text-[0.75rem] py-3 px-8 mt-6">SEARCH NOW</button>
        </div>

        @elseif(count($gridResults) > 0)
        <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($gridResults as $rec)
                @php
                    $opp = $rec['user'];
                    $score = $rec['score'];
                    $badges = $rec['badges'];
                    $components = $rec['components'] ?? [];
                    $mutualCount = $rec['mutual_connections_count'] ?? 0;
                    $scorePct = round($score * 100);

                    // Preferred playing time — for the filtered sport if set, else the primary one
                    $cardFilteredSportId = isset($filters['sport_id']) ? (int) $filters['sport_id'] : null;
                    $cardUs = $cardFilteredSportId
                        ? $opp->userSports->firstWhere('sport_id', $cardFilteredSportId)
                        : null;
                    $cardUs = $cardUs ?? $opp->userSports->sortBy('created_at')->first();
                    $cardPrefTime = $cardUs?->preferredTimeLabel() ?? '';
                @endphp
                <article class="pm-card p-6 flex flex-col gap-5 group" data-animate="fade-up">

                    {{-- Card header --}}
                    <div class="flex items-start justify-between gap-4">

                        <div class="flex gap-4 min-w-0 flex-1">
                            @if($opp->avatar_url)
                                <img src="{{ Storage::url($opp->avatar_url) }}" alt="{{ $opp->name }}"
                                     class="h-12 w-12 border border-white/15 group-hover:border-accent object-cover shrink-0 transition" />
                            @else
                                <div class="h-12 w-12 border border-white/15 group-hover:border-accent flex items-center justify-center font-display text-base text-white shrink-0 transition">
                                    {{ strtoupper(substr($opp->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <h3 class="font-display text-xl uppercase text-white tracking-wide truncate">{{ $opp->name }}</h3>
                                <p class="mt-1 font-display text-[0.65rem] tracking-widest text-white/40 uppercase">
                                    {{ $opp->home_address ?? 'Location not set' }}
                                    @if(isset($rec['distance_km'])) · {{ round($rec['distance_km'], 1) }} KM @endif
                                    @if($opp->last_active_at) · {{ $opp->last_active_at->diffForHumans() }} @endif
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <div class="font-display text-2xl text-accent leading-none">{{ $scorePct }}%</div>
                            <div class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase mt-1">match</div>
                            @if($mutualCount > 0)
                                <div class="mt-2">
                                    <span class="font-display text-[0.6rem] tracking-widest text-accent uppercase">{{ $mutualCount }} MUTUAL</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Score bar --}}
                    <div class="h-1 bg-white/10">
                        <div class="h-full bg-accent" style="width: {{ $scorePct }}%"></div>
                    </div>

                    {{-- Component breakdown --}}
                    @if(!empty($components))
                    <div class="space-y-2" x-data="{ open: false }">
                        <button @click="open = !open" class="flex w-full items-center justify-between font-display text-[0.7rem] tracking-widest text-white/50 hover:text-accent uppercase">
                            <span>SCORE BREAKDOWN</span>
                            <span x-show="!open">▾ Show</span>
                            <span x-show="open">▴ Hide</span>
                        </button>
                        <div x-show="open" x-transition class="space-y-2 border border-white/10 bg-ink-900 p-3 text-xs">
                            @foreach($components as $key => $comp)
                                <div class="flex items-center gap-2">
                                    <span class="font-display text-[0.65rem] tracking-widest text-white/50 uppercase min-w-[5rem]">{{ $key }}</span>
                                    <div class="h-1 flex-1 bg-white/10">
                                        <div class="h-full bg-accent" style="width: {{ $comp['weight'] > 0 ? round($comp['score'] / $comp['weight'] * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="font-display text-[0.7rem] tracking-widest text-accent uppercase min-w-[3rem] text-right">
                                        {{ round($comp['score'] * 100) }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Badges --}}
                    @if(!empty($badges))
                    <div class="flex flex-wrap gap-2">
                        @foreach($badges as $badge)
                            <span class="border border-accent/40 bg-accent/10 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-accent uppercase">{!! $badge !!}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Criteria match summary --}}
                    @if(!empty($components))
                    @php
                        $strongFactors = collect($components)->filter(fn($c) => $c['raw'] >= 0.7)->keys()->toArray();
                        $weakFactors = collect($components)->filter(fn($c) => $c['raw'] < 0.3)->keys()->toArray();
                    @endphp
                    @if(count($strongFactors) > 0)
                    <div class="border-l-2 border-accent bg-accent/5 px-3 py-2 text-xs text-white/80">
                        <span class="font-display text-[0.65rem] tracking-widest text-accent uppercase mr-1">STRONG MATCH</span>
                        {{ implode(', ', array_map('ucfirst', $strongFactors)) }}
                    </div>
                    @endif
                    @if(count($weakFactors) > 0)
                    <div class="border-l-2 border-white/20 bg-white/5 px-3 py-2 text-xs text-white/60">
                        <span class="font-display text-[0.65rem] tracking-widest text-white/50 uppercase mr-1">WEAK FACTORS</span>
                        {{ implode(', ', array_map('ucfirst', $weakFactors)) }}
                    </div>
                    @endif
                    @endif

                    {{-- Tags row: play_style / gender / age --}}
                    @if($opp->play_style || $opp->gender || $opp->age_range)
                    <div class="flex flex-wrap gap-1.5">
                        @if($opp->play_style)
                            <span class="border border-white/15 bg-white/5 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/60 uppercase">{{ $opp->play_style }}</span>
                        @endif
                        @if($opp->gender)
                            <span class="border border-white/15 bg-white/5 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/60 uppercase">{{ $opp->gender }}</span>
                        @endif
                        @if($opp->age_range)
                            <span class="border border-white/15 bg-white/5 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/60 uppercase">{{ $opp->age_range }}</span>
                        @endif
                    </div>
                    @endif

                    {{-- Sports tags — filtered sport shown first --}}
                    @if($opp->sports->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5">
                        @php
                            $filteredSportId = isset($filters['sport_id']) ? (int)$filters['sport_id'] : null;
                            $sortedSports = $opp->sports->sortByDesc(fn($s) => $filteredSportId && $s->id === $filteredSportId)->take(3);
                        @endphp
                        @foreach($sortedSports as $sport)
                            <span class="border px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest uppercase
                                {{ $filteredSportId && $sport->id === $filteredSportId
                                    ? 'border-accent/40 bg-accent/10 text-accent'
                                    : 'border-white/10 bg-ink-900 text-white/50' }}">
                                {{ $sport->name }}
                                @if($sport->pivot->skill_number) · LV {{ $sport->pivot->skill_number }} @endif
                            </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Preferred playing time --}}
                    @if($cardPrefTime)
                    <div class="flex items-center gap-2 font-display text-[0.6rem] tracking-widest text-white/50 uppercase">
                        <span class="text-accent">◷</span>
                        <span>{{ $cardPrefTime }}</span>
                    </div>
                    @endif

                    {{-- Action buttons --}}
                    <div class="mt-auto pt-4 flex flex-col gap-px" style="background:rgb(var(--bg-base))">
                        <a href="{{ route('events.challenge', $opp) }}" class="bg-[#D62B2B] text-white text-center py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent hover:text-ink-900 transition">
                            CHALLENGE
                        </a>

                        @php
                            $conn = $connectionStates[$opp->id] ?? null;
                        @endphp

                        @if($conn)
                            @if($conn->status === 'accepted')
                                <a href="{{ route('connections.chat', $conn) }}" class="bg-accent text-ink-900 text-center py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">
                                    OPEN CHAT
                                </a>
                                <span class="bg-ink-900 text-center py-3 font-display text-[0.65rem] tracking-widest text-emerald-400 uppercase">
                                    CONNECTED
                                </span>
                            @elseif($conn->status === 'pending')
                                @if($conn->requester_id === Auth::id())
                                    <span class="bg-ink-900 text-center py-3 font-display text-[0.7rem] tracking-widest text-amber-400 uppercase">
                                        Request Pending
                                    </span>
                                @else
                                    <div class="grid grid-cols-2 gap-px">
                                        <form method="POST" action="{{ route('connections.update', $conn) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="accept">
                                            <button class="w-full bg-accent text-ink-900 py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('connections.update', $conn) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="decline">
                                            <button class="w-full bg-ink-900 text-white/60 py-3 font-display text-[0.7rem] tracking-widest uppercase hover:text-white transition">Decline</button>
                                        </form>
                                    </div>
                                @endif
                            @else
                                <form method="POST" action="{{ route('connections.store') }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $opp->id }}">
                                    <button type="submit" class="w-full bg-accent text-ink-900 py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">CONNECT</button>
                                </form>
                            @endif
                        @else
                            <form method="POST" action="{{ route('connections.store') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $opp->id }}">
                                <button type="submit" class="w-full bg-accent text-ink-900 py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">CONNECT</button>
                            </form>
                        @endif

                        <a href="{{ route('player.profile', $opp) }}" class="bg-ink-900 text-center py-3 font-display text-[0.7rem] tracking-widest text-white/60 uppercase hover:text-accent transition">VIEW PROFILE</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($hasMore || $hasPrev)
        <div class="mt-10 flex items-center justify-center gap-2" data-animate="fade-up">
            @if($hasPrev)
                <a href="{{ route('matchmaking', array_merge(request()->query(), ['page' => $page - 1])) }}"
                   class="px-6 py-3 font-display text-[0.7rem] tracking-widest text-white/70 uppercase transition hover:text-white"
                   style="border:1px solid rgb(var(--border-base));background:rgb(var(--bg-surface))">
                    ← Previous
                </a>
            @endif
            <span class="px-6 py-3 font-display text-[0.7rem] tracking-widest uppercase"
                  style="border:1px solid rgba(249,115,22,0.4);background:rgba(249,115,22,0.08);color:#F97316">
                Page {{ $page }}
            </span>
            @if($hasMore)
                <a href="{{ route('matchmaking', array_merge(request()->query(), ['page' => $page + 1])) }}"
                   class="px-6 py-3 font-display text-[0.7rem] tracking-widest uppercase transition"
                   style="background:#D62B2B;color:#fff"
                   onmouseover="this.style.background='#F97316'" onmouseout="this.style.background='#D62B2B'">
                    Next →
                </a>
            @endif
        </div>
        @endif

        @else
        <div class="border border-white/10 bg-ink-800/30 p-16 text-center">
            <p class="font-display text-2xl uppercase text-white">No matches found</p>
            <p class="mt-3 text-sm text-white/50">Try expanding the radius, lowering the minimum score, or clearing filters.</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('matchmaking', array_merge(request()->except(['radius_km', 'page']), ['radius_km' => 50, 'searched' => 1])) }}"
                   class="pm-btn-ghost text-[0.7rem] py-3 px-5">WIDEN RADIUS TO 50 KM</a>
                <a href="{{ route('matchmaking', array_merge(request()->except(['min_score', 'page']), ['min_score' => 0.25, 'searched' => 1])) }}"
                   class="pm-btn-ghost text-[0.7rem] py-3 px-5">LOWER MIN SCORE</a>
                <a href="{{ route('matchmaking') }}" class="pm-btn-primary text-[0.7rem] py-3 px-5">RESET ALL FILTERS</a>
            </div>
        </div>
        @endif
    </section>

    {{-- =========================================================
        SAVE PRESET MODAL (themed replacement for prompt/alert)
    ========================================================= --}}
    <div x-show="presetModalOpen" x-transition.opacity
         @keydown.escape.window="presetModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.8);backdrop-filter:blur(4px)" @click="presetModalOpen = false"></div>
        <div class="relative z-10 w-full border border-accent/35 bg-ink-900 p-8" style="max-width:420px" @keydown.enter="confirmPreset()">
            <p class="pm-section-title">Save Filter Preset</p>
            <h3 class="font-display text-xl uppercase mt-2 mb-5">Name This Preset</h3>
            <input type="text" x-ref="presetInput" x-model="presetName"
                   placeholder="e.g. Tennis Players Jakarta"
                   class="pm-input w-full" maxlength="40" />
            <p x-show="presetError" x-text="presetError" class="mt-2 text-xs text-red-400" style="display:none"></p>
            <div class="mt-6 flex gap-3">
                <button type="button" @click="confirmPreset()" class="pm-btn-primary flex-1 text-[0.75rem] py-3 px-5">SAVE</button>
                <button type="button" @click="presetModalOpen = false" class="pm-btn-ghost text-[0.7rem] py-3 px-5">CANCEL</button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] border border-accent/40 bg-ink-900 px-5 py-3"
         style="display:none">
        <span class="font-display text-[0.7rem] tracking-widest text-accent uppercase" x-text="toast"></span>
    </div>
</div>

<style>
/* ── Match reveal overlay ── */
@keyframes pm-backdrop-in {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes pm-card-in {
    0%   { opacity: 0; transform: translateY(28px) scale(0.97); }
    60%  { opacity: 1; transform: translateY(-3px) scale(1.005); }
    100% { opacity: 1; transform: translateY(0)    scale(1); }
}
@keyframes pm-card-out {
    from { opacity: 1; transform: translateY(0) scale(1); }
    to   { opacity: 0; transform: translateY(16px) scale(0.97); }
}
@keyframes pm-backdrop-out {
    from { opacity: 1; }
    to   { opacity: 0; }
}

.pm-reveal-backdrop {
    animation: pm-backdrop-in 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.pm-reveal-card {
    animation: pm-card-in 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.05s both;
}
.pm-reveal-backdrop.pm-leaving {
    animation: pm-backdrop-out 0.3s ease both;
}
.pm-reveal-card.pm-leaving {
    animation: pm-card-out 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
}
</style>

@once
@push('scripts')
<script>
function matchmakingFilters() {
    return {
        savedPresets: JSON.parse(localStorage.getItem('playmate_match_presets') || '[]'),
        submitting: false,
        presetModalOpen: false,
        presetName: '',
        presetError: '',
        toast: '',
        _toastTimer: null,

        _form() {
            return this.$root.querySelector('form');
        },

        _activeParams() {
            const params = {};
            const fd = new FormData(this._form());
            for (let [key, val] of fd.entries()) {
                if (val && key !== 'searched') params[key] = val;
            }
            return params;
        },

        showToast(msg) {
            this.toast = msg;
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => this.toast = '', 2500);
        },

        handleSubmit(e) {
            const form = e.target;
            const start = form.start_time ? form.start_time.value : '';
            const end = form.end_time ? form.end_time.value : '';
            if (start && end && start >= end) {
                e.preventDefault();
                this.showToast('End time must be after start time.');
                return;
            }
            this.submitting = true;
        },

        openPresetModal() {
            if (Object.keys(this._activeParams()).length === 0) {
                this.showToast('No filters active to save.');
                return;
            }
            this.presetName = '';
            this.presetError = '';
            this.presetModalOpen = true;
            this.$nextTick(() => this.$refs.presetInput && this.$refs.presetInput.focus());
        },

        confirmPreset() {
            const name = this.presetName.trim();
            if (!name) {
                this.presetError = 'Please enter a name.';
                return;
            }
            const params = this._activeParams();
            if (Object.keys(params).length === 0) {
                this.presetModalOpen = false;
                this.showToast('No filters active to save.');
                return;
            }
            this.savedPresets.push({ name, params });
            localStorage.setItem('playmate_match_presets', JSON.stringify(this.savedPresets));
            this.presetModalOpen = false;
            this.showToast('Preset "' + name + '" saved.');
        },

        loadPreset(preset) {
            const form = this._form();
            // Clear all current selects/inputs first
            form.querySelectorAll('select, input[type="text"], input[type="number"], input[type="time"]').forEach(el => {
                if (el.tagName === 'SELECT') {
                    el.querySelectorAll('option').forEach(o => o.selected = el.required && o.value === '' ? true : false);
                } else {
                    el.value = '';
                }
            });
            form.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);

            // Apply preset params
            for (let [key, val] of Object.entries(preset.params)) {
                const el = form.elements[key];
                if (!el) continue;
                if (el.type === 'checkbox') {
                    el.checked = val == '1';
                } else {
                    el.value = val;
                }
            }
            this.submitting = true;
            form.submit();
        },

        deletePreset(idx) {
            this.savedPresets.splice(idx, 1);
            localStorage.setItem('playmate_match_presets', JSON.stringify(this.savedPresets));
        }
    };
}
</script>
@endpush
@endonce

@push('scripts')
    @include('partials._animejs-init-base')
@endpush
@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayMate Sports Club</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('storage/favicon.png') }}?v={{ time() }}">
    @include('partials._theme-init-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak]{display:none!important}</style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Oswald', 'Impact', 'sans-serif'],
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ink: {
                            900: 'rgb(var(--bg-base) / <alpha-value>)',
                            800: 'rgb(var(--bg-surface) / <alpha-value>)',
                            700: 'rgb(var(--bg-surface-2) / <alpha-value>)',
                        },
                        white: 'rgb(var(--fg) / <alpha-value>)',
                        accent: { DEFAULT:'#F97316', 500:'#F97316', 400:'#FB923C' },
                    },
                },
            },
        };
    </script>
    @include('partials._theme-vars')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { background-color: rgb(var(--bg-base)); }
        body { font-family: 'Inter', sans-serif; color: rgb(var(--fg)); -webkit-font-smoothing: antialiased; }
        h1, h2, h3, h4, .font-display { font-family: 'Oswald', Impact, sans-serif; letter-spacing: 0.02em; }

        /* ── NAV ── */
        .bs-nav { background: rgb(var(--bg-base) / 0.96); border-bottom: 1px solid rgb(var(--border-base)); backdrop-filter: blur(8px); }
        .bs-nav-link {
            font-family: 'Oswald', sans-serif;
            font-size: 0.78rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgb(var(--fg) / 0.7);
            transition: color 0.2s;
        }
        .bs-nav-link:hover { color: #F97316; }
        .bs-cta-btn {
            background: #D62B2B;
            color: #fff;
            padding: 0.55rem 1.3rem;
            font-family: 'Oswald', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            transition: background 0.2s;
            display: inline-block;
        }
        .bs-cta-btn:hover { background: #F97316; }

        /* ── SECTION LABEL (— LABEL —) ── */
        .bs-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-family: 'Oswald', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: rgb(var(--fg) / 0.5);
        }
        .bs-label::before, .bs-label::after {
            content: '';
            width: 50px;
            height: 1px;
            background: rgb(var(--fg) / 0.25);
            flex-shrink: 0;
        }

        /* ── HERO ── */
        .bs-hero {
            position: relative;
            background: #000;
            overflow: hidden;
            min-height: 92vh;
            --fg: 255 255 255;
        }
        .bs-hero video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }
        .bs-hero-overlay {
            position: absolute; inset: 0; z-index: 1;
            background: linear-gradient(180deg, rgba(0,0,0,0.80) 0%, rgba(0,0,0,0.45) 50%, rgba(0,0,0,0.88) 100%);
        }
        .bs-hero-content { position: relative; z-index: 2; }

        /* ── BUTTONS ── */
        .bs-btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.9rem 2.2rem;
            background: #D62B2B;
            color: #fff;
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem; font-weight: 600;
            letter-spacing: 0.18em; text-transform: uppercase;
            transition: background 0.2s;
        }
        .bs-btn-primary:hover { background: #F97316; }
        .bs-btn-outline {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.9rem 2.2rem;
            border: 2px solid rgb(var(--fg) / 0.3);
            color: rgb(var(--fg));
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem; font-weight: 500;
            letter-spacing: 0.18em; text-transform: uppercase;
            transition: border-color 0.2s, color 0.2s;
        }
        .bs-btn-outline:hover { border-color: #F97316; color: #F97316; }

        /* ── SPORT TILE ── */
        .bs-sport-tile {
            background: rgb(var(--bg-surface));
            border: 1px solid rgb(var(--border-base));
            padding: 2.5rem;
            transition: border-color 0.25s, transform 0.25s;
        }
        .bs-sport-tile:hover { border-color: rgba(249,115,22,0.55); transform: translateY(-4px); }

        /* ── STEP CARD ── */
        .bs-step { border: 1px solid rgb(var(--border-base)); padding: 2rem; text-align: center; }

        /* ── PARTNER CIRCLE ── */
        .bs-partner { display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
        .bs-partner-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgb(var(--bg-surface-3)); border: 2px solid rgb(var(--border-base));
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; transition: border-color 0.2s;
        }
        .bs-partner:hover .bs-partner-icon { border-color: #F97316; }
        .bs-partner-name { font-family: 'Oswald', sans-serif; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: rgb(var(--fg) / 0.65); }
        .bs-partner-sub  { font-size: 0.6rem; color: rgb(var(--fg) / 0.35); text-align: center; }

        .bs-divider { height: 1px; background: rgb(var(--border-base)); }

        @keyframes pm-reveal-fallback { to { opacity: 1; transform: none; } }
        [data-animate] { animation: pm-reveal-fallback 0.01s 2s forwards; }
    </style>
</head>
<body>
@include('partials._animejs-head')

{{-- ═══════════════════════ NAV ═══════════════════════ --}}
<header class="bs-nav sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12 relative">
        <div class="flex items-center justify-between" style="height:68px">

            {{-- Left nav & Mobile Toggle --}}
            <div class="flex items-center flex-1 justify-start">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white/70 hover:text-white mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('events.index') }}" class="bs-nav-link">Events</a>
                    <a href="{{ route('venues.index') }}" class="bs-nav-link">Venues</a>
                </nav>
            </div>

            {{-- Logo center --}}
            <div class="absolute left-1/2 transform -translate-x-1/2 flex justify-center">
                <a href="{{ route('home') }}" class="flex flex-col items-center leading-none group">
                    <span class="font-display text-white group-hover:text-[#F97316] transition" style="font-size:1.5rem;font-weight:700;letter-spacing:0.08em">PLAYMATE</span>
                    <span class="font-display text-white/35" style="font-size:0.5rem;letter-spacing:0.45em;text-transform:uppercase">SPORTS CLUB</span>
                </a>
            </div>

            {{-- Right --}}
            <div class="flex items-center gap-5 flex-1 justify-end">
                @include('partials._theme-toggle')
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden md:block bs-nav-link">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">@csrf
                        <button type="submit" class="bs-nav-link bg-transparent border-0 cursor-pointer">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hidden md:block bs-nav-link">Sign In</a>
                    <a href="{{ route('register') }}" class="hidden md:block bs-cta-btn">Join The Club</a>
                @endauth
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenuOpen" x-transition x-cloak class="md:hidden absolute top-full left-0 w-full shadow-2xl py-4 px-6 flex flex-col gap-4" style="background:rgb(var(--bg-base) / 0.98); border-bottom:1px solid rgb(var(--border-base)); backdrop-filter:blur(8px)">
            <a href="{{ route('events.index') }}" class="font-display text-sm tracking-widest text-white/80 uppercase">Events</a>
            <a href="{{ route('venues.index') }}" class="font-display text-sm tracking-widest text-white/80 uppercase">Venues</a>
            <hr class="border-white/10 my-2">
            @auth
            <a href="{{ route('dashboard') }}" class="font-display text-sm tracking-widest text-white/80 uppercase">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="font-display text-sm tracking-widest text-[#D62B2B] uppercase">Logout</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="font-display text-sm tracking-widest text-white/80 uppercase">Sign In</a>
            <a href="{{ route('register') }}" class="font-display text-sm tracking-widest text-[#F97316] uppercase">Join The Club</a>
            @endauth
        </div>
    </div>
</header>

{{-- ═══════════════════════ HERO ═══════════════════════ --}}
<section class="bs-hero">
    <video autoplay muted loop playsinline>
        <source src="{{ asset('storage/background.mp4') }}" type="video/mp4">
    </video>
    <div class="bs-hero-overlay"></div>
    <div class="bs-hero-content w-full flex flex-col items-center justify-center text-center px-6" style="min-height:92vh;padding-top:4rem;padding-bottom:4rem">
        <span class="bs-label mb-6" data-animate="fade-down">EST. 2026 — RACQUET &amp; COURT CLUB</span>
        <h1 class="font-display text-white uppercase mb-6" style="font-size:clamp(4.5rem,13vw,11rem);font-weight:700;line-height:0.9;letter-spacing:0.01em" data-animate="fade-up">
            Find Your<br>
            <span style="color:#F97316">Next Match.</span>
        </h1>
        <p class="text-white/60 max-w-2xl mb-10 leading-relaxed" style="font-size:1.05rem" data-animate="fade-up">
            A curated sports club for serious players. Connect with opponents who match your level, book premium courts, and build a network that plays as hard as you do.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14" data-animate="fade-up">
            @guest
                <a href="{{ route('register') }}" class="bs-btn-primary">Become a Member</a>
                <a href="{{ route('events.index') }}" class="bs-btn-outline">Browse Events</a>
            @else
                <a href="{{ route('dashboard') }}" class="bs-btn-primary">Enter Dashboard</a>
                <a href="{{ route('matchmaking') }}" class="bs-btn-outline">Find A Match</a>
            @endguest
        </div>

        {{-- Active Roster stats bar --}}
        <div class="w-full max-w-lg" data-animate="fade-up">
            <div style="border:1px solid rgb(var(--fg) / 0.12);padding:1.75rem 2rem;background:rgba(0,0,0,0.5);backdrop-filter:blur(6px)">
                <p class="bs-label mb-4">Active Roster</p>
                @php
                    $memberCount = \App\Models\User::count();
                    $sportCount = \App\Models\Sport::count();
                    $venueCount = \App\Models\Venue::count();
                @endphp
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="font-display text-white" style="font-size:3rem;font-weight:700;line-height:1" data-counter="{{ $memberCount }}">{{ sprintf('%02d', $memberCount) }}</p>
                        <p class="font-display text-white/40 mt-1" style="font-size:0.58rem;letter-spacing:0.3em;text-transform:uppercase">Members</p>
                    </div>
                    <div class="text-center">
                        <p class="font-display text-white" style="font-size:3rem;font-weight:700;line-height:1" data-counter="{{ $sportCount }}">{{ sprintf('%02d', $sportCount) }}</p>
                        <p class="font-display text-white/40 mt-1" style="font-size:0.58rem;letter-spacing:0.3em;text-transform:uppercase">Sports</p>
                    </div>
                    <div class="text-center">
                        <p class="font-display text-white" style="font-size:3rem;font-weight:700;line-height:1" data-counter="{{ $venueCount }}">{{ sprintf('%02d', $venueCount) }}</p>
                        <p class="font-display text-white/40 mt-1" style="font-size:0.58rem;letter-spacing:0.3em;text-transform:uppercase">Venues</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="bs-divider"></div>

{{-- ═══════════════════════ THE DISCIPLINES ═══════════════════════ --}}
<section style="background:rgb(var(--bg-base));padding:80px 0">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12">
        <div class="flex items-end justify-between mb-14">
            <div>
                <p class="bs-label justify-start mb-3" style="justify-content:flex-start" data-animate="fade-up">
                    <span style="width:50px;height:1px;background:rgb(var(--fg) / 0.25);display:inline-block;flex-shrink:0"></span>
                    The Disciplines
                </p>
                <h2 class="font-display uppercase text-white" style="font-size:clamp(2.5rem,6vw,4.5rem);font-weight:700;line-height:1" data-animate="fade-up">Pick Your Court</h2>
            </div>
            <p class="font-display text-white/30 hidden md:block" style="font-size:0.7rem;letter-spacing:0.2em" data-animate="fade-left">01 / 03</p>
        </div>
        <div class="grid md:grid-cols-3 gap-px" style="background:rgb(var(--border-base))">
            {{-- Tennis --}}
            <article class="bs-sport-tile group" data-animate="fade-up">
                <p class="font-display text-[#F97316]" style="font-size:0.72rem;letter-spacing:0.2em;text-transform:uppercase">SPORT 01</p>
                <h3 class="font-display uppercase text-white mt-4 group-hover:text-[#F97316] transition" style="font-size:2.2rem;font-weight:700">Tennis</h3>
                <p class="text-white/50 mt-4 text-sm leading-relaxed">NTRP rated from 1.0 to 7.0. Singles and doubles — club-level standards.</p>
                <div class="mt-8 pt-6" style="border-top:1px solid rgb(var(--fg) / 0.08);display:flex;align-items:center;justify-content:space-between">
                    <span class="font-display text-white/35" style="font-size:0.6rem;letter-spacing:0.2em;text-transform:uppercase">ALL LEVELS</span>
                    <span class="font-display text-[#F97316] group-hover:translate-x-1 transition-transform inline-block" style="font-size:0.7rem">→</span>
                </div>
            </article>
            {{-- Padel --}}
            <article class="bs-sport-tile group" data-animate="fade-up">
                <p class="font-display text-[#F97316]" style="font-size:0.72rem;letter-spacing:0.2em;text-transform:uppercase">SPORT 02</p>
                <h3 class="font-display uppercase text-white mt-4 group-hover:text-[#F97316] transition" style="font-size:2.2rem;font-weight:700">Padel</h3>
                <p class="text-white/50 mt-4 text-sm leading-relaxed">Doubles only. Levels 1 to 7. The fastest growing racquet sport.</p>
                <div class="mt-8 pt-6" style="border-top:1px solid rgb(var(--fg) / 0.08);display:flex;align-items:center;justify-content:space-between">
                    <span class="font-display text-white/35" style="font-size:0.6rem;letter-spacing:0.2em;text-transform:uppercase">DOUBLES</span>
                    <span class="font-display text-[#F97316] group-hover:translate-x-1 transition-transform inline-block" style="font-size:0.7rem">→</span>
                </div>
            </article>
            {{-- Badminton --}}
            <article class="bs-sport-tile group" data-animate="fade-up">
                <p class="font-display text-[#F97316]" style="font-size:0.72rem;letter-spacing:0.2em;text-transform:uppercase">SPORT 03</p>
                <h3 class="font-display uppercase text-white mt-4 group-hover:text-[#F97316] transition" style="font-size:2.2rem;font-weight:700">Badminton</h3>
                <p class="text-white/50 mt-4 text-sm leading-relaxed">From casual rallies to tournament play. Singles and doubles matches weekly.</p>
                <div class="mt-8 pt-6" style="border-top:1px solid rgb(var(--fg) / 0.08);display:flex;align-items:center;justify-content:space-between">
                    <span class="font-display text-white/35" style="font-size:0.6rem;letter-spacing:0.2em;text-transform:uppercase">SINGLES · DOUBLES</span>
                    <span class="font-display text-[#F97316] group-hover:translate-x-1 transition-transform inline-block" style="font-size:0.7rem">→</span>
                </div>
            </article>
        </div>
    </div>
</section>

<div class="bs-divider"></div>

{{-- ═══════════════════════ HOW THE CLUB WORKS ═══════════════════════ --}}
<section style="background:rgb(var(--bg-surface));padding:80px 0">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12">
        <p class="bs-label mb-4" data-animate="fade-up">The Method</p>
        <h2 class="font-display uppercase text-white text-center mb-16" style="font-size:clamp(2.5rem,6vw,4rem);font-weight:700" data-animate="fade-up">How the Club Works</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['01','Set Your Level','Pick your sport and rate your skill. We use it to score every potential opponent.'],
                ['02','Find The Match','Filter by sport, time, location and play style. Get a match score from 0 to 100.'],
                ['03','Book &amp; Play','Send a challenge, confirm the venue, meet on court. Review after the match.'],
            ] as $step)
            <div class="bs-step" data-animate="fade-up">
                <div class="font-display text-[#F97316]" style="font-size:4.5rem;font-weight:700;line-height:1;opacity:0.25;margin-bottom:1rem">{{ $step[0] }}</div>
                <h3 class="font-display uppercase text-white mb-3" style="font-size:1.5rem;font-weight:700">{!! $step[1] !!}</h3>
                <p class="text-white/50 text-sm leading-relaxed">{!! $step[2] !!}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="bs-divider"></div>

{{-- ═══════════════════════ UPCOMING EVENTS ═══════════════════════ --}}
<section style="background:rgb(var(--bg-base));padding:80px 0">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12">
        <p class="bs-label mb-4" data-animate="fade-up">What's On</p>
        <h2 class="font-display uppercase text-white text-center mb-12" style="font-size:clamp(2.5rem,6vw,4rem);font-weight:700" data-animate="fade-up">Upcoming Events</h2>
        @php
            $upcomingEvents = \App\Models\Event::with('sport')
                ->where('start_time', '>=', now())
                ->orderBy('start_time')
                ->limit(3)
                ->get();
        @endphp
        @if($upcomingEvents->count())
        <div class="grid md:grid-cols-3 gap-6" data-animate="fade-up">
            @foreach($upcomingEvents as $ev)
            @php
                $start    = \Carbon\Carbon::parse($ev->start_time);
                $sportName = $ev->sport?->name ?? null;
                $slots    = $ev->max_players ? ($ev->max_players - ($ev->participants_count ?? 0)) : null;
            @endphp
            <a href="{{ route('events.show', $ev) }}"
               class="block group"
               style="background:rgb(var(--bg-surface));border:1px solid rgb(var(--border-base));transition:border-color 0.25s"
               onmouseover="this.style.borderColor='rgba(249,115,22,0.5)'"
               onmouseout="this.style.borderColor='rgb(var(--border-base))'">

                {{-- Header bar --}}
                <div style="background:rgb(var(--bg-surface-2));border-bottom:1px solid rgb(var(--border-base));padding:1.25rem 1.5rem"
                     class="flex items-center justify-between gap-3">
                    {{-- Date block --}}
                    <div class="flex items-center gap-3">
                        <div style="background:rgba(249,115,22,0.08);border:1px solid rgba(249,115,22,0.25);padding:0.5rem 0.75rem;text-align:center;min-width:3rem">
                            <div class="font-display text-[#F97316] uppercase" style="font-size:0.55rem;letter-spacing:0.18em">{{ $start->format('M') }}</div>
                            <div class="font-display text-white" style="font-size:1.4rem;font-weight:700;line-height:1">{{ $start->format('d') }}</div>
                        </div>
                        <div>
                            <div class="font-display text-white/50 uppercase" style="font-size:0.55rem;letter-spacing:0.15em">{{ $start->format('l') }}</div>
                            <div class="font-display text-white/80" style="font-size:0.75rem;letter-spacing:0.05em">{{ $start->format('H:i') }}</div>
                        </div>
                    </div>
                    {{-- Sport badge --}}
                    @if($sportName)
                    <span class="font-display uppercase shrink-0"
                          style="font-size:0.55rem;letter-spacing:0.15em;border:1px solid rgba(249,115,22,0.3);color:rgba(249,115,22,0.8);padding:0.25rem 0.6rem">
                        {{ $sportName }}
                    </span>
                    @endif
                </div>

                {{-- Body --}}
                <div style="padding:1.25rem 1.5rem">
                    <h3 class="font-display uppercase text-white group-hover:text-[#F97316] transition mb-3"
                        style="font-size:0.95rem;font-weight:600;line-height:1.3;letter-spacing:0.03em">
                        {{ $ev->title }}
                    </h3>

                    @if($ev->venue_name)
                    <p class="font-display text-white/40 uppercase mb-1" style="font-size:0.62rem;letter-spacing:0.12em">
                        {{ $ev->venue_name }}
                    </p>
                    @endif

                    @if($ev->description)
                    <p class="text-white/40 text-xs leading-relaxed mt-2 mb-3"
                       style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                        {{ $ev->description }}
                    </p>
                    @endif

                    {{-- Footer row --}}
                    <div class="flex items-center justify-between mt-4 pt-4" style="border-top:1px solid rgb(var(--border-base))">
                        @if($ev->max_players)
                        <span class="font-display text-white/30 uppercase" style="font-size:0.58rem;letter-spacing:0.12em">
                            {{ $ev->participants()->count() }} / {{ $ev->max_players }} players
                        </span>
                        @else
                        <span></span>
                        @endif
                        <span class="font-display text-[#F97316] group-hover:translate-x-1 transition-transform inline-block"
                              style="font-size:0.62rem;letter-spacing:0.15em;text-transform:uppercase">
                            View Event &rarr;
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center" data-animate="fade-up">
            <p class="font-display text-white/30 uppercase tracking-widest text-sm mb-6">No upcoming events right now</p>
            <a href="{{ route('events.create') }}" class="bs-btn-primary">Host the First One</a>
        </div>
        @endif
    </div>
</section>

<div class="bs-divider"></div>

{{-- ═══════════════════════ CTA ═══════════════════════ --}}
<section style="background:rgb(var(--bg-base));padding:80px 0">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12">
        <div style="border:1px solid rgba(249,115,22,0.4);padding:4rem;text-align:center;background:rgba(249,115,22,0.04)" data-animate="zoom-in">
            <p class="bs-label mb-6">Membership</p>
            <h2 class="font-display uppercase text-white mb-6" style="font-size:clamp(2.5rem,7vw,5rem);font-weight:700;line-height:1">Ready to Join?</h2>
            <p class="text-white/60 max-w-lg mx-auto mb-10 leading-relaxed">Open a free account, set your profile, and start finding your next game today.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <a href="{{ route('register') }}" class="bs-btn-primary">Create Account</a>
                    <a href="{{ route('events.index') }}" class="bs-btn-outline">Browse Events</a>
                @else
                    <a href="{{ route('dashboard') }}" class="bs-btn-primary">Open Dashboard</a>
                @endguest
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════ FOOTER ═══════════════════════ --}}
<footer style="background:rgb(var(--bg-footer));border-top:1px solid rgb(var(--border-base))">
    <div class="max-w-screen-xl mx-auto px-6 lg:px-12 pt-12">
        <div class="grid md:grid-cols-4 gap-10 pb-10">
            <div>
                <div class="leading-none mb-4">
                    <div class="font-display text-white" style="font-size:1.7rem;font-weight:700;letter-spacing:0.06em">PLAYMATE</div>
                    <div class="font-display text-white/30" style="font-size:0.5rem;letter-spacing:0.45em;text-transform:uppercase">SPORTS CLUB</div>
                </div>
                <p class="text-white/40 text-sm leading-relaxed">A premium sports club for players who take their game — and their community — seriously.</p>
            </div>
            <div>
                <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Club</p>
                <ul class="space-y-3">
                    <li><a href="{{ route('events.index') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Events</a></li>
                    <li><a href="{{ route('venues.index') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Venues</a></li>
                    <li><a href="{{ route('matchmaking') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Find Opponent</a></li>
                </ul>
            </div>
            <div>
                <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Member</p>
                <ul class="space-y-3">
                    <li><a href="{{ route('dashboard') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Dashboard</a></li>
                    <li><a href="{{ route('my-events') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">My Schedule</a></li>
                    <li><a href="{{ route('connections.index') }}" class="font-display text-white/50 hover:text-[#F97316] transition" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Network</a></li>
                </ul>
            </div>
            <div>
                <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Discipline</p>
                <ul class="space-y-3">
                    <li class="font-display text-white/50" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Tennis</li>
                    <li class="font-display text-white/50" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Padel</li>
                    <li class="font-display text-white/50" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Badminton</li>
                </ul>
            </div>
        </div>
        <div style="border-top:1px solid rgb(var(--border-base));padding:18px 0">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="font-display text-white/30" style="font-size:0.62rem;letter-spacing:0.2em;text-transform:uppercase">&copy; {{ date('Y') }} PLAYMATE SPORTS CLUB</p>
                <div class="flex items-center gap-4">
                    <p class="font-display text-white/30" style="font-size:0.62rem;letter-spacing:0.2em;text-transform:uppercase">BUILT FOR THE GAME</p>
                        <!-- Social icons removed -->
                </div>
            </div>
        </div>
    </div>
</footer>

@include('partials._animejs-init-landing')
</body>
</html>

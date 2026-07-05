<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PlayMate')</title>
    @include('partials._theme-init-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak]{display:none!important}</style>
    @include('partials._theme-vars')
    @include('partials._chatbot-scripts')
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
                            600: 'rgb(var(--bg-surface-3) / <alpha-value>)',
                            500: 'rgb(var(--bg-surface-4) / <alpha-value>)',
                        },
                        white: 'rgb(var(--fg) / <alpha-value>)',
                        accent: {
                            DEFAULT: '#F97316',
                            50:  '#FFF7ED',
                            100: '#FFEDD5',
                            200: '#FED7AA',
                            300: '#FDBA74',
                            400: '#FB923C',
                            500: '#F97316',
                            600: '#EA580C',
                            700: '#C2410C',
                        },
                        brand: {
                            red: '#D62B2B',
                        },
                    },
                    letterSpacing: {
                        'wider2': '0.18em',
                    },
                },
            },
        };
    </script>
    <style>
        html, body { background-color: rgb(var(--bg-base)); }
        body {
            font-family: 'Inter', sans-serif;
            color: rgb(var(--fg));
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3, h4, .font-display { font-family: 'Oswald', Impact, sans-serif; letter-spacing: 0.02em; }
        .pm-rule { height: 1px; background: linear-gradient(90deg, transparent, rgb(var(--fg) / 0.15), transparent); }
        .pm-rule-dark { height: 1px; background: rgb(var(--border-base)); }
        .pm-tag {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-family: 'Oswald', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #F97316;
            border: 1px solid rgba(249,115,22,0.4);
            background: rgba(249,115,22,0.06);
        }
        .pm-input {
            width: 100%;
            background: rgb(var(--fg) / 0.04);
            border: 1px solid rgb(var(--border-base));
            color: rgb(var(--fg));
            padding: 0.875rem 1rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .pm-input::placeholder { color: rgb(var(--fg) / 0.35); }
        .pm-input:focus { border-color: #F97316; background: rgb(var(--fg) / 0.04); }
        .pm-input option { background-color: rgb(var(--bg-surface)); color: rgb(var(--fg)); }
        .pm-input option:checked { background-color: #F97316; color: #000; }
        .pm-btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            background: #D62B2B;
            color: #fff;
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transition: background 0.2s;
        }
        .pm-btn-primary:hover { background: #F97316; }
        .pm-btn-ghost {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            border: 2px solid rgb(var(--fg) / 0.25);
            color: rgb(var(--fg));
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transition: border-color 0.2s, color 0.2s;
        }
        .pm-btn-ghost:hover { border-color: #F97316; color: #F97316; }
        .pm-card {
            background: rgb(var(--bg-surface));
            border: 1px solid rgb(var(--border-base));
            transition: border-color 0.25s, transform 0.25s;
        }
        .pm-card:hover { border-color: rgba(249,115,22,0.55); transform: translateY(-3px); }
        .pm-section-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            font-size: 0.72rem;
            color: rgb(var(--fg) / 0.5);
        }
        .pm-section-title::before, .pm-section-title::after {
            content: '';
            flex: 1;
            max-width: 50px;
            height: 1px;
            background: rgb(var(--fg) / 0.25);
        }
        .pm-h1 {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.01em;
            line-height: 0.95;
        }
        .pm-stat-num {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            line-height: 1;
        }
        .pm-divider { height: 1px; background: rgb(var(--border-base)); }

        @keyframes pm-reveal-fallback { to { opacity: 1; transform: none; } }
        [data-animate] { animation: pm-reveal-fallback 0.01s 2s forwards; }
    </style>

    @include('partials._animejs-head')
</head>
<body class="min-h-screen bg-ink-900 text-white antialiased">

    {{-- Top bar --}}
    <header style="background:rgb(var(--bg-base) / 0.97);border-bottom:1px solid rgb(var(--border-base));position:sticky;top:0;z-index:50;backdrop-filter:blur(8px)">
        <div class="max-w-screen-xl mx-auto px-6 lg:px-12">
            <div class="flex items-center justify-between gap-6" style="height:68px">

                {{-- Left nav --}}
                @auth
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="font-display text-xs tracking-widest transition {{ request()->routeIs('dashboard') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Dashboard</a>
                    <a href="{{ route('matchmaking') }}" class="font-display text-xs tracking-widest transition {{ request()->routeIs('matchmaking') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Match</a>
                    <a href="{{ route('events.index') }}" class="font-display text-xs tracking-widest transition {{ request()->routeIs('events.*') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Events</a>
                </nav>
                @else
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('events.index') }}" class="font-display text-xs tracking-widest text-white/65 hover:text-white transition" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Events</a>
                    <a href="{{ route('venues.index') }}" class="font-display text-xs tracking-widest text-white/65 hover:text-white transition" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Venues</a>
                </nav>
                @endauth

                {{-- Logo center --}}
                <a href="{{ route('home') }}" class="flex-shrink-0 flex flex-col items-center leading-none group mx-auto lg:mx-0">
                    <span class="font-display text-white group-hover:text-[#F97316] transition" style="font-size:1.45rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase">PLAYMATE</span>
                    <span class="font-display text-white/35" style="font-size:0.5rem;letter-spacing:0.45em;text-transform:uppercase">SPORTS CLUB</span>
                </a>

                {{-- Right nav --}}
                @auth
                <div class="flex items-center gap-5">
                    <nav class="hidden lg:flex items-center gap-8">
                        <a href="{{ route('venues.index') }}" class="font-display transition {{ request()->routeIs('venues.*') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Venues</a>
                        <a href="{{ route('connections.index') }}" class="font-display transition {{ request()->routeIs('connections.*') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Network</a>
                        <a href="{{ route('my-events') }}" class="font-display transition {{ request()->routeIs('my-events*') ? 'text-[#F97316]' : 'text-white/65 hover:text-white' }}" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Schedule</a>
                    </nav>

                    @include('partials._theme-toggle')

                    {{-- Notification bell --}}
                    @php
                        $navNotifications = auth()->user()->notifications()->limit(8)->get();
                        $navUnreadCount = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="relative flex items-center justify-center text-white/65 hover:text-white transition" style="width:32px;height:32px" aria-label="Notifications">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            @if($navUnreadCount > 0)
                                <span class="absolute flex items-center justify-center rounded-full bg-brand-red text-white font-display" style="top:-4px;right:-4px;width:16px;height:16px;font-size:0.55rem;font-weight:700">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
                            @endif
                        </button>
                        <div x-show="open" x-cloak x-transition
                             class="absolute right-0 mt-3 border border-white/10 bg-ink-800 shadow-xl z-50"
                             style="width:22rem">
                            <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                                <p class="font-display text-[0.65rem] tracking-widest text-white/50 uppercase">Notifications</p>
                                @if($navUnreadCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="font-display text-[0.6rem] tracking-widest text-accent uppercase hover:text-white transition">Mark all read</button>
                                </form>
                                @endif
                            </div>
                            <div class="divide-y divide-white/5 overflow-y-auto" style="max-height:24rem">
                                @forelse($navNotifications as $n)
                                <a href="{{ route('notifications.open', $n) }}" class="block px-4 py-3 hover:bg-white/5 transition {{ $n->isUnread() ? 'bg-accent/5' : '' }}">
                                    <p class="text-sm {{ $n->isUnread() ? 'text-white' : 'text-white/50' }}">{{ $n->title }}</p>
                                    @if($n->body)
                                    <p class="text-xs text-white/40 mt-1 truncate">{{ $n->body }}</p>
                                    @endif
                                    <p class="text-[0.6rem] text-white/30 mt-1 uppercase tracking-widest">{{ $n->created_at->diffForHumans() }}</p>
                                </a>
                                @empty
                                <p class="px-4 py-8 text-center text-xs text-white/30 uppercase tracking-widest">No notifications yet</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('profile.index') }}" class="hidden md:flex items-center gap-2 group">
                        <div class="flex items-center justify-center font-display text-sm text-white group-hover:text-[#F97316] transition" style="width:32px;height:32px;border:1px solid rgb(var(--fg) / 0.2);font-weight:600">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="font-display text-white/35 hover:text-[#F97316] transition" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Logout</button>
                    </form>
                </div>
                @else
                <div class="flex items-center gap-5">
                    @include('partials._theme-toggle')
                    <a href="{{ route('login') }}" class="hidden lg:block font-display text-white/65 hover:text-white transition" style="font-size:0.78rem;letter-spacing:0.15em;text-transform:uppercase">Sign In</a>
                    <a href="{{ route('register') }}" class="font-display text-white" style="background:#D62B2B;padding:0.55rem 1.2rem;font-size:0.78rem;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;transition:background 0.2s" onmouseover="this.style.background='#F97316'" onmouseout="this.style.background='#D62B2B'">Join Now ★</a>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-screen-xl mx-auto px-6 lg:px-12 py-12">
        @if(session('success'))
            <div class="mb-8 border-l-2 border-accent bg-white/5 px-6 py-4 font-display text-xs tracking-widest text-white">
                {{ strtoupper(session('success')) }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-8 border-l-2 border-white/30 bg-white/5 px-6 py-4 font-display text-xs tracking-widest text-white/80">
                {{ strtoupper(session('info')) }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 border-l-2 border-red-500 bg-red-500/10 px-6 py-4 font-display text-xs tracking-widest text-white">
                {{ strtoupper(session('error')) }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="background:rgb(var(--bg-footer));border-top:1px solid rgb(var(--border-base));margin-top:4rem">
        <div class="max-w-screen-xl mx-auto px-6 lg:px-12 pt-12 pb-0">
            <div class="grid gap-10 md:grid-cols-4 pb-10">
                <div>
                    <div class="mb-4 leading-none">
                        <div class="font-display text-white" style="font-size:1.6rem;font-weight:700;letter-spacing:0.06em">PLAYMATE</div>
                        <div class="font-display text-white/30" style="font-size:0.5rem;letter-spacing:0.45em;text-transform:uppercase">SPORTS CLUB</div>
                    </div>
                    <p class="text-white/40 text-sm leading-relaxed">Connect, compete, and grow with athletes who match your level and drive.</p>
                </div>
                <div>
                    <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Club</p>
                    <ul class="space-y-2 text-sm text-white/50">
                        <li><a href="{{ route('events.index') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Events</a></li>
                        <li><a href="{{ route('venues.index') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Venues</a></li>
                        <li><a href="{{ route('matchmaking') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Find Opponent</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Member</p>
                    <ul class="space-y-2 text-sm text-white/50">
                        <li><a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Dashboard</a></li>
                        <li><a href="{{ route('my-events') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">My Schedule</a></li>
                        <li><a href="{{ route('connections.index') }}" class="hover:text-[#F97316] transition font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">Network</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-display text-white mb-5" style="font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase">Disciplines</p>
                    <ul class="space-y-2 text-white/50">
                        @foreach(['Tennis','Padel','Badminton','Football','Basketball'] as $s)
                        <li class="font-display" style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase">{{ $s }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div style="border-top:1px solid rgb(var(--border-base));padding:18px 0">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="font-display text-white/30" style="font-size:0.62rem;letter-spacing:0.2em;text-transform:uppercase">&copy; {{ date('Y') }} THE PLAYMATE SERIES. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="font-display text-white/30 hover:text-[#F97316] transition" style="font-size:0.62rem;letter-spacing:0.15em;text-transform:uppercase">Terms &amp; Conditions</a>
                        <div class="flex gap-2">
                            <a href="#" class="flex items-center justify-center text-white/40 hover:text-[#F97316] transition" style="width:28px;height:28px;border:1px solid rgb(var(--fg) / 0.15)">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="#" class="flex items-center justify-center text-white/40 hover:text-[#F97316] transition" style="width:28px;height:28px;border:1px solid rgb(var(--fg) / 0.15)">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
    @include('partials._animejs-init-base')
    @auth
        @php
            $sidebarSessions = \App\Models\ChatSession::where('user_id', auth()->id())
                ->latest('updated_at')
                ->limit(50)
                ->get(['id', 'title', 'updated_at']);
            $activeSessionId = null;
            $activeMessages  = collect();
        @endphp
        @include('partials._chatbot-widget')
    @endauth
</body>
</html>

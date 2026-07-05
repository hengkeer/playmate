<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join PlayMate</title>
    @include('partials._theme-init-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { display: ['Oswald', 'Impact', 'sans-serif'], sans: ['Inter', 'sans-serif'] },
                colors: { accent: { DEFAULT: '#F97316' }, white: 'rgb(var(--fg) / <alpha-value>)' },
            }}
        };
    </script>
    @include('partials._theme-vars')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { background: rgb(var(--bg-base)); color: rgb(var(--fg)); font-family: 'Inter', sans-serif; }
        .font-display, h1, h2, h3 { font-family: 'Oswald', Impact, sans-serif; }

        .pm-tag {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .35rem .85rem;
            font-family: 'Oswald'; font-size: .65rem; font-weight: 500;
            letter-spacing: .25em; text-transform: uppercase;
            color: #F97316; border: 1px solid rgba(249,115,22,.35);
            background: rgba(249,115,22,.06);
        }
        .pm-input {
            width: 100%; background: rgb(var(--fg) / .04);
            border: 1px solid rgb(var(--fg) / .10); color: rgb(var(--fg));
            padding: .85rem 1rem; font-size: .9rem; outline: none;
            transition: border-color .2s, background .2s; font-family: 'Inter', sans-serif;
        }
        .pm-input::placeholder { color: rgb(var(--fg) / .30); }
        .pm-input:focus { border-color: #F97316; background: rgb(var(--fg) / .06); }
        select.pm-input option { background: rgb(var(--bg-surface-3)); color: rgb(var(--fg)); }
        .pm-btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: .95rem 1.5rem; background: #D62B2B; color: #fff;
            font-family: 'Oswald'; font-size: .78rem; font-weight: 600;
            letter-spacing: .2em; text-transform: uppercase; width: 100%;
            border: none; cursor: pointer; transition: background .2s;
        }
        .pm-btn-primary:hover { background: #F97316; }
        .pm-label {
            font-family: 'Oswald'; font-weight: 500; text-transform: uppercase;
            letter-spacing: .18em; font-size: .65rem; color: rgb(var(--fg) / .45);
            display: block; margin-bottom: .5rem;
        }
    </style>
</head>
<body class="min-h-screen flex">
    <div class="fixed top-6 right-6 z-50">@include('partials._theme-toggle')</div>

    {{-- Left panel — branding --}}
    <div class="hidden lg:flex flex-col justify-between w-[42%] p-14" style="border-right:1px solid rgb(var(--border-base));background:rgb(var(--bg-base))">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
            <div class="h-10 w-10 border border-[#F97316] flex items-center justify-center">
                <span class="font-display text-[#F97316] text-sm tracking-widest">PM</span>
            </div>
            <div class="flex flex-col leading-none">
                <span class="font-display text-xl tracking-wider text-white">PLAYMATE</span>
                <span class="font-display text-[0.55rem] tracking-[.4em] text-white/30">SPORTS CLUB</span>
            </div>
        </a>

        {{-- Big headline --}}
        <div>
            <p class="font-display text-[0.65rem] tracking-[.3em] text-[#F97316] uppercase mb-4">New Membership</p>
            <h2 class="font-display uppercase text-white leading-none" style="font-size:clamp(2.8rem,5vw,4.5rem);font-weight:700">
                Join the<br><span style="color:#F97316">Club.</span>
            </h2>
            <p class="text-white/40 text-sm mt-6 leading-relaxed max-w-xs">
                Create your profile, pick your sport, and start finding opponents in your area.
            </p>
            <div class="mt-8 space-y-3">
                @foreach(['Smart Matchmaking Engine', 'Event Hosting & Group Chat', 'Private Messaging', 'Venue Directory'] as $f)
                <div class="flex items-center gap-3">
                    <span style="width:6px;height:6px;background:#F97316;display:inline-block;flex-shrink:0"></span>
                    <span class="font-display text-[0.65rem] tracking-widest text-white/50 uppercase">{{ $f }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <p class="font-display text-[0.58rem] tracking-widest text-white/20 uppercase">
            © {{ date('Y') }} PlayMate · All Rights Reserved
        </p>
    </div>

    {{-- Right panel — form --}}
    <div class="flex-1 flex items-center justify-center p-6 lg:p-16 overflow-y-auto">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex justify-center mb-10 lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <div class="h-9 w-9 border border-[#F97316] flex items-center justify-center">
                        <span class="font-display text-[#F97316] text-sm tracking-widest">PM</span>
                    </div>
                    <span class="font-display text-lg tracking-wider text-white">PLAYMATE</span>
                </a>
            </div>

            <p class="font-display text-[0.65rem] tracking-[.3em] text-[#F97316] uppercase mb-2">Apply for Membership</p>
            <h1 class="font-display text-4xl uppercase text-white mb-8" style="font-weight:700">Create Account</h1>

            @if($errors->any())
                <div class="mb-6 border-l-2 border-red-500 bg-red-500/10 px-4 py-3 font-display text-xs tracking-widest text-red-400 uppercase">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="pm-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="pm-input" placeholder="e.g. Rizky Pratama">
                </div>

                <div>
                    <label class="pm-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="pm-input" placeholder="you@example.com">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="pm-label">Password</label>
                        <input type="password" name="password" required
                            class="pm-input" placeholder="Min. 8 chars">
                    </div>
                    <div>
                        <label class="pm-label">Confirm</label>
                        <input type="password" name="password_confirmation" required
                            class="pm-input" placeholder="Repeat">
                    </div>
                </div>

                <div>
                    <label class="pm-label">Play Style <span style="color:rgb(var(--fg) / .25);letter-spacing:normal;font-family:'Inter'" class="normal-case">optional</span></label>
                    <select name="play_style" class="pm-input">
                        <option value="">Not sure yet</option>
                        <option value="casual"       {{ old('play_style') === 'casual'       ? 'selected' : '' }}>Casual — just for fun</option>
                        <option value="competitive"  {{ old('play_style') === 'competitive'  ? 'selected' : '' }}>Competitive — I like to win</option>
                    </select>
                </div>

                <div class="pt-1">
                    <button type="submit" class="pm-btn-primary">Create Membership →</button>
                </div>
            </form>

            <div class="my-8" style="height:1px;background:linear-gradient(90deg,transparent,rgb(var(--fg) / .08),transparent)"></div>

            <p class="text-center text-sm text-white/40">
                Already a member?
                <a href="{{ route('login') }}" class="text-[#F97316] font-display text-xs tracking-widest hover:text-white ml-1 transition">
                    SIGN IN →
                </a>
            </p>
        </div>
    </div>

</body>
</html>

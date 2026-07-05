@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-12">

    {{-- Hero — animates on page load --}}
    <section class="pm-anim-load border border-white/10 bg-ink-800/40">
        <div class="grid lg:grid-cols-12 gap-0">
            <div class="lg:col-span-8 p-10 lg:p-16 border-r border-white/10">
                <span class="pm-tag" data-animate="fade-down">Member Dashboard</span>
                <p class="font-display text-xs tracking-widest text-white/40 mt-6" data-animate="fade-up">WELCOME BACK</p>
                <h1 class="font-display text-5xl md:text-6xl uppercase mt-2" data-animate="fade-up">Build Your<br><span class="text-accent">Sports Career</span></h1>
                <p class="text-white/60 mt-6 max-w-xl leading-relaxed" data-animate="fade-up">Find opponents by skill, time and location. Host events. Join a curated community of active players.</p>
                <div class="flex flex-wrap gap-3 mt-10" data-animate="fade-up">
                    <a href="{{ route('matchmaking') }}" class="pm-btn-primary">Find Opponent</a>
                    <a href="{{ route('events.create') }}" class="pm-btn-ghost">Host Event</a>
                    <a href="{{ route('venues.index') }}" class="pm-btn-ghost">Browse Venues</a>
                </div>
            </div>
            <div class="lg:col-span-4 p-10 lg:p-16 bg-accent/5" data-animate="fade-left">
                <p class="pm-section-title">This Week</p>
                <div class="space-y-6 mt-6">
                    <div>
                        <p class="pm-stat-num text-5xl text-white" data-counter="0">00</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">MATCHES BOOKED</p>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <p class="pm-stat-num text-5xl text-white" data-counter="0">00</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">CONNECTIONS</p>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <p class="pm-stat-num text-5xl text-accent" data-counter="0">00</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">EVENTS HOSTED</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Quick actions — scroll-triggered --}}
    <section>
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title" data-animate="fade-up">Quick Actions</p>
                <h2 class="font-display text-3xl uppercase mt-3" data-animate="fade-up">What Will You Do Today?</h2>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-white/10">
            <a href="{{ route('matchmaking') }}" class="group bg-ink-900 p-8 block transition hover:bg-ink-800" data-animate="fade-up">
                <div class="flex items-center justify-between">
                    <p class="font-display text-xs tracking-widest text-accent">01</p>
                    <span class="font-display text-accent text-2xl transition-transform group-hover:translate-x-1">→</span>
                </div>
                <h3 class="font-display text-2xl uppercase mt-8 group-hover:text-accent transition">Matchmaking</h3>
                <p class="text-white/50 text-sm mt-3 leading-relaxed">Smart opponent scoring by skill, time, and location.</p>
            </a>
            <a href="{{ route('events.create') }}" class="group bg-ink-900 p-8 block transition hover:bg-ink-800" data-animate="fade-up">
                <div class="flex items-center justify-between">
                    <p class="font-display text-xs tracking-widest text-accent">02</p>
                    <span class="font-display text-accent text-2xl transition-transform group-hover:translate-x-1">→</span>
                </div>
                <h3 class="font-display text-2xl uppercase mt-8 group-hover:text-accent transition">Host Event</h3>
                <p class="text-white/50 text-sm mt-3 leading-relaxed">Create a sports event and manage participants.</p>
            </a>
            <a href="{{ route('venues.index') }}" class="group bg-ink-900 p-8 block transition hover:bg-ink-800" data-animate="fade-up">
                <div class="flex items-center justify-between">
                    <p class="font-display text-xs tracking-widest text-accent">03</p>
                    <span class="font-display text-accent text-2xl transition-transform group-hover:translate-x-1">→</span>
                </div>
                <h3 class="font-display text-2xl uppercase mt-8 group-hover:text-accent transition">Venues</h3>
                <p class="text-white/50 text-sm mt-3 leading-relaxed">Browse courts and venue availability.</p>
            </a>
            <a href="{{ route('my-events') }}" class="group bg-ink-900 p-8 block transition hover:bg-ink-800" data-animate="fade-up">
                <div class="flex items-center justify-between">
                    <p class="font-display text-xs tracking-widest text-accent">04</p>
                    <span class="font-display text-accent text-2xl transition-transform group-hover:translate-x-1">→</span>
                </div>
                <h3 class="font-display text-2xl uppercase mt-8 group-hover:text-accent transition">My Events</h3>
                <p class="text-white/50 text-sm mt-3 leading-relaxed">View your joined events and history.</p>
            </a>
        </div>
    </section>

    {{-- Recent / Activity — scroll-triggered --}}
    <section>
        <div class="border border-white/10 p-10" data-animate="fade-up">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="pm-section-title">Recent Activity</p>
                    <h2 class="font-display text-3xl uppercase mt-3">Latest On Court</h2>
                </div>
                <a href="{{ route('my-events') }}" class="font-display text-xs tracking-widest text-accent hover:text-accent-400">VIEW ALL →</a>
            </div>
            <div class="border-t border-white/10">
                <p class="text-white/40 text-sm py-10 text-center font-display tracking-widest text-[0.7rem]">NO RECENT ACTIVITY YET — START BY FINDING AN OPPONENT</p>
            </div>
        </div>
    </section>

</div>

@push('scripts')
    @include('partials._animejs-init-base')
@endpush
@endsection

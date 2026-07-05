@extends('layouts.app')

@section('title', 'My Network')

@section('content')
<div class="space-y-12">

    {{-- Hero --}}
    <section class="border border-white/10 bg-ink-800/40">
        <div class="grid lg:grid-cols-12 gap-0">
            <div class="lg:col-span-8 p-10 lg:p-14 border-r border-white/10">
                <span class="pm-tag">Network</span>
                <p class="font-display text-xs tracking-widest text-white/40 mt-6">YOUR SPORTS ROSTER</p>
                <h1 class="font-display text-4xl md:text-5xl uppercase mt-2">Your<br><span class="text-accent">Sports Network</span></h1>
                <p class="text-white/60 mt-6 max-w-xl leading-relaxed">Manage your connections — accept requests, chat with teammates, and build your court community.</p>
            </div>
            <div class="lg:col-span-4 p-10 lg:p-14 bg-accent/5">
                <p class="pm-section-title">Network Stats</p>
                <div class="space-y-6 mt-6">
                    <div>
                        <p class="pm-stat-num text-5xl text-white">{{ $incoming->count() }}</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">PENDING REQUESTS</p>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <p class="pm-stat-num text-5xl text-accent">{{ $connections->count() }}</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">ACTIVE CONNECTIONS</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Incoming Requests --}}
    @if($incoming->count() > 0)
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Pending</p>
                <h2 class="font-display text-2xl uppercase mt-2">Incoming Requests</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase bg-accent/10 px-3 py-1 border border-accent/30">{{ $incoming->count() }} NEW</span>
        </div>

        <div class="space-y-3">
            @foreach($incoming as $conn)
                @php $requester = $conn->requester; @endphp
                <div class="pm-card p-5 flex items-center gap-4">
                    {{-- Avatar --}}
                    <div class="h-12 w-12 border border-white/15 flex items-center justify-center font-display text-base text-white shrink-0">
                        {{ strtoupper(substr($requester->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-lg uppercase text-white">{{ $requester->name }}</h3>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 uppercase mt-1">
                            @foreach($requester->userSports->take(3) as $us)
                                {{ $us->sport->name ?? '' }}
                            @endforeach
                            {{ $requester->home_address ?? '' }}
                            &middot;
                            SENT {{ $requester->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <form method="POST" action="{{ route('connections.update', $conn) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="accept">
                            <button type="submit" class="bg-accent text-ink-900 py-3 px-5 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('connections.update', $conn) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="decline">
                            <button type="submit" class="bg-ink-900 border border-white/10 text-white/60 py-3 px-5 font-display text-[0.7rem] tracking-widest uppercase hover:text-white transition">Decline</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Sent Requests --}}
    @if($sent->count() > 0)
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Outgoing</p>
                <h2 class="font-display text-2xl uppercase mt-2">Sent Requests</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase bg-amber-500/10 px-3 py-1 border border-amber-500/30 text-amber-400">{{ $sent->count() }} OUT</span>
        </div>

        <div class="space-y-3">
            @foreach($sent as $conn)
                @php $receiver = $conn->receiver; @endphp
                <div class="border border-amber-500/20 bg-ink-800/40 p-4 flex items-center gap-4">
                    <div class="h-10 w-10 border border-amber-500/30 flex items-center justify-center font-display text-sm text-amber-400 shrink-0">
                        {{ strtoupper(substr($receiver->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-base uppercase text-white">{{ $receiver->name }}</h3>
                        <p class="font-display text-[0.6rem] tracking-widest text-amber-500/70 uppercase mt-1">
                            AWAITING RESPONSE &middot; {{ $receiver->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="font-display text-[0.65rem] tracking-widest text-amber-400 uppercase">PENDING</span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Active Connections --}}
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Connected</p>
                <h2 class="font-display text-2xl uppercase mt-2">My Connections</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase">{{ $connections->count() }} TOTAL</span>
        </div>

        @if($connections->isEmpty())
            <div class="border border-white/10 bg-ink-800/30 p-16 text-center">
                <p class="font-display text-2xl uppercase text-white/60">No Connections Yet</p>
                <p class="text-white/40 text-sm mt-3 max-w-md mx-auto">Find opponents and connect with them to start building your network.</p>
                <a href="{{ route('matchmaking') }}" class="pm-btn-primary inline-flex mt-6 text-[0.7rem] py-3 px-5">
                    Find Opponents
                </a>
            </div>
        @else
            <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
                @foreach($connections as $conn)
                    @php $other = $conn->getOtherUser(Auth::user()); @endphp
                    <article class="pm-card p-6 flex flex-col gap-4">
                        {{-- Header --}}
                        <div class="flex items-start gap-3">
                            <div class="h-12 w-12 border border-white/15 flex items-center justify-center font-display text-base text-white shrink-0">
                                {{ strtoupper(substr($other->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-display text-lg uppercase text-white truncate">{{ $other->name }}</h3>
                                <p class="font-display text-[0.65rem] tracking-widest text-white/40 uppercase mt-1">
                                    @foreach($other->userSports->take(3) as $us)
                                        {{ $us->sport->name ?? '' }}
                                    @endforeach
                                    {{ $other->home_address ?? '' }}
                                </p>
                            </div>
                        </div>

                        {{-- Sports tags --}}
                        @if($other->sports->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($other->sports->take(3) as $sport)
                                <span class="border border-white/10 bg-ink-900 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/50 uppercase">
                                    {{ $sport->name }}
                                    @if($sport->pivot->skill_number) LV {{ $sport->pivot->skill_number }}
                                @endif
                            </span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Action buttons --}}
                        <div class="mt-auto pt-2 flex flex-col gap-px bg-white/10">
                            <a href="{{ route('connections.chat', $conn) }}"
                               class="bg-accent text-ink-900 text-center py-3 font-display text-[0.7rem] tracking-widest uppercase hover:bg-accent-400 transition">
                                CHAT
                            </a>
                            <div class="grid grid-cols-2 gap-px">
                                <a href="{{ route('player.profile', $other) }}"
                                   class="bg-ink-900 text-center py-3 font-display text-[0.65rem] tracking-widest text-white/60 uppercase hover:text-accent transition">
                                    VIEW PROFILE
                                </a>
                                <a href="{{ route('reviews.create', ['user_id' => $other->id, 'source_type' => 'connection', 'source_id' => $conn->id]) }}"
                                   class="bg-ink-900 text-center py-3 font-display text-[0.65rem] tracking-widest text-amber-400 uppercase hover:text-amber-300 transition">
                                    REVIEW
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection

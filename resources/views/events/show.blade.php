@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="space-y-12">

    {{-- Back + Header --}}
    <header class="border-b border-white/10 pb-10">
        <a href="{{ route('events.index') }}"
           class="inline-flex items-center gap-2 font-display text-[0.65rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
            &larr; All Events
        </a>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.5fr_1fr] lg:items-end">
            <div class="space-y-4">
                <p class="font-display text-[0.65rem] tracking-widest text-accent uppercase">
                    {{ $event->sport?->name ?? 'General Sport' }}
                    @if($event->approval_required)
                        &nbsp;&middot;&nbsp;<span class="text-amber-400">Approval Required</span>
                    @endif
                </p>
                <h1 class="font-display text-5xl uppercase leading-tight text-white">
                    {{ $event->title }}
                </h1>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1 text-sm text-white/50">
                    <span>{{ $event->venue_name ?? 'TBD' }}</span>
                    <span class="text-white/20">&middot;</span>
                    <span>{{ $event->start_time->format('D, M j') }} &middot; {{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }}</span>
                    <span class="text-white/20">&middot;</span>
                    <span>Hosted by <strong class="text-white">{{ $event->host->name }}</strong></span>
                    <span class="border border-white/20 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/60 uppercase">
                        {{ $event->match_type === 'doubles' ? 'Doubles' : 'Singles' }}
                    </span>
                </div>
            </div>

            @if($event->isHostedBy(auth()->user()))
                <form action="{{ route('events.cancel', $event) }}" method="POST"
                      onsubmit="return confirm('Cancel this event?');"
                      class="lg:justify-self-end">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="border border-red-400/40 px-6 py-3 font-display text-[0.65rem] tracking-widest text-red-300 hover:bg-red-500/10 uppercase transition">
                        Cancel Event
                    </button>
                </form>
            @endif
        </div>
    </header>

    {{-- Action Bar --}}
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center">
        @auth
            @php
                $hasJoined  = $event->hasUser(auth()->user());
                $isApproved = $event->isUserApproved(auth()->user());
                $isHost     = $event->isHostedBy(auth()->user());
            @endphp

            @if($isHost)
                <a href="{{ route('chat', $event) }}"
                   class="pm-btn-primary inline-flex items-center gap-3">
                    Open Group Chat &rarr;
                </a>
            @elseif(!$hasJoined)
                <form action="{{ route('events.join', $event) }}" method="POST">
                    @csrf
                    <button type="submit" class="pm-btn-primary">
                        {{ $event->approval_required ? 'Request to Join' : 'Join Event' }}
                    </button>
                </form>
            @elseif($isApproved)
                <a href="{{ route('chat', $event) }}"
                   class="pm-btn-primary inline-flex items-center gap-3">
                    Open Group Chat &rarr;
                </a>
                <form action="{{ route('events.leave', $event) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="pm-btn-ghost border-red-400/40 text-red-300 hover:bg-red-500/10">
                        Leave Event
                    </button>
                </form>
            @else
                <span class="border border-amber-400/40 bg-amber-500/10 px-6 py-3 font-display text-[0.65rem] tracking-widest text-amber-300 uppercase">
                    Awaiting Host Approval
                </span>
            @endif
        @else
            <a href="{{ route('login') }}" class="pm-btn-primary">Sign in to Join</a>
        @endauth
    </section>

    {{-- Info Grid --}}
    <section class="grid gap-px sm:grid-cols-2 lg:grid-cols-4" style="background:rgb(var(--bg-base))">
        <div class="bg-ink-900 p-6 space-y-1">
            <p class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase">Date</p>
            <p class="font-display text-2xl uppercase text-white">{{ $event->start_time->format('D, M j') }}</p>
        </div>
        <div class="bg-ink-900 p-6 space-y-1">
            <p class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase">Time</p>
            <p class="font-display text-2xl uppercase text-white">{{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }}</p>
        </div>
        <div class="bg-ink-900 p-6 space-y-1">
            <p class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase">Format</p>
            <p class="font-display text-2xl uppercase text-white">{{ $event->match_type === 'doubles' ? 'Doubles' : 'Singles' }}</p>
        </div>
        <div class="bg-ink-900 p-6 space-y-1">
            <p class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase">Price</p>
            <p class="font-display text-2xl uppercase text-accent">
                @if($event->price) Rp {{ number_format($event->price, 0, ',', '.') }} @else Free @endif
            </p>
        </div>
    </section>

    {{-- Host: Pending Approval Requests --}}
    @if($event->isHostedBy(auth()->user()))
        @php $pendingParticipants = $event->participants()->where('status','pending')->where('is_invite', false)->with('user')->get(); @endphp
        @if($pendingParticipants->isNotEmpty())
        <section class="border border-amber-400/30 bg-amber-500/5 p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-display text-[0.6rem] tracking-widest text-amber-400 uppercase">Approval Queue</p>
                    <h2 class="font-display text-xl uppercase text-white mt-1">
                        {{ $pendingParticipants->count() }} Pending Request{{ $pendingParticipants->count() > 1 ? 's' : '' }}
                    </h2>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($pendingParticipants as $p)
                <div class="bg-ink-900 border border-white/10 p-4 flex items-center gap-4">
                    <div class="h-10 w-10 border border-white/15 flex items-center justify-center font-display text-sm text-accent shrink-0">
                        {{ strtoupper(substr($p->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-display text-sm uppercase text-white truncate">{{ $p->user->name }}</p>
                        <p class="font-display text-[0.55rem] tracking-widest text-white/30 uppercase">{{ $p->joined_at?->diffForHumans() }}</p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <form action="{{ route('host-requests.approve', $p) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="font-display text-[0.55rem] tracking-widest uppercase px-3 py-1.5 border border-accent/40 text-accent hover:bg-accent hover:text-ink-900 transition">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('host-requests.reject', $p) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="font-display text-[0.55rem] tracking-widest uppercase px-3 py-1.5 border border-white/20 text-white/40 hover:border-red-400/40 hover:text-red-300 transition">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    @endif

    {{-- Participants Roster --}}
    <section class="space-y-6 border-t border-white/10 pt-10">
        <div>
            <p class="pm-section-title">Roster</p>
            <h2 class="font-display text-3xl uppercase mt-2">
                Participants
                <span class="text-accent">{{ $event->approvedParticipants()->count() }} / {{ $event->max_slots }}</span>
            </h2>
        </div>

        @php $approvedList = $event->approvedParticipants()->with('user')->get(); @endphp

        @if($approvedList->isEmpty())
            <div class="border border-dashed border-white/15 p-12 text-center">
                <p class="font-display text-sm uppercase text-white/40">No confirmed participants yet</p>
            </div>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($approvedList as $p)
                <div class="flex items-center gap-4 border border-white/10 bg-ink-900 p-5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center bg-accent/10 border border-accent/30 font-display text-lg text-accent">
                        {{ strtoupper(substr($p->user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-display text-sm uppercase text-white truncate">{{ $p->user->name }}</p>
                        @if($p->user_id === $event->host_id)
                            <p class="font-display text-[0.55rem] tracking-widest text-accent uppercase">Host</p>
                        @else
                            <p class="font-display text-[0.55rem] tracking-widest text-white/30 uppercase">Confirmed</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Details & Venue --}}
    <section class="border-t border-white/10 pt-10">
        <div class="mb-8">
            <p class="pm-section-title">About</p>
            <h2 class="font-display text-2xl uppercase mt-2 text-white">Event Details & Venue</h2>
        </div>

        <div class="grid gap-6 lg:grid-cols-2 items-start">
            {{-- Description Card --}}
            <div class="bg-ink-900 border border-white/10 p-8 h-full">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="font-display text-lg uppercase text-white">Description</h3>
                </div>
                @if($event->description)
                    <div class="prose prose-invert prose-sm max-w-none text-white/70 text-base leading-relaxed">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                @else
                    <p class="text-white/40 italic">No additional description provided by the host.</p>
                @endif
            </div>

            {{-- Venue Card --}}
            @php
                $venue = \App\Models\Venue::where('name', $event->venue_name)->first();
            @endphp
            
            <div>
                @if($venue)
                    <div class="border border-white/10 bg-ink-900 overflow-hidden group h-full">
                        @if($venue->image_url)
                            <div class="relative h-64 w-full overflow-hidden">
                                <img src="{{ asset('storage/' . $venue->image_url) }}" onerror="this.src='{{ $venue->image_url }}'" alt="{{ $venue->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-transparent to-transparent opacity-90"></div>
                            </div>
                        @endif
                        <div class="p-8 relative -mt-16">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-accent text-ink-900 font-display text-[0.65rem] tracking-widest uppercase mb-3">
                                Venue
                            </div>
                            <h4 class="font-display text-2xl text-white uppercase">{{ $venue->name }}</h4>
                            <p class="text-base text-white/60 mt-3 flex items-start gap-3">
                                <svg class="w-5 h-5 text-accent mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $venue->address }}</span>
                            </p>
                            @if($venue->open_hours)
                                <p class="text-sm text-white/40 mt-3 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-white/40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $venue->open_hours }}
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="border border-white/10 bg-ink-900 p-8 h-full flex items-start gap-4">
                        <div class="bg-accent/10 p-3 text-accent rounded-full shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-display text-2xl text-white uppercase">{{ $event->venue_name }}</h4>
                            <p class="text-base text-white/60 mt-2">Detailed address not available for this custom location.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection

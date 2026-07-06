@extends('layouts.app')

@section('title', 'My Schedule')

@section('content')
<div class="space-y-12">

    {{-- Hero --}}
    <section class="border border-white/10 bg-ink-800/40">
        <div class="grid lg:grid-cols-12 gap-0">
            <div class="lg:col-span-8 p-10 lg:p-14 border-r border-white/10">
                <span class="pm-tag">Schedule</span>
                <p class="font-display text-xs tracking-widest text-white/40 mt-6">YOUR COURT CALENDAR</p>
                <h1 class="font-display text-4xl md:text-5xl uppercase mt-2">My<br><span class="text-accent">Schedule</span></h1>
                <p class="text-white/60 mt-6 max-w-xl leading-relaxed">All your event activity — joined, hosted, pending, and history.</p>
            </div>
            <div class="lg:col-span-4 p-10 lg:p-14 bg-accent/5">
                <p class="pm-section-title">Overview</p>
                <div class="space-y-6 mt-6">
                    <div>
                        <p class="pm-stat-num text-5xl text-white">{{ str_pad($upcoming->count(), 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">UPCOMING</p>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <p class="pm-stat-num text-5xl text-accent">{{ str_pad($hosted->count(), 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-2">HOSTED</p>
                    </div>
                    @if($pendingRequestsCount > 0)
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <a href="{{ route('my-events.host-requests') }}" class="block">
                            <p class="pm-stat-num text-5xl text-amber-400">{{ str_pad($pendingRequestsCount, 2, '0', STR_PAD_LEFT) }}</p>
                            <p class="font-display text-[0.65rem] tracking-widest text-amber-400 mt-2">PENDING REQUESTS &rarr;</p>
                        </a>
                    </div>
                    @endif
                    @if($incomingChallengesCount > 0)
                    <div class="h-px bg-white/10"></div>
                    <div>
                        <a href="#incoming-challenges" class="block">
                            <p class="pm-stat-num text-5xl text-brand-red">{{ str_pad($incomingChallengesCount, 2, '0', STR_PAD_LEFT) }}</p>
                            <p class="font-display text-[0.65rem] tracking-widest text-brand-red mt-2">CHALLENGES AWAITING YOU &rarr;</p>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Upcoming Events --}}
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Next On Court</p>
                <h2 class="font-display text-2xl uppercase mt-2">Upcoming Events</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase">{{ $upcoming->count() }} TOTAL</span>
        </div>

        @if($upcoming->isEmpty())
            <div class="border border-white/10 bg-ink-800/30 p-16 text-center">
                <p class="font-display text-2xl uppercase text-white/60">No Upcoming Events</p>
                <p class="text-white/40 text-sm mt-3 max-w-md mx-auto">Browse open sessions and join your next match.</p>
                <a href="{{ route('events.index') }}" class="pm-btn-primary inline-flex mt-6 text-[0.7rem] py-3 px-5">
                    Browse Events
                </a>
            </div>
        @else
            <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
                @foreach($upcoming as $event)
                    @include('events.partials.event-card', ['event' => $event, 'showChat' => true])
                @endforeach
            </div>
        @endif
    </section>

    {{-- Hosted Events --}}
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Hosting</p>
                <h2 class="font-display text-2xl uppercase mt-2">Your Hosted Events</h2>
            </div>
            <div class="flex items-center gap-3">
                @if($pendingRequestsCount > 0)
                    <a href="{{ route('my-events.host-requests') }}"
                       class="font-display text-[0.7rem] tracking-widest text-amber-400 uppercase border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 hover:bg-amber-500/20 transition">
                        {{ $pendingRequestsCount }} PENDING
                    </a>
                @endif
                <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase">{{ $hosted->count() }} TOTAL</span>
            </div>
        </div>

        @if($hosted->isEmpty())
            <div class="border border-white/10 bg-ink-800/30 p-16 text-center">
                <p class="font-display text-2xl uppercase text-white/60">No Hosted Events</p>
                <p class="text-white/40 text-sm mt-3 max-w-md mx-auto">Create your first event and start building your roster.</p>
                <a href="{{ route('events.create') }}" class="pm-btn-primary inline-flex mt-6 text-[0.7rem] py-3 px-5">
                    Host an Event
                </a>
            </div>
        @else
            <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
                @foreach($hosted as $event)
                    @include('events.partials.event-card', ['event' => $event, 'isHost' => true, 'showChat' => true])
                @endforeach
            </div>
        @endif
    </section>

    {{-- Incoming Challenges --}}
    @if($incomingChallenges->isNotEmpty())
    <section id="incoming-challenges">
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">Incoming</p>
                <h2 class="font-display text-2xl uppercase mt-2">Challenges Awaiting You</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-brand-red uppercase">{{ $incomingChallenges->count() }} PENDING</span>
        </div>
        <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($incomingChallenges as $event)
                @php 
                    $participant = $event->participants
                        ->where('user_id', auth()->id())
                        ->where('status', 'pending')
                        ->where('is_invite', true)
                        ->first(); 
                @endphp
                <div class="bg-ink-900 flex flex-col border border-brand-red/30">
                    @include('events.partials.event-card', ['event' => $event])
                    <div class="flex gap-px" style="background:rgb(var(--bg-base))">
                        <form method="POST" action="{{ route('challenges.accept', $participant) }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-ink-900 px-4 py-3.5 font-display text-[0.6rem] tracking-widest uppercase text-accent hover:bg-accent hover:text-ink-900 transition">
                                Accept
                            </button>
                        </form>
                        <form method="POST" action="{{ route('challenges.decline', $participant) }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-ink-900 px-4 py-3.5 font-display text-[0.6rem] tracking-widest uppercase text-white/40 hover:text-red-300 transition">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Pending & Waiting --}}
    @if($pending->isNotEmpty() || $waiting->isNotEmpty())
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">In Queue</p>
                <h2 class="font-display text-2xl uppercase mt-2">Pending &amp; Waiting</h2>
            </div>
        </div>
        <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($pending as $event)
                <div class="relative">
                    @include('events.partials.event-card', ['event' => $event])
                    <div class="absolute top-3 right-3 border border-amber-500/40 bg-amber-500/10 px-2.5 py-1 font-display text-[0.6rem] tracking-widest text-amber-400 uppercase">
                        Awaiting Host
                    </div>
                </div>
            @endforeach
            @foreach($waiting as $event)
                <div class="relative">
                    @include('events.partials.event-card', ['event' => $event])
                    <div class="absolute top-3 right-3 border border-white/20 bg-ink-900 px-2.5 py-1 font-display text-[0.6rem] tracking-widest text-white/60 uppercase">
                        On Waiting List
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Past Events --}}
    @if($past->isNotEmpty())
    <section>
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="pm-section-title">History</p>
                <h2 class="font-display text-2xl uppercase mt-2">Past Events</h2>
            </div>
            <span class="font-display text-[0.7rem] tracking-widest text-white/50 uppercase">{{ $past->count() }} ARCHIVED</span>
        </div>
        <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3 opacity-70" style="background:rgb(var(--bg-base))">
            @foreach($past as $event)
                @include('events.partials.event-card', ['event' => $event])
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

@extends('layouts.app')

@section('title', 'Host Requests')

@section('content')
<div class="space-y-10">

    {{-- Header --}}
    <header class="border-b border-white/10 pb-8">
        <a href="{{ route('my-events') }}"
           class="inline-flex items-center gap-2 font-display text-[0.65rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
            &larr; My Schedule
        </a>
        <p class="pm-section-title mt-6">Approval Queue</p>
        <h1 class="font-display text-4xl uppercase mt-3">
            Host <span class="text-accent">Requests</span>
        </h1>
        <p class="text-white/50 text-sm mt-3 max-w-lg">Pending join requests for your events — approve or decline players.</p>
    </header>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="border border-green-500/30 bg-green-500/10 px-5 py-3 font-display text-xs tracking-widest text-green-400 uppercase">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="border border-red-500/30 bg-red-500/10 px-5 py-3 font-display text-xs tracking-widest text-red-400 uppercase">
            {{ session('error') }}
        </div>
    @endif

    {{-- Pending Requests --}}
    <section class="space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-xl uppercase text-white">Pending Requests</h2>
            @if($pendingRequests->isNotEmpty())
                <span class="font-display text-[0.65rem] tracking-widest text-amber-400 border border-amber-500/30 bg-amber-500/10 px-3 py-1 uppercase">
                    {{ $pendingRequests->count() }} waiting
                </span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
            <div class="border border-dashed border-white/15 bg-ink-900/30 p-16 text-center">
                <p class="font-display text-xl uppercase text-white/60">All caught up</p>
                <p class="text-white/40 text-sm mt-2">No pending requests right now.</p>
                <a href="{{ route('my-events') }}" class="pm-btn-ghost inline-flex mt-6 text-[0.7rem] py-3 px-5">
                    Back to My Schedule
                </a>
            </div>
        @else
            <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
                @foreach($pendingRequests as $participant)
                    @php
                        $event  = $participant->event;
                        $player = $participant->user;
                        $isFull = $event->isFull();
                    @endphp
                    <div class="bg-ink-900 flex flex-col">

                        {{-- Event banner --}}
                        <div class="border-b border-white/10 p-5 space-y-1" style="background:rgb(var(--bg-surface))">
                            <p class="font-display text-[0.6rem] tracking-widest text-accent uppercase">
                                {{ $event->sport?->name ?? 'General' }}
                                @if($isFull)
                                    &nbsp;&middot;&nbsp;<span class="text-red-400">Event Full</span>
                                @endif
                            </p>
                            <h3 class="font-display text-lg uppercase text-white leading-tight">{{ $event->title }}</h3>
                            <p class="font-display text-[0.6rem] tracking-widest text-white/30 uppercase">
                                {{ $event->start_time->format('D, M j · H:i') }}
                            </p>
                        </div>

                        {{-- Player info --}}
                        <div class="flex items-center gap-4 border-b border-white/10 p-5">
                            <div class="h-12 w-12 border border-white/15 flex items-center justify-center font-display text-base text-accent shrink-0">
                                {{ strtoupper(substr($player->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-display text-sm uppercase text-white truncate">{{ $player->name }}</p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @if($player->gender)
                                        <span class="font-display text-[0.55rem] tracking-widest text-white/40 uppercase">{{ ucfirst($player->gender) }}</span>
                                    @endif
                                    @if($player->age_range)
                                        <span class="font-display text-[0.55rem] tracking-widest text-white/40 uppercase">{{ $player->age_range }}</span>
                                    @endif
                                    @if($player->play_style)
                                        <span class="font-display text-[0.55rem] tracking-widest text-white/40 uppercase">{{ ucfirst($player->play_style) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Player's sports --}}
                        @if($player->userSports->isNotEmpty())
                        <div class="border-b border-white/10 px-5 py-4 space-y-2">
                            <p class="font-display text-[0.55rem] tracking-widest text-white/30 uppercase">Sports</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($player->userSports->take(3) as $us)
                                    <span class="border border-white/10 px-2.5 py-0.5 font-display text-[0.6rem] tracking-widest text-white/50 uppercase">
                                        {{ $us->sport->name }}
                                        @if($us->skill_value)
                                            &middot; {{ $us->skill_value }}
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Rating --}}
                        @if($player->averageRating)
                        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-3">
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-sm {{ $i <= round($player->averageRating) ? 'text-amber-400' : 'text-white/15' }}">★</span>
                                @endfor
                            </div>
                            <span class="font-display text-[0.6rem] tracking-widest text-white/40 uppercase">
                                {{ number_format($player->averageRating, 1) }} · {{ $player->totalReviews }} reviews
                            </span>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="mt-auto flex gap-px" style="background:rgb(var(--bg-base))">
                            <form method="POST" action="{{ route('host-requests.approve', $participant) }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-ink-900 px-4 py-3.5 font-display text-[0.6rem] tracking-widest uppercase text-accent hover:bg-accent hover:text-ink-900 transition {{ $isFull ? 'opacity-40 cursor-not-allowed' : '' }}"
                                        @if($isFull) disabled @endif>
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('host-requests.reject', $participant) }}" class="flex-1">
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
        @endif
    </section>

</div>
@endsection

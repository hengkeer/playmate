@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="space-y-10">

    {{-- Profile Header --}}
    <section class="pm-card">
        <div class="border border-white/10 bg-accent/5">
            <div class="grid lg:grid-cols-12">
                {{-- Left: avatar + identity --}}
                <div class="lg:col-span-8 p-8 lg:p-12 border-r border-white/10">
                    <span class="pm-tag">Player Profile</span>

                    <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-start">
                        {{-- Avatar --}}
                        @if($user->photo_url)
                            <img src="{{ $user->photo_url }}" class="h-24 w-24 object-cover border border-white/15" />
                        @else
                            <div class="flex h-24 w-24 items-center justify-center border border-accent bg-accent/10 font-display text-4xl text-accent">
                                {{ $user->avatarInitial() }}
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="font-display text-[0.65rem] tracking-widest text-white/40">PLAYER</p>
                            <h1 class="pm-h1 text-4xl md:text-5xl mt-2 break-words">{{ $user->name }}</h1>

                            @if($user->home_address)
                                <p class="mt-3 font-display text-xs tracking-widest text-white/60">{{ strtoupper($user->home_address) }}</p>
                            @endif

                            {{-- Tags row --}}
                            <div class="mt-5 flex flex-wrap gap-2">
                                @if($user->play_style)
                                    <span class="pm-tag">{{ strtoupper($user->play_style) }}</span>
                                @endif
                                @if($user->gender)
                                    <span class="pm-tag">{{ strtoupper($user->gender) }}</span>
                                @endif
                                @if($user->age_range)
                                    <span class="pm-tag">{{ $user->age_range }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: rating --}}
                <div class="lg:col-span-4 p-8 lg:p-12 bg-ink-900/50">
                    <p class="pm-section-title">Overall Rating</p>
                    @if($user->averageRating)
                        <p class="pm-stat-num text-6xl text-accent mt-6">{{ number_format($user->averageRating, 1) }}</p>
                        <div class="mt-3 flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= round($user->averageRating) ? 'text-accent' : 'text-white/15' }} font-display text-lg">&#9733;</span>
                            @endfor
                        </div>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-4">FROM {{ $user->totalReviews }} REVIEWS</p>
                    @else
                        <p class="pm-stat-num text-6xl text-white/20 mt-6">--</p>
                        <p class="font-display text-[0.65rem] tracking-widest text-white/40 mt-4">NO REVIEWS YET</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-3 divide-x divide-white/10 border-t border-white/10">
            <div class="p-6 text-center">
                <p class="pm-stat-num text-4xl text-white">{{ str_pad($user->total_events_joined ?? 0, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="font-display text-[0.6rem] tracking-widest text-white/40 mt-3">EVENTS JOINED</p>
            </div>
            <div class="p-6 text-center">
                <p class="pm-stat-num text-4xl text-white">{{ str_pad($user->hostedEvents()->count(), 2, '0', STR_PAD_LEFT) }}</p>
                <p class="font-display text-[0.6rem] tracking-widest text-white/40 mt-3">EVENTS HOSTED</p>
            </div>
            <div class="p-6 text-center">
                @if($user->averageRating)
                    <p class="pm-stat-num text-4xl text-accent">{{ number_format($user->averageRating, 1) }}<span class="text-lg text-white/30">/5</span></p>
                    <p class="font-display text-[0.6rem] tracking-widest text-white/40 mt-3">{{ $user->totalReviews }} REVIEWS</p>
                @else
                    <p class="pm-stat-num text-4xl text-white/20">--</p>
                    <p class="font-display text-[0.6rem] tracking-widest text-white/40 mt-3">NO REVIEWS YET</p>
                @endif
            </div>
        </div>

        {{-- Bio --}}
        @if($user->bio)
        <div class="border-t border-white/10 p-8 lg:p-10">
            <p class="pm-section-title">About</p>
            <p class="mt-4 text-white/70 text-sm leading-relaxed max-w-3xl">{{ $user->bio }}</p>
        </div>
        @endif

        {{-- CTA --}}
        @auth
            @if(Auth::id() !== $user->id)
            <div class="border-t border-white/10 p-8 lg:p-10 flex flex-wrap gap-3">
                @php
                    $existingConn = \App\Models\Connection::where(function($q) use($user) {
                        $q->where('requester_id', Auth::id())->where('receiver_id', $user->id);
                    })->orWhere(function($q) use($user) {
                        $q->where('receiver_id', Auth::id())->where('requester_id', $user->id);
                    })->first();
                @endphp

                @if($existingConn)
                    @if($existingConn->status === 'pending')
                        @if($existingConn->requester_id === Auth::id())
                            <span class="inline-flex items-center gap-2 border border-white/20 bg-white/5 px-6 py-3 font-display text-xs tracking-widest text-white/70">
                                PENDING REQUEST SENT
                            </span>
                        @else
                            <form method="POST" action="{{ route('connections.update', $existingConn) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="accept">
                                <button class="pm-btn-primary">Accept Request</button>
                            </form>
                            <form method="POST" action="{{ route('connections.update', $existingConn) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="decline">
                                <button class="pm-btn-ghost">Decline</button>
                            </form>
                        @endif
                    @elseif($existingConn->status === 'accepted')
                        <a href="{{ route('connections.chat', $existingConn) }}" class="pm-btn-primary">Open Chat</a>
                        <span class="inline-flex items-center gap-2 border border-accent/40 bg-accent/5 px-6 py-3 font-display text-xs tracking-widest text-accent">
                            CONNECTED
                        </span>
                    @endif
                @else
                    <form method="POST" action="{{ route('connections.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <button class="pm-btn-primary">Send Connection</button>
                    </form>
                @endif

                {{-- Review button --}}
                @if(!$existingConn || $existingConn->status === 'accepted')
                    @php
                        $alreadyReviewed = \App\Models\UserReview::where('reviewer_id', Auth::id())
                            ->where('reviewed_user_id', $user->id)
                            ->where('source_type', 'connection')
                            ->exists();
                    @endphp
                    @if(!$alreadyReviewed)
                        <a href="{{ route('reviews.create', ['user_id' => $user->id, 'source_type' => 'connection', 'source_id' => $existingConn?->id]) }}"
                           class="pm-btn-ghost">Leave Review</a>
                    @else
                        <span class="inline-flex items-center gap-2 border border-accent/40 bg-accent/5 px-6 py-3 font-display text-xs tracking-widest text-accent">
                            REVIEW GIVEN
                        </span>
                    @endif
                @endif
            </div>
            @endif
        @endauth
    </section>

    {{-- Sports Section --}}
    @if($user->userSports->count() > 0)
    <section class="pm-card p-8 lg:p-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title">Disciplines</p>
                <h2 class="font-display text-3xl uppercase mt-3">Sports &amp; Skill</h2>
            </div>
            <span class="pm-tag">{{ $user->userSports->count() }} SPORTS</span>
        </div>

        <div class="grid gap-px sm:grid-cols-2 lg:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($user->userSports as $us)
                <div class="bg-ink-900 p-6 transition hover:bg-ink-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-display text-[0.6rem] tracking-widest text-accent">SPORT 0{{ $loop->iteration }}</p>
                            <p class="font-display text-xl uppercase mt-2">{{ $us->sport->name }}</p>
                            @if($us->skill_value)
                                <p class="text-white/50 text-xs mt-1">{{ $us->skill_value }}</p>
                            @endif
                        </div>
                        @if($us->play_style)
                            <span class="pm-tag">{{ strtoupper($us->play_style) }}</span>
                        @endif
                    </div>

                    {{-- Skill bar --}}
                    @if($us->skill_number)
                        <div class="mt-6">
                            <div class="flex items-center justify-between font-display text-[0.6rem] tracking-widest text-white/40">
                                <span>LEVEL</span>
                                <span class="text-accent">{{ str_pad($us->skill_number, 2, '0', STR_PAD_LEFT) }}/10</span>
                            </div>
                            <div class="mt-2 h-px bg-white/10 overflow-hidden">
                                <div class="h-full bg-accent" style="width: {{ $us->skill_number * 10 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Reviews Section --}}
    <section class="pm-card p-8 lg:p-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title">Reputation</p>
                <h2 class="font-display text-3xl uppercase mt-3">Player Reviews</h2>
            </div>
            @if($user->reviewsReceived->count() > 0)
                <span class="pm-tag">{{ $user->reviewsReceived->count() }} REVIEWS</span>
            @endif
        </div>

        @if($user->reviewsReceived->count() > 0)
            <div class="space-y-px bg-white/10">
                @foreach($user->reviewsReceived->take(10) as $review)
                <div class="bg-ink-900 p-6">
                    <div class="flex items-start gap-4">
                        {{-- Reviewer avatar --}}
                        <div class="h-12 w-12 border border-white/15 flex items-center justify-center font-display text-base text-white shrink-0">
                            {{ $review->reviewer->avatarInitial() }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <p class="font-display uppercase text-sm truncate">{{ $review->reviewer->name }}</p>
                                    {{-- Stars --}}
                                    <div class="flex gap-0.5 shrink-0">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $review->rating ? 'text-accent' : 'text-white/15' }} font-display text-xs">&#9733;</span>
                                        @endfor
                                    </div>
                                </div>
                                <span class="font-display text-[0.6rem] tracking-widest text-white/40 shrink-0">{{ strtoupper($review->created_at->diffForHumans()) }}</span>
                            </div>

                            {{-- Source badge --}}
                            @if($review->source_type)
                            <div class="mb-3">
                                <span class="pm-tag">VIA {{ strtoupper($review->source_type === 'connection' ? 'CONNECTION' : 'EVENT') }}</span>
                            </div>
                            @endif

                            @if($review->comment)
                                <p class="text-white/70 text-sm leading-relaxed">{{ $review->comment }}</p>
                            @else
                                <p class="text-white/30 text-xs italic">No comment left</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="border border-dashed border-white/15 p-12 text-center">
                <p class="font-display text-2xl uppercase text-white/30">No Reviews Yet</p>
                <p class="text-white/40 text-sm mt-3 max-w-md mx-auto">Be the first to leave a review after playing together.</p>
            </div>
        @endif
    </section>

    {{-- Recent Activity / Events Joined --}}
    @if($user->joinedEvents->count() > 0)
    <section class="pm-card p-8 lg:p-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title">Activity</p>
                <h2 class="font-display text-3xl uppercase mt-3">Recent Events</h2>
            </div>
        </div>

        <div class="space-y-px bg-white/10">
            @foreach($user->joinedEvents->take(5) as $event)
            <div class="bg-ink-900 p-5 flex items-center gap-4 transition hover:bg-ink-800">
                <div class="h-12 w-12 border border-white/15 flex items-center justify-center font-display text-accent shrink-0">
                    0{{ $loop->iteration }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-display uppercase text-base truncate">{{ $event->title }}</p>
                    <p class="font-display text-[0.6rem] tracking-widest text-white/40 mt-1">
                        {{ strtoupper($event->start_time->format('M d, Y')) }}
                        @if($event->venue_name)
                            &middot; {{ strtoupper($event->venue_name) }}
                        @endif
                    </p>
                </div>
                <span class="pm-tag {{ $event->status === 'upcoming' ? '' : 'opacity-60' }}">
                    {{ strtoupper($event->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

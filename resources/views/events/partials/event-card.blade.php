{{-- resources/views/events/partials/event-card.blade.php --}}
@props(['event', 'isHost' => false, 'showChat' => false])

<article class="pm-card flex flex-col group">
    {{-- Editorial top bar --}}
    <div class="flex items-start justify-between gap-3 border-b border-white/10 px-6 py-5">
        <div class="space-y-2 min-w-0">
            <p class="font-display text-[0.65rem] tracking-widest text-accent uppercase">{{ $event->sport?->name ?? 'General' }}</p>
            <h3 class="font-display text-xl uppercase leading-tight text-white truncate">{{ $event->title }}</h3>
        </div>
        <span class="shrink-0 border border-white/20 px-2.5 py-1 font-display text-[0.6rem] tracking-widest text-white/70 uppercase">
            {{ $event->match_type === 'doubles' ? 'Doubles' : 'Singles' }}
        </span>
    </div>

    <div class="space-y-3 px-6 py-5">
        <div class="flex items-start gap-3 text-sm text-white/70">
            <span class="text-accent mt-0.5">&#9679;</span>
            <span class="truncate">{{ $event->venue_name ?? $event->location ?? 'Venue TBD' }}</span>
        </div>
        <div class="flex items-start gap-3 text-sm text-white/70">
            <span class="text-accent mt-0.5">&#9679;</span>
            <span>{{ $event->start_time->format('D, M j') }} &middot; {{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }}</span>
        </div>
        <div class="flex items-start gap-3 text-sm text-white/70">
            <span class="text-accent mt-0.5">&#9679;</span>
            <span>
                <strong class="text-white">{{ $event->approvedParticipants->count() ?? $event->users->count() }}</strong> / {{ $event->max_slots }} slots
                @if($event->price)
                    &middot; <span class="text-accent">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                @endif
            </span>
        </div>
    </div>

    <div class="mt-auto flex border-t border-white/10">
        <a href="{{ route('events.show', $event) }}" class="flex-1 px-5 py-4 text-center font-display text-[0.65rem] tracking-widest text-white uppercase transition hover:bg-accent hover:text-ink-900">
            View Event
        </a>
        @if($showChat && ($event->isUserApproved(auth()->user()) || $event->isHostedBy(auth()->user())))
            <a href="{{ route('chat', $event) }}" class="flex-1 border-l border-white/10 px-5 py-4 text-center font-display text-[0.65rem] tracking-widest text-accent uppercase transition hover:bg-accent hover:text-ink-900">
                Chat
            </a>
        @endif
    </div>
</article>

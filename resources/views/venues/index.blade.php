@extends('layouts.app')

@section('title', 'Venue Directory')

@section('content')
<div class="space-y-10">

    {{-- Header --}}
    <section class="border border-white/10 bg-ink-800/40" data-animate="fade-up">
        <div class="grid lg:grid-cols-12 gap-0">
            <div class="lg:col-span-8 p-10 lg:p-14 border-r border-white/10">
                <span class="pm-tag">Venue Directory</span>
                <p class="font-display text-xs tracking-widest text-white/40 mt-6">FIND YOUR COURT</p>
                <h1 class="font-display text-4xl md:text-5xl uppercase mt-2">
                    Sports Venues<br><span class="text-accent">Near You</span>
                </h1>
                <p class="text-white/60 mt-6 max-w-xl leading-relaxed">
                    Browse sports facilities in your area. Find the right court for your next event — check location, facilities, and operating hours.
                </p>
            </div>
            <div class="lg:col-span-4 p-10 lg:p-14 bg-accent/5">
                <p class="pm-section-title">Quick Filter</p>
                <form method="GET" class="space-y-4 mt-6">
                    <label class="block space-y-2">
                        <span class="font-display text-[0.68rem] tracking-widest text-white/50 uppercase">Sport</span>
                        <select name="sport_id" class="pm-input">
                            <option value="">All Sports</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                    {{ $sport->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="font-display text-[0.68rem] tracking-widest text-white/50 uppercase">Area / City</span>
                        <input type="text" name="area" value="{{ request('area') }}" placeholder="e.g. Jakarta Selatan" class="pm-input" />
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="pm-btn-primary flex-1 text-[0.75rem] py-3">Search</button>
                        <a href="{{ route('venues.index') }}" class="pm-btn-ghost text-[0.72rem] py-3 px-5">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Results --}}
    @if($venues->isEmpty())
        <div class="border border-white/10 p-20 text-center" style="background:rgb(var(--bg-surface))">
            <p class="font-display text-2xl uppercase text-white/50">No Venues Found</p>
            <p class="text-white/40 text-sm mt-3">Try a different sport or area.</p>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between">
            <p class="font-display text-[0.7rem] tracking-widest text-white/40 uppercase">{{ $venues->total() }} venues found</p>
        </div>

        <div class="grid gap-px sm:grid-cols-2 xl:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($venues as $venue)
            <a href="{{ route('venues.show', $venue) }}"
               class="pm-card flex flex-col group" data-animate="fade-up">

                {{-- Thumbnail --}}
                @if($venue->image_url)
                <div class="overflow-hidden" style="height:180px;background:rgb(var(--bg-surface))">
                    <img src="{{ Storage::url($venue->image_url) }}"
                         alt="{{ $venue->name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                </div>
                @else
                <div class="flex items-center justify-center" style="height:180px;background:rgb(var(--bg-surface));border-bottom:1px solid rgb(var(--border-base))">
                    <svg class="w-10 h-10 text-white/10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
                @endif

                {{-- Top: name & sport --}}
                <div class="p-6 pb-4" style="border-bottom:1px solid rgb(var(--border-base))">
                    <p class="font-display text-[0.65rem] tracking-widest text-accent uppercase mb-2">
                        {{ $venue->sport?->name ?? 'Multi-Sport' }}
                    </p>
                    <h3 class="font-display text-xl uppercase text-white group-hover:text-accent transition leading-tight">
                        {{ $venue->name }}
                    </h3>
                </div>

                {{-- Info --}}
                <div class="p-6 flex-1 space-y-3">
                    {{-- Address --}}
                    <div class="flex items-start gap-3 text-sm text-white/60">
                        <svg class="w-3.5 h-3.5 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span class="leading-snug">{{ $venue->address ?? 'Address not listed' }}{{ $venue->area ? ', ' . $venue->area : '' }}</span>
                    </div>

                    {{-- Hours --}}
                    @if($venue->open_hours)
                    <div class="flex items-center gap-3 text-sm text-white/60">
                        <svg class="w-3.5 h-3.5 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>{{ $venue->open_hours }}</span>
                    </div>
                    @endif

                    {{-- Price estimate — shown as reference, not bookable --}}
                    @if($venue->price_estimate)
                    <div class="flex items-center gap-3 text-sm text-white/60">
                        <svg class="w-3.5 h-3.5 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <span>Est. Rp {{ number_format($venue->price_estimate, 0, ',', '.') }} / hour</span>
                    </div>
                    @endif
                </div>

                {{-- Footer CTA --}}
                <div class="px-6 py-4 font-display text-[0.65rem] tracking-widest uppercase flex items-center justify-between"
                     style="border-top:1px solid rgb(var(--border-base));color:rgb(var(--fg) / 0.4)">
                    <span>View Details</span>
                    <span class="text-accent group-hover:translate-x-1 transition-transform inline-block">→</span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="flex justify-center pt-4">
            {{ $venues->links() }}
        </div>
    @endif

</div>
@endsection

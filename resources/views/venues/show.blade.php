@extends('layouts.app')

@section('title', $venue->name)

@section('content')
<div class="space-y-10">

    {{-- Back --}}
    <a href="{{ route('venues.index') }}"
       class="inline-flex items-center gap-2 font-display text-[0.7rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
        ← All Venues
    </a>

    {{-- Header --}}
    <header style="border-bottom:1px solid rgb(var(--border-base));padding-bottom:2.5rem" data-animate="fade-up">
        <div class="grid lg:grid-cols-2 gap-10 items-end">
            <div>
                <p class="font-display text-[0.68rem] tracking-widest text-accent uppercase mb-3">
                    {{ $venue->sport?->name ?? 'Multi-Sport' }} · Venue Directory
                </p>
                <h1 class="font-display uppercase text-white" style="font-size:clamp(2rem,5vw,3.5rem);font-weight:700;line-height:1">
                    {{ $venue->name }}
                </h1>
                <div class="mt-5 space-y-2">
                    <div class="flex items-start gap-3 text-sm text-white/60">
                        <svg class="w-4 h-4 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>{{ $venue->address }}{{ $venue->area ? ', ' . $venue->area : '' }}</span>
                    </div>
                    @if($venue->open_hours)
                    <div class="flex items-center gap-3 text-sm text-white/60">
                        <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>{{ $venue->open_hours }}</span>
                    </div>
                    @endif
                    @if($venue->contact)
                    <div class="flex items-center gap-3 text-sm text-white/60">
                        <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.27 6.27l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <span>{{ $venue->contact }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Price reference (informational only) --}}
            @if($venue->price_estimate)
            <div style="border:1px solid rgb(var(--border-base));padding:1.5rem 2rem;background:rgb(var(--bg-surface))">
                <p class="font-display text-[0.62rem] tracking-widest text-white/35 uppercase mb-2">Estimated Rate</p>
                <p class="font-display text-white" style="font-size:2.2rem;font-weight:700;line-height:1">
                    Rp {{ number_format($venue->price_estimate, 0, ',', '.') }}
                </p>
                <p class="font-display text-white/35 text-xs tracking-wider mt-1">per hour · contact venue to confirm</p>
            </div>
            @endif
        </div>
    </header>

    {{-- Photo Gallery --}}
    @php
        $allImages = $venue->images ?? ($venue->image_url ? [$venue->image_url] : []);
    @endphp

    @if(count($allImages) > 0)
    <section data-animate="fade-up">
        {{-- Hero image --}}
        <div class="overflow-hidden mb-px" style="height:420px;background:rgb(var(--bg-surface));border:1px solid rgb(var(--border-base))">
            <img id="gallery-main"
                 src="{{ Storage::url($allImages[0]) }}"
                 alt="{{ $venue->name }}"
                 class="w-full h-full object-cover transition-all duration-300">
        </div>

        {{-- Thumbnails --}}
        @if(count($allImages) > 1)
        <div class="grid gap-px mt-px" style="grid-template-columns: repeat({{ min(count($allImages)-1, 5) }}, 1fr);background:rgb(var(--bg-base))">
            @foreach(array_slice($allImages, 1, 5) as $idx => $img)
            <button onclick="document.getElementById('gallery-main').src='{{ Storage::url($img) }}'"
                    class="overflow-hidden group" style="height:90px;background:rgb(var(--bg-surface));border:1px solid rgb(var(--border-base))">
                <img src="{{ Storage::url($img) }}"
                     alt="{{ $venue->name }} photo {{ $idx + 2 }}"
                     class="w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-opacity duration-200">
            </button>
            @endforeach
        </div>
        @endif
    </section>
    @endif

    {{-- CTA Actions --}}
    <section class="flex flex-col sm:flex-row gap-3" data-animate="fade-up">
        <a href="{{ route('events.create', ['venue_id' => $venue->id]) }}"
           class="pm-btn-primary">
            Host an Event Here
        </a>
        <a href="{{ route('matchmaking') }}"
           class="pm-btn-ghost">
            Find Opponents Nearby
        </a>
    </section>

    {{-- Description --}}
    @if($venue->description)
    <section style="border-top:1px solid rgb(var(--border-base));padding-top:2.5rem" data-animate="fade-up">
        <div class="grid lg:grid-cols-[1fr_2fr] gap-10">
            <div>
                <p class="pm-section-title justify-start mb-3">About</p>
                <h2 class="font-display text-2xl uppercase text-white" style="font-weight:700">Venue Info</h2>
            </div>
            <p class="text-white/65 leading-relaxed">{{ $venue->description }}</p>
        </div>
    </section>
    @endif

    {{-- Info Cards --}}
    <section data-animate="fade-up">
        <div class="grid sm:grid-cols-3 gap-px" style="background:rgb(var(--bg-base))">
            <div style="background:rgb(var(--bg-surface));padding:1.5rem 2rem;border:1px solid rgb(var(--border-base))">
                <p class="font-display text-[0.62rem] tracking-widest text-white/35 uppercase mb-3">Sport Type</p>
                <p class="font-display text-white uppercase" style="font-size:1.2rem;font-weight:600">
                    {{ $venue->sport?->name ?? 'Multi-Sport' }}
                </p>
            </div>
            <div style="background:rgb(var(--bg-surface));padding:1.5rem 2rem;border:1px solid rgb(var(--border-base))">
                <p class="font-display text-[0.62rem] tracking-widest text-white/35 uppercase mb-3">Operating Hours</p>
                <p class="font-display text-white uppercase" style="font-size:1.2rem;font-weight:600">
                    {{ $venue->open_hours ?? 'Contact Venue' }}
                </p>
            </div>
            <div style="background:rgb(var(--bg-surface));padding:1.5rem 2rem;border:1px solid rgb(var(--border-base))">
                <p class="font-display text-[0.62rem] tracking-widest text-white/35 uppercase mb-3">Area</p>
                <p class="font-display text-white uppercase" style="font-size:1.2rem;font-weight:600">
                    {{ $venue->area ?? '—' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Map --}}
    @if($venue->latitude && $venue->longitude)
    <section style="border-top:1px solid rgb(var(--border-base));padding-top:2.5rem" data-animate="fade-up">
        <p class="pm-section-title justify-start mb-3">Location</p>
        <h2 class="font-display text-2xl uppercase text-white mb-6" style="font-weight:700">Venue Map</h2>

        <div id="venue-map" style="height:400px;border:1px solid rgb(var(--border-base));background:rgb(var(--bg-surface))"></div>

        <p class="mt-3 text-white/40 text-sm">
            {{ $venue->address }}{{ $venue->area ? ', ' . $venue->area : '' }}
        </p>
    </section>
    @endif

    {{-- Notice --}}
    <section data-animate="fade-up">
        <div style="border-left:2px solid rgba(249,115,22,0.5);background:rgba(249,115,22,0.04);padding:1.25rem 1.5rem">
            <p class="font-display text-[0.65rem] tracking-widest text-accent uppercase mb-1">How to Use This Venue</p>
            <p class="text-white/60 text-sm leading-relaxed">
                This directory listing is for reference only. To use this venue for a PlayMate event, click <strong class="text-white">"Host an Event Here"</strong> above. For booking inquiries, contact the venue directly using the information provided.
            </p>
        </div>
    </section>

</div>

@if($venue->latitude && $venue->longitude)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('venue-map', {
        center: [{{ $venue->latitude }}, {{ $venue->longitude }}],
        zoom: 15,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        maxZoom: 19,
    }).addTo(map);

    var icon = L.divIcon({
        className: '',
        html: '<div style="width:14px;height:14px;background:#F97316;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 4px rgba(249,115,22,0.3)"></div>',
        iconAnchor: [7, 7],
    });

    L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}], { icon: icon })
        .addTo(map)
        .bindPopup('<strong style="font-family:Oswald,sans-serif;text-transform:uppercase;font-size:0.85rem">{{ addslashes($venue->name) }}</strong><br><span style="font-size:0.75rem;color:#999">{{ addslashes($venue->address) }}</span>')
        .openPopup();
});
</script>
@endpush
@endif

@endsection

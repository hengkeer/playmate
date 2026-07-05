@extends('layouts.app')

@section('title', 'Challenge ' . ($opponent->name ?? 'Player'))

@section('content')
<div class="space-y-8 max-w-2xl mx-auto">

    {{-- Back --}}
    <a href="{{ route('matchmaking') }}"
       class="inline-flex items-center gap-2 font-display text-[0.7rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
        ← Back to Matchmaking
    </a>

    {{-- Challenger Banner --}}
    @isset($opponent)
    <div class="border border-accent/40 bg-accent/5 p-5 flex items-center gap-5">
        <div class="h-14 w-14 border border-accent flex items-center justify-center font-display text-2xl text-accent shrink-0 bg-accent/10">
            {{ strtoupper(substr($opponent->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-display text-[0.65rem] tracking-widest text-accent uppercase mb-1">Challenging</p>
            <p class="font-display text-xl uppercase text-white truncate">{{ $opponent->name }}</p>
            @if($opponent->sports->isNotEmpty())
                <p class="font-display text-[0.65rem] tracking-widest text-white/40 uppercase mt-1">
                    {{ $opponent->sports->pluck('name')->implode(' · ') }}
                    @if($opponent->sports->first()?->pivot?->skill_value)
                        · {{ $opponent->sports->first()->pivot->skill_value }}
                    @endif
                </p>
            @endif
        </div>
        <span class="font-display text-[0.65rem] tracking-widest text-accent border border-accent/40 bg-accent/10 px-3 py-2 uppercase shrink-0">
            DIRECT CHALLENGE
        </span>
    </div>
    @endisset

    {{-- Form Card --}}
    <section class="pm-card p-8 lg:p-10">
        <p class="pm-section-title">Setup Match</p>
        <h1 class="font-display text-3xl uppercase mt-3 mb-8">
            @isset($opponent)
                Challenge <span class="text-accent">{{ $opponent->name }}</span>
            @else
                Host a Game
            @endisset
        </h1>

        @if ($errors->any())
            <div class="mb-6 border border-red-500/40 bg-red-500/5 px-4 py-3 font-display text-xs tracking-widest text-red-400">
                {{ strtoupper($errors->first()) }}
            </div>
        @endif

        <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
            @csrf

            {{-- Pass opponent id so EventController pre-invites them --}}
            @isset($opponent)
                <input type="hidden" name="opponent_id" value="{{ $opponent->id }}">
            @endisset

            {{-- Auto-generate a sensible title --}}
            <input type="hidden" name="title"
                value="@isset($opponent){{ auth()->user()->name }} vs {{ $opponent->name }}@else{{ auth()->user()->name }}'s Game @endisset">

            {{-- Sport --}}
            <div class="space-y-2">
                <span class="pm-section-title">Sport</span>
                <select name="sport_id" class="pm-input">
                    <option value="">Select sport (optional)</option>
                    @foreach($sports as $sport)
                        <option value="{{ $sport->id }}"
                            @isset($opponent)
                                {{ $opponent->sports->contains('id', $sport->id) ? 'selected' : '' }}
                            @endisset>
                            {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Venue Autocomplete --}}
            <div class="space-y-2">
                <span class="pm-section-title">Venue *</span>
                <x-venue-picker :selected-name="old('venue_name', '')" />
                <p class="text-white/30 text-[0.7rem]">Ketik nama venue — pilih dari daftar atau isi manual. Map otomatis pindah ke lokasi venue.</p>
            </div>

            {{-- Map Picker — latitude & longitude disimpan di sini --}}
            <div class="space-y-2">
                <span class="pm-section-title">Lokasi di Map</span>
                <x-map-picker
                    lat-id="latitude"
                    lng-id="longitude"
                    addr-id="map_address"
                    label="Venue Location"
                    :height="260"
                    :default-lat="-6.2088"
                    :default-lng="106.8456"
                />
            </div>

            {{-- Date & Time --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <span class="pm-section-title">Start Time *</span>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                        required class="pm-input" />
                </div>
                <div class="space-y-2">
                    <span class="pm-section-title">End Time *</span>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                        required class="pm-input" />
                </div>
            </div>

            {{-- Match Type & Slots --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <span class="pm-section-title">Match Type</span>
                    <select name="match_type" class="pm-input">
                        <option value="singles">Singles (1v1)</option>
                        <option value="doubles">Doubles (2v2)</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <span class="pm-section-title">Max Slots</span>
                    <input type="number" name="max_slots" value="{{ old('max_slots', 2) }}"
                        min="2" max="4" class="pm-input" />
                </div>
            </div>

            {{-- Hidden defaults --}}
            <input type="hidden" name="visibility" value="private">
            <input type="hidden" name="approval_required" value="0">

            {{-- Submit --}}
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="pm-btn-primary">
                    @isset($opponent)
                        Send Challenge
                    @else
                        Create Game
                    @endisset
                </button>
                <a href="{{ route('matchmaking') }}"
                   class="font-display text-[0.7rem] tracking-widest text-white/40 hover:text-white uppercase transition">
                    Cancel
                </a>
            </div>
        </form>
    </section>

</div>
@endsection

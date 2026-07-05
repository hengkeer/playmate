@extends('layouts.app')

@section('title', 'Host an Event')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">

    {{-- Back --}}
    <a href="{{ route('events.index') }}"
       class="inline-flex items-center gap-2 font-display text-[0.7rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
        ← Back to Events
    </a>

    {{-- Challenger Banner (when coming from matchmaking challenge flow) --}}
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
        <p class="pm-section-title">Create</p>
        <h1 class="font-display text-3xl uppercase mt-3 mb-2">
            Host a <span class="text-accent">New Event</span>
        </h1>
        <p class="text-white/50 text-sm mb-8">Fill in the details below. Your event will be listed for others to join.</p>

        @if ($errors->any())
            <div class="mb-6 border border-red-500/40 bg-red-500/5 px-4 py-3 font-display text-xs tracking-widest text-red-400">
                {{ strtoupper($errors->first()) }}
            </div>
        @endif

        <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
            @csrf

            @isset($opponent)
                <input type="hidden" name="opponent_id" value="{{ $opponent->id }}">
            @endisset

            {{-- Title & Sport --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <span class="pm-section-title">Event Title *</span>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. Saturday Tennis Meetup"
                        class="pm-input" />
                </div>
                <div class="space-y-2">
                    <span class="pm-section-title">Sport</span>
                    <select name="sport_id" class="pm-input">
                        <option value="">Select sport (optional)</option>
                        @foreach($sports as $sport)
                            <option value="{{ $sport->id }}" {{ (old('sport_id') ?? $venue?->sport_id) == $sport->id ? 'selected' : '' }}>
                                {{ $sport->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div class="space-y-2">
                <span class="pm-section-title">Description</span>
                <textarea name="description" rows="3"
                    placeholder="Event details, skill level required, what to bring..."
                    class="pm-input resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Venue Autocomplete --}}
            <div class="space-y-2">
                <span class="pm-section-title">Venue *</span>
                <x-venue-picker
                    :selected-name="old('venue_name', $venue?->name ?? '')"
                    :selected-lat="old('venue_name') ? null : ($venue?->latitude)"
                    :selected-lng="old('venue_name') ? null : ($venue?->longitude)"
                />
                <p class="text-white/30 text-[0.7rem]">Ketik nama venue — pilih dari daftar atau isi manual. Map otomatis pindah ke lokasi venue.</p>
            </div>

            {{-- Map Picker — latitude & longitude disimpan di sini --}}
            <div class="space-y-2">
                <span class="pm-section-title">Lokasi di Map</span>
                <x-map-picker
                    lat-id="latitude"
                    lng-id="longitude"
                    addr-id="map_address"
                    label="Event Location"
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

            {{-- Slots, Price, Visibility --}}
            <div class="grid gap-5 sm:grid-cols-3">
                <div class="space-y-2">
                    <span class="pm-section-title">Max Slots</span>
                    <input type="number" name="max_slots" value="{{ old('max_slots', 4) }}"
                        min="2" max="50" class="pm-input" />
                </div>
                <div class="space-y-2">
                    <span class="pm-section-title">Price (IDR)</span>
                    <input type="number" name="price" value="{{ old('price') }}"
                        placeholder="e.g. 150000" class="pm-input" />
                </div>
                <div class="space-y-2">
                    <span class="pm-section-title">Visibility</span>
                    <select name="visibility" class="pm-input">
                        <option value="public" {{ old('visibility') !== 'private' ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="space-y-2">
                <span class="pm-section-title">Payment Info</span>
                <textarea name="payment_info" rows="2"
                    placeholder="e.g. Transfer to BCA 1234567890 a/n Rizky, send proof to group chat"
                    class="pm-input resize-none">{{ old('payment_info') }}</textarea>
            </div>

            {{-- Match Type & Approval --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <span class="pm-section-title">Match Type</span>
                    <select name="match_type" class="pm-input">
                        <option value="singles" {{ old('match_type') !== 'doubles' ? 'selected' : '' }}>Singles (1v1)</option>
                        <option value="doubles" {{ old('match_type') === 'doubles' ? 'selected' : '' }}>Doubles (2v2)</option>
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="approval_required" value="1"
                                {{ old('approval_required') ? 'checked' : '' }}
                                class="sr-only peer" id="approval_check">
                            <div class="w-10 h-5 bg-white/10 border border-white/20 peer-checked:bg-accent/30 peer-checked:border-accent/60 transition"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white/40 peer-checked:bg-accent peer-checked:translate-x-5 transition-all"></div>
                        </div>
                        <div>
                            <p class="font-display text-[0.65rem] tracking-widest text-white/60 uppercase">Require Approval</p>
                            <p class="text-white/35 text-[0.7rem] mt-0.5">Host must approve each joiner</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="pm-btn-primary">
                    @isset($opponent)
                        Send Challenge
                    @else
                        Create Event
                    @endisset
                </button>
                <a href="{{ route('events.index') }}"
                   class="font-display text-[0.7rem] tracking-widest text-white/40 hover:text-white uppercase transition">
                    Cancel
                </a>
            </div>
        </form>
    </section>

</div>
@endsection

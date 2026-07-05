@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="space-y-10">

    {{-- Profile Header --}}
    <section class="pm-card">
        <div class="border border-white/10 bg-accent/5">
            <div class="grid lg:grid-cols-12">
                {{-- Left: avatar + identity --}}
                <div class="lg:col-span-8 p-8 lg:p-12 border-r border-white/10">
                    <span class="pm-tag">My Profile</span>

                    <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-start">
                        @if($user->photo_url)
                            <img src="{{ $user->photo_url }}" class="h-24 w-24 object-cover border border-white/15" />
                        @else
                            <div class="flex h-24 w-24 items-center justify-center border border-accent bg-accent/10 font-display text-4xl text-accent">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="font-display text-[0.65rem] tracking-widest text-white/40">PLAYER</p>
                            <h1 class="pm-h1 text-4xl md:text-5xl mt-2 break-words">{{ $user->name }}</h1>

                            <p class="mt-3 font-display text-xs tracking-widest text-white/60">{{ strtoupper($user->home_address ?? 'LOCATION NOT SET') }}</p>

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

                {{-- Right: stats --}}
                <div class="lg:col-span-4 p-8 lg:p-12 bg-ink-900/50 space-y-6">
                    <div>
                        <p class="pm-section-title">Events Joined</p>
                        <p class="pm-stat-num text-5xl text-white mt-3">{{ str_pad($user->total_events_joined, 2, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="pm-section-title">Events Hosted</p>
                        <p class="pm-stat-num text-5xl text-white mt-3">{{ str_pad($user->hostedEvents()->count(), 2, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="pm-section-title">Last Active</p>
                        <p class="font-display text-sm text-white mt-3 uppercase">{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'NEVER' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Bio --}}
    @if($user->bio)
    <section class="pm-card p-8 lg:p-10">
        <p class="pm-section-title">About</p>
        <p class="mt-4 text-white/70 text-sm leading-relaxed max-w-3xl">{{ $user->bio }}</p>
    </section>
    @endif

    {{-- Sports Profile (read-only) --}}
    <section class="pm-card p-8 lg:p-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title">Disciplines</p>
                <h2 class="font-display text-3xl uppercase mt-3">Sports &amp; Skill Levels</h2>
            </div>
            <span class="pm-tag">{{ $user->userSports->count() }} SPORTS</span>
        </div>

        @if($user->userSports->isNotEmpty())
        <div class="grid gap-px sm:grid-cols-3" style="background:rgb(var(--bg-base))">
            @foreach($user->userSports as $us)
                <div class="bg-ink-900 p-6">
                    <p class="font-display text-[0.6rem] tracking-widest text-accent">SPORT</p>
                    <p class="font-display text-xl uppercase mt-2">{{ $us->sport->name }}</p>
                    <p class="text-white/50 text-xs mt-2">
                        LEVEL: <span class="text-accent font-display">{{ $us->skill_number ?? '--' }}</span>
                        @if($us->skill_value && $us->skill_value !== (string)($us->skill_number))
                            <span class="ml-1 text-white/30">({{ $us->skill_value }})</span>
                        @endif
                    </p>
                    @if($us->play_style)
                        <span class="pm-tag mt-3">{{ strtoupper($us->play_style) }}</span>
                    @endif
                </div>
            @endforeach
        </div>
        @else
        <p class="text-white/40 text-sm">No sports added yet &mdash; add them in the editor below.</p>
        @endif
    </section>

    {{-- Edit Form --}}
    <section class="pm-card p-8 lg:p-10">
        <p class="pm-section-title">Editor</p>
        <h2 class="font-display text-3xl uppercase mt-3 mb-8">Edit Profile</h2>

        @if(session('success'))
            <div class="mb-6 border border-accent/40 bg-accent/5 px-4 py-3 font-display text-xs tracking-widest text-accent">
                {{ strtoupper(session('success')) }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Basic info --}}
            <div class="space-y-5">
                <p class="pm-section-title">Basic Information</p>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block space-y-1.5">
                        <span class="font-display text-[0.6rem] tracking-widest text-white/40">FULL NAME</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="pm-input" />
                    </label>

                    <label class="block space-y-1.5">
                        <span class="font-display text-[0.6rem] tracking-widest text-white/40">PROFILE PHOTO</span>
                        <div class="flex items-center gap-3">
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}" class="h-10 w-10 object-cover border border-white/15" />
                            @endif
                            <input type="file" name="photo" accept="image/*"
                                class="pm-input flex-1 file:bg-accent/10 file:border-0 file:text-accent file:font-display file:tracking-widest file:text-[0.6rem] file:px-3 file:py-1.5 file:mr-3" />
                        </div>
                        <span class="font-display text-[0.6rem] tracking-widest text-white/30">JPG, PNG, GIF &mdash; MAX 2MB</span>
                    </label>

                    <label class="block space-y-1.5 sm:col-span-2">
                        <span class="font-display text-[0.6rem] tracking-widest text-white/40">BIO</span>
                        <textarea name="bio" rows="3" placeholder="Tell others about yourself..." class="pm-input">{{ old('bio', $user->bio) }}</textarea>
                    </label>

                    <label class="block space-y-1.5">
                        <span class="font-display text-[0.6rem] tracking-widest text-white/40">GENDER</span>
                        <select name="gender" class="pm-input">
                            <option value="">Not set</option>
                            <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </label>

                    <label class="block space-y-1.5">
                        <span class="font-display text-[0.6rem] tracking-widest text-white/40">AGE RANGE</span>
                        <select name="age_range" class="pm-input">
                            <option value="">Not set</option>
                            @foreach(['18-25', '26-35', '36-50', '51+'] as $range)
                                <option value="{{ $range }}" {{ $user->age_range === $range ? 'selected' : '' }}>{{ $range }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            {{-- Location --}}
            <div class="space-y-4">
                <p class="pm-section-title">Location</p>
                <x-map-picker
                    lat-id="latitude"
                    lng-id="longitude"
                    addr-id="home_address"
                    :lat-val="$user->latitude"
                    :lng-val="$user->longitude"
                    :addr-val="$user->home_address"
                    label="Your Location"
                    :height="280"
                    :default-lat="-6.2088"
                    :default-lng="106.8456"
                />
                @if($user->latitude && $user->longitude)
                    <p class="font-display text-[0.6rem] tracking-widest text-accent flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-accent inline-block"></span>
                        LOCATION SAVED
                    </p>
                @endif
            </div>

            {{-- Play style --}}
            <div class="space-y-4">
                <p class="pm-section-title">Play Style</p>
                <label class="block space-y-1.5 max-w-xs">
                    <select name="play_style" class="pm-input">
                        <option value="">Not set</option>
                        <option value="casual" {{ $user->play_style === 'casual' ? 'selected' : '' }}>Casual</option>
                        <option value="competitive" {{ $user->play_style === 'competitive' ? 'selected' : '' }}>Competitive</option>
                    </select>
                </label>
            </div>

            {{-- Sports Editor --}}
            <div class="space-y-4">
                <div class="flex items-end justify-between">
                    <p class="pm-section-title">Sports &amp; Skill</p>
                    <p class="font-display text-[0.6rem] tracking-widest text-white/40">SELECT SPORT THEN CHOOSE LEVEL</p>
                </div>

                <div class="space-y-px bg-white/10" id="sports-list">
                    @php $sportsList = $sports ?? \App\Models\Sport::all(); @endphp

                    @php
                        $existingSports = $user->userSports;
                        $maxRows = 3;
                        $totalRows = max($existingSports->count(), 1); // at least 1 row
                        $totalRows = min($totalRows, $maxRows);
                        // Always show existing rows + fill empty slots up to $maxRows
                        $emptyCount = $maxRows - $existingSports->count();
                        $dayLabels  = ['Su','Mo','Tu','We','Th','Fr','Sa'];
                    @endphp

                    {{-- Existing sport rows --}}
                    @foreach($existingSports as $i => $us)
                    @php
                        $selectedDays = [];
                        if ($us->preferred_days) {
                            $raw = is_string($us->preferred_days) ? json_decode($us->preferred_days, true) : $us->preferred_days;
                            if (is_array($raw)) $selectedDays = array_map('intval', $raw);
                        }
                    @endphp
                    <div class="sport-row bg-ink-900 p-5 space-y-4">
                        <div class="grid gap-3 lg:grid-cols-12 items-center">
                            <select name="sports[{{ $i }}][sport_id]" class="pm-input lg:col-span-5"
                                onchange="rebuildSkillLevels(this)">
                                <option value="">Sport...</option>
                                @foreach($sportsList as $sport)
                                    <option value="{{ $sport->id }}" {{ $us->sport_id == $sport->id ? 'selected' : '' }}>
                                        {{ $sport->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sports[{{ $i }}][skill_number]" class="pm-input lg:col-span-3">
                                <option value="" disabled>Level</option>
                                @foreach($us->sport->getSkillLevels() as $level)
                                    {{-- Match by stored label (skill_value), not skill_number — skill_number is
                                         normalized 1-10 while option values are the sport's raw scale (e.g. Tennis 1-12).
                                         Comparing skill_number directly against raw values picks the wrong option. --}}
                                    <option value="{{ $level['value'] }}" {{ $us->skill_value === $level['name'] ? 'selected' : '' }}>
                                        {{ $level['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sports[{{ $i }}][play_style]" class="pm-input lg:col-span-2">
                                <option value="">Style</option>
                                <option value="casual" {{ $us->play_style === 'casual' ? 'selected' : '' }}>Casual</option>
                                <option value="competitive" {{ $us->play_style === 'competitive' ? 'selected' : '' }}>Competitive</option>
                            </select>
                        </div>
                        <div class="border border-white/10 p-4 space-y-3">
                            <p class="font-display text-[0.6rem] tracking-widest text-white/40">PREFERRED PLAYING TIME (OPTIONAL)</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <label class="font-display text-[0.6rem] tracking-widest text-white/40">FROM</label>
                                    <input type="time" name="sports[{{ $i }}][preferred_start_time]"
                                        value="{{ $us->preferred_start_time ?? '' }}" class="pm-input !py-1.5 !text-xs w-32" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="font-display text-[0.6rem] tracking-widest text-white/40">TO</label>
                                    <input type="time" name="sports[{{ $i }}][preferred_end_time]"
                                        value="{{ $us->preferred_end_time ?? '' }}" class="pm-input !py-1.5 !text-xs w-32" />
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="font-display text-[0.6rem] tracking-widest text-white/40">DAYS:</span>
                                    @for($d = 0; $d <= 6; $d++)
                                        <label class="relative">
                                            <input type="checkbox" name="sports[{{ $i }}][preferred_days][]" value="{{ $d }}"
                                                {{ in_array($d, $selectedDays) ? 'checked' : '' }}
                                                class="peer sr-only" />
                                            <span class="flex h-7 w-7 cursor-pointer items-center justify-center border border-white/15 font-display text-[0.55rem] tracking-widest text-white/40 peer-checked:bg-accent peer-checked:text-ink-900 peer-checked:border-accent hover:border-white/40">
                                                {{ strtoupper($dayLabels[$d]) }}
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Empty rows to fill up to maxRows --}}
                    @for($e = 0; $e < $emptyCount; $e++)
                    @php $i = $existingSports->count() + $e; @endphp
                    <div class="sport-row bg-ink-900 p-5 space-y-4">
                        <div class="grid gap-3 lg:grid-cols-12 items-center">
                            <select name="sports[{{ $i }}][sport_id]" class="pm-input lg:col-span-5"
                                onchange="rebuildSkillLevels(this)">
                                <option value="">{{ $i === 0 ? 'Select sport...' : 'Add another sport (optional)' }}</option>
                                @foreach($sportsList as $sport)
                                    <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                                @endforeach
                            </select>
                            <select name="sports[{{ $i }}][skill_number]" class="pm-input lg:col-span-3">
                                <option value="" disabled selected>Level</option>
                            </select>
                            <select name="sports[{{ $i }}][play_style]" class="pm-input lg:col-span-2">
                                <option value="">Style</option>
                                <option value="casual">Casual</option>
                                <option value="competitive">Competitive</option>
                            </select>
                        </div>
                        <div class="border border-white/10 p-4 space-y-3">
                            <p class="font-display text-[0.6rem] tracking-widest text-white/40">PREFERRED PLAYING TIME (OPTIONAL)</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <label class="font-display text-[0.6rem] tracking-widest text-white/40">FROM</label>
                                    <input type="time" name="sports[{{ $i }}][preferred_start_time]" value="" class="pm-input !py-1.5 !text-xs w-32" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="font-display text-[0.6rem] tracking-widest text-white/40">TO</label>
                                    <input type="time" name="sports[{{ $i }}][preferred_end_time]" value="" class="pm-input !py-1.5 !text-xs w-32" />
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="font-display text-[0.6rem] tracking-widest text-white/40">DAYS:</span>
                                    @for($d = 0; $d <= 6; $d++)
                                        <label class="relative">
                                            <input type="checkbox" name="sports[{{ $i }}][preferred_days][]" value="{{ $d }}"
                                                class="peer sr-only" />
                                            <span class="flex h-7 w-7 cursor-pointer items-center justify-center border border-white/15 font-display text-[0.55rem] tracking-widest text-white/40 peer-checked:bg-accent peer-checked:text-ink-900 peer-checked:border-accent hover:border-white/40">
                                                {{ strtoupper($dayLabels[$d]) }}
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="pm-btn-primary">Save Profile</button>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
    var SPORT_LEVELS = @json($sportsList->mapWithKeys(fn($s) => [$s->id => $s->getSkillLevels()]));

    function rebuildSkillLevels(sportSelect, selectedValue) {
        var row = sportSelect.closest('.sport-row');
        var levelSelect = row.querySelector('[name$="[skill_number]"]');
        if (!levelSelect) return;

        var sportId = parseInt(sportSelect.value);
        var levels  = sportId ? (SPORT_LEVELS[sportId] || []) : [];

        levelSelect.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value    = '';
        placeholder.disabled = true;
        placeholder.selected = !selectedValue;
        placeholder.textContent = 'Level';
        levelSelect.appendChild(placeholder);

        levels.forEach(function(level) {
            var opt = document.createElement('option');
            opt.value       = level.value;
            opt.textContent = level.name;
            if (selectedValue !== undefined && level.value == selectedValue) {
                opt.selected = true;
            }
            levelSelect.appendChild(opt);
        });
    }
    </script>
    @endpush

    {{-- My Connections --}}
    <section class="pm-card p-8 lg:p-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="pm-section-title">Network</p>
                <h2 class="font-display text-3xl uppercase mt-3">My Connections</h2>
            </div>
            <a href="{{ route('connections.index') }}" class="pm-btn-ghost">Manage</a>
        </div>

        @php
            $myConns = $user->acceptedConnections()->with(['requester', 'receiver'])->get()->take(4);
        @endphp
        @if($myConns->isNotEmpty())
            <div class="grid gap-px sm:grid-cols-2 lg:grid-cols-4" style="background:rgb(var(--bg-base))">
                @foreach($myConns as $conn)
                    @php $other = $conn->getOtherUser($user); @endphp
                    <a href="{{ route('player.profile', $other) }}" class="bg-ink-900 p-4 flex items-center gap-3 transition hover:bg-ink-800">
                        <div class="h-10 w-10 border border-white/15 flex items-center justify-center font-display text-sm text-accent">
                            {{ $other->avatarInitial() }}
                        </div>
                        <span class="font-display text-sm uppercase truncate">{{ $other->name }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-white/40 text-sm">No connections yet. <a href="{{ route('matchmaking') }}" class="text-accent hover:underline font-display tracking-widest text-xs">FIND OPPONENTS &rarr;</a></p>
        @endif
    </section>

</div>
@endsection

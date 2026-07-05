{{--
    Venue Autocomplete Picker
    Props:
      :selected-name  — pre-filled venue name (old input / venue from venue_id)
      :selected-lat   — pre-filled latitude (optional, triggers map)
      :selected-lng   — pre-filled longitude (optional, triggers map)
--}}
@props([
    'selectedName' => '',
    'selectedLat'  => null,
    'selectedLng'  => null,
])

<div
    x-data="{
        query: @js($selectedName),
        results: [],
        open: false,
        loading: false,
        hasCoords: @js(!is_null($selectedLat) && !is_null($selectedLng)),
        debounceTimer: null,

        async search() {
            clearTimeout(this.debounceTimer);
            if (this.query.length < 1) { this.results = []; this.open = false; return; }
            this.debounceTimer = setTimeout(async () => {
                this.loading = true;
                const res = await fetch('/venues/search?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.open = this.results.length > 0;
                this.loading = false;
            }, 250);
        },

        select(venue) {
            this.query    = venue.name;
            this.hasCoords = !!(venue.latitude && venue.longitude);
            this.open     = false;
            this.results  = [];

            // Broadcast to map-picker via custom event
            if (venue.latitude && venue.longitude) {
                window.dispatchEvent(new CustomEvent('playmate:venue-selected', {
                    detail: {
                        lat:  parseFloat(venue.latitude),
                        lng:  parseFloat(venue.longitude),
                        name: venue.name
                    }
                }));
            }
        },

        clear() {
            this.query     = '';
            this.hasCoords = false;
            this.open      = false;
        },

        init() {
            @if(!is_null($selectedLat) && !is_null($selectedLng))
            window.dispatchEvent(new CustomEvent('playmate:venue-selected', {
                detail: {
                    lat:  {{ $selectedLat }},
                    lng:  {{ $selectedLng }},
                    name: @js($selectedName)
                }
            }));
            @endif
        }
    }"
    x-init="init()"
    @click.outside="open = false"
    class="relative"
>
    {{-- venue_name is the only field owned by this component --}}
    <input type="hidden" name="venue_name" :value="query">

    {{-- Text input --}}
    <div class="relative">
        <input
            type="text"
            x-model="query"
            @input="search(); hasCoords = false;"
            @focus="query.length >= 1 && search()"
            @keydown.escape="open = false"
            @keydown.arrow-down.prevent="$refs.dropdown?.querySelector('button')?.focus()"
            placeholder="Ketik nama venue..."
            autocomplete="off"
            class="pm-input pr-10"
        />
        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
            <template x-if="loading">
                <svg class="w-4 h-4 animate-spin text-accent" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </template>
            <template x-if="!loading && query.length > 0">
                <button type="button" @click="clear()" class="text-white/30 hover:text-white/70 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </template>
        </div>
    </div>

    {{-- Koordinat terkunci --}}
    <p x-show="hasCoords"
       class="mt-1.5 font-display text-[0.6rem] tracking-widest text-accent uppercase">
        ✓ Koordinat tersimpan — map diupdate
    </p>

    {{-- Dropdown --}}
    <div
        x-ref="dropdown"
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 left-0 right-0 mt-1 border border-white/10 divide-y divide-white/5"
        style="background:rgb(var(--bg-surface-2));max-height:320px;overflow-y:auto"
    >
        <template x-for="(venue, i) in results" :key="venue.id">
            <button
                type="button"
                @click="select(venue)"
                @keydown.arrow-down.prevent="$el.nextElementSibling?.focus()"
                @keydown.arrow-up.prevent="$el.previousElementSibling?.focus()"
                @keydown.enter.prevent="select(venue)"
                class="w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-accent/10 focus:bg-accent/10 transition outline-none group"
            >
                <span class="font-display text-[0.58rem] tracking-widest border border-accent/30 text-accent/70 px-2 py-0.5 uppercase shrink-0 mt-0.5"
                      x-text="venue.sport ?? '—'"></span>
                <div class="flex-1 min-w-0">
                    <p class="font-display text-sm uppercase text-white group-hover:text-accent transition truncate"
                       x-text="venue.name"></p>
                    <p class="text-white/40 text-xs mt-0.5 truncate"
                       x-text="[venue.area, venue.address].filter(v => v).join(' · ')"></p>
                </div>
                <template x-if="venue.latitude && venue.longitude">
                    <svg class="w-3.5 h-3.5 text-accent/50 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </template>
            </button>
        </template>

        <template x-if="!loading && results.length === 0 && query.length >= 2">
            <div class="px-4 py-3 text-white/30 font-display text-xs tracking-widest uppercase text-center">
                Venue tidak ditemukan — ketik manual OK
            </div>
        </template>
    </div>
</div>

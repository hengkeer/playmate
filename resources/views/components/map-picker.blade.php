{{-- resources/views/components/map-picker.blade.php --}}
@props([
    'latId'       => 'latitude',
    'lngId'       => 'longitude',
    'addrId'      => 'address',
    'latVal'      => null,
    'lngVal'      => null,
    'addrVal'     => null,
    'label'       => 'Location',
    'height'      => 300,
    'defaultLat'  => -6.2088,
    'defaultLng'  => 106.8456,
    'defaultZoom' => 13,
])

<div
    x-data="mapPicker()"
    x-init="init()"

    data-lat-id="{{ $latId }}"
    data-lng-id="{{ $lngId }}"
    data-addr-id="{{ $addrId }}"

    data-lat-val="{{ $latVal ?? $defaultLat }}"
    data-lng-val="{{ $lngVal ?? $defaultLng }}"
    data-addr-val="{{ $addrVal ?? '' }}"

    data-default-lat="{{ $defaultLat }}"
    data-default-lng="{{ $defaultLng }}"
    data-default-zoom="{{ $defaultZoom }}"

    class="space-y-3"
    wire:ignore
>
    {{-- Label --}}
    <label class="block font-display text-[0.6rem] tracking-widest text-white/40">
        {{ strtoupper($label) }}
    </label>

    {{-- Address Search (Nominatim) --}}
    <div class="relative">
        <input
            type="text"
            x-model="address"
            @input.debounce.500ms="searchLocation()"
            placeholder="Search address..."
            class="pm-input"
        >
        <div
            x-show="results.length > 0"
            x-cloak
            class="absolute z-50 mt-2 w-full border border-white/10 bg-ink-800 shadow-lg max-h-60 overflow-y-auto"
        >
            <template x-for="item in results" :key="item.place_id">
                <button
                    type="button"
                    @click="chooseResult(item)"
                    class="w-full px-4 py-2 text-left text-sm text-white/80 hover:bg-accent/10 hover:text-accent border-b border-white/5 last:border-b-0"
                >
                    <span x-text="item.display_name"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Hidden Fields — owned by this component, submitted with form --}}
    <input type="hidden" id="{{ $latId }}" name="{{ $latId }}">
    <input type="hidden" id="{{ $lngId }}" name="{{ $lngId }}">
    <input type="hidden" id="{{ $addrId }}" name="{{ $addrId }}">

    {{-- Map container --}}
    <div
        x-ref="map"
        class="border border-white/10 overflow-hidden"
        style="height: {{ $height }}px; width: 100%;"
    ></div>

    {{-- Bottom bar --}}
    <div class="flex justify-between items-center text-xs text-white/50 gap-3">
        <span class="truncate" x-text="address || 'Click map to set location'"></span>
        <button type="button" @click="useMyLocation()" class="pm-btn-ghost shrink-0">
            Use My Location
        </button>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
.leaflet-container { z-index: 1; }
</style>

<script>
function mapPicker() {
    return {
        map: null,
        marker: null,
        address: '',
        results: [],
        lat: null,
        lng: null,

        init() {
            const root   = this.$root;
            this.lat     = parseFloat(root.dataset.latVal);
            this.lng     = parseFloat(root.dataset.lngVal);
            this.address = root.dataset.addrVal || '';

            this.loadLeaflet(() => this.renderMap());

            // Listen for venue selected from venue-picker autocomplete
            window.addEventListener('playmate:venue-selected', (e) => {
                const { lat, lng, name } = e.detail;
                this.address = name;
                // setPosition requires map to be ready
                if (this.map && this.marker) {
                    this.setPosition(lat, lng, false); // false = skip reverse geocode
                } else {
                    // Map not ready yet — store pending and apply after render
                    this._pending = { lat, lng, name };
                }
            });
        },

        loadLeaflet(callback) {
            if (window.L) { callback(); return; }

            if (!document.getElementById('leaflet-css')) {
                const css  = document.createElement('link');
                css.id     = 'leaflet-css';
                css.rel    = 'stylesheet';
                css.href   = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(css);
            }

            const script    = document.createElement('script');
            script.src      = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload   = callback;
            document.body.appendChild(script);
        },

        renderMap() {
            this.map = L.map(this.$refs.map).setView(
                [this.lat, this.lng],
                parseInt(this.$root.dataset.defaultZoom)
            );

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);

            this.syncFields();

            this.map.on('click', (e) => {
                this.setPosition(e.latlng.lat, e.latlng.lng, true);
            });

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.setPosition(pos.lat, pos.lng, true);
            });

            setTimeout(() => {
                this.map.invalidateSize();
                // Apply any pending venue selection that arrived before map was ready
                if (this._pending) {
                    this.address = this._pending.name;
                    this.setPosition(this._pending.lat, this._pending.lng, false);
                    this._pending = null;
                }
            }, 300);
        },

        // reverseGeocode flag: true when user clicks/drags, false when venue-picker drives
        setPosition(lat, lng, reverseGeocode = true) {
            this.lat = lat;
            this.lng = lng;
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], 16);
            this.syncFields();
            if (reverseGeocode) this.reverseGeocode();
        },

        syncFields() {
            const root = this.$root;
            document.getElementById(root.dataset.latId).value  = this.lat;
            document.getElementById(root.dataset.lngId).value  = this.lng;
            document.getElementById(root.dataset.addrId).value = this.address;
        },

        async reverseGeocode() {
            try {
                const res  = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${this.lat}&lon=${this.lng}`,
                    { headers: { 'Accept-Language': 'id,en' } }
                );
                const data = await res.json();
                if (data.display_name) {
                    this.address = data.display_name;
                    this.syncFields();
                }
            } catch (e) { console.error(e); }
        },

        async searchLocation() {
            if (this.address.length < 3) { this.results = []; return; }
            try {
                const res  = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(this.address)}&limit=5&countrycodes=id`,
                    { headers: { 'Accept-Language': 'id,en' } }
                );
                this.results = await res.json();
            } catch (e) { console.error(e); }
        },

        chooseResult(item) {
            this.results = [];
            this.address = item.display_name;
            this.setPosition(parseFloat(item.lat), parseFloat(item.lon), false);
        },

        useMyLocation() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                this.setPosition(pos.coords.latitude, pos.coords.longitude, true);
            });
        }
    };
}
</script>

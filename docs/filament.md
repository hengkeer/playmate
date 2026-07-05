# Filament Admin Panel

> PlayMate — Filament v5 Admin Reference
> URL: `/admin` (requires `role = admin`)
> Last updated: June 2026

---

## 1. Access Control

The Filament admin panel is gated to users with `role = 'admin'` only.

**Implementation:** `User` model implements `FilamentUser` interface:
```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->isAdmin(); // role === 'admin'
}
```

**Admin users:** Set `role = 'admin'` in DB or via Filament Users resource. Default is `'user'`.

---

## 2. Resources

All resources auto-discovered via `discoverResources(in: app_path('Filament/Resources'))`.

| Resource | Model | Status | Notes |
|---|---|---|---|
| `UserResource` | User | ✅ Active | Full CRUD, role field (admin/user badge), email, password |
| `EventResource` | Event | ✅ Active | Full event CRUD |
| `EventParticipantResource` | EventParticipant | ✅ Active | Status badge (pending/approved/rejected), slot_number, joined_at |
| `SportResource` | Sport | ✅ Active | Multi-sport definitions with skill levels |
| `VenueResource` | Venue | ✅ Active | Venue CRUD with **Filament Pinpoint** map picker |
| `EventMessageResource` | EventMessage | ✅ Read-only | Chat messages — index only, no Create/Edit |
| `FieldResource` | Field | ❌ Deleted | Legacy court booking — removed June 2026 |
| `TimeSlotResource` | TimeSlot | ❌ Deleted | Legacy booking slots — removed June 2026 |
| `GameMatchResource` | GameMatch | ❌ Deleted | Unused (0 DB rows) — removed June 2026 |

---

## 3. VenueResource — Filament Pinpoint

Uses `Fahiem\FilamentPinpoint\Pinpoint` form component in `VenueForm`:

```php
Pinpoint::make('location')
    ->provider('leaflet')
    ->defaultLocation(-6.2088, 106.8456)
    ->height(350)
    ->latField('latitude')
    ->lngField('longitude')
    ->addressField('address')
    ->searchable()
    ->draggable()
```

**Features:**
- Address search via Nominatim (no API key needed)
- Click/drag map marker to set location
- Reverse geocoding on pin move
- "Use My Location" browser geolocation button

---

## 4. Filament Pattern (v5)

- **List pages:** `ListXxx.php` extends `ListRecords`
- **Form pages:** `EditXxx.php` extends `EditRecord`
- **Schemas:** Use `Form::configure(fn(Schema $s) => ...)` / `Table::configure(fn(Table $t) => ...)`
- **Navigation icons:** `Heroicon::OutlinedXxx` (not string names)

---

## 5. Vendor Override — Filament Pinpoint

**File:** `resources/views/vendor/filament-pinpoint/pinpoint-leaflet.blade.php`

Overrides the package's original Blade view with the following fixes (May 2026):

| Fix | Detail |
|---|---|
| Map clipping | `overflow:clip` → `overflow:hidden` on map container |
| Tile recalculation | `map.invalidateSize()` added in `selectSearchResult()` and `onSearchInput()` |
| `x-cloak` conflict | Removed `x-cloak` from search results; added explicit `x-transition` classes |
| Z-index layering | `z-index: 0 !important` on `.leaflet-container` (keeps map below dropdown `z-index: 9999`) |
| Autocomplete interference | `autocomplete="off"` added to search input |

---

## 6. Known Dark Mode Issue ⚠️

**Severity: Medium**

The Nominatim search results dropdown renders as a white box with invisible text in Filament dark mode (works correctly in light mode).

**Workaround:** Use light mode, or select location by clicking/dragging the map marker instead of using address search.

**Fix pending:** CSS color contrast issue in the vendor override at `resources/views/vendor/filament-pinpoint/pinpoint-leaflet.blade.php`.

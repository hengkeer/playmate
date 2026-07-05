<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'skill_levels'];

    protected function casts(): array
    {
        return [
            'skill_levels' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserSport::class);
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /** The raw max skill value for this sport (before normalization to 1–10). */
    public function maxSkillValue(): int
    {
        return match ($this->slug) {
            'tennis'    => 12,
            'badminton' => 8,
            'padel'     => 7,
            default     => 10,
        };
    }

    /**
     * Get human-readable skill label by raw value.
     * Looks up by 'value' key — NOT by array index.
     */
    public function getSkillLabel(int $rawValue): string
    {
        $levels = $this->getSkillLevels();
        foreach ($levels as $level) {
            if (isset($level['value']) && (int) $level['value'] === $rawValue) {
                return $level['name'] ?? "Level $rawValue";
            }
        }
        return "Level $rawValue";
    }

    /**
     * Always returns a SAFE array of skill levels in structured format:
     *   [{ "value": 1, "name": "..." }, { "value": 2, "name": "..." }, ...]
     *
     * Handles three cases:
     * 1. Already a PHP array (current/new format) → pass through
     * 2. Raw JSON string → json_decode + legacy conversion if needed
     * 3. Legacy simple array ["Beginner", ...] or numeric [1, 2, ...] → convert to new format
     *
     * Auto-detects legacy formats:
     *   ["Beginner", "Intermediate", ...]   → {value:1, name:"Beginner"}, ...
     *   [1, 2, 3, 4, 5] (numeric)          → {value:1, name:"Level 1"}, ...
     */
    public function getSkillLevels(): array
    {
        $raw = $this->skill_levels;

        // Case 1: already a proper PHP array — check structure
        if (is_array($raw)) {
            // If it looks like the new structured format, return as-is
            if (!empty($raw) && isset($raw[0])) {
                $first = $raw[0];
                // New format: {value: x, name: "y"} — already structured
                if (isset($first['value']) || isset($first['name'])) {
                    return $raw;
                }
                // Legacy numeric array [1, 2, 3] or string array ["A", "B"]
                if (is_string($first)) {
                    return $this->convertLegacyStringArray($raw);
                }
                if (is_numeric($first)) {
                    return $this->convertLegacyNumericArray($raw);
                }
            }
            return $raw;
        }

        // Case 2: JSON string — decode then check structure
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            // If JSON decode failed or returned non-array, return empty
            if (!is_array($decoded)) {
                return [];
            }
            $raw = $decoded;
        }

        // Case 3: decoded array — apply legacy conversion logic
        if (!empty($raw) && isset($raw[0])) {
            $first = $raw[0];
            if (isset($first['value']) || isset($first['name'])) {
                // Already structured (possibly double-encoded string in DB)
                return $raw;
            }
            if (is_string($first)) {
                return $this->convertLegacyStringArray($raw);
            }
            if (is_numeric($first)) {
                return $this->convertLegacyNumericArray($raw);
            }
        }

        return [];
    }

    /**
     * Convert legacy array of strings → new structured format.
     * e.g. ["Beginner", "Intermediate", "Advanced"]
     *   → [{value:1, name:"Beginner"}, {value:2, name:"Intermediate"}, ...]
     */
    protected function convertLegacyStringArray(array $levels): array
    {
        $result = [];
        foreach ($levels as $index => $label) {
            $result[] = [
                'value' => $index + 1,
                'name'  => (string) $label,
            ];
        }
        return $result;
    }

    /**
     * Convert legacy numeric-only array → new structured format.
     * e.g. [1, 2, 3, 4, 5] → [{value:1, name:"Level 1"}, {value:2, name:"Level 2"}, ...]
     */
    protected function convertLegacyNumericArray(array $levels): array
    {
        $result = [];
        foreach ($levels as $value) {
            $result[] = [
                'value' => (int) $value,
                'name'  => "Level " . (int) $value,
            ];
        }
        return $result;
    }

    /**
     * Get skill label from a NORMALIZED 1–10 skill_number.
     * Finds the nearest raw value, then returns its label.
     */
    public function getLabelFromNormalized(int $normalized): string
    {
        $maxRaw = $this->maxSkillValue();
        // Reverse: 1→1, 10→maxRaw
        $raw = (int) round((($normalized - 1) / 9) * ($maxRaw - 1) + 1);
        $raw = max(1, min($maxRaw, $raw));
        return $this->getSkillLabel($raw);
    }

    /**
     * Clamp a raw skill value to the valid range for this sport,
     * then normalize it to the uniform 1–10 scale.
     *
     * Formula (improved linear): (value - 1) / (maxValue - 1) * 9 + 1
     * Then round and clamp to 1–10.
     *
     * Distribution (vs old formula):
     *   Tennis  (1-12): new→ 1→1, 6→5, 7→6, 12→10  (old: 1→1, 6→6, 12→10)
     *   Badminton (1-8): new→ 1→1, 4→4, 8→10     (old: 1→1, 4→5, 8→10)
     *   Padel   (1-7):  new→ 1→1, 4→5, 7→10      (old: 1→1, 4→6, 7→10)
     */
    public function normalizeSkill(int $rawValue): int
    {
        $maxValue = $this->maxSkillValue();
        $clamped  = max(1, min($maxValue, $rawValue));
        $normalized = (($clamped - 1) / max(1, $maxValue - 1)) * 9 + 1;
        return max(1, min(10, (int) round($normalized)));
    }
}
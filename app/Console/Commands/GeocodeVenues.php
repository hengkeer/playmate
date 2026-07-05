<?php

namespace App\Console\Commands;

use App\Models\Venue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GeocodeVenues extends Command
{
    protected $signature   = 'venue:geocode {--all : Re-geocode venues that already have coordinates}';
    protected $description = 'Geocode venues with missing latitude/longitude using OpenStreetMap Nominatim';

    public function handle(): int
    {
        $query = $this->option('all')
            ? Venue::all()
            : Venue::whereNull('latitude')->orWhereNull('longitude')->get();

        if ($query->isEmpty()) {
            $this->info('All venues already have coordinates.');
            return 0;
        }

        $this->info("Geocoding {$query->count()} venue(s)...");
        $this->newLine();

        $ok   = 0;
        $fail = 0;

        foreach ($query as $venue) {
            // Build a clean search query: prefer address, fallback to name + area
            $searchStr = trim($venue->address
                ? "{$venue->name}, {$venue->address}"
                : "{$venue->name}, {$venue->area}, Tangerang Selatan, Indonesia");

            // Nominatim requires a unique User-Agent
            $response = Http::withHeaders([
                'User-Agent' => 'PlayMate/1.0 (laravel-app)',
                'Accept-Language' => 'en',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q'      => $searchStr,
                'format' => 'json',
                'limit'  => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];
                $lat    = (float) $result['lat'];
                $lng    = (float) $result['lon'];

                $venue->update([
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ]);

                $this->line("  ✅ <info>{$venue->name}</info> → {$lat}, {$lng}");
                $ok++;
            } else {
                // Try fallback: just name + "Tangerang Selatan"
                $fallback = "{$venue->name}, Tangerang Selatan, Banten, Indonesia";
                $response2 = Http::withHeaders([
                    'User-Agent' => 'PlayMate/1.0 (laravel-app)',
                    'Accept-Language' => 'en',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q'      => $fallback,
                    'format' => 'json',
                    'limit'  => 1,
                ]);

                if ($response2->successful() && count($response2->json()) > 0) {
                    $result = $response2->json()[0];
                    $lat    = (float) $result['lat'];
                    $lng    = (float) $result['lon'];

                    $venue->update([
                        'latitude'  => $lat,
                        'longitude' => $lng,
                    ]);

                    $this->line("  ✅ <info>{$venue->name}</info> (fallback) → {$lat}, {$lng}");
                    $ok++;
                } else {
                    $this->warn("  ❌ {$venue->name} — not found");
                    $fail++;
                }
            }

            // Nominatim rate limit: max 1 request/second
            sleep(1);
        }

        $this->newLine();
        $this->info("Done. ✅ {$ok} geocoded, ❌ {$fail} failed.");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Venue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportVenues extends Command
{
    protected $signature   = 'import:venues {--file= : Path to venues.json} {--fresh : Delete existing venues first}';
    protected $description = 'Import scraped venues from ayo-scraper/output/venues.json';

    public function handle(): int
    {
        $file = $this->option('file')
            ?? base_path('../ayo-scraper/output/venues.json');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            $this->line("Usage: php artisan import:venues --file=/path/to/venues.json");
            return 1;
        }

        $venues = json_decode(file_get_contents($file), true);
        if (! $venues) {
            $this->error("Invalid JSON or empty file.");
            return 1;
        }

        if ($this->option('fresh')) {
            $this->warn("Deleting existing venues...");
            Venue::truncate();
        }

        // Copy images to Laravel public storage
        $imageSourceDir = dirname($file) . '/images/';
        $imageDestDir   = storage_path('app/public/venues/');
        if (! is_dir($imageDestDir)) {
            mkdir($imageDestDir, 0755, true);
        }

        $imported = 0;
        $skipped  = 0;

        $this->withProgressBar($venues, function (array $data) use ($imageSourceDir, $imageDestDir, &$imported, &$skipped) {
            // Skip if no sport_id (futsal, basket, etc. not in PlayMate)
            if (empty($data['sport_id'])) {
                $skipped++;
                return;
            }

            // Copy images
            $copiedImage = null;
            if (! empty($data['images'])) {
                foreach ($data['images'] as $filename) {
                    $src = $imageSourceDir . $filename;
                    $dst = $imageDestDir . $filename;
                    if (file_exists($src) && ! file_exists($dst)) {
                        copy($src, $dst);
                    }
                }
                $copiedImage = 'venues/' . $data['images'][0];
            }

            Venue::updateOrCreate(
                ['name' => $data['name'], 'area' => $data['area']],
                [
                    'sport_id'       => $data['sport_id'],
                    'address'        => $data['address']        ?? '',
                    'area'           => $data['area']           ?? '',
                    'latitude'       => $data['latitude']       ?? null,
                    'longitude'      => $data['longitude']      ?? null,
                    'open_hours'     => $data['open_hours']     ?? null,
                    'description'    => $data['description']    ?? null,
                    'price_estimate' => $data['price_estimate'] ?? null,
                    'contact'        => $data['contact']        ?? null,
                    'image_url'      => $copiedImage,
                    'images'         => ! empty($data['images'])
                        ? array_map(fn($f) => 'venues/' . $f, $data['images'])
                        : null,
                    'is_active'      => true,
                ]
            );

            $imported++;
        });

        $this->newLine(2);
        $this->info("✅ Import complete: {$imported} imported, {$skipped} skipped (no sport match).");
        $this->line("Run <comment>php artisan storage:link</comment> if images are not showing.");

        return 0;
    }
}

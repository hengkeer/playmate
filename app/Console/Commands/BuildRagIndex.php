<?php

namespace App\Console\Commands;

use App\Exceptions\GeminiException;
use App\Services\GeminiClient;
use Illuminate\Console\Command;

class BuildRagIndex extends Command
{
    protected $signature = 'rag:build {--refresh : Force rebuild even if index exists}';
    protected $description = 'Chunk docs/*.md, embed via Gemini, and write storage/app/private/rag/{chunks,embeddings}.json';

    public function handle(GeminiClient $gemini): int
    {
        $docsPath = base_path('docs');
        if (!is_dir($docsPath)) {
            $this->error("docs/ folder not found at {$docsPath}");
            return self::FAILURE;
        }

        $outDir = (string) config('gemini.rag_index_path');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        if (!$this->option('refresh') && file_exists($outDir . '/chunks.json') && file_exists($outDir . '/embeddings.json')) {
            $existing = count(json_decode(file_get_contents($outDir . '/chunks.json'), true) ?? []);
            if ($this->confirm("Index already exists with {$existing} chunks. Rebuild?", false)) {
                // proceed
            } else {
                $this->info('Build skipped.');
                return self::SUCCESS;
            }
        }

        $files = glob($docsPath . '/*.md') ?: [];
        $files = array_filter($files, fn($f) => !str_contains($f, 'PROJECT-DOCS'));

        $this->info('Splitting ' . count($files) . ' markdown files into chunks...');
        $chunks = [];
        foreach ($files as $file) {
            foreach ($this->splitFile($file) as $c) {
                $chunks[] = $c;
            }
        }

        $this->info('Embedding ' . count($chunks) . ' chunks via Gemini (this may take ~' . (int) (count($chunks) * 0.7) . 's)...');

        $embeddings = [];
        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        foreach ($chunks as $i => $chunk) {
            try {
                $embeddings[] = $gemini->embed($chunk['text']);
            } catch (GeminiException $e) {
                $bar->finish();
                $this->newLine();
                $this->error("Embed failed at chunk #{$i} ({$chunk['source']}): " . $e->getMessage());
                return self::FAILURE;
            }
            $bar->advance();
            usleep(700_000); // ~0.7s, stay under 60 RPM
        }

        $bar->finish();
        $this->newLine(2);

        file_put_contents($outDir . '/chunks.json', json_encode($chunks, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents($outDir . '/embeddings.json', json_encode($embeddings, JSON_UNESCAPED_UNICODE));

        $this->info("Indexed " . count($chunks) . " chunks from " . count($files) . " files.");
        $this->info("Wrote: {$outDir}/chunks.json");
        $this->info("Wrote: {$outDir}/embeddings.json");

        return self::SUCCESS;
    }

    /**
     * Split a markdown file by H2 headings. Long sections get a sliding-window sub-split.
     *
     * @return array<int, array{text: string, source: string}>
     */
    protected function splitFile(string $path): array
    {
        $rel = 'docs/' . basename($path);
        $content = file_get_contents($path);
        $content = preg_replace('/```[\s\S]*?```/', '', $content) ?? $content;
        $content = preg_replace('/\n{3,}/', "\n\n", $content) ?? $content;

        $sections = preg_split('/(?=^## )/m', $content, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        foreach ($sections as $section) {
            $lines = explode("\n", $section, 2);
            $heading = trim($lines[0] ?? '');
            $body    = $lines[1] ?? '';
            $body    = trim($body);
            if ($body === '' && $heading === '') {
                continue;
            }

            $headingLabel = $heading === '' ? 'Intro' : preg_replace('/^#+\s*/', '', $heading);
            $sourceBase   = $rel . '#' . Str_slug_local($headingLabel);

            $text = $heading === '' ? $body : "{$heading}\n\n{$body}";

            if (mb_strlen($text, 'UTF-8') <= 1500) {
                $chunks[] = ['text' => $text, 'source' => $sourceBase];
            } else {
                $window = 800;
                $stride = 400;
                $offset = 0;
                $idx = 0;
                $len = mb_strlen($text, 'UTF-8');
                while ($offset < $len) {
                    $piece = mb_substr($text, $offset, $window, 'UTF-8');
                    $chunks[] = ['text' => $piece, 'source' => "{$sourceBase}:chunk-{$idx}"];
                    $offset += $stride;
                    $idx++;
                    if ($idx > 20) break; // safety cap
                }
            }
        }

        return $chunks;
    }
}

if (!function_exists('Str_slug_local')) {
    function Str_slug_local(string $s): string
    {
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        return trim($s, '-');
    }
}

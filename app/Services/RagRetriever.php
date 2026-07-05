<?php

namespace App\Services;

use App\Exceptions\RagIndexMissingException;

class RagRetriever
{
    protected ?array $chunks = null;
    protected ?array $embeddings = null;

    public function __construct(protected GeminiClient $gemini) {}

    /**
     * Search the local RAG index using cosine similarity.
     *
     * @return array<int, array{chunk: string, source: string, score: float}>
     */
    public function search(string $query, int $k = 5): array
    {
        $this->loadIndex();

        $queryVec = $this->gemini->embed($query);
        $queryNorm = $this->norm($queryVec);

        if ($queryNorm == 0.0) {
            return [];
        }

        $scored = [];
        foreach ($this->embeddings as $i => $chunkVec) {
            $chunkNorm = $this->norm($chunkVec);
            if ($chunkNorm == 0.0) {
                continue;
            }
            $score = $this->dot($queryVec, $chunkVec) / ($queryNorm * $chunkNorm);
            $scored[] = [
                'chunk'  => $this->chunks[$i]['text'] ?? '',
                'source' => $this->chunks[$i]['source'] ?? '',
                'score'  => $score,
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $k);
    }

    /**
     * Build a context string from top chunks, respecting char budget.
     */
    public function buildContext(array $results, int $maxChars = 6000): string
    {
        $parts = [];
        $used  = 0;

        foreach ($results as $r) {
            $line = "[sumber: {$r['source']}]\n{$r['chunk']}";
            $len  = strlen($line) + 2;
            if ($used + $len > $maxChars) {
                break;
            }
            $parts[] = $line;
            $used += $len;
        }

        return implode("\n\n---\n\n", $parts);
    }

    public function isReady(): bool
    {
        $dir = config('gemini.rag_index_path');
        return file_exists($dir . '/chunks.json') && file_exists($dir . '/embeddings.json');
    }

    protected function loadIndex(): void
    {
        if ($this->chunks !== null && $this->embeddings !== null) {
            return;
        }

        $dir = config('gemini.rag_index_path');
        $chunksPath = $dir . '/chunks.json';
        $embPath    = $dir . '/embeddings.json';

        if (!file_exists($chunksPath) || !file_exists($embPath)) {
            throw new RagIndexMissingException('RAG index not built. Run: php artisan rag:build');
        }

        $this->chunks     = json_decode(file_get_contents($chunksPath), true) ?? [];
        $this->embeddings = json_decode(file_get_contents($embPath), true) ?? [];
    }

    protected function dot(array $a, array $b): float
    {
        $sum = 0.0;
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $sum += $a[$i] * $b[$i];
        }
        return $sum;
    }

    protected function norm(array $v): float
    {
        return sqrt($this->dot($v, $v));
    }
}

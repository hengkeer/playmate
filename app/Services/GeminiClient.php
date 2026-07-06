<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeminiClient
{
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl = 'https://generativelanguage.googleapis.com',
        protected string $embeddingModel = 'gemini-embedding-001',
        protected string $chatModel = 'gemini-2.0-flash',
    ) {}

    /**
     * Embed a single text into a float vector.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $endpoint = "/v1beta/models/{$this->embeddingModel}:embedContent";

        $text = $this->ensureUtf8($text);

        $body = [
            'content'              => ['parts' => [['text' => $text]]],
            'outputDimensionality' => 768,
        ];

        $data = $this->post($endpoint, $body);

        return $data['embedding']['values'] ?? throw new GeminiException('Empty embedding response');
    }

    protected function ensureUtf8(string $text): string
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        return mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    }

    /**
     * Call Gemini chat model. Returns [reply, tokens].
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, tokens: int|null}
     */
    public function chat(string $systemPrompt, array $history, string $userMessage, ?string $context = null): array
    {
        $contents = [];

        foreach ($history as $turn) {
            $role = $turn['role'] === 'user' ? 'user' : 'model';
            $contents[] = ['role' => $role, 'parts' => [['text' => $this->ensureUtf8($turn['content'])]]];
        }

        $userText = $context
            ? "CONTEXT (dokumentasi PlayMate):\n{$context}\n\n---\n\nPERTANYAAN USER:\n{$userMessage}"
            : $userMessage;

        $contents[] = ['role' => 'user', 'parts' => [['text' => $this->ensureUtf8($userText)]]];

        $body = [
            'systemInstruction' => ['parts' => [['text' => $this->ensureUtf8($systemPrompt)]]],
            'contents'          => $contents,
            'generationConfig'  => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 2048,
            ],
        ];

        $endpoint = "/v1beta/models/{$this->chatModel}:generateContent";

        $data = $this->post($endpoint, $body);

        $reply = $data['candidates'][0]['content']['parts'][0]['text']
            ?? throw new GeminiException('Empty chat response');
        $tokens = $data['usageMetadata']['totalTokenCount'] ?? null;
        $finishReason = $data['candidates'][0]['finishReason'] ?? null;

        $reply = trim($reply);
        if ($finishReason === 'MAX_TOKENS') {
            $reply .= "\n\n_(jawaban terpotong karena terlalu panjang — coba tanyakan dengan lebih spesifik)_";
        }

        return ['reply' => $reply, 'tokens' => $tokens];
    }

    /**
     * @return array<string, mixed>
     */
    protected function post(string $endpoint, array $body): array
    {
        if (empty($this->apiKey)) {
            throw new GeminiException('GEMINI_API_KEY is not configured');
        }

        $url = $this->baseUrl . $endpoint . '?key=' . $this->apiKey;

        $response = $this->http()
            ->post($url, $body);

        if ($response->status() === 429) {
            throw new GeminiException('Gemini rate limit exceeded');
        }

        if ($response->serverError()) {
            throw new GeminiException('Gemini server error: ' . $response->status());
        }

        if ($response->clientError() && $response->status() !== 429) {
            throw new GeminiException('Gemini client error: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    protected function http(): PendingRequest
    {
        return Http::timeout(30)->acceptJson()->asJson();
    }
}

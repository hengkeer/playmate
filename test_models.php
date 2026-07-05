<?php
$apiKey = getenv('GEMINI_API_KEY') ?: trim(file_get_contents('.env.test') ?: '');

// Read from .env file
$env = parse_ini_file('.env');
$apiKey = $env['GEMINI_API_KEY'];

$models = [
    'gemini-2.0-flash',
    'gemini-2.0-flash-lite',
    'gemini-2.5-flash-preview-05-20',
    'gemini-2.5-flash',
];

foreach ($models as $model) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'contents' => [['parts' => [['text' => 'Say OK']]]]
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode === 200) {
        echo "✓ [{$model}] WORKS!\n";
    } elseif ($httpCode === 429) {
        $retryIn = '';
        if (preg_match('/retry in ([\d.]+)s/', $data['error']['message'] ?? '', $m)) {
            $retryIn = " (retry in {$m[1]}s)";
        }
        echo "⚠ [{$model}] Rate limited{$retryIn}\n";
    } elseif ($httpCode === 404) {
        echo "✗ [{$model}] Model not found\n";
    } else {
        $msg = $data['error']['message'] ?? 'Unknown error';
        echo "✗ [{$model}] HTTP {$httpCode}: " . substr($msg, 0, 80) . "\n";
    }
}

<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\GeminiClient;

$key = config('gemini.api_key');
echo "API Key: " . (strlen($key) > 0 ? "✓ Ada\n" : "✗ Tidak ada\n");

if (!$key) {
    echo "ERROR: GEMINI_API_KEY tidak diset di .env\n";
    exit(1);
}

$client = new GeminiClient($key);

try {
    echo "Testing Gemini API...\n";
    $result = $client->chat(
        "You are helpful assistant.",
        [],
        "Say 'API works' if you can hear me",
        "Test context"
    );

    echo "✓ SUCCESS! Gemini API berfungsi.\n";
    echo "Response: " . $result['reply'] . "\n";
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
}

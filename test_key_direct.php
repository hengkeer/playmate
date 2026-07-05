<?php
$apiKey = 'AQ.Ab8RN6KELhSeLfyKbloabreC47ubNJOSCrjTm9Y-XPsQbpgd-g';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey",
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'contents' => [
            'parts' => [['text' => 'Say OK']]
        ]
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 500) . "\n";

$data = json_decode($response, true);
if (isset($data['error'])) {
    echo "Error: " . $data['error']['message'] . "\n";
} elseif (isset($data['candidates'])) {
    echo "✓ API WORKS!\n";
}
?>

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\ChatSession;
use App\Services\ChatbotService;

// Get user
$user = User::first();
if (!$user) {
    echo "No user found\n";
    exit(1);
}

echo "User: {$user->name}\n";

// Get or create session
$session = ChatSession::where('user_id', $user->id)->first();
if (!$session) {
    $session = ChatSession::create(['user_id' => $user->id, 'title' => null]);
    echo "Created new session: {$session->id}\n";
} else {
    echo "Using existing session: {$session->id}\n";
}

// Test chatbot service
$bot = app(ChatbotService::class);
try {
    echo "\nSending message...\n";
    $msg = $bot->ask($session, "test");
    echo "✓ SUCCESS\n";
    echo "Reply: " . $msg->content . "\n";
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Exception: " . get_class($e) . "\n";
}

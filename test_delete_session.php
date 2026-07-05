<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\ChatSession;

// Get user
$user = User::first();
$session = ChatSession::where('user_id', $user->id)->first();

if (!$session) {
    echo "No session to delete\n";
    exit;
}

echo "Session ID: {$session->id}\n";
echo "Messages count: " . $session->messages()->count() . "\n";

try {
    $session->delete();
    echo "✓ Session deleted successfully\n";

    // Verify
    $exists = ChatSession::find($session->id);
    echo "Session still exists: " . ($exists ? "YES ❌" : "NO ✓") . "\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

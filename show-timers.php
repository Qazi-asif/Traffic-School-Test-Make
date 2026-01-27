<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CourseTimer;

echo "=== Current Timer Configurations ===\n\n";

$timers = CourseTimer::all();

if ($timers->count() == 0) {
    echo "No timers configured.\n";
} else {
    foreach ($timers as $timer) {
        echo "Timer ID: {$timer->id}\n";
        echo "Chapter ID: {$timer->chapter_id}\n";
        echo "Chapter Type: {$timer->chapter_type}\n";
        echo "Required Time: {$timer->required_time_minutes} minutes\n";
        echo "Enabled: " . ($timer->is_enabled ? 'Yes' : 'No') . "\n";
        echo "Allow Pause: " . ($timer->allow_pause ? 'Yes' : 'No') . "\n";
        echo "Bypass for Admin: " . ($timer->bypass_for_admin ? 'Yes' : 'No') . "\n";
        echo "Created: {$timer->created_at}\n";
        echo "Updated: {$timer->updated_at}\n";
        echo "---\n";
    }
}

echo "\n✅ Timer system is ready for testing!\n";
echo "Available chapters with timers: " . $timers->pluck('chapter_id')->join(', ') . "\n";
?>
<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CourseTimer;

try {
    // Check if timer already exists for chapter 7
    $existingTimer = CourseTimer::where('chapter_id', 7)
        ->where('chapter_type', 'chapters')
        ->first();
    
    if ($existingTimer) {
        echo "Timer already exists for Chapter 7:\n";
        echo "- ID: {$existingTimer->id}\n";
        echo "- Required Time: {$existingTimer->required_time_minutes} minutes\n";
        echo "- Enabled: " . ($existingTimer->is_enabled ? 'Yes' : 'No') . "\n";
    } else {
        // Create new timer for chapter 7
        $timer = CourseTimer::create([
            'chapter_id' => 7,
            'chapter_type' => 'chapters',
            'required_time_minutes' => 3, // 3 minutes
            'is_enabled' => true,
            'allow_pause' => true,
            'bypass_for_admin' => true,
        ]);
        
        echo "✅ Timer created successfully for Chapter 7!\n";
        echo "- Timer ID: {$timer->id}\n";
        echo "- Chapter ID: {$timer->chapter_id}\n";
        echo "- Required Time: {$timer->required_time_minutes} minutes\n";
        echo "- Enabled: " . ($timer->is_enabled ? 'Yes' : 'No') . "\n";
        echo "- Allow Pause: " . ($timer->allow_pause ? 'Yes' : 'No') . "\n";
        echo "- Bypass for Admin: " . ($timer->bypass_for_admin ? 'Yes' : 'No') . "\n";
    }
    
    // Show all timers
    echo "\n=== All Timer Configurations ===\n";
    $allTimers = CourseTimer::all();
    foreach ($allTimers as $timer) {
        echo "Chapter {$timer->chapter_id}: {$timer->required_time_minutes} minutes " . 
             ($timer->is_enabled ? '(Enabled)' : '(Disabled)') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
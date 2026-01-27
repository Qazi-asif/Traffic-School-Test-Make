<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Timer System Test Setup ===\n";
    
    // Create a test user if it doesn't exist
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'password' => bcrypt('password123'),
            'role_id' => 1, // Admin role
            'email_verified_at' => now(),
        ]
    );
    
    echo "✅ Test user created/found: {$user->email} (ID: {$user->id})\n";
    
    // Create a test timer configuration
    $timer = \App\Models\CourseTimer::updateOrCreate(
        [
            'chapter_id' => 1,
            'chapter_type' => 'chapters',
        ],
        [
            'required_time_minutes' => 2, // 2 minutes for testing
            'is_enabled' => true,
            'allow_pause' => false,
            'bypass_for_admin' => false,
        ]
    );
    
    echo "✅ Test timer created: Chapter {$timer->chapter_id} ({$timer->chapter_type}) - {$timer->required_time_minutes} minutes\n";
    
    // Check if chapter exists
    $chapter = \App\Models\Chapter::find(1);
    if (!$chapter) {
        // Create a test chapter
        $chapter = \App\Models\Chapter::create([
            'id' => 1,
            'title' => 'Test Chapter',
            'content' => 'This is a test chapter for timer testing.',
            'course_id' => 1,
            'order_index' => 1,
        ]);
        echo "✅ Test chapter created: {$chapter->title}\n";
    } else {
        echo "✅ Test chapter found: {$chapter->title}\n";
    }
    
    // Check authentication configuration
    $guards = config('auth.guards');
    echo "✅ Available auth guards: " . implode(', ', array_keys($guards)) . "\n";
    
    // Test timer service
    $timerService = new \App\Services\CourseTimerService();
    echo "✅ Timer service instantiated successfully\n";
    
    echo "\n=== Setup Complete ===\n";
    echo "You can now:\n";
    echo "1. Start the Laravel server: php artisan serve\n";
    echo "2. Login with: test@example.com / password123\n";
    echo "3. Visit: http://127.0.0.1:8000/test-timer\n";
    echo "4. Test with Chapter ID: 1\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
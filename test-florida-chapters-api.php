<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Florida Chapters API ===\n\n";

// Test course 9 (Aggressive Driving Course)
$courseId = 9;

echo "Testing course {$courseId}...\n";

try {
    // Simulate the API call
    $controller = new App\Http\Controllers\ChapterController();
    
    // Create a mock request for florida-courses
    $request = new Illuminate\Http\Request();
    $request->server->set('REQUEST_URI', "/api/florida-courses/{$courseId}/chapters");
    app()->instance('request', $request);
    
    $response = $controller->indexWeb($courseId);
    $chapters = json_decode($response->getContent(), true);
    
    echo "API Response Status: " . $response->getStatusCode() . "\n";
    echo "Number of chapters found: " . count($chapters) . "\n\n";
    
    if (count($chapters) > 0) {
        echo "Chapters:\n";
        foreach ($chapters as $chapter) {
            echo "- Chapter {$chapter['id']}: '{$chapter['title']}' (Order: {$chapter['order_index']})\n";
        }
    } else {
        echo "No chapters found!\n";
    }
    
    echo "\n=== Test completed ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
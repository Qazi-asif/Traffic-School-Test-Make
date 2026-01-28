<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Chapter Update Fix Test ===\n";

try {
    // Check table structure
    $columns = DB::getSchemaBuilder()->getColumnListing('chapters');
    echo "✅ Chapters table columns: " . implode(', ', $columns) . "\n";
    
    // Check if video_url exists
    $hasVideoUrl = in_array('video_url', $columns);
    echo "📹 Has video_url column: " . ($hasVideoUrl ? 'YES' : 'NO') . "\n";
    
    // Get a test chapter
    $chapter = DB::table('chapters')->first();
    if ($chapter) {
        echo "✅ Found test chapter: ID {$chapter->id} - '{$chapter->title}'\n";
        
        // Test update data structure
        $updateData = [
            'title' => $chapter->title,
            'content' => $chapter->content . "\n<!-- Updated at " . date('Y-m-d H:i:s') . " -->",
        ];
        
        // Add columns that exist
        if (in_array('duration', $columns)) {
            $updateData['duration'] = 30;
        }
        
        if (in_array('video_url', $columns)) {
            $updateData['video_url'] = null; // Test with null value
        }
        
        if (in_array('is_active', $columns)) {
            $updateData['is_active'] = true;
        }
        
        echo "📝 Update data keys: " . implode(', ', array_keys($updateData)) . "\n";
        
        // Test the update
        $result = DB::table('chapters')->where('id', $chapter->id)->update($updateData);
        
        if ($result) {
            echo "✅ Chapter update test PASSED - Updated successfully\n";
        } else {
            echo "❌ Chapter update test FAILED - No rows updated\n";
        }
        
    } else {
        echo "❌ No chapters found in database\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== FLORIDA COURSES TABLE STRUCTURE DEBUG ===\n\n";

try {
    // Check if table exists
    $tableExists = Schema::hasTable('florida_courses');
    echo "Table 'florida_courses' exists: " . ($tableExists ? 'YES' : 'NO') . "\n\n";
    
    if ($tableExists) {
        // Get table structure
        echo "=== COLUMN INFORMATION ===\n";
        $columns = DB::select("DESCRIBE florida_courses");
        
        foreach ($columns as $column) {
            echo "Column: {$column->Field}\n";
            echo "  Type: {$column->Type}\n";
            echo "  Null: {$column->Null}\n";
            echo "  Key: {$column->Key}\n";
            echo "  Default: {$column->Default}\n";
            echo "  Extra: {$column->Extra}\n\n";
        }
        
        // Check specific columns
        echo "=== SPECIFIC COLUMN CHECKS ===\n";
        $hasState = Schema::hasColumn('florida_courses', 'state');
        $hasStateCode = Schema::hasColumn('florida_courses', 'state_code');
        
        echo "Has 'state' column: " . ($hasState ? 'YES' : 'NO') . "\n";
        echo "Has 'state_code' column: " . ($hasStateCode ? 'YES' : 'NO') . "\n\n";
        
        // Check sample data
        echo "=== SAMPLE DATA ===\n";
        $sampleData = DB::table('florida_courses')->limit(3)->get();
        
        if ($sampleData->count() > 0) {
            foreach ($sampleData as $row) {
                echo "Course ID: {$row->id}\n";
                echo "Title: {$row->title}\n";
                if (isset($row->state)) {
                    echo "State: {$row->state}\n";
                }
                if (isset($row->state_code)) {
                    echo "State Code: {$row->state_code}\n";
                }
                echo "---\n";
            }
        } else {
            echo "No sample data found\n";
        }
        
        // Test course creation
        echo "\n=== TESTING COURSE CREATION ===\n";
        
        $testData = [
            'title' => 'Test Course - Debug',
            'description' => 'Test course for debugging',
            'state_code' => 'FL',
            'passing_score' => 80,
            'duration' => 240,
            'price' => 29.99,
            'is_active' => true,
            'course_type' => 'BDI',
        ];
        
        echo "Attempting to create course with data:\n";
        print_r($testData);
        
        try {
            $courseId = DB::table('florida_courses')->insertGetId($testData);
            echo "SUCCESS: Course created with ID: {$courseId}\n";
            
            // Clean up test data
            DB::table('florida_courses')->where('id', $courseId)->delete();
            echo "Test course deleted\n";
            
        } catch (Exception $e) {
            echo "ERROR creating course: " . $e->getMessage() . "\n";
            echo "Error code: " . $e->getCode() . "\n";
            
            // Try with 'state' instead of 'state_code'
            echo "\nTrying with 'state' field instead...\n";
            $testData2 = $testData;
            unset($testData2['state_code']);
            $testData2['state'] = 'FL';
            
            try {
                $courseId = DB::table('florida_courses')->insertGetId($testData2);
                echo "SUCCESS: Course created with 'state' field, ID: {$courseId}\n";
                
                // Clean up test data
                DB::table('florida_courses')->where('id', $courseId)->delete();
                echo "Test course deleted\n";
                
            } catch (Exception $e2) {
                echo "ERROR with 'state' field too: " . $e2->getMessage() . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
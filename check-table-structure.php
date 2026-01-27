<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking final_exam_results table structure ===\n\n";

try {
    $columns = DB::select("SHOW COLUMNS FROM final_exam_results");
    
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "  ✓ {$col->Field} ({$col->Type})\n";
    }
    
    echo "\n";
    
    // Check for required columns
    $required = ['is_passing', 'final_exam_score', 'overall_score', 'status', 'grade_letter'];
    $existing = array_column($columns, 'Field');
    $missing = array_diff($required, $existing);
    
    if (empty($missing)) {
        echo "✅ All required columns exist!\n";
    } else {
        echo "❌ Missing columns:\n";
        foreach ($missing as $col) {
            echo "  - $col\n";
        }
        echo "\nRun: php fix-final-exam-table.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

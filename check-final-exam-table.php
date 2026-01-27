<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Checking final_exam_results table ===\n\n";

try {
    // Check if table exists
    $tableExists = Schema::hasTable('final_exam_results');
    
    if (!$tableExists) {
        echo "❌ Table 'final_exam_results' does NOT exist\n";
        echo "\nRun this command to create it:\n";
        echo "php artisan migrate --path=database/migrations/2025_12_31_000003_create_final_exam_results_table.php\n";
    } else {
        echo "✅ Table 'final_exam_results' exists\n\n";
        
        // Check columns
        $columns = DB::select("SHOW COLUMNS FROM final_exam_results");
        echo "Columns in final_exam_results:\n";
        foreach ($columns as $col) {
            echo "  - {$col->Field} ({$col->Type})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

<?php

/**
 * Add missing columns to user_course_enrollments table
 * Run: php add-missing-columns.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "=== Adding Missing Columns ===\n\n";

try {
    // Check if columns exist
    $columns = DB::select("SHOW COLUMNS FROM user_course_enrollments WHERE Field IN ('final_exam_completed', 'final_exam_result_id')");
    
    if (count($columns) === 2) {
        echo "✅ Columns already exist. No action needed.\n";
        exit(0);
    }
    
    echo "Adding missing columns to user_course_enrollments...\n";
    
    Schema::table('user_course_enrollments', function (Blueprint $table) {
        if (!Schema::hasColumn('user_course_enrollments', 'final_exam_completed')) {
            $table->boolean('final_exam_completed')->default(false)->after('status');
            echo "  ✅ Added: final_exam_completed\n";
        }
        if (!Schema::hasColumn('user_course_enrollments', 'final_exam_result_id')) {
            $table->unsignedBigInteger('final_exam_result_id')->nullable()->after('final_exam_completed');
            echo "  ✅ Added: final_exam_result_id\n";
        }
    });
    
    echo "\n✅ SUCCESS: Columns added successfully!\n";
    echo "\nRun the test again: php test-final-exam-fixes.php\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

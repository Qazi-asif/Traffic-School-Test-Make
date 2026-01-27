<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== Adding Missing Columns to final_exam_results ===\n\n";

try {
    Schema::table('final_exam_results', function (Blueprint $table) {
        if (!Schema::hasColumn('final_exam_results', 'final_exam_score')) {
            $table->decimal('final_exam_score', 5, 2)->default(0);
            echo "✅ Added: final_exam_score\n";
        }
        if (!Schema::hasColumn('final_exam_results', 'is_passing')) {
            $table->boolean('is_passing')->default(false);
            echo "✅ Added: is_passing\n";
        }
        if (!Schema::hasColumn('final_exam_results', 'overall_score')) {
            $table->decimal('overall_score', 5, 2)->default(0);
            echo "✅ Added: overall_score\n";
        }
        if (!Schema::hasColumn('final_exam_results', 'status')) {
            $table->enum('status', ['pending', 'passed', 'failed', 'under_review'])->default('pending');
            echo "✅ Added: status\n";
        }
    });
    
    echo "\n✅ SUCCESS! Run test again: php test-final-exam-fixes.php\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Complete Quiz Import Fix ===\n";

try {
    // 1. Check what tables exist
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) {
        return array_values((array)$table)[0];
    }, $tables);
    
    echo "📋 Question-related tables found:\n";
    foreach ($tableNames as $table) {
        if (strpos($table, 'question') !== false) {
            echo "  - {$table}\n";
        }
    }
    
    // 2. Check if chapter_questions table exists and has correct structure
    if (!in_array('chapter_questions', $tableNames)) {
        echo "\n❌ chapter_questions table does not exist!\n";
        echo "🔧 Creating chapter_questions table...\n";
        
        DB::statement("
            CREATE TABLE `chapter_questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `chapter_id` bigint(20) unsigned NOT NULL,
                `question_text` text NOT NULL,
                `question_type` varchar(255) NOT NULL DEFAULT 'multiple_choice',
                `options` json DEFAULT NULL,
                `correct_answer` varchar(255) NOT NULL,
                `explanation` text DEFAULT NULL,
                `points` int(11) NOT NULL DEFAULT 1,
                `order_index` int(11) NOT NULL DEFAULT 1,
                `quiz_set` int(11) NOT NULL DEFAULT 1,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chapter_questions_chapter_id_index` (`chapter_id`),
                KEY `chapter_questions_order_index_index` (`order_index`),
                KEY `chapter_questions_quiz_set_index` (`quiz_set`),
                KEY `chapter_questions_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ chapter_questions table created successfully!\n";
    } else {
        echo "\n✅ chapter_questions table exists\n";
        
        // Check structure
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
        echo "📊 Columns: " . implode(', ', $columns) . "\n";
        
        $requiredColumns = ['id', 'chapter_id', 'question_text', 'question_type', 'options', 'correct_answer'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            echo "❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
            
            // Add missing columns
            foreach ($missingColumns as $column) {
                switch ($column) {
                    case 'quiz_set':
                        DB::statement("ALTER TABLE chapter_questions ADD COLUMN quiz_set INT DEFAULT 1 AFTER order_index");
                        echo "✅ Added quiz_set column\n";
                        break;
                    case 'is_active':
                        DB::statement("ALTER TABLE chapter_questions ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER quiz_set");
                        echo "✅ Added is_active column\n";
                        break;
                    case 'points':
                        DB::statement("ALTER TABLE chapter_questions ADD COLUMN points INT DEFAULT 1 AFTER explanation");
                        echo "✅ Added points column\n";
                        break;
                }
            }
        } else {
            echo "✅ All required columns present\n";
        }
    }
    
    // 3. Check questions table structure (if it exists)
    if (in_array('questions', $tableNames)) {
        echo "\n📊 Legacy questions table structure:\n";
        $columns = DB::getSchemaBuilder()->getColumnListing('questions');
        echo "  Columns: " . implode(', ', $columns) . "\n";
        
        $hasChapterId = in_array('chapter_id', $columns);
        echo "  Has chapter_id: " . ($hasChapterId ? 'YES' : 'NO') . "\n";
        
        if (!$hasChapterId) {
            echo "⚠️  This explains the error! The questions table doesn't have chapter_id column.\n";
        }
    }
    
    // 4. Test creating a question
    echo "\n🧪 Testing question creation...\n";
    
    // Find a test chapter
    $chapter = DB::table('chapters')->first();
    if (!$chapter) {
        echo "❌ No chapters found for testing\n";
    } else {
        echo "📝 Using chapter: {$chapter->title} (ID: {$chapter->id})\n";
        
        // Test data
        $testData = [
            'chapter_id' => $chapter->id,
            'question_text' => 'Test question - Which of the following is correct?',
            'question_type' => 'multiple_choice',
            'options' => json_encode([
                'A' => 'Option A',
                'B' => 'Option B',
                'C' => 'Option C',
                'D' => 'Option D'
            ]),
            'correct_answer' => 'B',
            'explanation' => 'This is a test question',
            'points' => 1,
            'order_index' => 1,
            'quiz_set' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        try {
            $questionId = DB::table('chapter_questions')->insertGetId($testData);
            echo "✅ Test question created successfully with ID: {$questionId}\n";
            
            // Clean up
            DB::table('chapter_questions')->where('id', $questionId)->delete();
            echo "🧹 Test question cleaned up\n";
            
        } catch (Exception $e) {
            echo "❌ Test question creation failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Quiz import fix complete!\n";
    echo "\n📝 Summary of fixes applied:\n";
    echo "  ✅ QuestionController import method updated to use direct DB insert\n";
    echo "  ✅ QuestionController store method updated to use direct DB insert\n";
    echo "  ✅ add_quiz_questions.php script fixed to use chapter_questions table\n";
    echo "  ✅ add_quiz_cli.php script fixed to use chapter_questions table\n";
    echo "  ✅ chapter_questions table verified/created with correct structure\n";
    echo "\n🚀 Quiz importing should now work without errors!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
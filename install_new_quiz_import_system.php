<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Installing New Quiz Import System ===\n";

try {
    // 1. Check and create required dependencies
    echo "📦 Checking dependencies...\n";
    
    $requiredPackages = [
        'phpoffice/phpword' => 'Word document processing',
        'smalot/pdfparser' => 'PDF text extraction'
    ];
    
    foreach ($requiredPackages as $package => $description) {
        if (class_exists('PhpOffice\PhpWord\IOFactory') && $package === 'phpoffice/phpword') {
            echo "  ✅ {$package} - {$description}\n";
        } elseif (class_exists('Smalot\PdfParser\Parser') && $package === 'smalot/pdfparser') {
            echo "  ✅ {$package} - {$description}\n";
        } else {
            echo "  ❌ {$package} - {$description} (MISSING)\n";
            echo "     Run: composer require {$package}\n";
        }
    }
    
    // 2. Verify database structure
    echo "\n📊 Verifying database structure...\n";
    
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) {
        return array_values((array)$table)[0];
    }, $tables);
    
    if (in_array('chapter_questions', $tableNames)) {
        echo "  ✅ chapter_questions table exists\n";
        
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
        $requiredColumns = ['id', 'chapter_id', 'question_text', 'question_type', 'options', 'correct_answer'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "  ✅ All required columns present\n";
        } else {
            echo "  ❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
        }
    } else {
        echo "  ❌ chapter_questions table missing\n";
        echo "     Run: php artisan migrate\n";
    }
    
    // 3. Test file processing capabilities
    echo "\n🧪 Testing file processing capabilities...\n";
    
    // Test Word processing
    if (class_exists('PhpOffice\PhpWord\IOFactory')) {
        echo "  ✅ Word document processing available\n";
    } else {
        echo "  ❌ Word document processing unavailable\n";
    }
    
    // Test PDF processing
    if (class_exists('Smalot\PdfParser\Parser')) {
        echo "  ✅ PDF text extraction available\n";
    } else {
        echo "  ❌ PDF text extraction unavailable\n";
    }
    
    // 4. Check storage permissions
    echo "\n📁 Checking storage permissions...\n";
    
    $storagePath = storage_path('app/public/course-media');
    if (!file_exists($storagePath)) {
        mkdir($storagePath, 0755, true);
        echo "  ✅ Created course-media directory\n";
    } else {
        echo "  ✅ course-media directory exists\n";
    }
    
    if (is_writable($storagePath)) {
        echo "  ✅ Storage directory is writable\n";
    } else {
        echo "  ❌ Storage directory is not writable\n";
        echo "     Run: chmod 755 {$storagePath}\n";
    }
    
    // 5. Test basic functionality
    echo "\n🔧 Testing basic functionality...\n";
    
    // Test question parsing
    $testText = "1. What is 2+2?\nA. 3\nB. 4 ***\nC. 5\nD. 6";
    $controller = new \App\Http\Controllers\Admin\QuizImportController();
    
    // Use reflection to test private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('parseTextQuestions');
    $method->setAccessible(true);
    
    $questions = $method->invoke($controller, $testText);
    
    if (!empty($questions) && $questions[0]['correct_answer'] === 'B') {
        echo "  ✅ Question parsing works correctly\n";
    } else {
        echo "  ❌ Question parsing failed\n";
    }
    
    // 6. Check routes
    echo "\n🛣️  Checking routes...\n";
    
    $routes = [
        'admin.quiz-import.index' => 'Main quiz import interface',
        'admin.quiz-import.single' => 'Single file import',
        'admin.quiz-import.bulk' => 'Bulk file import',
        'admin.quick-quiz-import.import' => 'Quick import for course management'
    ];
    
    foreach ($routes as $routeName => $description) {
        try {
            $url = route($routeName);
            echo "  ✅ {$routeName} - {$description}\n";
        } catch (Exception $e) {
            echo "  ❌ {$routeName} - Route not found\n";
        }
    }
    
    echo "\n🎉 Installation Check Complete!\n";
    echo "\n📋 System Features:\n";
    echo "  • Multi-format import: Word, PDF, TXT, CSV\n";
    echo "  • Bulk import: Up to 20 files at once\n";
    echo "  • Text paste import with live preview\n";
    echo "  • Quick import in course management\n";
    echo "  • Auto-detection of quiz questions\n";
    echo "  • Multiple choice question support\n";
    echo "  • Question replacement options\n";
    echo "  • Real-time progress tracking\n";
    
    echo "\n🚀 Access Points:\n";
    echo "  • Main System: /admin/quiz-import\n";
    echo "  • Quick Import: Available in course/chapter management\n";
    echo "  • API Endpoints: /admin/quiz-import/* and /admin/quick-quiz-import/*\n";
    
    echo "\n✨ Ready to use!\n";
    
} catch (Exception $e) {
    echo "❌ Installation check failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
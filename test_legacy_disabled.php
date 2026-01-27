<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;

echo "=== TESTING WITH LEGACY TABLE DISABLED ===\n\n";

echo "Configuration:\n";
echo "- DISABLE_LEGACY_QUESTIONS_TABLE: " . (config('quiz.disable_legacy_questions_table') ? 'true' : 'false') . "\n\n";

// Test chapters with different question distributions
$testChapters = [
    2 => 'Chapter with questions ONLY in legacy table',
    29 => 'Chapter with questions ONLY in chapter_questions table', 
    34 => 'Chapter with questions in BOTH tables (had duplicates)'
];

foreach ($testChapters as $chapterId => $description) {
    echo "Testing Chapter {$chapterId}: {$description}\n";
    
    // Check database directly
    $chapterQuestionsCount = \App\Models\ChapterQuestion::where('chapter_id', $chapterId)->count();
    $questionsCount = \App\Models\Question::where('chapter_id', $chapterId)->count();
    
    echo "  Database:\n";
    echo "    - chapter_questions table: {$chapterQuestionsCount} questions\n";
    echo "    - questions table (legacy): {$questionsCount} questions\n";
    
    // Test API response
    $controller = new QuestionController();
    $response = $controller->index($chapterId);
    $responseData = $response->getData();
    
    echo "  API Response:\n";
    echo "    - Questions returned: " . count($responseData) . "\n";
    
    if (count($responseData) > 0) {
        echo "    - First question ID: {$responseData[0]->id}\n";
        echo "    - First question: '" . substr($responseData[0]->question_text, 0, 50) . "...'\n";
    }
    
    // Analysis
    if ($chapterQuestionsCount > 0 && count($responseData) === $chapterQuestionsCount) {
        echo "  ✅ CORRECT: Using chapter_questions table only\n";
    } elseif ($questionsCount > 0 && count($responseData) === 0) {
        echo "  ✅ CORRECT: Legacy table ignored (as expected)\n";
    } elseif (count($responseData) === $questionsCount) {
        echo "  ❌ ISSUE: Still using legacy table\n";
    } else {
        echo "  ⚠️  UNEXPECTED: Returned " . count($responseData) . " questions\n";
    }
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "With DISABLE_LEGACY_QUESTIONS_TABLE=true:\n";
echo "- Chapters with questions in chapter_questions table: Will show questions\n";
echo "- Chapters with questions ONLY in legacy table: Will show 0 questions\n";
echo "- This prevents ALL duplicates and forces use of modern table\n\n";

echo "If frontend still shows old questions:\n";
echo "1. Clear browser cache completely\n";
echo "2. Hard refresh (Ctrl+F5)\n";
echo "3. Check browser developer tools for cached API responses\n";
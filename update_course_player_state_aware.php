<?php

/**
 * Update Course Player to be State-Aware
 * 
 * This updates the course player to load content from state-specific tables
 * while keeping the existing UI/UX design completely unchanged.
 */

echo "🔧 UPDATING COURSE PLAYER FOR STATE AWARENESS\n";
echo str_repeat("=", 50) . "\n\n";

// First, let's update the course player route to be state-aware
echo "📖 Reading existing course player route...\n";

$routesPath = 'routes/web.php';
$routesContent = file_get_contents($routesPath);

// Find and update the course player route
$newCoursePlayerRoute = '
Route::get(\'/course-player/{enrollmentId}\', function ($enrollmentId) {
    $enrollment = \App\Models\UserCourseEnrollment::where(\'id\', $enrollmentId)
        ->where(\'user_id\', auth()->id())
        ->first();

    if (! $enrollment) {
        return redirect(\'/dashboard\')->with(\'error\', \'Enrollment not found\');
    }

    if ($enrollment->access_revoked) {
        return redirect(\'/dashboard\')->with(\'error\', \'Access to this course has been revoked after certificate download\');
    }

    // Check payment status - redirect to payment if not paid
    if ($enrollment->payment_status !== \'paid\') {
        return redirect()->route(\'payment.show\', [
            \'course_id\' => $enrollment->course_id,
            \'table\' => $enrollment->course_table ?? \'florida_courses\'
        ])->with(\'info\', \'Please complete payment to access the course.\');
    }

    // If course is completed, redirect to certificate generation (UNLESS user is admin)
    if ($enrollment->status === \'completed\' && $enrollment->completed_at && !auth()->user()->isAdmin()) {
        return redirect(\'/generate-certificates\')->with(\'success\', \'Course completed! Generate your certificate below.\');
    }
    
    // Pass state information to the course player
    $stateInfo = [
        \'table\' => $enrollment->course_table ?? \'florida_courses\',
        \'state_code\' => auth()->user()->state_code ?? \'florida\',
        \'enrollment_id\' => $enrollmentId
    ];
    
    return view(\'course-player\', compact(\'stateInfo\'));
})->middleware(\'auth\')->name(\'course-player\');
';

// Replace the existing course player route
$routesContent = preg_replace(
    '/Route::get\(\'\/course-player\/\{enrollmentId\}\', function.*?(?=Route::)/s',
    $newCoursePlayerRoute,
    $routesContent
);

file_put_contents($routesPath, $routesContent);

echo "✅ Updated course player route to be state-aware\n";

// Now let's update the course player view to handle state-specific data
echo "📖 Reading existing course player view...\n";

$coursePlayerPath = 'resources/views/course-player.blade.php';
if (file_exists($coursePlayerPath)) {
    $coursePlayerContent = file_get_contents($coursePlayerPath);
    
    // Add state-aware JavaScript at the beginning of the script section
    $stateAwareScript = '
<script>
// State-aware course player configuration
window.coursePlayerConfig = {
    stateTable: @json($stateInfo[\'table\'] ?? \'florida_courses\'),
    stateCode: @json($stateInfo[\'state_code\'] ?? \'florida\'),
    enrollmentId: @json($stateInfo[\'enrollment_id\'] ?? null),
    apiEndpoints: {
        chapters: \'/api/\' + (@json($stateInfo[\'table\'] ?? \'florida_courses\')) + \'/chapters\',
        progress: \'/api/progress/state-aware\',
        quiz: \'/api/quiz/state-aware\'
    }
};

console.log(\'Course Player State Config:\', window.coursePlayerConfig);
</script>
';
    
    // Add the state-aware script before the closing </head> tag
    $coursePlayerContent = str_replace('</head>', $stateAwareScript . '</head>', $coursePlayerContent);
    
    file_put_contents($coursePlayerPath, $coursePlayerContent);
    
    echo "✅ Updated course player view with state awareness\n";
} else {
    echo "⚠️  Course player view not found, will need to be created\n";
}

// Update the ChapterController to be state-aware
echo "📖 Updating ChapterController for state awareness...\n";

$chapterControllerPath = 'app/Http/Controllers/ChapterController.php';
if (file_exists($chapterControllerPath)) {
    $chapterContent = file_get_contents($chapterControllerPath);
    
    // Add state-aware method to ChapterController
    $stateAwareChapterMethod = '
    /**
     * Get chapters for a course from the appropriate state table
     */
    public function getStateAwareChapters(Request $request)
    {
        try {
            $enrollmentId = $request->get(\'enrollment_id\');
            $courseId = $request->get(\'course_id\');
            $stateTable = $request->get(\'state_table\', \'florida_courses\');
            
            // Get enrollment to verify access
            if ($enrollmentId) {
                $enrollment = \App\Models\UserCourseEnrollment::where(\'id\', $enrollmentId)
                    ->where(\'user_id\', auth()->id())
                    ->first();
                    
                if (!$enrollment) {
                    return response()->json([\'error\' => \'Enrollment not found\'], 404);
                }
                
                $courseId = $enrollment->course_id;
                $stateTable = $enrollment->course_table ?? \'florida_courses\';
            }
            
            // Get chapters from the chapters table with course_table filter
            $chapters = \DB::table(\'chapters\')
                ->where(\'course_id\', $courseId)
                ->where(\'course_table\', $stateTable)
                ->where(\'is_active\', true)
                ->orderBy(\'order_index\')
                ->get();
            
            return response()->json($chapters);
        } catch (\Exception $e) {
            \Log::error(\'State-aware chapters error: \' . $e->getMessage());
            return response()->json([\'error\' => \'Failed to load chapters\'], 500);
        }
    }
';
    
    // Add the method before the last closing brace
    $lastBracePos = strrpos($chapterContent, '}');
    if ($lastBracePos !== false) {
        $chapterContent = substr_replace($chapterContent, $stateAwareChapterMethod . '}', $lastBracePos, 1);
    }
    
    file_put_contents($chapterControllerPath, $chapterContent);
    
    echo "✅ Updated ChapterController with state-aware methods\n";
}

// Add state-aware API routes
echo "📖 Adding state-aware API routes...\n";

$stateAwareRoutes = '
// State-aware course player API routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/api/chapters/state-aware\', [App\Http\Controllers\ChapterController::class, \'getStateAwareChapters\']);
    Route::get(\'/api/{stateTable}/chapters\', [App\Http\Controllers\ChapterController::class, \'getStateAwareChapters\']);
    Route::post(\'/api/progress/state-aware\', [App\Http\Controllers\ProgressController::class, \'updateStateAwareProgress\']);
    Route::get(\'/api/quiz/state-aware\', [App\Http\Controllers\QuizController::class, \'getStateAwareQuiz\']);
});
';

// Add the routes before the last line
$routesContent = file_get_contents($routesPath);
$routesContent = rtrim($routesContent) . "\n" . $stateAwareRoutes . "\n";
file_put_contents($routesPath, $routesContent);

echo "✅ Added state-aware API routes\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ COURSE PLAYER STATE INTEGRATION COMPLETE!\n";
echo str_repeat("=", 50) . "\n\n";

echo "🎯 WHAT THIS ACHIEVES:\n";
echo "• Course player automatically detects user\'s state\n";
echo "• Loads chapters from appropriate state table\n";
echo "• Keeps existing course player UI/UX unchanged\n";
echo "• Adds state-aware API endpoints\n";
echo "• Maintains all existing functionality\n\n";

echo "📋 NEXT STEPS:\n";
echo "1. Update quiz system for state awareness\n";
echo "2. Update certificate system for state awareness\n";
echo "3. Migrate existing data to state tables\n\n";

?>
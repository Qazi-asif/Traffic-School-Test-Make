<?php

// =====================================================
// FREE RESPONSE QUIZ ROUTES
// =====================================================

// Add these routes to your routes/web.php file

// =====================================================
// 1. ADMIN ROUTES - Free Response Quiz Management
// =====================================================

Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    
    // Free Response Quiz Questions Management
    Route::resource('/admin/free-response-quiz', App\Http\Controllers\Admin\FreeResponseQuizController::class)->names([
        'index' => 'admin.free-response-quiz.index',
        'create' => 'admin.free-response-quiz.create',
        'store' => 'admin.free-response-quiz.store',
        'show' => 'admin.free-response-quiz.show',
        'edit' => 'admin.free-response-quiz.edit',
        'update' => 'admin.free-response-quiz.update',
        'destroy' => 'admin.free-response-quiz.destroy',
    ]);
    
    // Toggle question active status
    Route::post('/admin/free-response-quiz/{id}/toggle', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'toggleActive'])
        ->name('admin.free-response-quiz.toggle');
    
    // Get sample answer and grading rubric for a question
    Route::get('/admin/free-response-quiz/{id}/sample-answer', function($id) {
        $question = DB::table('free_response_questions')->where('id', $id)->first();
        return response()->json([
            'sample_answer' => $question->sample_answer ?? 'No sample answer provided.',
            'grading_rubric' => $question->grading_rubric ?? null
        ]);
    })->name('admin.free-response-quiz.sample-answer');

    // =====================================================
    // 2. ADMIN ROUTES - Quiz Placement Management
    // =====================================================

    // Free Response Quiz Placement Management (NEW)
    Route::resource('/admin/free-response-quiz-placements', App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class)->names([
        'index' => 'admin.free-response-quiz-placements.index',
        'create' => 'admin.free-response-quiz-placements.create',
        'store' => 'admin.free-response-quiz-placements.store',
        'show' => 'admin.free-response-quiz-placements.show',
        'edit' => 'admin.free-response-quiz-placements.edit',
        'update' => 'admin.free-response-quiz-placements.update',
        'destroy' => 'admin.free-response-quiz-placements.destroy',
    ]);
    
    // Toggle placement active status
    Route::post('/admin/free-response-quiz-placements/{id}/toggle', [App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class, 'toggleActive'])
        ->name('admin.free-response-quiz-placements.toggle');

    // =====================================================
    // 3. ADMIN ROUTES - Quiz Submissions Management
    // =====================================================
    
    // View all free response submissions
    Route::get('/admin/free-response-quiz-submissions', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'submissions'])
        ->name('admin.free-response-quiz.submissions');
    
    // Grade a free response answer
    Route::post('/admin/free-response-quiz-submissions/{id}/grade', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'gradeAnswer'])
        ->name('admin.free-response-quiz.grade');
});

// =====================================================
// 4. STUDENT ROUTES - Taking Free Response Quizzes
// =====================================================

Route::middleware(['auth'])->group(function () {
    
    // Show free response quiz (general endpoint)
    Route::get('/free-response-quiz', [App\Http\Controllers\FreeResponseQuizController::class, 'show'])
        ->name('free-response-quiz.show');
    
    // Submit free response quiz answers
    Route::post('/free-response-quiz/submit', [App\Http\Controllers\FreeResponseQuizController::class, 'submit'])
        ->name('free-response-quiz.submit');
    
    // Show specific placement quiz
    Route::get('/free-response-quiz/placement/{placementId}', [App\Http\Controllers\FreeResponseQuizController::class, 'showPlacement'])
        ->name('free-response-quiz.placement');
    
    // Submit answers for specific placement
    Route::post('/free-response-quiz/placement/{placementId}/submit', [App\Http\Controllers\FreeResponseQuizController::class, 'submitPlacement'])
        ->name('free-response-quiz.placement.submit');
});

// =====================================================
// 5. API ROUTES - For Course Player Integration
// =====================================================

Route::middleware(['auth'])->group(function () {
    
    // Get free response questions for course player
    Route::get('/api/free-response-questions', [App\Http\Controllers\FreeResponseQuizController::class, 'getQuestions'])
        ->name('api.free-response-questions');
    
    // Get questions for specific placement
    Route::get('/api/free-response-questions/placement/{placementId}', [App\Http\Controllers\FreeResponseQuizController::class, 'getQuestionsForPlacement'])
        ->name('api.free-response-questions.placement');
    
    // Get chapters with quiz placements (enhanced endpoint)
    Route::get('/web/courses/{courseId}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb'])
        ->name('web.courses.chapters');
});

// =====================================================
// 6. DEBUG ROUTES (Optional - for development)
// =====================================================

Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    
    // Debug: Check free response tables
    Route::get('/debug/free-response-tables', function() {
        $placements = DB::table('free_response_quiz_placements')->get();
        $questions = DB::table('free_response_questions')->get();
        $answers = DB::table('free_response_answers')->get();
        
        return response()->json([
            'placements_count' => $placements->count(),
            'questions_count' => $questions->count(),
            'answers_count' => $answers->count(),
            'placements' => $placements,
            'questions' => $questions->take(5), // First 5 questions
            'answers' => $answers->take(5), // First 5 answers
        ]);
    })->name('debug.free-response-tables');
    
    // Debug: Check course chapters with placements
    Route::get('/debug/course-chapters/{courseId}', function($courseId) {
        $chapters = app(App\Http\Controllers\ChapterController::class)->indexWeb(request()->merge(['courseId' => $courseId]));
        return $chapters;
    })->name('debug.course-chapters');
});

?>
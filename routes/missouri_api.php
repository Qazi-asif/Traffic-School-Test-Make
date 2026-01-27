<?php

use App\Http\Controllers\MissouriController;
use Illuminate\Support\Facades\Route;

Route::prefix('missouri')->middleware('auth:api')->group(function () {

    // Form 4444 Management
    Route::post('/form4444/generate', [MissouriController::class, 'generateForm4444']);
    Route::get('/submission-status/{userId}', [MissouriController::class, 'getSubmissionStatus']);
    Route::post('/form4444/{formId}/submit-dor', [MissouriController::class, 'submitToDOR']);
    Route::get('/expiring-forms', [MissouriController::class, 'getExpiringForms']);

    // Course Structure
    Route::get('/course-structure', function () {
        return response()->json(\App\Models\MissouriCourseStructure::MISSOURI_CHAPTERS);
    });

    // Quiz Management
    Route::get('/quiz/{chapterId}', function ($chapterId) {
        $questions = \App\Models\MissouriQuizBank::where('chapter_id', $chapterId)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return response()->json($questions);
    });

    // Student Management
    Route::post('/students', function (\Illuminate\Http\Request $request) {
        $student = \App\Models\MissouriStudent::create($request->validated());

        return response()->json($student);
    });

    Route::get('/students/{userId}', function ($userId) {
        $student = \App\Models\MissouriStudent::where('user_id', $userId)->first();

        return response()->json($student);
    });
});

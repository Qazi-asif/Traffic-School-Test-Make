<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (No CSRF)
|--------------------------------------------------------------------------
|
| These routes are loaded without CSRF protection for course management
|
*/

// DOCX Import without CSRF
Route::post('/import-docx', [App\Http\Controllers\ChapterController::class, 'importDocx']);

// Course management without CSRF
Route::get('/courses', [App\Http\Controllers\CourseController::class, 'indexWeb']);
Route::get('/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
Route::post('/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);

// Test routes
Route::get('/test-chapters/{courseId}', function($courseId) {
    try {
        $chapters = \App\Models\Chapter::where('course_id', $courseId)->get();
        return response()->json([
            'success' => true,
            'course_id' => $courseId,
            'chapters_count' => $chapters->count(),
            'chapters' => $chapters
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
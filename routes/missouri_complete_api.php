<?php

use App\Http\Controllers\CourseCompletionController;
use App\Http\Controllers\FinalExamController;
use App\Http\Controllers\MissouriController;
use Illuminate\Support\Facades\Route;

Route::prefix('missouri')->middleware('auth:api')->group(function () {

    // Chapter Management
    Route::get('/chapters', function () {
        return response()->json(\App\Models\MissouriCourseStructure::all());
    });

    Route::post('/chapters/{chapterId}/start', function ($chapterId) {
        \App\Models\ChapterProgress::updateOrCreate([
            'user_id' => auth()->id(),
            'chapter_id' => $chapterId,
        ], [
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json(['success' => true]);
    });

    // Quiz System
    Route::get('/chapters/{chapterId}/quiz', function ($chapterId) {
        $questions = \App\Models\MissouriQuizBank::where('chapter_id', $chapterId)
            ->inRandomOrder()->limit(10)->get();

        return response()->json($questions);
    });

    Route::post('/chapters/{chapterId}/quiz/submit', function (\Illuminate\Http\Request $request, $chapterId) {
        $answers = $request->answers;
        $correct = 0;

        foreach ($answers as $questionId => $answer) {
            $question = \App\Models\MissouriQuizBank::find($questionId);
            if ($question && $question->correct_answer === $answer) {
                $correct++;
            }
        }

        $score = ($correct / count($answers)) * 100;
        $passed = $score >= 80;

        // Update chapter progress
        \App\Models\ChapterProgress::updateOrCreate([
            'user_id' => auth()->id(),
            'chapter_id' => $chapterId,
        ], [
            'completed_at' => $passed ? now() : null,
            'quiz_score' => $score,
            'quiz_passed' => $passed,
            'status' => $passed ? 'completed' : 'quiz_failed',
        ]);

        return response()->json([
            'score' => $score,
            'passed' => $passed,
            'can_retake' => ! $passed,
        ]);
    });

    // Final Exam
    Route::get('/final-exam', [FinalExamController::class, 'generateFinalExam']);
    Route::post('/final-exam/submit', [FinalExamController::class, 'submitFinalExam']);

    // Course Completion
    Route::get('/completion/check/{userId}', [CourseCompletionController::class, 'checkEligibility']);
    Route::post('/completion/complete', [CourseCompletionController::class, 'completeCourse']);

    // Form 4444 Management
    Route::post('/form4444/generate', [MissouriController::class, 'generateForm4444']);
    Route::get('/submission-status/{userId}', [MissouriController::class, 'getSubmissionStatus']);
    Route::post('/form4444/{formId}/submit-dor', [MissouriController::class, 'submitToDOR']);

    // Progress Tracking
    Route::get('/progress/{userId}', function ($userId) {
        $progress = \App\Models\ChapterProgress::where('user_id', $userId)
            ->with('chapter')
            ->get();

        return response()->json($progress);
    });
});

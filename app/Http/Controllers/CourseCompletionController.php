<?php

namespace App\Http\Controllers;

use App\Events\CourseCompleted;
use App\Models\ChapterProgress;
use App\Models\MissouriForm4444;
use App\Models\QuizAttempt;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;

class CourseCompletionController extends Controller
{
    public function checkEligibility($userId)
    {
        // Check if all 11 chapters completed
        $completedChapters = ChapterProgress::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        // Check if final exam passed
        $finalExamPassed = QuizAttempt::where('user_id', $userId)
            ->where('quiz_type', 'final_exam')
            ->where('passed', true)
            ->exists();

        $canComplete = $completedChapters >= 11 && $finalExamPassed;

        return response()->json([
            'can_complete' => $canComplete,
            'chapters_completed' => $completedChapters,
            'chapters_required' => 11,
            'final_exam_passed' => $finalExamPassed,
            'requirements_met' => [
                'all_chapters' => $completedChapters >= 11,
                'final_exam' => $finalExamPassed,
            ],
        ]);
    }

    public function completeCourse(Request $request)
    {
        $userId = $request->user_id;
        $enrollmentId = $request->enrollment_id;

        // Verify eligibility
        $eligibility = $this->checkEligibility($userId);
        if (! $eligibility->getData()->can_complete) {
            return response()->json(['error' => 'Course requirements not met'], 400);
        }

        // Mark enrollment as completed
        $enrollment = UserCourseEnrollment::findOrFail($enrollmentId);
        $enrollment->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);

        // Dispatch course completed event
        event(new CourseCompleted($enrollment));

        // Generate Form 4444
        $form = MissouriForm4444::create([
            'user_id' => $userId,
            'enrollment_id' => $enrollmentId,
            'form_number' => 'MO-4444-'.time(),
            'completion_date' => now(),
            'submission_deadline' => now()->addDays(15),
            'submission_method' => $request->submission_method ?? 'point_reduction',
            'status' => 'ready_for_submission',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course completed successfully',
            'form_4444' => $form,
            'completion_date' => now(),
        ]);
    }
}

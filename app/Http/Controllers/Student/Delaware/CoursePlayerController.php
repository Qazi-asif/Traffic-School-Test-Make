<?php

namespace App\Http\Controllers\Student\Delaware;

use App\Http\Controllers\Controller;
use App\Models\Delaware\Course;
use App\Models\Delaware\Chapter;
use App\Models\Delaware\Enrollment;
use App\Models\Delaware\Progress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoursePlayerController extends Controller
{
    /**
     * Show available Delaware courses
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get Delaware courses
            $courses = Course::where('is_active', true)->get();
            
            return view('student.delaware.dashboard', compact('courses', 'user'));
        } catch (\Exception $e) {
            return view('student.delaware.dashboard', [
                'courses' => collect(),
                'user' => Auth::user(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display course player
     */
    public function show($id)
    {
        $user = Auth::user();
        $course = Course::with(['chapters' => function($query) {
            $query->active()->ordered();
        }])->findOrFail($id);

        // Get enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if (!$enrollment || $enrollment->payment_status !== 'paid') {
            return redirect()->route('student.delaware.courses.index')
                ->with('error', 'Course access denied. Please complete payment.');
        }

        // Get progress for all chapters
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');

        // Get current chapter
        $currentChapter = $this->getCurrentChapter($course, $progress);

        // Delaware-specific features
        $quizRotationSet = $enrollment->getAssignedQuizRotationSet();
        $insuranceDiscountEligible = $enrollment->isEligibleForInsuranceDiscount();

        return view('student.delaware.courses.show', compact(
            'course', 
            'enrollment', 
            'progress', 
            'currentChapter',
            'quizRotationSet',
            'insuranceDiscountEligible'
        ));
    }

    /**
     * Begin course with timer
     */
    public function startCourse($id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        // Get or create enrollment
        $enrollment = Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $id,
        ], [
            'payment_status' => 'pending',
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        // Check payment status
        if ($enrollment->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'error' => 'Payment required to start course.',
                'redirect' => route('payment.show', $enrollment->id)
            ], 402);
        }

        // Mark as started
        if (!$enrollment->started_at) {
            $enrollment->update([
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);
        }

        // Get first chapter
        $firstChapter = Chapter::where('course_id', $id)
            ->active()
            ->ordered()
            ->first();

        if (!$firstChapter) {
            return response()->json([
                'success' => false,
                'error' => 'No chapters available for this course.'
            ], 404);
        }

        // Create progress for first chapter
        $progress = Progress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'chapter_id' => $firstChapter->id,
        ], [
            'rotation_set_used' => $enrollment->getAssignedQuizRotationSet(),
        ]);

        $progress->markAsStarted();

        return response()->json([
            'success' => true,
            'message' => 'Course started successfully!',
            'enrollment_id' => $enrollment->id,
            'current_chapter' => [
                'id' => $firstChapter->id,
                'title' => $firstChapter->title,
                'duration_minutes' => $firstChapter->duration_minutes,
                'has_interactive_content' => $firstChapter->has_interactive_content,
                'interactive_content_url' => $firstChapter->interactive_content_url,
            ],
            'timer_started' => true,
            'quiz_rotation_enabled' => $course->quiz_rotation_enabled,
            'quiz_rotation_set' => $enrollment->getAssignedQuizRotationSet(),
            'aggressive_driving_course' => $course->aggressive_driving_course,
            'insurance_discount_eligible' => $course->insurance_discount_eligible,
        ]);
    }

    /**
     * Navigate to next chapter
     */
    public function nextChapter($courseId, $chapterId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $currentChapter = Chapter::findOrFail($chapterId);
        
        // Check if current chapter is completed
        $currentProgress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->first();

        if (!$currentProgress || !$currentProgress->canComplete()) {
            return response()->json([
                'success' => false,
                'error' => 'Current chapter requirements not met. Please complete all content and interactive elements.',
                'interactive_content_required' => $currentChapter->has_interactive_content,
                'interactive_content_completed' => $currentProgress ? $currentProgress->interactive_content_completed : false,
            ], 422);
        }

        // Get next chapter
        $nextChapter = Chapter::where('course_id', $courseId)
            ->where('order_index', '>', $currentChapter->order_index)
            ->active()
            ->ordered()
            ->first();

        if (!$nextChapter) {
            // Course completed
            $this->checkCourseCompletion($enrollment);
            
            return response()->json([
                'success' => true,
                'course_completed' => true,
                'message' => 'Congratulations! You have completed the course.',
                'insurance_discount_eligible' => $enrollment->isEligibleForInsuranceDiscount(),
                'aggressive_driving_completed' => $enrollment->aggressive_driving_completion,
                'redirect' => route('student.delaware.certificates.index'),
            ]);
        }

        // Create progress for next chapter
        $nextProgress = Progress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'chapter_id' => $nextChapter->id,
        ], [
            'rotation_set_used' => $enrollment->getAssignedQuizRotationSet(),
        ]);

        $nextProgress->markAsStarted();

        return response()->json([
            'success' => true,
            'next_chapter' => [
                'id' => $nextChapter->id,
                'title' => $nextChapter->title,
                'content' => $nextChapter->content,
                'duration_minutes' => $nextChapter->duration_minutes,
                'order_index' => $nextChapter->order_index,
                'has_interactive_content' => $nextChapter->has_interactive_content,
                'interactive_content_url' => $nextChapter->interactive_content_url,
            ],
        ]);
    }

    /**
     * Mark chapter as complete
     */
    public function completeChapter($courseId, $chapterId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->firstOrFail();

        // Check minimum time requirement
        if ($chapter->enforce_minimum_time && $progress->time_spent_minutes < $chapter->duration_minutes) {
            return response()->json([
                'success' => false,
                'error' => 'Minimum time requirement not met for this chapter.',
                'required_minutes' => $chapter->duration_minutes,
                'spent_minutes' => $progress->time_spent_minutes,
            ], 422);
        }

        // Delaware-specific: Check interactive content requirement
        if ($chapter->has_interactive_content && !$progress->interactive_content_completed) {
            return response()->json([
                'success' => false,
                'error' => 'Interactive content must be completed before finishing this chapter.',
                'interactive_content_url' => $chapter->interactive_content_url,
            ], 422);
        }

        // Mark as completed
        $progress->markAsCompleted();

        // Update enrollment progress
        $this->updateEnrollmentProgress($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Chapter completed successfully!',
            'progress' => [
                'chapter_completed' => true,
                'enrollment_progress' => $enrollment->fresh()->progress_percentage,
                'interactive_content_completed' => $progress->interactive_content_completed,
            ],
        ]);
    }

    /**
     * Mark interactive content as completed (Delaware-specific)
     */
    public function completeInteractiveContent(Request $request, $courseId, $chapterId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        
        if (!$chapter->has_interactive_content) {
            return response()->json([
                'success' => false,
                'error' => 'This chapter does not have interactive content.',
            ], 422);
        }

        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->firstOrFail();

        // Mark interactive content as completed
        $progress->markInteractiveContentCompleted();

        // Update topic coverage if applicable
        $course = $enrollment->course;
        if ($course->aggressive_driving_course) {
            $progress->updateTopicCoverage(true, false);
        }
        if ($course->insurance_discount_eligible) {
            $progress->updateTopicCoverage(false, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interactive content completed successfully!',
            'interactive_content_completed' => true,
            'can_complete_chapter' => $progress->canComplete(),
        ]);
    }

    /**
     * Get current chapter based on progress
     */
    private function getCurrentChapter($course, $progress)
    {
        // Find first incomplete chapter
        foreach ($course->chapters as $chapter) {
            $chapterProgress = $progress->get($chapter->id);
            if (!$chapterProgress || !$chapterProgress->is_completed) {
                return $chapter;
            }
        }
        
        // All chapters completed, return last chapter
        return $course->chapters->last();
    }

    /**
     * Update enrollment progress
     */
    private function updateEnrollmentProgress($enrollment)
    {
        $totalChapters = Chapter::where('course_id', $enrollment->course_id)->active()->count();
        $completedChapters = Progress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $progressPercentage = $totalChapters > 0 ? 
            round(($completedChapters / $totalChapters) * 100) : 0;

        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'last_activity_at' => now(),
        ]);

        return $progressPercentage;
    }

    /**
     * Check if course is completed
     */
    private function checkCourseCompletion($enrollment)
    {
        $progressPercentage = $this->updateEnrollmentProgress($enrollment);
        
        if ($progressPercentage >= 100 && !$enrollment->completed_at) {
            $course = $enrollment->course;
            
            // Check if all required topics are covered
            $allTopicsCovered = true;
            if ($course->aggressive_driving_course) {
                $aggressiveTopicsCovered = Progress::where('enrollment_id', $enrollment->id)
                    ->aggressiveDrivingCovered()
                    ->count();
                $allTopicsCovered = $allTopicsCovered && ($aggressiveTopicsCovered > 0);
            }
            
            if ($course->insurance_discount_eligible) {
                $insuranceTopicsCovered = Progress::where('enrollment_id', $enrollment->id)
                    ->insuranceDiscountCovered()
                    ->count();
                $allTopicsCovered = $allTopicsCovered && ($insuranceTopicsCovered > 0);
            }

            if ($allTopicsCovered) {
                $enrollment->update([
                    'completed_at' => now(),
                    'status' => 'completed',
                    'aggressive_driving_completion' => $course->aggressive_driving_course,
                ]);

                // Trigger course completion event
                event(new \App\Events\CourseCompleted($enrollment));
            }
        }
    }
}
<?php

namespace App\Http\Controllers\Student\Texas;

use App\Http\Controllers\Controller;
use App\Models\Texas\Course;
use App\Models\Texas\Chapter;
use App\Models\Texas\Enrollment;
use App\Models\Texas\Progress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoursePlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show available Texas courses
     */
    public function index()
    {
        $user = Auth::user();
        $courses = Course::where('is_active', true)->get();
        
        // Get user's enrollments
        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->get()
            ->keyBy('course_id');

        return view('student.texas.courses.index', compact('courses', 'enrollments'));
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
            return redirect()->route('student.texas.courses.index')
                ->with('error', 'Course access denied. Please complete payment.');
        }

        // Get progress for all chapters
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');

        // Get current chapter
        $currentChapter = $this->getCurrentChapter($course, $progress);

        // Check proctoring requirements
        $proctoringRequired = $enrollment->proctoring_required && !$enrollment->proctoring_completed;

        return view('student.texas.courses.show', compact(
            'course', 
            'enrollment', 
            'progress', 
            'currentChapter',
            'proctoringRequired'
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
            'proctoring_required' => $course->requires_proctoring,
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
                'requires_video_completion' => $firstChapter->requires_video_completion,
                'video_url' => $firstChapter->video_url,
                'video_duration_minutes' => $firstChapter->video_duration_minutes,
            ],
            'timer_started' => true,
            'proctoring_required' => $enrollment->proctoring_required,
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
                'error' => 'Current chapter requirements not met. Please complete all content and videos.',
                'video_required' => $currentChapter->requires_video_completion,
                'video_completed' => $currentProgress ? $currentProgress->video_completed : false,
            ], 422);
        }

        // Get next chapter
        $nextChapter = Chapter::where('course_id', $courseId)
            ->where('order_index', '>', $currentChapter->order_index)
            ->active()
            ->ordered()
            ->first();

        if (!$nextChapter) {
            // Course completed - check proctoring requirements
            $this->checkCourseCompletion($enrollment);
            
            return response()->json([
                'success' => true,
                'course_completed' => true,
                'message' => 'Congratulations! You have completed the course.',
                'proctoring_required' => $enrollment->proctoring_required,
                'proctoring_completed' => $enrollment->proctoring_completed,
                'can_get_certificate' => $enrollment->canComplete(),
                'redirect' => route('student.texas.certificates.index'),
            ]);
        }

        // Create progress for next chapter
        $nextProgress = Progress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'chapter_id' => $nextChapter->id,
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
                'requires_video_completion' => $nextChapter->requires_video_completion,
                'video_url' => $nextChapter->video_url,
                'video_duration_minutes' => $nextChapter->video_duration_minutes,
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

        // Texas-specific: Check video completion requirement
        if ($chapter->requires_video_completion && !$progress->video_completed) {
            return response()->json([
                'success' => false,
                'error' => 'Video must be completed before finishing this chapter.',
                'video_url' => $chapter->video_url,
                'video_duration_minutes' => $chapter->video_duration_minutes,
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
                'video_completed' => $progress->video_completed,
            ],
        ]);
    }

    /**
     * Mark video as completed (Texas-specific)
     */
    public function completeVideo(Request $request, $courseId, $chapterId)
    {
        $request->validate([
            'watch_time' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->firstOrFail();

        // Check if video watch time meets requirement
        $requiredWatchTime = $chapter->video_duration_minutes * 0.9; // 90% of video duration
        if ($request->watch_time < $requiredWatchTime) {
            return response()->json([
                'success' => false,
                'error' => 'Video must be watched for at least 90% of its duration.',
                'required_minutes' => $requiredWatchTime,
                'watched_minutes' => $request->watch_time,
            ], 422);
        }

        // Mark video as completed
        $progress->markVideoCompleted($request->watch_time);

        return response()->json([
            'success' => true,
            'message' => 'Video completed successfully!',
            'video_completed' => true,
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
     * Check if course is completed (Texas requires proctoring completion)
     */
    private function checkCourseCompletion($enrollment)
    {
        $progressPercentage = $this->updateEnrollmentProgress($enrollment);
        
        if ($progressPercentage >= 100 && !$enrollment->completed_at && $enrollment->canComplete()) {
            $enrollment->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            // Trigger course completion event
            event(new \App\Events\CourseCompleted($enrollment));
        }
    }
}
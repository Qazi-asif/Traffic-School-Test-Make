<?php

namespace App\Http\Controllers\Student\Florida;

use App\Http\Controllers\Controller;
use App\Models\Florida\Course;
use App\Models\Florida\Chapter;
use App\Models\Florida\Enrollment;
use App\Models\Florida\Progress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoursePlayerController extends Controller
{
    /**
     * Show available Florida courses
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get Florida courses
            $courses = Course::where('is_active', true)->get();
            
            // Get user's enrollments if user is authenticated
            $enrollments = collect();
            if ($user) {
                try {
                    $enrollments = Enrollment::where('user_id', $user->id)
                        ->with('course')
                        ->get()
                        ->keyBy('course_id');
                } catch (\Exception $e) {
                    // If enrollment table doesn't exist or has issues
                    $enrollments = collect();
                }
            }

            return view('student.florida.dashboard', compact('courses', 'enrollments', 'user'));
        } catch (\Exception $e) {
            // Fallback if database/model issues - show basic page
            return view('student.florida.dashboard', [
                'courses' => collect(),
                'enrollments' => collect(),
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
            return redirect()->route('student.florida.courses.index')
                ->with('error', 'Course access denied. Please complete payment.');
        }

        // Get progress for all chapters
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');

        // Get current chapter (first incomplete or first chapter)
        $currentChapter = $this->getCurrentChapter($course, $progress);

        return view('student.florida.courses.show', compact(
            'course', 
            'enrollment', 
            'progress', 
            'currentChapter'
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
            ],
            'timer_started' => true,
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

        if (!$currentProgress || !$currentProgress->is_completed) {
            return response()->json([
                'success' => false,
                'error' => 'Current chapter must be completed before proceeding.',
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
                'redirect' => route('student.florida.certificates.index'),
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
            ],
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
            $enrollment->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            // Trigger course completion event
            event(new \App\Events\CourseCompleted($enrollment));
        }
    }
}
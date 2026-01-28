<?php

namespace App\Http\Controllers\States\Florida;

use App\Http\Controllers\Controller;
use App\Models\Florida\Course;
use App\Models\Florida\Chapter;
use App\Models\Florida\Enrollment;
use App\Models\Florida\Progress;
use App\Models\Florida\QuizResult;
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
     * Display the course player for Florida courses
     */
    public function show(Request $request, $courseId)
    {
        $user = Auth::user();
        $course = Course::with(['chapters' => function($query) {
            $query->active()->ordered();
        }])->findOrFail($courseId);

        // Get or create enrollment
        $enrollment = Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'payment_status' => 'pending',
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        // Check if user has access
        if ($enrollment->access_revoked || $enrollment->payment_status !== 'paid') {
            return redirect()->route('payment.show', $enrollment->id)
                ->with('error', 'Payment required to access course content.');
        }

        // Get current chapter or first chapter
        $currentChapter = $this->getCurrentChapter($enrollment, $request->get('chapter'));
        
        // Get progress for current chapter
        $progress = Progress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'chapter_id' => $currentChapter->id,
        ]);

        // Mark chapter as started
        $progress->markAsStarted();

        // Get quiz results for current chapter
        $quizResults = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $currentChapter->id)
            ->orderBy('attempt_number', 'desc')
            ->get();

        return view('course-player.florida.show', compact(
            'course',
            'enrollment',
            'currentChapter',
            'progress',
            'quizResults'
        ));
    }

    /**
     * Update chapter progress
     */
    public function updateProgress(Request $request, $courseId, $chapterId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'time_spent' => 'integer|min:0',
            'last_position' => 'string|nullable',
        ]);

        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->firstOrFail();

        // Update progress
        $progress->updateProgress(
            $request->progress_percentage,
            $request->last_position
        );

        // Add time spent
        if ($request->time_spent) {
            $progress->addTimeSpent($request->time_spent);
        }

        // Update enrollment overall progress
        $this->updateEnrollmentProgress($enrollment);

        return response()->json([
            'success' => true,
            'progress' => $progress->fresh(),
            'enrollment_progress' => $enrollment->fresh()->progress_percentage,
        ]);
    }

    /**
     * Submit quiz answers
     */
    public function submitQuiz(Request $request, $courseId, $chapterId, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_spent' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        $quiz = $chapter->chapterQuizzes()->findOrFail($quizId);

        // Get current attempt number
        $attemptNumber = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('quiz_id', $quizId)
            ->max('attempt_number') + 1;

        // Check max attempts
        if ($attemptNumber > $quiz->max_attempts) {
            return response()->json([
                'success' => false,
                'error' => 'Maximum attempts exceeded for this quiz.',
            ], 422);
        }

        // Grade the quiz
        $result = $this->gradeQuiz($quiz, $request->answers, $enrollment, $attemptNumber, $request->time_spent);

        // Update chapter progress if quiz passed
        if ($result->passed) {
            $progress = Progress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->first();

            if ($progress) {
                $progress->update([
                    'quiz_passed' => true,
                    'quiz_best_score' => max($progress->quiz_best_score ?? 0, $result->score),
                ]);

                // Mark chapter as completed if not already
                if (!$progress->is_completed) {
                    $progress->markAsCompleted();
                }
            }
        }

        return response()->json([
            'success' => true,
            'result' => $result,
            'passed' => $result->passed,
            'score' => $result->score,
            'percentage' => $result->percentage,
        ]);
    }

    /**
     * Get next available chapter
     */
    public function nextChapter(Request $request, $courseId, $chapterId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $currentChapter = Chapter::findOrFail($chapterId);
        $nextChapter = Chapter::where('course_id', $courseId)
            ->where('order_index', '>', $currentChapter->order_index)
            ->active()
            ->ordered()
            ->first();

        if (!$nextChapter) {
            return response()->json([
                'success' => false,
                'message' => 'No more chapters available.',
                'course_completed' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'next_chapter' => [
                'id' => $nextChapter->id,
                'title' => $nextChapter->title,
                'url' => route('course-player.florida.show', [
                    'course' => $courseId,
                    'chapter' => $nextChapter->id
                ]),
            ],
        ]);
    }

    /**
     * Get current chapter based on progress
     */
    private function getCurrentChapter(Enrollment $enrollment, $requestedChapterId = null)
    {
        if ($requestedChapterId) {
            $chapter = Chapter::where('course_id', $enrollment->course_id)
                ->where('id', $requestedChapterId)
                ->active()
                ->first();
            
            if ($chapter) {
                return $chapter;
            }
        }

        // Get the first incomplete chapter or first chapter
        $incompleteChapter = Chapter::where('course_id', $enrollment->course_id)
            ->active()
            ->whereDoesntHave('progress', function($query) use ($enrollment) {
                $query->where('enrollment_id', $enrollment->id)
                    ->where('is_completed', true);
            })
            ->ordered()
            ->first();

        return $incompleteChapter ?: Chapter::where('course_id', $enrollment->course_id)
            ->active()
            ->ordered()
            ->first();
    }

    /**
     * Grade quiz and create result record
     */
    private function gradeQuiz($quiz, $answers, $enrollment, $attemptNumber, $timeSpent)
    {
        $questions = $quiz->questions()->active()->get();
        $totalQuestions = $questions->count();
        $correctAnswers = 0;

        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            if ($question->isCorrectAnswer($userAnswer)) {
                $correctAnswers++;
            }
        }

        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passed = $score >= $quiz->passing_score;

        return QuizResult::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'quiz_id' => $quiz->id,
            'chapter_id' => $quiz->chapter_id,
            'attempt_number' => $attemptNumber,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'time_spent_minutes' => ceil($timeSpent / 60),
            'passed' => $passed,
            'answers_json' => $answers,
            'started_at' => now()->subMinutes(ceil($timeSpent / 60)),
            'completed_at' => now(),
        ]);
    }

    /**
     * Update overall enrollment progress
     */
    private function updateEnrollmentProgress(Enrollment $enrollment)
    {
        $totalChapters = Chapter::where('course_id', $enrollment->course_id)
            ->active()
            ->count();

        $completedChapters = Progress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $progressPercentage = $totalChapters > 0 ? 
            round(($completedChapters / $totalChapters) * 100) : 0;

        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'last_activity_at' => now(),
        ]);

        // Check if course is completed
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
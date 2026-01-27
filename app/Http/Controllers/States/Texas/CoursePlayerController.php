<?php

namespace App\Http\Controllers\States\Texas;

use App\Http\Controllers\Controller;
use App\Models\Texas\Course;
use App\Models\Texas\Chapter;
use App\Models\Texas\Enrollment;
use App\Models\Texas\Progress;
use App\Models\Texas\QuizResult;
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
     * Display the course player for Texas courses
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
            'proctoring_required' => $course->requires_proctoring,
        ]);

        // Check if user has access
        if ($enrollment->access_revoked || $enrollment->payment_status !== 'paid') {
            return redirect()->route('payment.show', $enrollment->id)
                ->with('error', 'Payment required to access course content.');
        }

        // Get current chapter
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

        // Check proctoring requirements
        $proctoringRequired = $enrollment->proctoring_required && !$enrollment->proctoring_completed;

        return view('course-player.texas.show', compact(
            'course',
            'enrollment',
            'currentChapter',
            'progress',
            'quizResults',
            'proctoringRequired'
        ));
    }

    /**
     * Update chapter progress with Texas-specific video completion tracking
     */
    public function updateProgress(Request $request, $courseId, $chapterId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'time_spent' => 'integer|min:0',
            'last_position' => 'string|nullable',
            'video_completed' => 'boolean',
            'video_watch_time' => 'integer|min:0',
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

        // Handle video completion for Texas
        if ($request->video_completed) {
            $progress->markVideoCompleted($request->video_watch_time);
        }

        // Update enrollment overall progress
        $this->updateEnrollmentProgress($enrollment);

        return response()->json([
            'success' => true,
            'progress' => $progress->fresh(),
            'enrollment_progress' => $enrollment->fresh()->progress_percentage,
            'video_completed' => $progress->video_completed,
            'can_complete' => $progress->canComplete(),
        ]);
    }

    /**
     * Submit quiz answers with Texas proctoring verification
     */
    public function submitQuiz(Request $request, $courseId, $chapterId, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_spent' => 'required|integer|min:1',
            'proctoring_session_id' => 'string|nullable',
            'proctor_notes' => 'string|nullable',
        ]);

        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        $quiz = $chapter->chapterQuizzes()->findOrFail($quizId);

        // Check proctoring requirements
        if ($quiz->requires_proctoring && !$request->proctoring_session_id) {
            return response()->json([
                'success' => false,
                'error' => 'Proctoring session required for this quiz.',
            ], 422);
        }

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
        $result = $this->gradeQuiz(
            $quiz, 
            $request->answers, 
            $enrollment, 
            $attemptNumber, 
            $request->time_spent,
            $request->proctoring_session_id,
            $request->proctor_notes
        );

        // Update chapter progress if quiz passed
        if ($result->passed && $result->isValidForCompletion()) {
            $progress = Progress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->first();

            if ($progress) {
                $progress->update([
                    'quiz_passed' => true,
                    'quiz_best_score' => max($progress->quiz_best_score ?? 0, $result->score),
                    'proctoring_session_id' => $request->proctoring_session_id,
                ]);

                // Mark chapter as completed if requirements met
                if (!$progress->is_completed && $progress->canComplete()) {
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
            'proctoring_verified' => $result->proctoring_verified,
            'valid_for_completion' => $result->isValidForCompletion(),
        ]);
    }

    /**
     * Complete proctoring verification
     */
    public function completeProctoring(Request $request, $courseId)
    {
        $request->validate([
            'proctoring_session_id' => 'required|string',
            'verification_code' => 'required|string',
        ]);

        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        // Verify proctoring session (this would integrate with actual proctoring service)
        $verified = $this->verifyProctoringSession(
            $request->proctoring_session_id,
            $request->verification_code
        );

        if ($verified) {
            $enrollment->update([
                'proctoring_completed' => true,
                'proctoring_verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proctoring verification completed successfully.',
                'can_complete_course' => $enrollment->canComplete(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Proctoring verification failed.',
        ], 422);
    }

    /**
     * Verify proctoring session (mock implementation)
     */
    private function verifyProctoringSession($sessionId, $verificationCode)
    {
        // This would integrate with actual proctoring service
        // For now, return true for demo purposes
        return strlen($sessionId) > 10 && strlen($verificationCode) > 5;
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
     * Grade quiz and create result record with Texas proctoring features
     */
    private function gradeQuiz($quiz, $answers, $enrollment, $attemptNumber, $timeSpent, $proctoringSessionId = null, $proctorNotes = null)
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

        // Proctoring verification for Texas
        $proctoringVerified = !$quiz->requires_proctoring || !empty($proctoringSessionId);

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
            'proctoring_verified' => $proctoringVerified,
            'proctor_notes' => $proctorNotes,
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

        // Check if course is completed (Texas requires proctoring completion)
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
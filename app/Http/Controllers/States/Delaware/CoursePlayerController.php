<?php

namespace App\Http\Controllers\States\Delaware;

use App\Http\Controllers\Controller;
use App\Models\Delaware\Course;
use App\Models\Delaware\Chapter;
use App\Models\Delaware\Enrollment;
use App\Models\Delaware\Progress;
use App\Models\Delaware\QuizResult;
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
     * Display the course player for Delaware courses
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
            'insurance_discount_requested' => $course->insurance_discount_eligible,
        ]);

        // Assign quiz rotation set if not already assigned
        $rotationSet = $enrollment->getAssignedQuizRotationSet();

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
        ], [
            'rotation_set_used' => $rotationSet,
        ]);

        // Mark chapter as started
        $progress->markAsStarted();

        // Get quiz results for current chapter
        $quizResults = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $currentChapter->id)
            ->orderBy('attempt_number', 'desc')
            ->get();

        // Check insurance discount eligibility
        $insuranceDiscountEligible = $enrollment->isEligibleForInsuranceDiscount();

        return view('course-player.delaware.show', compact(
            'course',
            'enrollment',
            'currentChapter',
            'progress',
            'quizResults',
            'rotationSet',
            'insuranceDiscountEligible'
        ));
    }

    /**
     * Update chapter progress with Delaware-specific interactive content tracking
     */
    public function updateProgress(Request $request, $courseId, $chapterId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'time_spent' => 'integer|min:0',
            'last_position' => 'string|nullable',
            'interactive_content_completed' => 'boolean',
            'aggressive_driving_topics' => 'boolean',
            'insurance_discount_topics' => 'boolean',
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

        // Handle interactive content completion for Delaware
        if ($request->interactive_content_completed) {
            $progress->markInteractiveContentCompleted();
        }

        // Update topic coverage
        if ($request->aggressive_driving_topics || $request->insurance_discount_topics) {
            $progress->updateTopicCoverage(
                $request->aggressive_driving_topics,
                $request->insurance_discount_topics
            );
        }

        // Update enrollment overall progress
        $this->updateEnrollmentProgress($enrollment);

        return response()->json([
            'success' => true,
            'progress' => $progress->fresh(),
            'enrollment_progress' => $enrollment->fresh()->progress_percentage,
            'interactive_content_completed' => $progress->interactive_content_completed,
            'can_complete' => $progress->canComplete(),
            'insurance_discount_eligible' => $enrollment->fresh()->isEligibleForInsuranceDiscount(),
        ]);
    }

    /**
     * Submit quiz answers with Delaware rotation system
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

        // Get assigned rotation set
        $rotationSet = $enrollment->getAssignedQuizRotationSet();

        // Grade the quiz
        $result = $this->gradeQuiz(
            $quiz, 
            $request->answers, 
            $enrollment, 
            $attemptNumber, 
            $request->time_spent,
            $rotationSet
        );

        // Update chapter progress if quiz passed
        if ($result->passed) {
            $progress = Progress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->first();

            if ($progress) {
                $progress->update([
                    'quiz_passed' => true,
                    'quiz_best_score' => max($progress->quiz_best_score ?? 0, $result->score),
                    'rotation_set_used' => $rotationSet,
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
            'rotation_set_used' => $rotationSet,
            'aggressive_driving_score' => $result->aggressive_driving_score,
            'insurance_discount_eligible' => $result->insurance_discount_eligible,
        ]);
    }

    /**
     * Get rotated quiz questions for Delaware
     */
    public function getRotatedQuestions(Request $request, $courseId, $chapterId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapter = Chapter::findOrFail($chapterId);
        $rotationSet = $enrollment->getAssignedQuizRotationSet();

        // Get questions for the assigned rotation set
        $questions = $chapter->getRotatedQuizQuestions($user->id, 10);

        return response()->json([
            'success' => true,
            'questions' => $questions->map(function($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'points' => $question->points,
                    'aggressive_driving_related' => $question->aggressive_driving_related,
                    'insurance_discount_topic' => $question->insurance_discount_topic,
                ];
            }),
            'rotation_set' => $rotationSet,
        ]);
    }

    /**
     * Request insurance discount certificate
     */
    public function requestInsuranceDiscount(Request $request, $courseId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        if (!$enrollment->isEligibleForInsuranceDiscount()) {
            return response()->json([
                'success' => false,
                'error' => 'Not eligible for insurance discount certificate.',
            ], 422);
        }

        $enrollment->update([
            'insurance_discount_requested' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Insurance discount certificate requested successfully.',
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
     * Grade quiz with Delaware-specific rotation and topic scoring
     */
    private function gradeQuiz($quiz, $answers, $enrollment, $attemptNumber, $timeSpent, $rotationSet)
    {
        // Get questions for the specific rotation set
        $questions = $quiz->getQuestionsForRotationSet($rotationSet, 10);
        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $aggressiveDrivingCorrect = 0;
        $insuranceDiscountCorrect = 0;
        $aggressiveDrivingTotal = 0;
        $insuranceDiscountTotal = 0;

        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $question->isCorrectAnswer($userAnswer);
            
            if ($isCorrect) {
                $correctAnswers++;
            }

            // Track aggressive driving questions
            if ($question->aggressive_driving_related) {
                $aggressiveDrivingTotal++;
                if ($isCorrect) {
                    $aggressiveDrivingCorrect++;
                }
            }

            // Track insurance discount questions
            if ($question->insurance_discount_topic) {
                $insuranceDiscountTotal++;
                if ($isCorrect) {
                    $insuranceDiscountCorrect++;
                }
            }
        }

        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passed = $score >= $quiz->passing_score;

        // Calculate aggressive driving score
        $aggressiveDrivingScore = $aggressiveDrivingTotal > 0 ? 
            ($aggressiveDrivingCorrect / $aggressiveDrivingTotal) * 100 : 0;

        // Determine insurance discount eligibility
        $insuranceDiscountEligible = $passed && 
            $aggressiveDrivingScore >= 80 && 
            $enrollment->course->insurance_discount_eligible;

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
            'rotation_set_used' => $rotationSet,
            'aggressive_driving_score' => $aggressiveDrivingScore,
            'insurance_discount_eligible' => $insuranceDiscountEligible,
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

        // Update aggressive driving completion
        $aggressiveDrivingCompleted = false;
        if ($enrollment->course->aggressive_driving_course) {
            $aggressiveDrivingCompleted = Progress::where('enrollment_id', $enrollment->id)
                ->where('aggressive_driving_topics_covered', true)
                ->count() >= $totalChapters;
        }

        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'last_activity_at' => now(),
            'aggressive_driving_completion' => $aggressiveDrivingCompleted,
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
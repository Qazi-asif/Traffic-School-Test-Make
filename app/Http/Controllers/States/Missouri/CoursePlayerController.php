<?php

namespace App\Http\Controllers\States\Missouri;

use App\Http\Controllers\Controller;
use App\Models\Missouri\Course;
use App\Models\Missouri\Chapter;
use App\Models\Missouri\Enrollment;
use App\Models\Missouri\Progress;
use App\Models\Missouri\QuizResult;
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
     * Display the course player for Missouri courses
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

        // Check Form 4444 eligibility
        $form4444Eligible = $this->checkForm4444Eligibility($enrollment);

        return view('course-player.missouri.show', compact(
            'course',
            'enrollment',
            'currentChapter',
            'progress',
            'quizResults',
            'form4444Eligible'
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
            'form_4444_eligible' => $this->checkForm4444Eligibility($enrollment->fresh()),
        ]);
    }

    /**
     * Submit quiz answers with Missouri-specific rotation logic
     */
    public function submitQuiz(Request $request, $courseId, $chapterId, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_spent' => 'required|integer|min:1',
            'quiz_set' => 'string|nullable',
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

        // Determine quiz set for Missouri rotation
        $quizSet = $request->quiz_set ?: $this->determineQuizSet($user->id, $quiz->id);

        // Grade the quiz
        $result = $this->gradeQuiz($quiz, $request->answers, $enrollment, $attemptNumber, $request->time_spent, $quizSet);

        // Update chapter progress if quiz passed
        if ($result->passed) {
            $progress = Progress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->first();

            if ($progress) {
                $progress->update([
                    'quiz_passed' => true,
                    'quiz_best_score' => max($progress->quiz_best_score ?? 0, $result->score),
                    'quiz_set_used' => $quizSet,
                ]);

                // Mark chapter as completed and check Form 4444 eligibility
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
            'quiz_set_used' => $quizSet,
        ]);
    }

    /**
     * Generate Form 4444 for Missouri students
     */
    public function generateForm4444(Request $request, $courseId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        // Check eligibility
        if (!$this->checkForm4444Eligibility($enrollment)) {
            return response()->json([
                'success' => false,
                'error' => 'Not eligible for Form 4444 generation.',
            ], 422);
        }

        // Generate Form 4444
        $form4444 = \App\Models\MissouriForm4444::create([
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $courseId,
            'student_name' => $user->first_name . ' ' . $user->last_name,
            'completion_date' => $enrollment->completed_at ?: now(),
            'course_hours' => $enrollment->course->total_duration / 60,
            'generated_at' => now(),
        ]);

        // Update enrollment
        $enrollment->update([
            'form_4444_generated' => true,
        ]);

        return response()->json([
            'success' => true,
            'form_4444_id' => $form4444->id,
            'download_url' => route('missouri.form4444.download', $form4444->id),
        ]);
    }

    /**
     * Determine quiz set for Missouri rotation
     */
    private function determineQuizSet($userId, $quizId)
    {
        $sets = ['A', 'B', 'C'];
        $index = ($userId + $quizId) % count($sets);
        return $sets[$index];
    }

    /**
     * Check Form 4444 eligibility
     */
    private function checkForm4444Eligibility(Enrollment $enrollment)
    {
        if (!$enrollment->course->requires_form_4444) {
            return false;
        }

        // Must complete all chapters and pass final exam
        $totalChapters = Chapter::where('course_id', $enrollment->course_id)->active()->count();
        $completedChapters = Progress::where('enrollment_id', $enrollment->id)
            ->form4444Eligible()
            ->count();

        return $completedChapters >= $totalChapters && 
               $enrollment->completed_at && 
               $enrollment->final_exam_completed;
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
     * Grade quiz and create result record with Missouri-specific features
     */
    private function gradeQuiz($quiz, $answers, $enrollment, $attemptNumber, $timeSpent, $quizSet)
    {
        // Get questions for the specific quiz set
        $questions = $quiz->questions()
            ->active()
            ->where('quiz_set', $quizSet)
            ->get();

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
            'quiz_set_used' => $quizSet,
            'rotation_seed' => $enrollment->user_id + $quiz->id,
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
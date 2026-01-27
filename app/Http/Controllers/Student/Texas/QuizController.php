<?php

namespace App\Http\Controllers\Student\Texas;

use App\Http\Controllers\Controller;
use App\Models\Texas\Chapter;
use App\Models\Texas\ChapterQuiz;
use App\Models\Texas\QuizQuestion;
use App\Models\Texas\QuizResult;
use App\Models\Texas\Enrollment;
use App\Models\Texas\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display quiz questions for a chapter
     */
    public function show($chapterId)
    {
        $user = Auth::user();
        $chapter = Chapter::with('course')->findOrFail($chapterId);
        
        // Get enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $chapter->course_id)
            ->firstOrFail();

        if ($enrollment->payment_status !== 'paid') {
            return redirect()->route('student.texas.courses.index')
                ->with('error', 'Course access denied. Please complete payment.');
        }

        // Check if chapter progress exists and is started
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->first();

        if (!$progress || !$progress->started_at) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('error', 'Please complete the chapter content before taking the quiz.');
        }

        // Texas-specific: Check video completion requirement
        if ($chapter->requires_video_completion && !$progress->video_completed) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('error', 'Please complete the chapter video before taking the quiz.');
        }

        // Check proctoring requirements
        if ($enrollment->proctoring_required && !$enrollment->proctoring_completed) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('error', 'Proctoring session must be completed before taking quizzes.');
        }

        // Get quiz questions
        $questions = QuizQuestion::where('chapter_id', $chapterId)
            ->active()
            ->orderBy('order_index')
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('info', 'No quiz available for this chapter.');
        }

        // Get previous quiz attempts
        $previousAttempts = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->orderBy('created_at', 'desc')
            ->get();

        $canRetake = $this->canRetakeQuiz($chapter, $previousAttempts);

        return view('student.texas.quiz.show', compact(
            'chapter',
            'questions',
            'enrollment',
            'progress',
            'previousAttempts',
            'canRetake'
        ));
    }

    /**
     * Process quiz answers
     */
    public function submit(Request $request, $chapterId)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string',
            'time_taken' => 'required|integer|min:1',
            'proctoring_verified' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $chapter = Chapter::with('course')->findOrFail($chapterId);
        
        // Get enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $chapter->course_id)
            ->firstOrFail();

        // Texas-specific: Verify proctoring if required
        if ($enrollment->proctoring_required && !$enrollment->proctoring_completed) {
            return response()->json([
                'success' => false,
                'error' => 'Proctoring verification required before submitting quiz.',
            ], 422);
        }

        // Check if user can take quiz
        $previousAttempts = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->get();

        if (!$this->canRetakeQuiz($chapter, $previousAttempts)) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz retake limit exceeded.',
            ], 422);
        }

        // Get questions and calculate score
        $questions = QuizQuestion::where('chapter_id', $chapterId)
            ->active()
            ->get()
            ->keyBy('id');

        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $results = [];

        foreach ($request->answers as $questionId => $answer) {
            $question = $questions->get($questionId);
            if ($question) {
                $isCorrect = $question->correct_answer === $answer;
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                $results[] = [
                    'question_id' => $questionId,
                    'selected_answer' => $answer,
                    'correct_answer' => $question->correct_answer,
                    'is_correct' => $isCorrect,
                ];
            }
        }

        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
        $passed = $score >= $chapter->course->min_pass_score;

        // Create quiz result
        $quizResult = QuizResult::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'chapter_id' => $chapterId,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'time_taken_minutes' => $request->time_taken,
            'passed' => $passed,
            'answers_json' => json_encode($results),
            'attempt_number' => $previousAttempts->count() + 1,
            'proctoring_verified' => $enrollment->proctoring_completed,
        ]);

        // Update progress
        $progress = Progress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->first();

        if ($progress) {
            $progress->update([
                'quiz_passed' => $passed,
                'quiz_attempts' => $previousAttempts->count() + 1,
                'quiz_best_score' => max($progress->quiz_best_score ?? 0, $score),
            ]);
        }

        return response()->json([
            'success' => true,
            'quiz_result_id' => $quizResult->id,
            'score' => $score,
            'passed' => $passed,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'passing_score' => $chapter->course->min_pass_score,
            'proctoring_verified' => $enrollment->proctoring_completed,
            'can_retake' => !$passed && $this->canRetakeQuiz($chapter, $previousAttempts->push($quizResult)),
            'redirect' => route('student.texas.quiz.results', $quizResult->id),
        ]);
    }

    /**
     * Show quiz results
     */
    public function results($resultId)
    {
        $user = Auth::user();
        $quizResult = QuizResult::with(['chapter.course', 'enrollment'])
            ->where('user_id', $user->id)
            ->findOrFail($resultId);

        $answers = json_decode($quizResult->answers_json, true);
        
        // Get questions for detailed results
        $questionIds = collect($answers)->pluck('question_id');
        $questions = QuizQuestion::whereIn('id', $questionIds)->get()->keyBy('id');

        return view('student.texas.quiz.results', compact(
            'quizResult',
            'answers',
            'questions'
        ));
    }

    /**
     * Allow quiz retake
     */
    public function retry($chapterId)
    {
        $user = Auth::user();
        $chapter = Chapter::with('course')->findOrFail($chapterId);
        
        // Get enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $chapter->course_id)
            ->firstOrFail();

        // Check proctoring requirements
        if ($enrollment->proctoring_required && !$enrollment->proctoring_completed) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('error', 'Proctoring session must be completed before retaking quizzes.');
        }

        // Check previous attempts
        $previousAttempts = QuizResult::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->get();

        if (!$this->canRetakeQuiz($chapter, $previousAttempts)) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('error', 'Quiz retake limit exceeded.');
        }

        // Check if already passed
        $hasPassed = $previousAttempts->where('passed', true)->isNotEmpty();
        if ($hasPassed) {
            return redirect()->route('student.texas.courses.show', $chapter->course_id)
                ->with('info', 'You have already passed this quiz.');
        }

        return redirect()->route('student.texas.quiz.show', $chapterId)
            ->with('info', 'You can retake this quiz. Good luck!');
    }

    /**
     * Check if user can retake quiz
     */
    private function canRetakeQuiz($chapter, $attempts)
    {
        // Texas allows 2 attempts per quiz (stricter than other states)
        $maxAttempts = $chapter->course->max_quiz_attempts ?? 2;
        
        // If user has passed, no more retakes needed
        if ($attempts->where('passed', true)->isNotEmpty()) {
            return false;
        }
        
        return $attempts->count() < $maxAttempts;
    }
}
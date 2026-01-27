<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    public function startQuiz(UserCourseEnrollment $enrollment, ?Chapter $chapter = null)
    {
        $questions = $chapter
            ? $chapter->questions()->orderBy('order_index')->get()
            : $enrollment->course->questions()->whereNull('chapter_id')->orderBy('order_index')->get();

        return response()->json([
            'questions' => $questions,
            'total_questions' => $questions->count(),
            'chapter_id' => $chapter?->id,
        ]);
    }

    public function submitQuiz(Request $request, UserCourseEnrollment $enrollment, ?Chapter $chapter = null)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_answer' => 'required|string',
            'time_spent' => 'required|integer|min:0',
        ]);

        $questions = Question::whereIn('id', collect($validated['answers'])->pluck('question_id'))->get();
        $totalQuestions = $questions->count();
        $correctAnswers = 0;

        foreach ($validated['answers'] as $answer) {
            $question = $questions->find($answer['question_id']);
            if ($question && $question->correct_answer === $answer['selected_answer']) {
                $correctAnswers++;
            }
        }

        $score = ($correctAnswers / $totalQuestions) * 100;
        $minPassScore = $enrollment->course->min_pass_score;
        $passed = $score >= $minPassScore;

        $attempt = QuizAttempt::create([
            'enrollment_id' => $enrollment->id,
            'chapter_id' => $chapter?->id,
            'questions_attempted' => $validated['answers'],
            'score' => $score,
            'total_questions' => $totalQuestions,
            'passed' => $passed,
            'time_spent' => $validated['time_spent'],
            'attempted_at' => now(),
            'completed_at' => now(),
        ]);

        return response()->json([
            'attempt' => $attempt,
            'score' => $score,
            'passed' => $passed,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
        ]);
    }

    public function getQuizResults(UserCourseEnrollment $enrollment, ?Chapter $chapter = null)
    {
        $attempt = QuizAttempt::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapter?->id)
            ->latest()
            ->first();

        return response()->json($attempt);
    }
}

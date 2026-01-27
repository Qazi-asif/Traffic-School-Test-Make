<?php

namespace App\Http\Controllers;

use App\Models\MissouriQuizBank;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class FinalExamController extends Controller
{
    public function generateFinalExam()
    {
        // Get 50 random questions from all chapters
        $questions = MissouriQuizBank::inRandomOrder()
            ->limit(50)
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'options' => [
                        'A' => $q->option_a,
                        'B' => $q->option_b,
                        'C' => $q->option_c,
                        'D' => $q->option_d,
                    ],
                ];
            });

        return response()->json([
            'exam_id' => uniqid('final_'),
            'questions' => $questions,
            'passing_score' => 80,
            'time_limit' => null, // No time limit
        ]);
    }

    public function submitFinalExam(Request $request)
    {
        $answers = $request->answers; // Array of question_id => answer
        $correct = 0;
        $total = count($answers);

        foreach ($answers as $questionId => $userAnswer) {
            $question = MissouriQuizBank::find($questionId);
            if ($question && $question->correct_answer === $userAnswer) {
                $correct++;
            }
        }

        $score = ($correct / $total) * 100;
        $passed = $score >= 80;

        // Save attempt
        QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_type' => 'final_exam',
            'score' => $score,
            'passed' => $passed,
            'answers' => json_encode($answers),
        ]);

        return response()->json([
            'score' => $score,
            'correct' => $correct,
            'total' => $total,
            'passed' => $passed,
            'can_retake' => ! $passed,
        ]);
    }
}

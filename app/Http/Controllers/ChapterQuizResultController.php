<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChapterQuizResultController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'chapter_id' => 'required|integer',
                'enrollment_id' => 'nullable|integer',
                'total_questions' => 'required|integer',
                'correct_answers' => 'required|integer',
                'wrong_answers' => 'required|integer',
                'percentage' => 'required|numeric',
                'answers' => 'required|array'
            ]);

            $data = [
                'user_id' => Auth::id(),
                'chapter_id' => $validated['chapter_id'],
                'total_questions' => $validated['total_questions'],
                'correct_answers' => $validated['correct_answers'],
                'wrong_answers' => $validated['wrong_answers'],
                'percentage' => $validated['percentage'],
                'answers' => json_encode($validated['answers']), // Use 'answers' column, not 'answers_json'
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Add enrollment_id if provided
            if (isset($validated['enrollment_id'])) {
                $data['enrollment_id'] = $validated['enrollment_id'];
            }

            $quizResultId = DB::table('chapter_quiz_results')->insertGetId($data);

            return response()->json([
                'success' => true,
                'quiz_result_id' => $quizResultId,
                'message' => 'Quiz result saved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Quiz result save error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save quiz result: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getResult($chapterId)
    {
        try {
            $result = DB::table('chapter_quiz_results')
                ->where('user_id', Auth::id())
                ->where('chapter_id', $chapterId)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($result) {
                return response()->json([
                    'quiz_result' => $result
                ]);
            }

            return response()->json([
                'quiz_result' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get quiz result'
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityVerificationController extends Controller
{
    public function getAllQuestions()
    {
        try {
            \Log::info('=== getAllQuestions START ===');
            \Log::info('Request URL: ' . request()->fullUrl());
            \Log::info('Request Method: ' . request()->method());
            
            // Check if table exists
            $tableExists = \Schema::hasTable('security_questions');
            \Log::info('Table security_questions exists: ' . ($tableExists ? 'YES' : 'NO'));
            
            if (!$tableExists) {
                \Log::error('Table does not exist!');
                return response()->json(['error' => 'Table does not exist'], 500);
            }
            
            // Try raw query first
            $rawCount = \DB::table('security_questions')->count();
            \Log::info('Raw query count: ' . $rawCount);
            
            // Try Eloquent query
            \Log::info('Attempting Eloquent query...');
            $questions = SecurityQuestion::active()->ordered()->get();
            \Log::info('Eloquent query successful, count: ' . $questions->count());
            
            if ($questions->isEmpty()) {
                \Log::warning('No active questions found');
                // Try without active filter
                $allQuestions = SecurityQuestion::all();
                \Log::info('All questions (no filter) count: ' . $allQuestions->count());
                
                if ($allQuestions->isNotEmpty()) {
                    \Log::info('Questions exist but are not active. Returning them anyway.');
                    $questions = $allQuestions;
                } else {
                    \Log::error('No questions found at all');
                    return response()->json(['error' => 'No security questions available'], 200);
                }
            }
            
            \Log::info('Building questions map...');
            $questionsMap = [];
            foreach ($questions as $question) {
                \Log::info('Processing question: ' . $question->question_key);
                $fullQuestion = $question->full_question;
                \Log::info('Full question text: ' . $fullQuestion);
                $questionsMap['security_' . $question->question_key] = [
                    'question' => $fullQuestion,
                    'answer_type' => $question->answer_type
                ];
            }
            
            \Log::info('Questions map built, keys: ' . json_encode(array_keys($questionsMap)));
            \Log::info('=== getAllQuestions SUCCESS ===');
            
            return response()->json($questionsMap);
            
        } catch (\Exception $e) {
            \Log::error('=== getAllQuestions ERROR ===');
            \Log::error('Exception class: ' . get_class($e));
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to load security questions',
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ], 500);
        }
    }

    public function getRandomQuestions(Request $request)
    {
        try {
            \Log::info('=== getRandomQuestions START ===');
            \Log::info('Request data: ' . json_encode($request->all()));
            
            $user = Auth::user();
            \Log::info('User authenticated: ' . ($user ? 'YES (ID: ' . $user->id . ')' : 'NO'));
            
            if (!$user) {
                \Log::warning('User not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $chapterCount = $request->input('chapter_count', 0);
            \Log::info('Chapter count: ' . $chapterCount);
            
            $questionKeys = $this->getSequentialQuestions($chapterCount);
            \Log::info('Question keys: ' . json_encode($questionKeys));

            $questions = [];
            foreach ($questionKeys as $key) {
                \Log::info('Processing question key: ' . $key);
                
                $securityQuestion = SecurityQuestion::where('question_key', $key)
                    ->where('is_active', true)
                    ->first();
                
                \Log::info('Question found: ' . ($securityQuestion ? 'YES' : 'NO'));
                
                if ($securityQuestion) {
                    \Log::info('Question data: ' . json_encode($securityQuestion->toArray()));
                    $questions[] = [
                        'id' => $key,
                        'question' => $securityQuestion->full_question
                    ];
                } else {
                    \Log::warning('Question not found for key: ' . $key);
                }
            }

            \Log::info('Total questions returned: ' . count($questions));
            \Log::info('=== getRandomQuestions SUCCESS ===');
            
            return response()->json([
                'questions' => $questions,
                'session_id' => uniqid('sec_', true),
                'chapter_count' => $chapterCount
            ]);
        } catch (\Exception $e) {
            \Log::error('=== getRandomQuestions ERROR ===');
            \Log::error('Exception class: ' . get_class($e));
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to load security questions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getSequentialQuestions($chapterCount)
    {
        $activeQuestions = SecurityQuestion::active()->ordered()->pluck('question_key')->toArray();
        
        if (empty($activeQuestions)) {
            return ['q1'];
        }
        
        $totalQuestions = count($activeQuestions);
        $adjustedCount = $chapterCount % $totalQuestions;
        
        return [$activeQuestions[$adjustedCount]];
    }

    public function verifyAnswers(Request $request)
    {
        try {
            \Log::info('=== verifyAnswers START ===');
            
            $user = Auth::user();
            \Log::info('User authenticated: ' . ($user ? 'YES (ID: ' . $user->id . ')' : 'NO'));
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $answers = $request->input('answers', []);
            \Log::info('Answers received: ' . json_encode(array_keys($answers)));
            \Log::info('Total answers: ' . count($answers));
            
            $correct = 0;
            $total = count($answers);
            $errors = [];

            foreach ($answers as $questionId => $userAnswer) {
                \Log::info('Processing question: ' . $questionId);
                \Log::info('User answer: ' . $userAnswer);
                
                $correctAnswer = $user->{'security_' . $questionId};
                \Log::info('Correct answer from user model: ' . ($correctAnswer ? 'EXISTS' : 'MISSING'));
                
                $securityQuestion = SecurityQuestion::where('question_key', $questionId)->first();
                \Log::info('Question found in DB: ' . ($securityQuestion ? 'YES' : 'NO'));
                
                $questionText = $securityQuestion ? $securityQuestion->full_question : 'Unknown question';
                
                $userAnswerNormalized = trim($userAnswer);
                $correctAnswerNormalized = trim($correctAnswer);
                
                \Log::info('User answer normalized: ' . $userAnswerNormalized);
                \Log::info('Correct answer normalized: ' . $correctAnswerNormalized);
                
                if ($securityQuestion && $securityQuestion->answer_type === 'text') {
                    \Log::info('Text answer type - converting to lowercase');
                    $userAnswerNormalized = strtolower($userAnswerNormalized);
                    $correctAnswerNormalized = strtolower($correctAnswerNormalized);
                }
                
                $isCorrect = ($userAnswerNormalized === $correctAnswerNormalized);
                \Log::info('Answer correct: ' . ($isCorrect ? 'YES' : 'NO'));
                
                if ($isCorrect) {
                    $correct++;
                } else {
                    $errors[] = [
                        'question_id' => $questionId,
                        'question' => $questionText,
                        'message' => 'Incorrect answer. Please try again.'
                    ];
                }
            }

            $allCorrect = ($correct === $total);
            \Log::info('Final result - Correct: ' . $correct . '/' . $total);
            \Log::info('All correct: ' . ($allCorrect ? 'YES' : 'NO'));
            \Log::info('=== verifyAnswers SUCCESS ===');

            return response()->json([
                'success' => $allCorrect,
                'correct' => $correct,
                'total' => $total,
                'errors' => $errors,
                'message' => $allCorrect ? 'All answers correct!' : 'Some answers are incorrect. Please try again.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('=== verifyAnswers ERROR ===');
            \Log::error('Exception: ' . get_class($e));
            \Log::error('Message: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile());
            \Log::error('Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Verification failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

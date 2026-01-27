<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class QuizMaintenanceController extends Controller
{
    /**
     * Show the quiz maintenance dashboard
     */
    public function index()
    {
        return view('admin.quiz-maintenance.index');
    }

    /**
     * Diagnose broken quizzes via AJAX
     */
    public function diagnose()
    {
        try {
            $results = [
                'total_checked' => 0,
                'total_broken' => 0,
                'broken_questions' => []
            ];

            $tables = [
                'chapter_questions' => 'chapter_id',
                'questions' => 'chapter_id',
                'final_exam_questions' => 'course_id'
            ];

            foreach ($tables as $tableName => $foreignKey) {
                try {
                    $questions = DB::table($tableName)->get();
                    
                    foreach ($questions as $q) {
                        $results['total_checked']++;
                        $issues = $this->checkQuestion($q);
                        
                        if (!empty($issues)) {
                            $results['total_broken']++;
                            $results['broken_questions'][] = [
                                'id' => $q->id,
                                'table' => $tableName,
                                'foreign_key' => $foreignKey,
                                'foreign_value' => $q->$foreignKey,
                                'question_text' => substr($q->question_text, 0, 100),
                                'issues' => $issues
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // Skip tables that don't exist
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix broken quizzes via AJAX
     */
    public function fix(Request $request)
    {
        try {
            $dryRun = $request->input('dry_run', false);
            
            $results = [
                'fixed' => 0,
                'errors' => 0,
                'details' => []
            ];

            $tables = ['chapter_questions', 'questions', 'final_exam_questions'];

            foreach ($tables as $tableName) {
                try {
                    $questions = DB::table($tableName)->get();
                    
                    foreach ($questions as $q) {
                        $options = json_decode($q->options, true);
                        $correctAnswer = $q->correct_answer;
                        
                        // Handle case where correct_answer is JSON-encoded
                        if (is_string($correctAnswer) && (substr($correctAnswer, 0, 1) === '[' || substr($correctAnswer, 0, 1) === '{')) {
                            $decoded = json_decode($correctAnswer, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $correctAnswer = is_array($decoded) ? (isset($decoded[0]) ? $decoded[0] : '') : $decoded;
                            }
                        }
                        
                        // If correct_answer is still an array, convert to string
                        if (is_array($correctAnswer)) {
                            $correctAnswer = isset($correctAnswer[0]) ? $correctAnswer[0] : '';
                        }
                        
                        // Ensure correct_answer is a string and trim it
                        $correctAnswer = trim((string) $correctAnswer);
                        
                        if (!is_array($options) || empty($options)) {
                            $results['errors']++;
                            continue;
                        }
                        
                        $needsUpdate = false;
                        $newCorrectAnswer = $correctAnswer;
                        
                        // Normalize options
                        $normalizedOptions = [];
                        $optionIndex = 0;
                        foreach ($options as $key => $value) {
                            $letter = chr(65 + $optionIndex);
                            // Ensure value is a string
                            $normalizedOptions[$letter] = trim((string) $value);
                            $optionIndex++;
                        }
                        
                        // Normalize correct answer to letter
                        if (!preg_match('/^[A-E]$/i', $correctAnswer)) {
                            foreach ($normalizedOptions as $letter => $optionText) {
                                $cleanOption = preg_replace('/^[A-E]\.\s*/i', '', $optionText);
                                $cleanCorrect = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswer);
                                
                                if (strcasecmp(trim($cleanOption), trim($cleanCorrect)) === 0) {
                                    $newCorrectAnswer = $letter;
                                    $needsUpdate = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($newCorrectAnswer !== $correctAnswer) {
                            $needsUpdate = true;
                        }
                        
                        if ($needsUpdate) {
                            if (!$dryRun) {
                                DB::table($tableName)
                                    ->where('id', $q->id)
                                    ->update([
                                        'correct_answer' => $newCorrectAnswer,
                                        'options' => json_encode($normalizedOptions),
                                        'updated_at' => now()
                                    ]);
                            }
                            
                            $results['fixed']++;
                            $results['details'][] = [
                                'table' => $tableName,
                                'id' => $q->id,
                                'old' => $correctAnswer,
                                'new' => $newCorrectAnswer
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'dry_run' => $dryRun
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function checkQuestion($q)
    {
        $issues = [];
        $options = json_decode($q->options, true);
        $correctAnswer = $q->correct_answer;
        
        // Handle case where correct_answer is JSON-encoded
        if (is_string($correctAnswer) && (substr($correctAnswer, 0, 1) === '[' || substr($correctAnswer, 0, 1) === '{')) {
            $decoded = json_decode($correctAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $correctAnswer = is_array($decoded) ? (isset($decoded[0]) ? $decoded[0] : '') : $decoded;
            }
        }
        
        // If correct_answer is still an array, convert to string
        if (is_array($correctAnswer)) {
            $correctAnswer = isset($correctAnswer[0]) ? $correctAnswer[0] : '';
        }
        
        // Ensure correct_answer is a string
        $correctAnswer = (string) $correctAnswer;
        
        if (!is_array($options)) {
            $issues[] = "Options is not an array";
            return $issues;
        }
        
        if (empty($options)) {
            $issues[] = "Options array is empty";
        }
        
        if (empty(trim($correctAnswer))) {
            $issues[] = "Correct answer is empty";
        }
        
        if ($correctAnswer !== trim($correctAnswer)) {
            $issues[] = "Correct answer has whitespace";
        }
        
        // Check if correct answer exists in options
        $correctAnswerFound = false;
        $correctAnswerNorm = trim($correctAnswer);
        
        foreach ($options as $key => $value) {
            // Ensure key and value are strings
            $key = (string) $key;
            $value = (string) $value;
            
            $keyNorm = trim($key);
            $valueNorm = trim($value);
            
            if (strcasecmp($keyNorm, $correctAnswerNorm) === 0 || 
                strcasecmp($valueNorm, $correctAnswerNorm) === 0) {
                $correctAnswerFound = true;
                break;
            }
            
            $valueClean = preg_replace('/^[A-E]\.\s*/i', '', $valueNorm);
            $correctClean = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswerNorm);
            
            if (strcasecmp($valueClean, $correctClean) === 0) {
                $correctAnswerFound = true;
                break;
            }
        }
        
        if (!$correctAnswerFound) {
            $issues[] = "Correct answer not found in options";
        }
        
        return $issues;
    }
}

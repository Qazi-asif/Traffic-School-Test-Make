<?php

namespace App\Http\Controllers;

use App\Models\ChapterQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;

class QuestionController extends Controller
{
    public function index($chapterId)
    {
        try {
            \Log::info("QuestionController: Fetching questions for chapter {$chapterId}");

            // Handle special "final-exam" chapter
            if ($chapterId === 'final-exam') {
                $questions = \DB::table('final_exam_questions')
                    ->orderBy('order_index')
                    ->get();
                    
                \Log::info("QuestionController: Found {$questions->count()} final exam questions");
                
                $processedQuestions = [];
                foreach ($questions as $question) {
                    $options = json_decode($question->options, true) ?: [];
                    
                    $processedQuestions[] = [
                        'id' => $question->id,
                        'chapter_id' => 'final-exam',
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'points' => $question->points,
                        'order_index' => $question->order_index,
                        'options' => $options,
                    ];
                }
                
                return response()->json($processedQuestions);
            }

            // FIXED: Prioritize chapter_questions table to avoid duplicates
            // Check configuration to see if legacy table should be ignored
            $disableLegacyTable = config('quiz.disable_legacy_questions_table', false);
            
            $questionsFromNew = ChapterQuestion::where('chapter_id', $chapterId)
                ->orderBy('order_index')
                ->get();
            
            // Only get from legacy table if no questions in new table AND legacy is not disabled
            if ($questionsFromNew->isEmpty() && !$disableLegacyTable) {
                $questionsFromLegacy = Question::where('chapter_id', $chapterId)
                    ->orderBy('order_index')
                    ->get();
                $questions = $questionsFromLegacy;
                \Log::info("QuestionController: Using legacy questions table - found {$questions->count()} questions");
            } else {
                $questions = $questionsFromNew;
                if ($disableLegacyTable && $questionsFromNew->isEmpty()) {
                    \Log::info("QuestionController: Legacy table disabled, no questions found in chapter_questions table");
                } else {
                    \Log::info("QuestionController: Using chapter_questions table - found {$questions->count()} questions");
                }
            }

            $processedQuestions = [];

            foreach ($questions as $question) {
                $data = [
                    'id' => $question->id,
                    'chapter_id' => $question->chapter_id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'correct_answer' => $question->correct_answer,
                    'explanation' => $question->explanation,
                    'points' => $question->points,
                    'order_index' => $question->order_index,
                    'options' => [],
                ];

                // Handle options safely
                if ($question->options) {
                    if (is_string($question->options)) {
                        $decoded = json_decode($question->options, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $data['options'] = $decoded;
                        }
                    } elseif (is_array($question->options)) {
                        $data['options'] = $question->options;
                    }
                }

                $processedQuestions[] = $data;
            }

            \Log::info('QuestionController: Processed '.count($processedQuestions).' questions successfully');

            return response()->json($processedQuestions);
        } catch (\Exception $e) {
            \Log::error('QuestionController error: '.$e->getMessage());

            return response()->json([]);
        }
    }

    public function store(Request $request, $chapterId)
    {
        \Log::info('QuestionController store called', [
            'chapterId' => $chapterId,
            'request_data' => $request->all(),
            'query_params' => $request->query(),
        ]);

        try {
            $validated = $request->validate([
                'question_text' => 'required|string',
                'question_type' => 'required|in:multiple_choice,true_false',
                'options' => 'required|string',
                'correct_answer' => 'required|string',
                'explanation' => 'nullable|string',
                'points' => 'required|integer|min:1',
                'order_index' => 'required|integer|min:1',
                'quiz_set' => 'nullable|integer|in:1,2', // Add quiz_set validation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in QuestionController store', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 400);
        }

        // Handle special "final-exam" chapter
        if ($chapterId === 'final-exam') {
            // Check for course_id in both request body and query parameters
            $courseId = $request->input('course_id') ?: $request->query('course_id');
            \Log::info('Final exam question - course_id check', [
                'input_course_id' => $request->input('course_id'),
                'query_course_id' => $request->query('course_id'),
                'final_course_id' => $courseId
            ]);
            
            if (!$courseId) {
                \Log::error('Missing course_id for final exam question');
                return response()->json(['error' => 'course_id is required for final exam questions'], 400);
            }

            // Create final exam question
            $questionId = \DB::table('final_exam_questions')->insertGetId([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'options' => $validated['options'],
                'correct_answer' => $validated['correct_answer'],
                'explanation' => $validated['explanation'],
                'points' => $validated['points'],
                'order_index' => $validated['order_index'],
                'course_id' => $courseId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return the created question with final-exam chapter_id
            $question = \DB::table('final_exam_questions')->where('id', $questionId)->first();
            $question->chapter_id = 'final-exam';

            return response()->json($question, 201);
        }

        // Handle regular chapter questions - ALWAYS use chapter_questions table for new questions
        $chapter = \App\Models\Chapter::find($chapterId);
        $courseId = $chapter ? $chapter->course_id : null;

        $validated['chapter_id'] = $chapterId;
        $validated['course_id'] = $courseId;
        
        // Set default quiz_set to 1 if not provided
        if (!isset($validated['quiz_set'])) {
            $validated['quiz_set'] = 1;
        }
        
        // ALWAYS save new questions in chapter_questions table (not legacy questions table)
        \Log::info('Saving new question in chapter_questions table', [
            'chapter_id' => $chapterId,
            'course_id' => $courseId,
            'quiz_set' => $validated['quiz_set']
        ]);
        
        $question = ChapterQuestion::create($validated);

        return response()->json($question, 201);
    }

    public function show($id)
    {
        try {
            \Log::info("QuestionController: Looking for question ID {$id}");

            // Handle prefixed IDs from old questions (e.g., "old_24")
            if (strpos($id, 'old_') === 0) {
                $realId = str_replace('old_', '', $id);
                $question = \DB::table('questions')->where('id', $realId)->first();
                
                if ($question) {
                    \Log::info("QuestionController: Found old question ID {$realId}");
                    
                    $data = [
                        'id' => $id, // Keep the prefixed ID
                        'chapter_id' => $question->chapter_id,
                        'question_text' => $question->question_text ?? $question->question ?? '',
                        'question_type' => $question->question_type ?? 'multiple_choice',
                        'correct_answer' => $question->correct_answer ?? '',
                        'explanation' => $question->explanation ?? '',
                        'points' => $question->points ?? 1,
                        'order_index' => $question->order_index ?? 1,
                        'quiz_set' => 1, // Default to quiz set 1 for old questions
                        'options' => [],
                    ];

                    // Handle options safely
                    if ($question->options) {
                        if (is_string($question->options)) {
                            $decoded = json_decode($question->options, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $data['options'] = $decoded;
                            } else {
                                $data['options'] = [$question->options];
                            }
                        }
                    }

                    return response()->json($data);
                }
                
                return response()->json(['error' => 'Question not found'], 404);
            }

            // Check if this is a final exam question request
            $chapterId = request()->get('chapter_id') ?? request()->header('X-Chapter-Id');
            if ($chapterId && strpos($chapterId, 'final-exam') !== false) {
                \Log::info("QuestionController: Looking for final exam question ID {$id}");
                
                $question = \DB::table('final_exam_questions')->where('id', $id)->first();
                
                if ($question) {
                    \Log::info("QuestionController: Found final exam question ID {$id}");
                    
                    $data = [
                        'id' => $question->id,
                        'chapter_id' => 'final-exam',
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'points' => $question->points,
                        'order_index' => $question->order_index,
                        'options' => [],
                    ];

                    // Handle options safely
                    if ($question->options) {
                        if (is_string($question->options)) {
                            $decoded = json_decode($question->options, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $data['options'] = $decoded;
                            }
                        }
                    }

                    return response()->json($data);
                }
                
                \Log::warning("QuestionController: Final exam question ID {$id} not found");
                return response()->json(['error' => 'Final exam question not found'], 404);
            }

            // Check both tables and prioritize based on chapter context
            $questionFromQuestions = \App\Models\Question::find($id);
            $questionFromChapterQuestions = ChapterQuestion::find($id);
            
            $question = null;
            
            // If we have both, we need to determine which one to use
            if ($questionFromQuestions && $questionFromChapterQuestions) {
                \Log::info("QuestionController: Found question ID {$id} in BOTH tables");
                \Log::info("Questions table: Chapter {$questionFromQuestions->chapter_id} - {$questionFromQuestions->question_text}");
                \Log::info("ChapterQuestions table: Chapter {$questionFromChapterQuestions->chapter_id} - {$questionFromChapterQuestions->question_text}");
                
                // Check if we have chapter context from the request
                $requestedChapter = request()->get('chapter_id') ?? request()->header('X-Chapter-Id');
                
                if ($requestedChapter) {
                    // Use the question that matches the requested chapter
                    if ($questionFromQuestions->chapter_id == $requestedChapter) {
                        $question = $questionFromQuestions;
                        \Log::info("QuestionController: Using questions table (matches chapter {$requestedChapter})");
                    } elseif ($questionFromChapterQuestions->chapter_id == $requestedChapter) {
                        $question = $questionFromChapterQuestions;
                        \Log::info("QuestionController: Using chapter_questions table (matches chapter {$requestedChapter})");
                    } else {
                        // Default to questions table if neither matches
                        $question = $questionFromQuestions;
                        \Log::info("QuestionController: Using questions table (default, no chapter match)");
                    }
                } else {
                    // No chapter context, prefer questions table (newer)
                    $question = $questionFromQuestions;
                    \Log::info("QuestionController: Using questions table (no chapter context, preferring newer)");
                }
            } elseif ($questionFromQuestions) {
                $question = $questionFromQuestions;
                \Log::info("QuestionController: Found question ID {$id} in questions table only");
            } elseif ($questionFromChapterQuestions) {
                $question = $questionFromChapterQuestions;
                \Log::info("QuestionController: Found question ID {$id} in chapter_questions table only");
            }

            if (! $question) {
                \Log::warning("QuestionController: Question ID {$id} not found in either table");
                return response()->json(['error' => 'Question not found'], 404);
            }

            $data = [
                'id' => $question->id,
                'chapter_id' => $question->chapter_id,
                'question_text' => $question->question_text ?? '',
                'question_type' => $question->question_type ?? 'multiple_choice',
                'correct_answer' => $question->correct_answer ?? '',
                'explanation' => $question->explanation ?? '',
                'points' => $question->points ?? 1,
                'order_index' => $question->order_index ?? 1,
                'quiz_set' => $question->quiz_set ?? 1,
                'options' => [],
            ];

            // Handle options safely
            if ($question->options) {
                if (is_string($question->options)) {
                    $decoded = json_decode($question->options, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $data['options'] = $decoded;
                    } else {
                        // If JSON decode fails, treat as plain text
                        $data['options'] = [$question->options];
                    }
                } elseif (is_array($question->options)) {
                    $data['options'] = $question->options;
                }
            }

            \Log::info("QuestionController: Returning data for question ID {$id}: ".json_encode($data));

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('QuestionController show error: '.$e->getMessage());

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info("QuestionController: Updating question ID {$id}");

            $validated = $request->validate([
                'question_text' => 'required|string',
                'question_type' => 'required|in:multiple_choice,true_false',
                'options' => 'required|string',
                'correct_answer' => 'required|string',
                'explanation' => 'nullable|string',
                'points' => 'required|integer|min:1',
                'order_index' => 'required|integer|min:1',
                'quiz_set' => 'nullable|integer|in:1,2',
            ]);

            // Handle prefixed IDs from old questions (e.g., "old_24")
            if (strpos($id, 'old_') === 0) {
                $realId = str_replace('old_', '', $id);
                $updated = \DB::table('questions')->where('id', $realId)->update([
                    'question_text' => $validated['question_text'],
                    'question_type' => $validated['question_type'],
                    'options' => $validated['options'],
                    'correct_answer' => $validated['correct_answer'],
                    'explanation' => $validated['explanation'],
                    'points' => $validated['points'],
                    'order_index' => $validated['order_index'],
                    'updated_at' => now(),
                ]);

                if ($updated) {
                    $question = \DB::table('questions')->where('id', $realId)->first();
                    \Log::info("QuestionController: Successfully updated old question ID {$realId}");
                    return response()->json($question);
                }
                
                return response()->json(['error' => 'Question not found'], 404);
            }

            // Try final exam questions first
            $finalExamQuestion = \DB::table('final_exam_questions')->where('id', $id)->first();
            if ($finalExamQuestion) {
                \DB::table('final_exam_questions')->where('id', $id)->update([
                    'question_text' => $validated['question_text'],
                    'question_type' => $validated['question_type'],
                    'options' => $validated['options'],
                    'correct_answer' => $validated['correct_answer'],
                    'explanation' => $validated['explanation'],
                    'points' => $validated['points'],
                    'order_index' => $validated['order_index'],
                    'updated_at' => now(),
                ]);

                $updatedQuestion = \DB::table('final_exam_questions')->where('id', $id)->first();
                $updatedQuestion->chapter_id = 'final-exam';

                \Log::info("QuestionController: Successfully updated final exam question ID {$id}");
                return response()->json($updatedQuestion);
            }

            // Try ChapterQuestion
            $question = ChapterQuestion::find($id);
            if ($question) {
                $question->update($validated);
                \Log::info("QuestionController: Successfully updated chapter question ID {$id}");
                return response()->json($question);
            }

            // If not found, try Question model
            $question = \App\Models\Question::find($id);
            if ($question) {
                $question->update($validated);
                \Log::info("QuestionController: Successfully updated question ID {$id}");
                return response()->json($question);
            }

            \Log::warning("QuestionController: Question ID {$id} not found for update");
            return response()->json(['error' => 'Question not found'], 404);
        } catch (\Exception $e) {
            \Log::error('QuestionController update error: '.$e->getMessage());
            return response()->json(['error' => 'Update failed'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            \Log::info("QuestionController destroy called for ID: {$id}");
            
            // Handle prefixed IDs from old questions (e.g., "old_24")
            if (strpos($id, 'old_') === 0) {
                $realId = str_replace('old_', '', $id);
                \Log::info("Deleting legacy question with real ID: {$realId}");
                $deleted = \DB::table('questions')->where('id', $realId)->delete();
                if ($deleted) {
                    \Log::info("Deleted from questions table (legacy)");
                    return response()->json(['message' => 'Question deleted successfully']);
                }
                return response()->json(['message' => 'Question not found'], 404);
            }
            
            // Try chapter_questions table first
            $question = ChapterQuestion::find($id);
            if ($question) {
                \Log::info("Found question ID {$id} in chapter_questions table, deleting...");
                $question->delete();
                \Log::info("Deleted from chapter_questions table ONLY");
                return response()->json(['message' => 'Question deleted successfully']);
            }
            
            // Try questions table (for legacy questions without old_ prefix)
            $question = Question::find($id);
            if ($question) {
                \Log::info("Found question ID {$id} in questions table, deleting...");
                $question->delete();
                \Log::info("Deleted from questions table ONLY");
                return response()->json(['message' => 'Question deleted successfully']);
            }
            
            // Try final exam questions table
            $deleted = \DB::table('final_exam_questions')->where('id', $id)->delete();
            if ($deleted) {
                \Log::info("Deleted from final_exam_questions table");
                return response()->json(['message' => 'Final exam question deleted successfully']);
            }
            
            \Log::warning("Question ID {$id} not found in any table");
            return response()->json(['message' => 'Question not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Question delete error: ' . $e->getMessage());
            return response()->json(['message' => 'Delete failed: '.$e->getMessage()], 500);
        }
    }

    public function import(Request $request, $chapterId)
    {
        try {
            $file = $request->file('file');
            if (!$file) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            // Extract text from DOCX by reading the XML directly
            $zip = new \ZipArchive();
            $text = '';
            
            if ($zip->open($file->getPathname()) === true) {
                $xml = $zip->getFromName('word/document.xml');
                if ($xml) {
                    // Remove XML tags to get plain text
                    $text = strip_tags($xml);
                    // Clean up extra whitespace
                    $text = preg_replace('/\s+/', ' ', $text);
                    // Add line breaks back
                    $text = str_replace('>', ">\n", $xml);
                    $text = strip_tags($text);
                }
                $zip->close();
            }

            \Log::info('Extracted text: ' . substr($text, 0, 500));

            // Parse questions with *** format
            $questions = $this->parseQuestionsWithStars($text);
            
            \Log::info('Parsed questions: ' . count($questions));

            // DELETE EXISTING QUESTIONS FOR THIS CHAPTER BEFORE IMPORTING
            // This prevents duplicates when re-importing questions
            $deletedCount = ChapterQuestion::where('chapter_id', $chapterId)->delete();
            \Log::info("Deleted {$deletedCount} existing questions for chapter {$chapterId} before import");

            $imported = 0;
            foreach ($questions as $index => $questionData) {
                ChapterQuestion::create([
                    'chapter_id' => $chapterId,
                    'question_text' => $questionData['question'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'order_index' => $index + 1,
                ]);
                $imported++;
            }

            return response()->json([
                'success' => true,
                'count' => $imported,
                'deleted' => $deletedCount,
                'message' => "Deleted {$deletedCount} old questions and imported {$imported} new questions",
                'debug' => [
                    'text_length' => strlen($text),
                    'text_preview' => substr($text, 0, 200),
                    'questions_parsed' => count($questions)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Question import error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function parseQuestionsWithStars($text)
    {
        $questions = [];
        $lines = explode("\n", $text);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a question (starts with number)
            if (preg_match('/^(\d+)\.\s+(.+)$/', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer
                    ];
                }
                
                // Start new question
                $currentQuestion = $matches[2];
                $currentOptions = [];
                $correctAnswer = null;
            }
            // Check if it's an option (starts with letter)
            elseif (preg_match('/^([A-E])\.\s+(.+?)(\s+\*\*\*)?$/', $line, $matches)) {
                $letter = $matches[1];
                $optionText = trim($matches[2]);
                $isCorrect = isset($matches[3]) && !empty($matches[3]);
                
                $currentOptions[$letter] = $optionText;
                
                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
        }

        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer
            ];
        }

        return $questions;
    }
}

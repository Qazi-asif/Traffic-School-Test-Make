<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleQuizImportController extends Controller
{
    /**
     * Show the simple quiz import interface
     */
    public function index()
    {
        $chapters = DB::table('chapters')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->select('chapters.*', 'courses.title as course_title')
            ->orderBy('courses.title')
            ->orderBy('chapters.order_index')
            ->get();

        return view('admin.simple-quiz-import.index', compact('chapters'));
    }

    /**
     * Handle text import (the most reliable method)
     */
    public function importText(Request $request)
    {
        $request->validate([
            'text_content' => 'required|string',
            'chapter_id' => 'required|integer|exists:chapters,id',
            'replace_existing' => 'boolean'
        ]);

        try {
            $textContent = $request->input('text_content');
            $chapterId = $request->input('chapter_id');
            $replaceExisting = $request->boolean('replace_existing');

            // Simple parsing - just look for basic patterns
            $questions = $this->parseSimpleQuestions($textContent);

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No questions found. Please use this format:\n\n1. Question text?\nA. Option A\nB. Option B **\nC. Option C\nD. Option D'
                ], 400);
            }

            $result = $this->saveSimpleQuestions($questions, $chapterId, $replaceExisting);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} questions",
                'imported' => $result['imported'],
                'deleted' => $result['deleted'],
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Simple quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle file upload (convert to text first)
     */
    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'chapter_id' => 'required|integer|exists:chapters,id',
            'replace_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Extract text from file
            $content = '';
            switch ($extension) {
                case 'txt':
                    $content = file_get_contents($file->getPathname());
                    break;
                case 'docx':
                case 'doc':
                    $content = $this->extractFromWordSimple($file);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Only TXT and DOCX files are supported'
                    ], 400);
            }

            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No content could be extracted from the file'
                ], 400);
            }

            // Parse questions
            $questions = $this->parseSimpleQuestions($content);

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No questions found in file. Please check the format.',
                    'content_preview' => substr($content, 0, 500)
                ], 400);
            }

            $chapterId = $request->input('chapter_id');
            $replaceExisting = $request->boolean('replace_existing');
            
            $result = $this->saveSimpleQuestions($questions, $chapterId, $replaceExisting);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} questions from {$file->getClientOriginalName()}",
                'imported' => $result['imported'],
                'deleted' => $result['deleted'],
                'questions' => array_slice($questions, 0, 3) // Show first 3 as preview
            ]);

        } catch (\Exception $e) {
            Log::error('File import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'File import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enhanced question parsing - handles continuous text and multiple formats
     */
    private function parseSimpleQuestions($content)
    {
        $questions = [];
        
        // Log the original content for debugging
        Log::info('Original content length: ' . strlen($content));
        Log::info('Content preview: ' . substr($content, 0, 500));
        
        // Enhanced preprocessing to handle continuous text better
        $content = $this->enhancedPreprocessContent($content);
        
        // Split by question markers (more aggressive approach)
        $questionBlocks = $this->splitIntoQuestionBlocks($content);
        
        Log::info('Found question blocks: ' . count($questionBlocks));
        
        foreach ($questionBlocks as $index => $block) {
            $parsedQuestion = $this->parseQuestionBlock($block, $index + 1);
            if ($parsedQuestion) {
                $questions[] = $parsedQuestion;
                Log::info("Parsed question " . ($index + 1) . ": " . substr($parsedQuestion['question'], 0, 50));
            }
        }
        
        Log::info('Total questions parsed: ' . count($questions));
        return $questions;
    }

    /**
     * Enhanced preprocessing to handle continuous text better
     */
    private function enhancedPreprocessContent($content)
    {
        // Remove extra whitespace but preserve structure
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Look for question separators like *** or multiple periods
        $content = preg_replace('/\s*\*{3,}\s*/', "\n\n", $content);
        
        // Add line breaks before question numbers - more aggressive pattern
        $content = preg_replace('/(\d+)[\.\)]\s*([A-Z])/i', "\n\n$1. $2", $content);
        
        // Add line breaks before options - but be more careful
        $content = preg_replace('/([A-E])[\.\)]\s*([A-Z])/i', "\n$1. $2", $content);
        
        // Clean up multiple line breaks
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }

    /**
     * Split content into question blocks
     */
    private function splitIntoQuestionBlocks($content)
    {
        // Try multiple splitting strategies
        
        // Strategy 1: Split by question numbers
        $blocks = preg_split('/(?=\d+[\.\)]\s*[A-Z])/i', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($blocks) < 2) {
            // Strategy 2: Split by *** markers
            $blocks = explode('***', $content);
        }
        
        if (count($blocks) < 2) {
            // Strategy 3: Try to find patterns manually
            $blocks = $this->manualQuestionSplit($content);
        }
        
        // Clean up blocks
        $cleanBlocks = [];
        foreach ($blocks as $block) {
            $block = trim($block);
            if (!empty($block) && strlen($block) > 10) { // Minimum question length
                $cleanBlocks[] = $block;
            }
        }
        
        return $cleanBlocks;
    }

    /**
     * Manual question splitting as fallback
     */
    private function manualQuestionSplit($content)
    {
        $blocks = [];
        $lines = explode("\n", $content);
        $currentBlock = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // If this looks like a new question start
            if (preg_match('/^\d+[\.\)]\s*/', $line) && !empty($currentBlock)) {
                $blocks[] = $currentBlock;
                $currentBlock = $line;
            } else {
                $currentBlock .= "\n" . $line;
            }
        }
        
        if (!empty($currentBlock)) {
            $blocks[] = $currentBlock;
        }
        
        return $blocks;
    }

    /**
     * Parse individual question block
     */
    private function parseQuestionBlock($block, $questionNumber)
    {
        $lines = explode("\n", $block);
        $question = '';
        $options = [];
        $correctAnswer = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Extract question text (remove number prefix)
            if (preg_match('/^\d+[\.\)]\s*(.+)/', $line, $matches)) {
                $question = trim($matches[1]);
                continue;
            }
            
            // If no question yet, this might be continuation of question
            if (empty($question) && !preg_match('/^[A-E][\.\)]/i', $line)) {
                $question .= ' ' . $line;
                continue;
            }
            
            // Parse options with various correct answer markers
            if (preg_match('/^([A-E])[\.\)]\s*(.+?)(\s*\*{2,}|\s*\(correct\)|\s*\[correct\])?$/i', $line, $matches)) {
                $letter = strtoupper($matches[1]);
                $text = trim($matches[2]);
                $isCorrect = !empty($matches[3]);
                
                // Clean up text
                $text = rtrim($text, '.');
                $text = trim($text);
                
                $options[$letter] = $text;
                
                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
            // Handle options that might have correct marker separated
            elseif (preg_match('/^([A-E])[\.\)]\s*(.+?)\s+(\*{2,})$/i', $line, $matches)) {
                $letter = strtoupper($matches[1]);
                $text = trim($matches[2]);
                $text = rtrim($text, '.');
                
                $options[$letter] = $text;
                $correctAnswer = $letter;
            }
        }
        
        // Validate we have a complete question
        if (empty($question) || empty($options)) {
            Log::warning("Incomplete question block for question {$questionNumber}", [
                'question' => $question,
                'options_count' => count($options),
                'block_preview' => substr($block, 0, 200)
            ]);
            return null;
        }
        
        return [
            'question' => $question,
            'options' => $options,
            'correct_answer' => $this->validateCorrectAnswer($correctAnswer)
        ];
    }

    /**
     * Validate and ensure correct answer is a single character
     */
    private function validateCorrectAnswer($correctAnswer)
    {
        if (empty($correctAnswer)) {
            return 'A'; // Default to A if no correct answer found
        }
        
        // Ensure it's a single uppercase letter
        $correctAnswer = strtoupper(trim($correctAnswer));
        
        // If it's longer than 1 character, take only the first character
        if (strlen($correctAnswer) > 1) {
            $correctAnswer = substr($correctAnswer, 0, 1);
        }
        
        // Ensure it's a valid option (A-E)
        if (!in_array($correctAnswer, ['A', 'B', 'C', 'D', 'E'])) {
            return 'A'; // Default to A if invalid
        }
        
        return $correctAnswer;
    }

    /**
     * Simple Word extraction
     */
    private function extractFromWordSimple($file)
    {
        try {
            // Try ZIP method first (more reliable)
            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) === true) {
                $xml = $zip->getFromName('word/document.xml');
                if ($xml) {
                    // Simple XML parsing
                    $content = strip_tags($xml);
                    $content = html_entity_decode($content);
                    $content = preg_replace('/\s+/', ' ', $content);
                    $zip->close();
                    return $content;
                }
                $zip->close();
            }
            
            return '';
        } catch (\Exception $e) {
            Log::error('Word extraction failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Save questions to database - enhanced with better column handling
     */
    private function saveSimpleQuestions($questions, $chapterId, $replaceExisting = false)
    {
        $deleted = 0;
        $imported = 0;

        // Check if we need to add missing columns BEFORE starting transaction
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
        Log::info('Available database columns: ' . implode(', ', $columns));
        $this->ensureRequiredColumns($columns);
        
        // Refresh column list after potential additions
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');

        try {
            DB::beginTransaction();
            
            // Delete existing if requested
            if ($replaceExisting) {
                $deleted = DB::table('chapter_questions')->where('chapter_id', $chapterId)->delete();
            }
            
            // Insert questions
            foreach ($questions as $index => $questionData) {
                if (empty($questionData['question']) || empty($questionData['options'])) {
                    Log::warning("Skipping incomplete question at index " . $index);
                    continue;
                }

                // Build insert data with only guaranteed columns
                $insertData = [
                    'chapter_id' => $chapterId,
                    'question_text' => $questionData['question'],
                    'correct_answer' => $this->validateCorrectAnswer($questionData['correct_answer']),
                    'order_index' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add optional columns only if they exist
                if (in_array('question_type', $columns)) {
                    $insertData['question_type'] = 'multiple_choice';
                }
                
                if (in_array('options', $columns)) {
                    $insertData['options'] = json_encode($questionData['options']);
                }
                
                if (in_array('explanation', $columns)) {
                    $insertData['explanation'] = $questionData['explanation'] ?? null;
                }
                
                // CRITICAL: Always set points to 1 if column exists
                if (in_array('points', $columns)) {
                    $insertData['points'] = 1;
                }
                
                if (in_array('quiz_set', $columns)) {
                    $insertData['quiz_set'] = 1;
                }
                
                if (in_array('is_active', $columns)) {
                    $insertData['is_active'] = true;
                }

                try {
                    DB::table('chapter_questions')->insert($insertData);
                    $imported++;
                    Log::info("Successfully inserted question " . ($index + 1));
                } catch (\Exception $e) {
                    // Log the specific question that failed
                    Log::error('Failed to insert question', [
                        'question_index' => $index + 1,
                        'question' => substr($questionData['question'], 0, 50) . '...',
                        'correct_answer' => $questionData['correct_answer'],
                        'validated_answer' => $this->validateCorrectAnswer($questionData['correct_answer']),
                        'error' => $e->getMessage(),
                        'insert_data' => $insertData
                    ]);
                    
                    // Try with minimal data as fallback
                    try {
                        $minimalData = [
                            'chapter_id' => $chapterId,
                            'question_text' => substr($questionData['question'], 0, 500), // Ensure it fits
                            'correct_answer' => $this->validateCorrectAnswer($questionData['correct_answer']),
                            'order_index' => $index + 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        DB::table('chapter_questions')->insert($minimalData);
                        $imported++;
                        Log::info("Fallback insert successful for question " . ($index + 1));
                    } catch (\Exception $e2) {
                        Log::error('Fallback insert also failed', [
                            'question_index' => $index + 1,
                            'question' => substr($questionData['question'], 0, 50) . '...',
                            'error' => $e2->getMessage()
                        ]);
                        // Skip this question and continue with others
                    }
                }
            }
            
            DB::commit();
            Log::info("Transaction committed. Imported: " . $imported . ", Deleted: " . $deleted);
            
        } catch (\Exception $e) {
            // Only rollback if transaction is active
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }
            Log::error('Transaction failed: ' . $e->getMessage());
            throw $e;
        }

        return [
            'imported' => $imported,
            'deleted' => $deleted
        ];
    }

    /**
     * Ensure required columns exist in the database
     */
    private function ensureRequiredColumns($existingColumns)
    {
        $requiredColumns = [
            'points' => 'INTEGER DEFAULT 1',
            'question_type' => 'VARCHAR(50) DEFAULT "multiple_choice"',
            'options' => 'JSON',
            'is_active' => 'BOOLEAN DEFAULT 1'
        ];

        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $existingColumns)) {
                try {
                    Log::info("Adding missing column: " . $column);
                    // Use separate connection for DDL to avoid transaction issues
                    DB::unprepared("ALTER TABLE chapter_questions ADD COLUMN {$column} {$definition}");
                } catch (\Exception $e) {
                    Log::warning("Could not add column " . $column . ": " . $e->getMessage());
                }
            }
        }
    }
}
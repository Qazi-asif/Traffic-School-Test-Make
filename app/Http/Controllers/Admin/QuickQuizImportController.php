<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class QuickQuizImportController extends Controller
{
    /**
     * Quick import quiz from text paste or file upload for a specific chapter
     */
    public function quickImport(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|integer|exists:chapters,id',
            'import_type' => 'required|in:text,file',
            'text_content' => 'required_if:import_type,text|string',
            'file' => 'required_if:import_type,file|file|max:51200',
            'replace_existing' => 'boolean'
        ]);

        try {
            $chapterId = $request->input('chapter_id');
            $importType = $request->input('import_type');
            $replaceExisting = $request->boolean('replace_existing');

            if ($importType === 'text') {
                $content = $request->input('text_content');
                $questions = $this->parseTextQuestions($content);
            } else {
                $file = $request->file('file');
                $content = $this->extractContentFromFile($file);
                $questions = $this->parseTextQuestions($content);
            }

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid questions found. Please check the format.'
                ], 400);
            }

            $result = $this->saveQuestions($questions, $chapterId, $replaceExisting);

            // Get chapter info for response
            $chapter = DB::table('chapters')
                ->join('courses', 'chapters.course_id', '=', 'courses.id')
                ->where('chapters.id', $chapterId)
                ->select('chapters.title as chapter_title', 'courses.title as course_title')
                ->first();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} questions to {$chapter->course_title} - {$chapter->chapter_title}",
                'imported' => $result['imported'],
                'deleted' => $result['deleted'],
                'questions' => array_slice($questions, 0, 3) // Show first 3 questions as preview
            ]);

        } catch (\Exception $e) {
            Log::error('Quick quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-detect and import quiz from chapter content
     */
    public function autoImportFromChapter(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|integer|exists:chapters,id',
            'chapter_content' => 'required|string'
        ]);

        try {
            $chapterId = $request->input('chapter_id');
            $content = $request->input('chapter_content');

            // Extract quiz questions from chapter content
            $questions = $this->extractQuizFromContent($content);

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No quiz questions detected in chapter content'
                ]);
            }

            $result = $this->saveQuestions($questions, $chapterId, false);

            return response()->json([
                'success' => true,
                'message' => "Auto-detected and imported {$result['imported']} quiz questions",
                'imported' => $result['imported'],
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Auto quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract content from uploaded file
     */
    private function extractContentFromFile($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        switch ($extension) {
            case 'docx':
            case 'doc':
                return $this->extractFromWord($file);
            case 'pdf':
                return $this->extractFromPdf($file);
            case 'txt':
                return file_get_contents($file->getPathname());
            default:
                throw new \Exception("Unsupported file format: {$extension}");
        }
    }

    /**
     * Extract text from Word document
     */
    private function extractFromWord($file)
    {
        try {
            $phpWord = IOFactory::load($file->getPathname());
            $content = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $content .= $childElement->getText() . "\n";
                            }
                        }
                    }
                }
            }

            return $content;
        } catch (\Exception $e) {
            // Fallback to ZIP method
            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) === true) {
                $xml = $zip->getFromName('word/document.xml');
                if ($xml) {
                    $content = strip_tags($xml);
                    $content = preg_replace('/\s+/', ' ', $content);
                    $zip->close();
                    return $content;
                }
                $zip->close();
            }
            throw new \Exception('Could not extract content from Word document');
        }
    }

    /**
     * Extract text from PDF
     */
    private function extractFromPdf($file)
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getPathname());
            return $pdf->getText();
        } catch (\Exception $e) {
            throw new \Exception('Could not extract text from PDF: ' . $e->getMessage());
        }
    }

    /**
     * Parse questions from text content
     */
    private function parseTextQuestions($content)
    {
        $questions = [];
        $lines = explode("\n", $content);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;
        $explanation = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a question (starts with number)
            if (preg_match('/^(\d+)[\.\)]\s*(.+)$/i', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer,
                        'explanation' => $explanation
                    ];
                }

                // Start new question
                $currentQuestion = trim($matches[2]);
                $currentOptions = [];
                $correctAnswer = null;
                $explanation = null;
            }
            // Check if it's an option (starts with letter)
            elseif (preg_match('/^([A-E])[\.\)]\s*(.+?)(\s*\*{2,}|\s*\(correct\)|\s*\[correct\])?$/i', $line, $matches)) {
                $letter = strtoupper($matches[1]);
                $optionText = trim($matches[2]);
                $isCorrect = !empty($matches[3]);

                $currentOptions[$letter] = $optionText;

                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
            // Check for explanation
            elseif (preg_match('/^(explanation|answer|note):\s*(.+)$/i', $line, $matches)) {
                $explanation = trim($matches[2]);
            }
        }

        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer,
                'explanation' => $explanation
            ];
        }

        return $questions;
    }

    /**
     * Extract quiz questions from chapter content (auto-detection)
     */
    private function extractQuizFromContent($content)
    {
        $questions = [];
        
        // Look for quiz sections in the content
        $patterns = [
            '/quiz\s*questions?:?\s*(.*?)(?=\n\n|\n[A-Z]|\z)/is',
            '/questions?:?\s*(.*?)(?=\n\n|\n[A-Z]|\z)/is',
            '/review\s*questions?:?\s*(.*?)(?=\n\n|\n[A-Z]|\z)/is'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $quizContent = $matches[1];
                $questions = array_merge($questions, $this->parseTextQuestions($quizContent));
            }
        }

        // Also try to parse the entire content for questions
        if (empty($questions)) {
            $questions = $this->parseTextQuestions($content);
        }

        return $questions;
    }

    /**
     * Save questions to database
     */
    private function saveQuestions($questions, $chapterId, $replaceExisting = false)
    {
        $deleted = 0;
        $imported = 0;

        DB::transaction(function () use ($questions, $chapterId, $replaceExisting, &$deleted, &$imported) {
            // Delete existing questions if requested
            if ($replaceExisting) {
                $deleted = DB::table('chapter_questions')->where('chapter_id', $chapterId)->delete();
            }

            // Insert new questions using only the columns that definitely exist
            foreach ($questions as $index => $questionData) {
                if (empty($questionData['question']) || empty($questionData['options'])) {
                    continue;
                }

                // Build insert data with only guaranteed columns
                $insertData = [
                    'chapter_id' => $chapterId,
                    'question_text' => $questionData['question'],
                    'correct_answer' => $questionData['correct_answer'] ?? 'A',
                    'points' => 1,
                    'order_index' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add optional columns only if they exist in the table
                try {
                    $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
                    
                    if (in_array('question_type', $columns)) {
                        $insertData['question_type'] = 'multiple_choice';
                    }
                    
                    if (in_array('options', $columns)) {
                        $insertData['options'] = json_encode($questionData['options']);
                    }
                    
                    if (in_array('explanation', $columns)) {
                        $insertData['explanation'] = $questionData['explanation'];
                    }
                    
                    if (in_array('quiz_set', $columns)) {
                        $insertData['quiz_set'] = 1;
                    }
                    
                    if (in_array('is_active', $columns)) {
                        $insertData['is_active'] = true;
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('Could not check table columns: ' . $e->getMessage());
                }

                try {
                    DB::table('chapter_questions')->insert($insertData);
                    $imported++;
                } catch (\Exception $e) {
                    Log::error('Failed to insert question: ' . $e->getMessage());
                    Log::error('Insert data: ' . json_encode($insertData));
                    
                    // Try with minimal data as fallback
                    try {
                        DB::table('chapter_questions')->insert([
                            'chapter_id' => $chapterId,
                            'question_text' => $questionData['question'],
                            'correct_answer' => $questionData['correct_answer'] ?? 'A',
                            'points' => 1,
                            'order_index' => $index + 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $imported++;
                    } catch (\Exception $e2) {
                        Log::error('Fallback insert also failed: ' . $e2->getMessage());
                    }
                }
            }
        });

        return [
            'imported' => $imported,
            'deleted' => $deleted
        ];
    }
}
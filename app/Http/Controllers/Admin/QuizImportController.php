<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class QuizImportController extends Controller
{
    /**
     * Show the quiz import interface
     */
    public function index()
    {
        $chapters = DB::table('chapters')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->select('chapters.*', 'courses.title as course_title')
            ->orderBy('courses.title')
            ->orderBy('chapters.order_index')
            ->get();

        return view('admin.quiz-import.index', compact('chapters'));
    }

    /**
     * Handle single file quiz import
     */
    public function importSingle(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'chapter_id' => 'required|integer|exists:chapters,id',
            'replace_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('file');
            $chapterId = $request->input('chapter_id');
            $replaceExisting = $request->boolean('replace_existing');

            $result = $this->processQuizFile($file, $chapterId, $replaceExisting);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} questions",
                'imported' => $result['imported'],
                'deleted' => $result['deleted'],
                'questions' => $result['questions']
            ]);

        } catch (\Exception $e) {
            Log::error('Quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk file quiz import
     */
    public function importBulk(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1|max:20',
            'files.*' => 'file|max:51200',
            'chapter_mapping' => 'required|array',
            'replace_existing' => 'boolean'
        ]);

        try {
            $files = $request->file('files');
            $chapterMapping = $request->input('chapter_mapping');
            $replaceExisting = $request->boolean('replace_existing');

            $results = [];
            $totalImported = 0;
            $totalDeleted = 0;

            foreach ($files as $index => $file) {
                if (!isset($chapterMapping[$index])) {
                    continue;
                }

                $chapterId = $chapterMapping[$index];
                $result = $this->processQuizFile($file, $chapterId, $replaceExisting);

                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'chapter_id' => $chapterId,
                    'imported' => $result['imported'],
                    'deleted' => $result['deleted']
                ];

                $totalImported += $result['imported'];
                $totalDeleted += $result['deleted'];
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk import completed: {$totalImported} questions imported from " . count($files) . " files",
                'total_imported' => $totalImported,
                'total_deleted' => $totalDeleted,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle text paste import
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

            $questions = $this->parseTextQuestions($textContent);

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid questions found in the text. Please check the format.'
                ], 400);
            }

            $result = $this->saveQuestions($questions, $chapterId, $replaceExisting);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} questions from text",
                'imported' => $result['imported'],
                'deleted' => $result['deleted'],
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Text quiz import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a single quiz file
     */
    private function processQuizFile($file, $chapterId, $replaceExisting = false)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        
        Log::info('Processing quiz file', [
            'filename' => $filename,
            'extension' => $extension,
            'size' => $fileSize,
            'chapter_id' => $chapterId
        ]);
        
        $content = '';

        switch ($extension) {
            case 'docx':
            case 'doc':
                $content = $this->extractFromWord($file);
                break;
            case 'pdf':
                $content = $this->extractFromPdf($file);
                break;
            case 'txt':
                $content = file_get_contents($file->getPathname());
                break;
            case 'csv':
                return $this->processCsvFile($file, $chapterId, $replaceExisting);
            default:
                throw new \Exception("Unsupported file format: {$extension}");
        }

        Log::info('File content extracted', [
            'filename' => $filename,
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 200) . '...'
        ]);

        if (empty($content)) {
            throw new \Exception("No content could be extracted from file: {$filename}");
        }

        $questions = $this->parseTextQuestions($content);

        if (empty($questions)) {
            Log::warning('No questions found in file', [
                'filename' => $filename,
                'content_length' => strlen($content),
                'content_sample' => substr($content, 0, 500)
            ]);
            throw new \Exception("No valid questions found in file: {$filename}. Please check the format.");
        }

        Log::info('Questions parsed successfully', [
            'filename' => $filename,
            'questions_count' => count($questions)
        ]);

        return $this->saveQuestions($questions, $chapterId, $replaceExisting);
    }

    /**
     * Extract text from Word document
     */
    private function extractFromWord($file)
    {
        $filename = $file->getClientOriginalName();
        Log::info('Extracting text from Word document', ['filename' => $filename]);
        
        try {
            $phpWord = IOFactory::load($file->getPathname());
            $content = '';

            foreach ($phpWord->getSections() as $sectionIndex => $section) {
                Log::debug("Processing section {$sectionIndex}");
                
                foreach ($section->getElements() as $elementIndex => $element) {
                    $elementClass = get_class($element);
                    Log::debug("Processing element {$elementIndex}: {$elementClass}");
                    
                    if (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        $content .= $text . "\n";
                        Log::debug("Extracted text: " . substr($text, 0, 100) . "...");
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $childIndex => $childElement) {
                            $childClass = get_class($childElement);
                            Log::debug("Processing child element {$childIndex}: {$childClass}");
                            
                            if (method_exists($childElement, 'getText')) {
                                $text = $childElement->getText();
                                $content .= $text . "\n";
                                Log::debug("Extracted child text: " . substr($text, 0, 100) . "...");
                            }
                        }
                    }
                }
            }

            Log::info('PHPWord extraction completed', [
                'filename' => $filename,
                'content_length' => strlen($content)
            ]);

            return $content;
        } catch (\Exception $e) {
            Log::warning('PHPWord extraction failed, trying ZIP method', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return $this->extractFromWordZip($file);
        }
    }

    /**
     * Fallback method to extract from Word using ZIP
     */
    private function extractFromWordZip($file)
    {
        $filename = $file->getClientOriginalName();
        Log::info('Attempting ZIP extraction for Word document', ['filename' => $filename]);
        
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $content = strip_tags($xml);
                $content = preg_replace('/\s+/', ' ', $content);
                $zip->close();
                
                Log::info('ZIP extraction successful', [
                    'filename' => $filename,
                    'content_length' => strlen($content)
                ]);
                
                return $content;
            }
            $zip->close();
        }
        
        Log::error('ZIP extraction failed', ['filename' => $filename]);
        throw new \Exception('Could not extract content from Word document: ' . $filename);
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
     * Process CSV file
     */
    private function processCsvFile($file, $chapterId, $replaceExisting = false)
    {
        $questions = [];
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \Exception('Could not open CSV file');
        }

        // Skip header row
        $header = fgetcsv($handle);
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) < 6) {
                Log::warning("CSV row {$rowNumber} has insufficient columns, skipping");
                continue;
            }

            $questions[] = [
                'question' => trim($row[0]),
                'options' => [
                    'A' => trim($row[1]),
                    'B' => trim($row[2]),
                    'C' => trim($row[3]),
                    'D' => trim($row[4])
                ],
                'correct_answer' => strtoupper(trim($row[5])),
                'explanation' => isset($row[6]) ? trim($row[6]) : null
            ];
        }

        fclose($handle);

        if (empty($questions)) {
            throw new \Exception('No valid questions found in CSV file');
        }

        return $this->saveQuestions($questions, $chapterId, $replaceExisting);
    }

    /**
     * Parse questions from text content
     */
    private function parseTextQuestions($content)
    {
        Log::info('Starting question parsing', ['content_length' => strlen($content)]);
        
        $questions = [];
        $lines = explode("\n", $content);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;
        $explanation = null;

        Log::info('Processing lines', ['total_lines' => count($lines)]);

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            Log::debug("Processing line {$lineNum}: {$line}");

            // Check if it's a question (starts with number)
            if (preg_match('/^(\d+)[\.\)]\s*(.+)$/i', $line, $matches)) {
                Log::info("Found question: {$matches[2]}");
                
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer,
                        'explanation' => $explanation
                    ];
                    Log::info("Saved previous question", ['total_questions' => count($questions)]);
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

                Log::info("Found option: {$letter} = {$optionText}" . ($isCorrect ? " (CORRECT)" : ""));

                $currentOptions[$letter] = $optionText;

                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
            // Check for explanation
            elseif (preg_match('/^(explanation|answer|note):\s*(.+)$/i', $line, $matches)) {
                $explanation = trim($matches[2]);
                Log::info("Found explanation: {$explanation}");
            } else {
                Log::debug("Unrecognized line format: {$line}");
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
            Log::info("Saved final question", ['total_questions' => count($questions)]);
        }

        Log::info('Question parsing completed', [
            'total_questions' => count($questions),
            'questions_preview' => array_map(function($q) {
                return [
                    'question' => substr($q['question'], 0, 50) . '...',
                    'options_count' => count($q['options']),
                    'correct_answer' => $q['correct_answer']
                ];
            }, array_slice($questions, 0, 3))
        ]);

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
            'deleted' => $deleted,
            'questions' => $questions
        ];
    }

    /**
     * Get chapters for a specific course
     */
    public function getChapters($courseId)
    {
        $chapters = DB::table('chapters')
            ->where('course_id', $courseId)
            ->orderBy('order_index')
            ->get(['id', 'title', 'order_index']);

        return response()->json($chapters);
    }

    /**
     * Preview questions from uploaded file
     */
    public function previewFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200'
        ]);

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $content = '';

            switch ($extension) {
                case 'docx':
                case 'doc':
                    $content = $this->extractFromWord($file);
                    break;
                case 'pdf':
                    $content = $this->extractFromPdf($file);
                    break;
                case 'txt':
                    $content = file_get_contents($file->getPathname());
                    break;
                case 'csv':
                    // Handle CSV preview differently
                    return $this->previewCsv($file);
                default:
                    throw new \Exception("Unsupported file format: {$extension}");
            }

            $questions = $this->parseTextQuestions($content);

            return response()->json([
                'success' => true,
                'questions' => array_slice($questions, 0, 5), // Preview first 5 questions
                'total_questions' => count($questions),
                'content_preview' => substr($content, 0, 500)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview CSV file
     */
    private function previewCsv($file)
    {
        $questions = [];
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \Exception('Could not open CSV file');
        }

        // Read header
        $header = fgetcsv($handle);

        // Read first 5 rows
        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
            if (count($row) >= 6) {
                $questions[] = [
                    'question' => trim($row[0]),
                    'options' => [
                        'A' => trim($row[1]),
                        'B' => trim($row[2]),
                        'C' => trim($row[3]),
                        'D' => trim($row[4])
                    ],
                    'correct_answer' => strtoupper(trim($row[5])),
                    'explanation' => isset($row[6]) ? trim($row[6]) : null
                ];
            }
            $rowCount++;
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'total_questions' => $rowCount,
            'header' => $header
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\FloridaCourse;
use App\Models\MissouriCourse;
use App\Models\TexasCourse;
use App\Models\DelawareCourse;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use ZipArchive;
use Exception;

class BulkUploadController extends Controller
{
    /**
     * Show bulk upload interface
     */
    public function index()
    {
        $courses = Course::all();
        $stateCourses = [
            'florida' => FloridaCourse::all(),
            'missouri' => MissouriCourse::all(),
            'texas' => TexasCourse::all(),
            'delaware' => DelawareCourse::all()
        ];
        
        return view('admin.bulk-upload.index', compact('courses', 'stateCourses'));
    }

    /**
     * Handle bulk course content upload
     */
    public function uploadCourseContent(Request $request)
    {
        // Remove all file size and content limits
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0);
        ini_set('upload_max_filesize', '2G');
        ini_set('post_max_size', '2G');
        
        $request->validate([
            'course_id' => 'required|integer',
            'course_type' => 'required|string|in:courses,florida_courses,missouri_courses,texas_courses,delaware_courses',
            'upload_type' => 'required|string|in:single_file,multiple_files,zip_archive',
            'files.*' => 'required|file',
            'auto_create_chapters' => 'boolean',
            'chapter_duration' => 'nullable|integer|min:1',
            'extract_images' => 'boolean',
            'preserve_formatting' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $results = [
                'success' => true,
                'chapters_created' => 0,
                'questions_created' => 0,
                'images_extracted' => 0,
                'files_processed' => 0,
                'errors' => []
            ];

            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                try {
                    $result = $this->processUploadedFile(
                        $file,
                        $request->course_id,
                        $request->course_type,
                        $request->all()
                    );
                    
                    $results['chapters_created'] += $result['chapters_created'];
                    $results['questions_created'] += $result['questions_created'];
                    $results['images_extracted'] += $result['images_extracted'];
                    $results['files_processed']++;
                    
                } catch (Exception $e) {
                    $results['errors'][] = "File {$file->getClientOriginalName()}: " . $e->getMessage();
                    Log::error('Bulk upload file error', [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            
            return response()->json($results);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk upload error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process individual uploaded file
     */
    private function processUploadedFile($file, $courseId, $courseType, $options)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $results = [
            'chapters_created' => 0,
            'questions_created' => 0,
            'images_extracted' => 0
        ];

        switch ($extension) {
            case 'docx':
            case 'doc':
                return $this->processWordDocument($file, $courseId, $courseType, $options);
                
            case 'zip':
                return $this->processZipArchive($file, $courseId, $courseType, $options);
                
            case 'txt':
                return $this->processTextFile($file, $courseId, $courseType, $options);
                
            case 'html':
            case 'htm':
                return $this->processHtmlFile($file, $courseId, $courseType, $options);
                
            case 'pdf':
                return $this->processPdfFile($file, $courseId, $courseType, $options);
                
            default:
                throw new Exception("Unsupported file type: {$extension}");
        }
    }

    /**
     * Process Word document with unlimited content support
     */
    private function processWordDocument($file, $courseId, $courseType, $options)
    {
        $results = [
            'chapters_created' => 0,
            'questions_created' => 0,
            'images_extracted' => 0
        ];

        // Save uploaded file temporarily
        $tempPath = $file->store('temp', 'local');
        $fullPath = storage_path('app/' . $tempPath);

        try {
            // Load Word document with unlimited memory
            Settings::setOutputEscapingEnabled(true);
            $phpWord = IOFactory::load($fullPath);
            
            $content = '';
            $images = [];
            $chapters = [];
            
            // Extract all content from all sections
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $elementContent = $this->extractElementContent($element, $images, $options);
                    $content .= $elementContent;
                }
            }

            // Auto-create chapters if enabled
            if ($options['auto_create_chapters'] ?? false) {
                $chapters = $this->autoCreateChapters($content, $courseId, $courseType, $options);
                $results['chapters_created'] = count($chapters);
            } else {
                // Create single chapter with all content
                $chapter = $this->createChapter([
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'content' => $content,
                    'course_id' => $courseId,
                    'course_type' => $courseType,
                    'duration' => $options['chapter_duration'] ?? 60,
                    'order_index' => $this->getNextChapterOrder($courseId, $courseType)
                ]);
                $results['chapters_created'] = 1;
            }

            // Extract and save images if enabled
            if ($options['extract_images'] ?? false) {
                $results['images_extracted'] = $this->saveExtractedImages($images, $courseId);
            }

            // Extract quiz questions if found
            $questions = $this->extractQuizQuestions($content);
            if (!empty($questions)) {
                foreach ($chapters as $chapter) {
                    $this->createQuizQuestions($questions, $chapter->id, $courseType);
                }
                $results['questions_created'] = count($questions);
            }

        } finally {
            // Clean up temporary file
            Storage::disk('local')->delete($tempPath);
        }

        return $results;
    }

    /**
     * Extract content from Word document elements
     */
    private function extractElementContent($element, &$images, $options)
    {
        $content = '';
        
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if ($options['preserve_formatting'] ?? true) {
                // Preserve basic formatting
                $content .= $this->preserveTextFormatting($text, $element);
            } else {
                $content .= $text . "\n";
            }
        }
        
        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $subElement) {
                $content .= $this->extractElementContent($subElement, $images, $options);
            }
        }
        
        // Extract images
        if (method_exists($element, 'getImagePath')) {
            $images[] = $element->getImagePath();
        }
        
        return $content;
    }

    /**
     * Process ZIP archive with multiple files
     */
    private function processZipArchive($file, $courseId, $courseType, $options)
    {
        $results = [
            'chapters_created' => 0,
            'questions_created' => 0,
            'images_extracted' => 0
        ];

        $tempPath = $file->store('temp', 'local');
        $fullPath = storage_path('app/' . $tempPath);
        $extractPath = storage_path('app/temp/extracted_' . time());

        try {
            $zip = new ZipArchive;
            if ($zip->open($fullPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();

                // Process all files in the extracted directory
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($extractPath)
                );

                foreach ($iterator as $extractedFile) {
                    if ($extractedFile->isFile()) {
                        $extension = strtolower($extractedFile->getExtension());
                        
                        if (in_array($extension, ['docx', 'doc', 'txt', 'html', 'htm'])) {
                            // Create a temporary uploaded file object
                            $tempFile = new \Illuminate\Http\UploadedFile(
                                $extractedFile->getPathname(),
                                $extractedFile->getFilename(),
                                mime_content_type($extractedFile->getPathname()),
                                null,
                                true
                            );
                            
                            $fileResult = $this->processUploadedFile($tempFile, $courseId, $courseType, $options);
                            $results['chapters_created'] += $fileResult['chapters_created'];
                            $results['questions_created'] += $fileResult['questions_created'];
                            $results['images_extracted'] += $fileResult['images_extracted'];
                        }
                    }
                }
            }
        } finally {
            // Clean up
            Storage::disk('local')->delete($tempPath);
            if (is_dir($extractPath)) {
                $this->deleteDirectory($extractPath);
            }
        }

        return $results;
    }

    /**
     * Process text file
     */
    private function processTextFile($file, $courseId, $courseType, $options)
    {
        $content = file_get_contents($file->getPathname());
        
        if ($options['auto_create_chapters'] ?? false) {
            $chapters = $this->autoCreateChapters($content, $courseId, $courseType, $options);
            return ['chapters_created' => count($chapters), 'questions_created' => 0, 'images_extracted' => 0];
        } else {
            $this->createChapter([
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'content' => $content,
                'course_id' => $courseId,
                'course_type' => $courseType,
                'duration' => $options['chapter_duration'] ?? 60,
                'order_index' => $this->getNextChapterOrder($courseId, $courseType)
            ]);
            return ['chapters_created' => 1, 'questions_created' => 0, 'images_extracted' => 0];
        }
    }

    /**
     * Process HTML file
     */
    private function processHtmlFile($file, $courseId, $courseType, $options)
    {
        $htmlContent = file_get_contents($file->getPathname());
        
        // Convert HTML to clean text while preserving structure
        $content = $this->htmlToText($htmlContent, $options['preserve_formatting'] ?? true);
        
        if ($options['auto_create_chapters'] ?? false) {
            $chapters = $this->autoCreateChapters($content, $courseId, $courseType, $options);
            return ['chapters_created' => count($chapters), 'questions_created' => 0, 'images_extracted' => 0];
        } else {
            $this->createChapter([
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'content' => $content,
                'course_id' => $courseId,
                'course_type' => $courseType,
                'duration' => $options['chapter_duration'] ?? 60,
                'order_index' => $this->getNextChapterOrder($courseId, $courseType)
            ]);
            return ['chapters_created' => 1, 'questions_created' => 0, 'images_extracted' => 0];
        }
    }

    /**
     * Process PDF file (basic text extraction)
     */
    private function processPdfFile($file, $courseId, $courseType, $options)
    {
        // For PDF processing, you might want to use a library like smalot/pdfparser
        // For now, we'll create a placeholder
        $content = "PDF content extraction requires additional setup. File: " . $file->getClientOriginalName();
        
        $this->createChapter([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'content' => $content,
            'course_id' => $courseId,
            'course_type' => $courseType,
            'duration' => $options['chapter_duration'] ?? 60,
            'order_index' => $this->getNextChapterOrder($courseId, $courseType)
        ]);
        
        return ['chapters_created' => 1, 'questions_created' => 0, 'images_extracted' => 0];
    }

    /**
     * Auto-create chapters from content
     */
    private function autoCreateChapters($content, $courseId, $courseType, $options)
    {
        $chapters = [];
        
        // Split content by common chapter indicators
        $chapterSplits = preg_split('/(?:Chapter\s+\d+|CHAPTER\s+\d+|\n\s*\d+\.\s+[A-Z])/i', $content);
        
        if (count($chapterSplits) <= 1) {
            // Try splitting by major headings
            $chapterSplits = preg_split('/\n\s*[A-Z][A-Z\s]{10,}\n/i', $content);
        }
        
        if (count($chapterSplits) <= 1) {
            // Try splitting by double line breaks with significant content
            $chapterSplits = preg_split('/\n\s*\n\s*(?=[A-Z])/i', $content);
        }

        $chapterNumber = 1;
        foreach ($chapterSplits as $chapterContent) {
            $chapterContent = trim($chapterContent);
            if (strlen($chapterContent) > 100) { // Only create chapters with substantial content
                $title = $this->extractChapterTitle($chapterContent) ?: "Chapter {$chapterNumber}";
                
                $chapter = $this->createChapter([
                    'title' => $title,
                    'content' => $chapterContent,
                    'course_id' => $courseId,
                    'course_type' => $courseType,
                    'duration' => $options['chapter_duration'] ?? 60,
                    'order_index' => $this->getNextChapterOrder($courseId, $courseType) + $chapterNumber - 1
                ]);
                
                $chapters[] = $chapter;
                $chapterNumber++;
            }
        }

        return $chapters;
    }

    /**
     * Extract chapter title from content
     */
    private function extractChapterTitle($content)
    {
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 5 && strlen($line) < 100) {
                // Check if it looks like a title
                if (preg_match('/^[A-Z][A-Za-z\s\d\-:]{5,99}$/', $line)) {
                    return $line;
                }
            }
        }
        return null;
    }

    /**
     * Create chapter in appropriate table
     */
    private function createChapter($data)
    {
        $chapterData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'course_id' => $data['course_id'],
            'duration' => $data['duration'],
            'order_index' => $data['order_index'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Add course_table field for proper relationship
        $chapterData['course_table'] = $data['course_type'];

        return Chapter::create($chapterData);
    }

    /**
     * Get next chapter order index
     */
    private function getNextChapterOrder($courseId, $courseType)
    {
        $maxOrder = Chapter::where('course_id', $courseId)
            ->where('course_table', $courseType)
            ->max('order_index');
            
        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Extract quiz questions from content
     */
    private function extractQuizQuestions($content)
    {
        $questions = [];
        
        // Pattern for multiple choice questions
        $pattern = '/(?:Question\s*\d*[:\.]?\s*)?(.+?)\n\s*(?:A[\.\)]\s*(.+?)\n\s*B[\.\)]\s*(.+?)\n\s*C[\.\)]\s*(.+?)\n\s*D[\.\)]\s*(.+?))\n/is';
        
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $questions[] = [
                    'question_text' => trim($match[1]),
                    'option_a' => trim($match[2]),
                    'option_b' => trim($match[3]),
                    'option_c' => trim($match[4]),
                    'option_d' => trim($match[5]),
                    'correct_answer' => 'A', // Default, should be determined by content analysis
                    'question_type' => 'multiple_choice'
                ];
            }
        }
        
        // Pattern for true/false questions
        $tfPattern = '/(?:Question\s*\d*[:\.]?\s*)?(.+?)\s*(?:\n\s*(?:True|False))/is';
        if (preg_match_all($tfPattern, $content, $tfMatches, PREG_SET_ORDER)) {
            foreach ($tfMatches as $match) {
                $questions[] = [
                    'question_text' => trim($match[1]),
                    'question_type' => 'true_false',
                    'correct_answer' => 'True' // Default
                ];
            }
        }

        return $questions;
    }

    /**
     * Create quiz questions for chapter
     */
    private function createQuizQuestions($questions, $chapterId, $courseType)
    {
        foreach ($questions as $index => $questionData) {
            ChapterQuestion::create([
                'chapter_id' => $chapterId,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => json_encode([
                    'A' => $questionData['option_a'] ?? null,
                    'B' => $questionData['option_b'] ?? null,
                    'C' => $questionData['option_c'] ?? null,
                    'D' => $questionData['option_d'] ?? null,
                ]),
                'correct_answer' => $questionData['correct_answer'],
                'order_index' => $index + 1,
                'is_active' => true,
                'state_specific' => $this->getStateFromCourseType($courseType),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Get state code from course type
     */
    private function getStateFromCourseType($courseType)
    {
        switch ($courseType) {
            case 'florida_courses':
                return 'FL';
            case 'missouri_courses':
                return 'MO';
            case 'texas_courses':
                return 'TX';
            case 'delaware_courses':
                return 'DE';
            default:
                return null;
        }
    }

    /**
     * Convert HTML to text while preserving formatting
     */
    private function htmlToText($html, $preserveFormatting = true)
    {
        if ($preserveFormatting) {
            // Convert HTML tags to text equivalents
            $html = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);
            $html = str_replace(['<p>', '</p>'], ["\n", "\n"], $html);
            $html = str_replace(['<h1>', '</h1>', '<h2>', '</h2>', '<h3>', '</h3>'], ["\n\n", "\n\n", "\n\n", "\n\n", "\n\n", "\n\n"], $html);
        }
        
        return strip_tags($html);
    }

    /**
     * Preserve text formatting from Word elements
     */
    private function preserveTextFormatting($text, $element)
    {
        // Basic formatting preservation
        $formatted = $text;
        
        // Add line breaks for paragraphs
        if (method_exists($element, 'getStyle')) {
            $style = $element->getStyle();
            if ($style && method_exists($style, 'getSpaceAfter')) {
                $formatted .= "\n";
            }
        }
        
        return $formatted;
    }

    /**
     * Save extracted images
     */
    private function saveExtractedImages($images, $courseId)
    {
        $savedCount = 0;
        
        foreach ($images as $imagePath) {
            try {
                if (file_exists($imagePath)) {
                    $filename = 'course_' . $courseId . '_' . time() . '_' . basename($imagePath);
                    $storagePath = 'course-images/' . $filename;
                    
                    Storage::disk('public')->put($storagePath, file_get_contents($imagePath));
                    $savedCount++;
                }
            } catch (Exception $e) {
                Log::warning('Failed to save extracted image', ['path' => $imagePath, 'error' => $e->getMessage()]);
            }
        }
        
        return $savedCount;
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }

    /**
     * Handle bulk quiz upload
     */
    public function uploadQuizContent(Request $request)
    {
        // Remove limits for quiz upload
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0);
        
        $request->validate([
            'chapter_id' => 'required|integer',
            'files.*' => 'required|file',
            'question_format' => 'required|string|in:auto_detect,multiple_choice,true_false,mixed',
            'auto_assign_answers' => 'boolean',
            'randomize_options' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $results = [
                'success' => true,
                'questions_created' => 0,
                'files_processed' => 0,
                'errors' => []
            ];

            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                try {
                    $questions = $this->extractQuestionsFromFile($file, $request->all());
                    
                    foreach ($questions as $questionData) {
                        $this->createQuizQuestion($questionData, $request->chapter_id);
                        $results['questions_created']++;
                    }
                    
                    $results['files_processed']++;
                    
                } catch (Exception $e) {
                    $results['errors'][] = "File {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            DB::commit();
            
            return response()->json($results);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Quiz upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract questions from uploaded file
     */
    private function extractQuestionsFromFile($file, $options)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        switch ($extension) {
            case 'docx':
            case 'doc':
                return $this->extractQuestionsFromWord($file, $options);
            case 'txt':
                return $this->extractQuestionsFromText($file, $options);
            case 'csv':
                return $this->extractQuestionsFromCsv($file, $options);
            case 'json':
                return $this->extractQuestionsFromJson($file, $options);
            default:
                throw new Exception("Unsupported file type for quiz: {$extension}");
        }
    }

    /**
     * Extract questions from Word document
     */
    private function extractQuestionsFromWord($file, $options)
    {
        $tempPath = $file->store('temp', 'local');
        $fullPath = storage_path('app/' . $tempPath);
        
        try {
            $phpWord = IOFactory::load($fullPath);
            $content = '';
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . "\n";
                    }
                }
            }
            
            return $this->parseQuestionsFromText($content, $options);
            
        } finally {
            Storage::disk('local')->delete($tempPath);
        }
    }

    /**
     * Extract questions from text file
     */
    private function extractQuestionsFromText($file, $options)
    {
        $content = file_get_contents($file->getPathname());
        return $this->parseQuestionsFromText($content, $options);
    }

    /**
     * Extract questions from CSV file
     */
    private function extractQuestionsFromCsv($file, $options)
    {
        $questions = [];
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $questions[] = [
                'question_text' => $data[0] ?? '',
                'option_a' => $data[1] ?? '',
                'option_b' => $data[2] ?? '',
                'option_c' => $data[3] ?? '',
                'option_d' => $data[4] ?? '',
                'correct_answer' => $data[5] ?? 'A',
                'question_type' => 'multiple_choice',
                'explanation' => $data[6] ?? null
            ];
        }
        
        fclose($handle);
        return $questions;
    }

    /**
     * Extract questions from JSON file
     */
    private function extractQuestionsFromJson($file, $options)
    {
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['questions'])) {
            throw new Exception('Invalid JSON format. Expected "questions" array.');
        }
        
        return $data['questions'];
    }

    /**
     * Parse questions from text content
     */
    private function parseQuestionsFromText($content, $options)
    {
        $questions = [];
        
        // Enhanced pattern matching for various question formats
        $patterns = [
            // Standard multiple choice with A) B) C) D)
            '/(?:Question\s*\d*[:\.]?\s*)?(.+?)\n\s*A[\.\)]\s*(.+?)\n\s*B[\.\)]\s*(.+?)\n\s*C[\.\)]\s*(.+?)\n\s*D[\.\)]\s*(.+?)(?:\n\s*(?:Answer|Correct)[:\s]*([ABCD]))?/is',
            
            // Multiple choice with 1) 2) 3) 4)
            '/(?:Question\s*\d*[:\.]?\s*)?(.+?)\n\s*1[\.\)]\s*(.+?)\n\s*2[\.\)]\s*(.+?)\n\s*3[\.\)]\s*(.+?)\n\s*4[\.\)]\s*(.+?)(?:\n\s*(?:Answer|Correct)[:\s]*([1234]))?/is',
            
            // True/False questions
            '/(?:Question\s*\d*[:\.]?\s*)?(.+?)\s*(?:\n\s*(?:True|False|T\/F))/is'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    if (count($match) >= 6) { // Multiple choice
                        $questions[] = [
                            'question_text' => trim($match[1]),
                            'option_a' => trim($match[2]),
                            'option_b' => trim($match[3]),
                            'option_c' => trim($match[4]),
                            'option_d' => trim($match[5]),
                            'correct_answer' => isset($match[6]) ? strtoupper($match[6]) : 'A',
                            'question_type' => 'multiple_choice'
                        ];
                    } else { // True/False
                        $questions[] = [
                            'question_text' => trim($match[1]),
                            'question_type' => 'true_false',
                            'correct_answer' => 'True'
                        ];
                    }
                }
            }
        }
        
        return $questions;
    }

    /**
     * Create individual quiz question
     */
    private function createQuizQuestion($questionData, $chapterId)
    {
        $options = null;
        
        if ($questionData['question_type'] === 'multiple_choice') {
            $options = [
                'A' => $questionData['option_a'] ?? '',
                'B' => $questionData['option_b'] ?? '',
                'C' => $questionData['option_c'] ?? '',
                'D' => $questionData['option_d'] ?? ''
            ];
        }
        
        return ChapterQuestion::create([
            'chapter_id' => $chapterId,
            'question_text' => $questionData['question_text'],
            'question_type' => $questionData['question_type'],
            'options' => $options ? json_encode($options) : null,
            'correct_answer' => $questionData['correct_answer'],
            'explanation' => $questionData['explanation'] ?? null,
            'order_index' => ChapterQuestion::where('chapter_id', $chapterId)->max('order_index') + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get content statistics
     */
    public function getStats()
    {
        $stats = [
            'courses' => Course::count(),
            'florida_courses' => FloridaCourse::count(),
            'missouri_courses' => MissouriCourse::count(),
            'texas_courses' => TexasCourse::count(),
            'delaware_courses' => DelawareCourse::count(),
            'total_chapters' => Chapter::count(),
            'total_questions' => ChapterQuestion::count(),
            'recent_uploads' => $this->getRecentUploads(),
            'storage_usage' => $this->getStorageUsage()
        ];

        return response()->json($stats);
    }

    /**
     * Get recent upload activity
     */
    private function getRecentUploads()
    {
        return [
            'chapters_today' => Chapter::whereDate('created_at', today())->count(),
            'questions_today' => ChapterQuestion::whereDate('created_at', today())->count(),
            'chapters_week' => Chapter::where('created_at', '>=', now()->subWeek())->count(),
            'questions_week' => ChapterQuestion::where('created_at', '>=', now()->subWeek())->count()
        ];
    }

    /**
     * Get storage usage information
     */
    private function getStorageUsage()
    {
        $publicPath = storage_path('app/public');
        $size = 0;
        
        if (is_dir($publicPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($publicPath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return [
            'total_size' => $size,
            'formatted_size' => $this->formatBytes($size),
            'images_count' => count(glob($publicPath . '/course-images/*')),
            'documents_count' => count(glob($publicPath . '/course-content/*'))
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Validate all content
     */
    public function validateContent()
    {
        try {
            $results = [
                'success' => true,
                'validated_chapters' => 0,
                'validated_questions' => 0,
                'issues_found' => [],
                'fixed_issues' => []
            ];

            // Validate chapters
            $chapters = Chapter::all();
            foreach ($chapters as $chapter) {
                $issues = $this->validateChapter($chapter);
                if (!empty($issues)) {
                    $results['issues_found'] = array_merge($results['issues_found'], $issues);
                }
                $results['validated_chapters']++;
            }

            // Validate questions
            $questions = ChapterQuestion::all();
            foreach ($questions as $question) {
                $issues = $this->validateQuestion($question);
                if (!empty($issues)) {
                    $results['issues_found'] = array_merge($results['issues_found'], $issues);
                }
                $results['validated_questions']++;
            }

            return response()->json($results);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate individual chapter
     */
    private function validateChapter($chapter)
    {
        $issues = [];
        
        // Check for empty content
        if (empty(trim($chapter->content))) {
            $issues[] = "Chapter '{$chapter->title}' has empty content";
        }
        
        // Check for missing title
        if (empty(trim($chapter->title))) {
            $issues[] = "Chapter ID {$chapter->id} has empty title";
        }
        
        // Check for invalid duration
        if ($chapter->duration <= 0) {
            $issues[] = "Chapter '{$chapter->title}' has invalid duration";
        }
        
        return $issues;
    }

    /**
     * Validate individual question
     */
    private function validateQuestion($question)
    {
        $issues = [];
        
        // Check for empty question text
        if (empty(trim($question->question_text))) {
            $issues[] = "Question ID {$question->id} has empty question text";
        }
        
        // Check for missing correct answer
        if (empty($question->correct_answer)) {
            $issues[] = "Question ID {$question->id} has no correct answer";
        }
        
        // Validate multiple choice questions
        if ($question->question_type === 'multiple_choice') {
            $options = json_decode($question->options, true);
            if (!$options || count($options) < 2) {
                $issues[] = "Question ID {$question->id} has insufficient answer options";
            }
        }
        
        return $issues;
    }

    /**
     * Optimize images
     */
    public function optimizeImages()
    {
        try {
            $results = [
                'success' => true,
                'images_processed' => 0,
                'space_saved' => 0,
                'errors' => []
            ];

            $imagePath = storage_path('app/public/course-images');
            
            if (!is_dir($imagePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image directory not found'
                ]);
            }

            $images = glob($imagePath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            
            foreach ($images as $imagePath) {
                try {
                    $originalSize = filesize($imagePath);
                    
                    // Basic image optimization (you can enhance this with actual image processing)
                    $optimized = $this->optimizeImage($imagePath);
                    
                    if ($optimized) {
                        $newSize = filesize($imagePath);
                        $results['space_saved'] += ($originalSize - $newSize);
                        $results['images_processed']++;
                    }
                    
                } catch (Exception $e) {
                    $results['errors'][] = "Failed to optimize " . basename($imagePath) . ": " . $e->getMessage();
                }
            }

            return response()->json($results);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Basic image optimization
     */
    private function optimizeImage($imagePath)
    {
        // This is a placeholder for actual image optimization
        // You can implement actual image compression here using libraries like Intervention Image
        return true;
    }

    /**
     * Export content
     */
    public function exportContent()
    {
        try {
            $exportData = [
                'courses' => Course::with('chapters.questions')->get(),
                'florida_courses' => FloridaCourse::all(),
                'missouri_courses' => MissouriCourse::all(),
                'texas_courses' => TexasCourse::all(),
                'delaware_courses' => DelawareCourse::all(),
                'export_date' => now()->toISOString(),
                'version' => '1.0'
            ];

            $filename = 'course_content_export_' . now()->format('Y_m_d_H_i_s') . '.json';
            $filePath = storage_path('app/exports/' . $filename);
            
            // Create exports directory if it doesn't exist
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            file_put_contents($filePath, json_encode($exportData, JSON_PRETTY_PRINT));
            
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
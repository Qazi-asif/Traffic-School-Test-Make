<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;

class EnhancedCoursePlayerController extends Controller
{
    /**
     * Show enhanced course player with unlimited content support
     */
    public function show(Request $request, $enrollmentId)
    {
        // Remove memory and time limits for large content
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0);
        
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Get all chapters with unlimited content support
        $chapters = Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table ?? 'courses')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();

        // Load progress for all chapters
        $progress = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');

        // Enhance chapters with progress and content optimization
        foreach ($chapters as $chapter) {
            $chapterProgress = $progress->get($chapter->id);
            $chapter->is_completed = $chapterProgress ? $chapterProgress->is_completed : false;
            $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
            $chapter->started_at = $chapterProgress ? $chapterProgress->started_at : null;
            $chapter->completed_at = $chapterProgress ? $chapterProgress->completed_at : null;
            
            // Optimize content for display
            $chapter->optimized_content = $this->optimizeContentForDisplay($chapter->content);
            $chapter->estimated_reading_time = $this->calculateReadingTime($chapter->content);
            $chapter->content_stats = $this->getContentStats($chapter->content);
            
            // Load questions count
            $chapter->questions_count = ChapterQuestion::where('chapter_id', $chapter->id)
                ->where('is_active', true)
                ->count();
        }

        return view('admin.enhanced-course-player', compact('enrollment', 'chapters', 'progress'));
    }

    /**
     * Get chapter content with unlimited size support
     */
    public function getChapterContent(Request $request, $enrollmentId, $chapterId)
    {
        // Remove limits for large content
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0);
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $chapter = Chapter::where('id', $chapterId)
            ->where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Process content for optimal display
        $processedContent = $this->processContentForDisplay($chapter->content);
        
        // Get questions if any
        $questions = ChapterQuestion::where('chapter_id', $chapter->id)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();

        // Mark chapter as accessed
        UserCourseProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id
            ],
            [
                'started_at' => now(),
                'last_accessed_at' => now(),
            ]
        );

        return response()->json([
            'chapter' => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'content' => $processedContent,
                'duration' => $chapter->duration,
                'order_index' => $chapter->order_index,
                'content_stats' => $this->getContentStats($chapter->content),
                'reading_time' => $this->calculateReadingTime($chapter->content)
            ],
            'questions' => $questions,
            'has_quiz' => $questions->count() > 0,
            'quiz_passing_score' => 80, // Default, can be customized
            'content_features' => $this->getContentFeatures($chapter->content)
        ]);
    }

    /**
     * Optimize content for display without limits
     */
    private function optimizeContentForDisplay($content)
    {
        // Handle very large content efficiently
        if (strlen($content) > 1000000) { // 1MB+
            return $this->processLargeContent($content);
        }
        
        return $this->processRegularContent($content);
    }

    /**
     * Process large content (1MB+) efficiently
     */
    private function processLargeContent($content)
    {
        // Split large content into manageable chunks
        $chunks = str_split($content, 100000); // 100KB chunks
        $processedChunks = [];
        
        foreach ($chunks as $chunk) {
            $processedChunks[] = $this->processContentChunk($chunk);
        }
        
        return implode('', $processedChunks);
    }

    /**
     * Process regular content
     */
    private function processRegularContent($content)
    {
        return $this->processContentChunk($content);
    }

    /**
     * Process individual content chunk
     */
    private function processContentChunk($content)
    {
        // Convert line breaks to HTML
        $content = nl2br($content);
        
        // Process images
        $content = $this->processImages($content);
        
        // Process links
        $content = $this->processLinks($content);
        
        // Process formatting
        $content = $this->processFormatting($content);
        
        return $content;
    }

    /**
     * Process images in content
     */
    private function processImages($content)
    {
        // Handle image references
        $content = preg_replace_callback(
            '/\[IMAGE:([^\]]+)\]/',
            function($matches) {
                $imageName = $matches[1];
                $imagePath = Storage::url('course-images/' . $imageName);
                return '<img src="' . $imagePath . '" class="img-fluid course-image" alt="Course Image" loading="lazy">';
            },
            $content
        );
        
        return $content;
    }

    /**
     * Process links in content
     */
    private function processLinks($content)
    {
        // Convert URLs to clickable links
        $content = preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" rel="noopener">$1</a>',
            $content
        );
        
        return $content;
    }

    /**
     * Process text formatting
     */
    private function processFormatting($content)
    {
        // Bold text
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        
        // Italic text
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Headers
        $content = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $content);
        
        return $content;
    }

    /**
     * Calculate estimated reading time
     */
    private function calculateReadingTime($content)
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingSpeed = 200; // words per minute
        $minutes = ceil($wordCount / $readingSpeed);
        
        return max(1, $minutes); // Minimum 1 minute
    }

    /**
     * Get content statistics
     */
    private function getContentStats($content)
    {
        $plainText = strip_tags($content);
        
        return [
            'character_count' => strlen($content),
            'word_count' => str_word_count($plainText),
            'paragraph_count' => substr_count($content, "\n\n") + 1,
            'image_count' => preg_match_all('/\[IMAGE:[^\]]+\]/', $content),
            'link_count' => preg_match_all('/(https?:\/\/[^\s]+)/', $content),
            'size_mb' => round(strlen($content) / 1024 / 1024, 2)
        ];
    }

    /**
     * Get content features
     */
    private function getContentFeatures($content)
    {
        return [
            'has_images' => strpos($content, '[IMAGE:') !== false,
            'has_links' => preg_match('/(https?:\/\/[^\s]+)/', $content),
            'has_formatting' => strpos($content, '**') !== false || strpos($content, '*') !== false,
            'has_headers' => strpos($content, '#') !== false,
            'is_large_content' => strlen($content) > 100000,
            'requires_chunking' => strlen($content) > 1000000
        ];
    }

    /**
     * Process content for display with chunking support
     */
    private function processContentForDisplay($content)
    {
        // If content is very large, implement progressive loading
        if (strlen($content) > 500000) { // 500KB+
            return $this->createProgressiveContent($content);
        }
        
        return $this->optimizeContentForDisplay($content);
    }

    /**
     * Create progressive loading content for very large content
     */
    private function createProgressiveContent($content)
    {
        $chunks = str_split($content, 50000); // 50KB chunks
        $progressiveContent = '';
        
        foreach ($chunks as $index => $chunk) {
            $chunkId = 'content-chunk-' . $index;
            
            if ($index === 0) {
                // First chunk loads immediately
                $progressiveContent .= '<div id="' . $chunkId . '" class="content-chunk">';
                $progressiveContent .= $this->processContentChunk($chunk);
                $progressiveContent .= '</div>';
            } else {
                // Subsequent chunks load on demand
                $progressiveContent .= '<div id="' . $chunkId . '" class="content-chunk lazy-load" data-chunk="' . $index . '">';
                $progressiveContent .= '<div class="loading-placeholder">Loading more content...</div>';
                $progressiveContent .= '<div class="chunk-content" style="display: none;">';
                $progressiveContent .= $this->processContentChunk($chunk);
                $progressiveContent .= '</div>';
                $progressiveContent .= '</div>';
            }
        }
        
        return $progressiveContent;
    }

    /**
     * Get content chunk for progressive loading
     */
    public function getContentChunk(Request $request, $enrollmentId, $chapterId, $chunkIndex)
    {
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $chapter = Chapter::where('id', $chapterId)
            ->where('course_id', $enrollment->course_id)
            ->firstOrFail();

        $chunks = str_split($chapter->content, 50000);
        
        if (!isset($chunks[$chunkIndex])) {
            return response()->json(['error' => 'Chunk not found'], 404);
        }

        $processedChunk = $this->processContentChunk($chunks[$chunkIndex]);

        return response()->json([
            'chunk_index' => $chunkIndex,
            'content' => $processedChunk,
            'is_last_chunk' => $chunkIndex === count($chunks) - 1
        ]);
    }

    /**
     * Submit quiz with unlimited questions support
     */
    public function submitQuiz(Request $request, $enrollmentId, $chapterId)
    {
        // Remove limits for large quiz processing
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0);
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $answers = $request->input('answers', []);
        
        // Get all questions (no limit)
        $questions = ChapterQuestion::where('chapter_id', $chapterId)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $questionResults = [];

        // Process all answers efficiently
        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $this->validateAnswer($question, $userAnswer);
            
            if ($isCorrect) {
                $correctAnswers++;
            }
            
            $questionResults[] = [
                'question_id' => $question->id,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect
            ];
        }

        $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passingScore = 80; // Can be customized
        $passed = $percentage >= $passingScore;

        // Save quiz result
        DB::table('chapter_quiz_results')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'chapter_id' => $chapterId,
                'enrollment_id' => $enrollment->id
            ],
            [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $totalQuestions - $correctAnswers,
                'percentage' => $percentage,
                'passed' => $passed,
                'passing_score_required' => $passingScore,
                'answers' => json_encode($answers),
                'question_results' => json_encode($questionResults),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json([
            'passed' => $passed,
            'percentage' => round($percentage, 2),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'wrong_answers' => $totalQuestions - $correctAnswers,
            'passing_score_required' => $passingScore,
            'question_results' => $questionResults,
            'performance_stats' => $this->calculatePerformanceStats($questionResults)
        ]);
    }

    /**
     * Validate answer
     */
    private function validateAnswer($question, $userAnswer)
    {
        if (!$userAnswer || !$question->correct_answer) {
            return false;
        }
        
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }

    /**
     * Calculate performance statistics
     */
    private function calculatePerformanceStats($questionResults)
    {
        $totalQuestions = count($questionResults);
        $correctAnswers = array_sum(array_column($questionResults, 'is_correct'));
        
        return [
            'accuracy_rate' => $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0,
            'questions_attempted' => count(array_filter($questionResults, function($result) {
                return !empty($result['user_answer']);
            })),
            'questions_skipped' => count(array_filter($questionResults, function($result) {
                return empty($result['user_answer']);
            })),
            'performance_level' => $this->getPerformanceLevel($correctAnswers, $totalQuestions)
        ];
    }

    /**
     * Get performance level
     */
    private function getPerformanceLevel($correct, $total)
    {
        if ($total === 0) return 'No Data';
        
        $percentage = ($correct / $total) * 100;
        
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 80) return 'Good';
        if ($percentage >= 70) return 'Average';
        if ($percentage >= 60) return 'Below Average';
        return 'Needs Improvement';
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
{
    public function getAllChapters()
    {
        try {
            // Cache the result for 10 minutes
            $result = cache()->remember('all_chapters_with_courses', 600, function () {
                $chapters = Chapter::orderBy('course_id')->orderBy('order_index')->get();

                // Batch load courses to avoid N+1 queries
                $courseIds = $chapters->pluck('course_id')->unique();
                $regularCourses = \App\Models\Course::whereIn('id', $courseIds)->get()->keyBy('id');
                $floridaCourses = \App\Models\FloridaCourse::whereIn('id', $courseIds)->get()->keyBy('id');

                return $chapters->map(function ($chapter) use ($regularCourses, $floridaCourses) {
                    $course = $regularCourses->get($chapter->course_id);
                    $courseType = 'courses';

                    if (!$course) {
                        $course = $floridaCourses->get($chapter->course_id);
                        $courseType = 'florida_courses';
                    }

                    return [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'display_title' => $chapter->title,
                        'course_id' => $chapter->course_id,
                        'course_name' => $course ? $course->title : 'Unknown Course',
                        'type' => $courseType,
                        'order_index' => $chapter->order_index,
                        'course' => $course ? [
                            'id' => $course->id,
                            'title' => $course->title,
                        ] : null,
                    ];
                })->sortBy('course_name')->values();
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error loading all chapters: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load chapters'], 500);
        }
    }

    public function index(Course $course)
    {
        $chapters = $course->chapters()->orderBy('order_index')->get();

        return response()->json($chapters);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'order_index' => 'required|integer',
        ]);

        $validated['course_id'] = $course->id;

        return response()->json(Chapter::create($validated), 201);
    }

    public function show(Chapter $chapter)
    {
        return response()->json($chapter->load('course'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'order_index' => 'required|integer',
        ]);

        $chapter->update($validated);

        return response()->json($chapter);
    }

    public function destroy(Chapter $chapter)
    {
        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted successfully']);
    }

    public function indexWeb($courseId)
    {
        try {
            \Log::info('ChapterController.indexWeb called', ['course_id' => $courseId]);
            
            // Simple approach - just get chapters for the course
            $chapters = \App\Models\Chapter::where('course_id', $courseId)
                ->orderBy('order_index', 'asc')
                ->get();
            
            \Log::info('Chapters found', ['count' => $chapters->count()]);
            
            return response()->json($chapters);
            
        } catch (\Exception $e) {
            \Log::error('ChapterController.indexWeb error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to load chapters',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeWeb(Request $request, $courseId)
    {
        try {
            \Log::info('Chapter store request received', [
                'course_id' => $courseId,
                'data' => $request->all(),
            ]);

            // Basic validation for fields that definitely exist
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'video_url' => 'nullable|string',
                'media.*' => 'nullable|file|max:51200',
            ]);

            // Start with absolutely basic data that every chapters table should have
            $chapterData = [
                'course_id' => $courseId,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ];

            // Add video_url if provided
            if (!empty($validated['video_url'])) {
                $chapterData['video_url'] = $validated['video_url'];
            }

            // Get table structure to see what columns exist
            $tableColumns = \DB::getSchemaBuilder()->getColumnListing('chapters');
            \Log::info('Available chapters table columns', ['columns' => $tableColumns]);

            // Conditionally add fields based on what exists in the table
            if (in_array('duration', $tableColumns)) {
                $chapterData['duration'] = 30; // Default 30 minutes
            }

            if (in_array('required_min_time', $tableColumns)) {
                $chapterData['required_min_time'] = 30; // Default 30 minutes
            }

            if (in_array('course_table', $tableColumns)) {
                if (request()->is('api/florida-courses/*')) {
                    $chapterData['course_table'] = 'florida_courses';
                } else {
                    $chapterData['course_table'] = 'courses';
                }
            }

            if (in_array('order_index', $tableColumns)) {
                // Get the next order index
                $maxOrder = \DB::table('chapters')->where('course_id', $courseId)->max('order_index') ?? 0;
                $chapterData['order_index'] = $maxOrder + 1;
            }

            if (in_array('is_active', $tableColumns)) {
                $chapterData['is_active'] = true;
            }

            // Handle file upload if present
            if ($request->hasFile('media')) {
                $files = $request->file('media');

                // Handle single file or array of files
                if (! is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                    if (! file_exists(storage_path('app/public/course-media'))) {
                        mkdir(storage_path('app/public/course-media'), 0755, true);
                    }

                    $path = $file->storeAs('course-media', $filename, 'public');
                    $mimeType = $file->getClientMimeType();
                    $fileUrl = '/storage/course-media/'.$filename;

                    // Handle videos
                    if (in_array($mimeType, ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'])) {
                        if (in_array('video_url', $tableColumns) && empty($chapterData['video_url'])) {
                            $chapterData['video_url'] = $fileUrl;
                        }
                    }
                    // Handle images - add to content
                    elseif (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                        $chapterData['content'] .= "\n\n<div class='chapter-media'><img src='{$fileUrl}' alt='{$originalName}' class='img-fluid' style='max-width: 100%;'></div>";
                    }
                    // Handle other files (PDFs, docs) - add download link
                    else {
                        $chapterData['content'] .= "\n\n<div class='chapter-media'><a href='{$fileUrl}' target='_blank' class='btn btn-outline-primary'><i class='fas fa-download'></i> Download {$originalName}</a></div>";
                    }

                    // Only process first file for now
                    break;
                }
            }

            \Log::info('Creating chapter with adaptive data', [
                'chapter_data' => $chapterData,
                'available_columns' => $tableColumns
            ]);

            // Use direct DB insert to avoid Eloquent fillable restrictions
            $chapterId = \DB::table('chapters')->insertGetId($chapterData);
            
            // Get the created chapter
            $chapter = \DB::table('chapters')->where('id', $chapterId)->first();

            \Log::info('Chapter created successfully', ['chapter_id' => $chapterId]);

            return response()->json($chapter, 201);
        } catch (\Exception $e) {
            \Log::error('Chapter store error: '.$e->getMessage());
            \Log::error('Chapter store error trace: '.$e->getTraceAsString());

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Failed to create chapter. Please check the logs for details.'
            ], 500);
        }
    }

    public function destroyWeb(Chapter $chapter)
    {
        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted successfully']);
    }

    public function updateWeb(Request $request, $id)
    {
        try {
            $chapter = \App\Models\Chapter::findOrFail($id);

            \Log::info('Chapter update request received', [
                'chapter_id' => $chapter->id,
                'data' => $request->all(),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'duration' => 'nullable|integer|min:1',
                'required_min_time' => 'nullable|integer|min:0',
                'order_index' => 'nullable|integer|min:1',
                'video_url' => 'nullable|string|max:500',
                'is_active' => 'nullable',
                'media' => 'nullable|array',
                'media.*' => 'file|max:51200',
            ]);

            // Get table structure to see what columns exist
            $tableColumns = \DB::getSchemaBuilder()->getColumnListing('chapters');
            \Log::info('Available chapters table columns', ['columns' => $tableColumns]);

            // Build update data based on existing columns
            $updateData = [
                'title' => $validated['title'],
                'content' => $validated['content'],
            ];

            // Only add fields that exist in the table
            if (in_array('duration', $tableColumns) && isset($validated['duration'])) {
                $updateData['duration'] = $validated['duration'];
            }

            if (in_array('required_min_time', $tableColumns) && isset($validated['required_min_time'])) {
                $updateData['required_min_time'] = $validated['required_min_time'] ?? $validated['duration'] ?? 30;
            }

            if (in_array('order_index', $tableColumns) && isset($validated['order_index'])) {
                $updateData['order_index'] = $validated['order_index'];
            }

            if (in_array('video_url', $tableColumns) && isset($validated['video_url'])) {
                $updateData['video_url'] = $validated['video_url'];
            }

            if (in_array('is_active', $tableColumns) && array_key_exists('is_active', $validated)) {
                $updateData['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            // Handle order_index change - reorder other chapters
            if (isset($validated['order_index']) && $validated['order_index'] != $chapter->order_index) {
                $oldOrder = $chapter->order_index;
                $newOrder = $validated['order_index'];
                $courseId = $chapter->course_id;
                $courseTable = $chapter->course_table ?? 'courses';

                \Log::info('Reordering chapters', [
                    'chapter_id' => $chapter->id,
                    'old_order' => $oldOrder,
                    'new_order' => $newOrder,
                ]);

                // Use transaction to ensure data integrity
                \DB::transaction(function () use ($chapter, $oldOrder, $newOrder, $courseId, $courseTable) {
                    if ($newOrder < $oldOrder) {
                        // Moving up: shift chapters between newOrder and oldOrder-1 down by 1
                        \App\Models\Chapter::where('course_id', $courseId)
                            ->where('id', '!=', $chapter->id)
                            ->where('order_index', '>=', $newOrder)
                            ->where('order_index', '<', $oldOrder)
                            ->increment('order_index');
                    } else {
                        // Moving down: shift chapters between oldOrder+1 and newOrder up by 1
                        \App\Models\Chapter::where('course_id', $courseId)
                            ->where('id', '!=', $chapter->id)
                            ->where('order_index', '>', $oldOrder)
                            ->where('order_index', '<=', $newOrder)
                            ->decrement('order_index');
                    }
                });
            }

            // Handle multiple file uploads if present
            if ($request->hasFile('media')) {
                $files = $request->file('media');
                if (! is_array($files)) {
                    $files = [$files]; // Convert single file to array
                }

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                    if (! file_exists(storage_path('app/public/course-media'))) {
                        mkdir(storage_path('app/public/course-media'), 0755, true);
                    }

                    $path = $file->storeAs('course-media', $filename, 'public');
                    $mimeType = $file->getClientMimeType();
                    $fileUrl = '/storage/course-media/'.$filename;

                    if (in_array($mimeType, ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'])) {
                        // For videos, add to content as embedded video
                        $updateData['content'] .= "\n\n<div class='chapter-media'><video src='{$fileUrl}' controls width='100%' style='max-height: 400px;'></video></div>";
                        
                        // Also set video_url if column exists and not already set
                        if (in_array('video_url', $tableColumns) && empty($updateData['video_url'])) {
                            $updateData['video_url'] = $fileUrl;
                        }
                    } elseif (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                        // For images, add to content as embedded image
                        $updateData['content'] .= "\n\n<div class='chapter-media'><img src='{$fileUrl}' alt='{$originalName}' class='img-fluid' style='max-width: 100%; height: auto;'></div>";
                    } else {
                        // For other files (PDF, docs), add as download link
                        $updateData['content'] .= "\n\n<div class='chapter-media'><a href='{$fileUrl}' target='_blank' class='btn btn-outline-primary'><i class='fas fa-download'></i> Download {$originalName}</a></div>";
                    }
                }
            }

            \Log::info('Updating chapter with adaptive data', [
                'chapter_id' => $chapter->id,
                'update_data' => array_keys($updateData),
                'available_columns' => $tableColumns
            ]);

            // Use direct DB update to avoid Eloquent fillable restrictions
            \DB::table('chapters')->where('id', $chapter->id)->update($updateData);
            
            // Refresh the chapter model
            $chapter->refresh();

            \Log::info('Chapter updated successfully', ['chapter_id' => $chapter->id]);

            return response()->json($chapter);
        } catch (\Exception $e) {
            \Log::error('Chapter update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to update chapter: '.$e->getMessage()], 500);
        }
    }

    public function saveQuizResults(Request $request)
    {
        try {
            $validated = $request->validate([
                'chapter_id' => 'required|integer',
                'enrollment_id' => 'required|integer',
                'total_questions' => 'required|integer',
                'correct_answers' => 'required|integer',
                'wrong_answers' => 'required|integer',
                'percentage' => 'required|numeric',
                'answers' => 'required|array',
            ]);

            // Get the enrollment to find the course
            $enrollment = \App\Models\UserCourseEnrollment::find($validated['enrollment_id']);
            $course = $enrollment ? \DB::table('florida_courses')->where('id', $enrollment->course_id)->first() : null;
            
            // Handle Delaware quiz rotation logic
            if ($course && $course->state_code === 'DE') {
                return $this->handleDelawareQuizRotation($validated, $enrollment);
            }

            // Save or update quiz result (allow retakes)
            $quizResult = \App\Models\ChapterQuizResult::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'chapter_id' => $validated['chapter_id'],
                ],
                [
                    'total_questions' => $validated['total_questions'],
                    'correct_answers' => $validated['correct_answers'],
                    'wrong_answers' => $validated['wrong_answers'],
                    'percentage' => $validated['percentage'],
                    'answers' => $validated['answers'],
                ]
            );

            if ($enrollment) {
                // Calculate new quiz average for this user and course
                $quizAverage = \App\Models\ChapterQuizResult::calculateUserQuizAverage(
                    auth()->id(), 
                    $enrollment->course_id
                );
                
                // Update the enrollment with the new quiz average
                $enrollment->update(['quiz_average' => $quizAverage]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Quiz results saved',
                    'quiz_average' => $quizAverage,
                    'chapter_score' => $validated['percentage']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Quiz results saved']);
        } catch (\Exception $e) {
            \Log::error('Failed to save quiz results: '.$e->getMessage());

            return response()->json(['error' => 'Failed to save results'], 500);
        }
    }

    public function getQuizResult($chapterId)
    {
        try {
            $quizResult = \App\Models\ChapterQuizResult::where('user_id', auth()->id())
                ->where('chapter_id', $chapterId)
                ->first();

            return response()->json([
                'quiz_result' => $quizResult
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get quiz result: '.$e->getMessage());
            return response()->json(['error' => 'Failed to get quiz result'], 500);
        }
    }

    /**
     * Handle Delaware quiz rotation logic
     * If student fails Quiz Set 1, show Quiz Set 2
     */
    private function handleDelawareQuizRotation($validated, $enrollment)
    {
        $chapterId = $validated['chapter_id'];
        $userId = auth()->id();
        $passed = $validated['percentage'] >= 70; // Assuming 70% is passing
        
        // Get or create progress record for this chapter
        $progress = \DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->first();
            
        if (!$progress) {
            // Create new progress record
            \DB::table('user_course_progress')->insert([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapterId,
                'current_quiz_set' => 1,
                'quiz_set_1_attempts' => 1,
                'quiz_set_2_attempts' => 0,
                'quiz_score' => $validated['percentage'],
                'is_completed' => $passed,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Update existing progress
            $currentQuizSet = $progress->current_quiz_set ?? 1;
            $set1Attempts = $progress->quiz_set_1_attempts ?? 0;
            $set2Attempts = $progress->quiz_set_2_attempts ?? 0;
            
            if ($currentQuizSet == 1) {
                $set1Attempts++;
                
                // If failed Quiz Set 1, switch to Quiz Set 2
                if (!$passed) {
                    $currentQuizSet = 2;
                }
            } else {
                $set2Attempts++;
            }
            
            \DB::table('user_course_progress')
                ->where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->update([
                    'current_quiz_set' => $currentQuizSet,
                    'quiz_set_1_attempts' => $set1Attempts,
                    'quiz_set_2_attempts' => $set2Attempts,
                    'quiz_score' => $validated['percentage'],
                    'is_completed' => $passed,
                    'updated_at' => now(),
                ]);
        }
        
        // Save quiz result
        $quizResult = \App\Models\ChapterQuizResult::updateOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapterId,
            ],
            [
                'total_questions' => $validated['total_questions'],
                'correct_answers' => $validated['correct_answers'],
                'wrong_answers' => $validated['wrong_answers'],
                'percentage' => $validated['percentage'],
                'answers' => $validated['answers'],
            ]
        );
        
        // Calculate and update quiz average
        $quizAverage = \App\Models\ChapterQuizResult::calculateUserQuizAverage(
            $userId, 
            $enrollment->course_id
        );
        
        $enrollment->update(['quiz_average' => $quizAverage]);
        
        // Return response with quiz set information
        $response = [
            'success' => true,
            'message' => $passed ? 'Quiz passed!' : 'Quiz failed.',
            'passed' => $passed,
            'delaware_quiz_rotation' => true,
        ];
        
        // If failed Quiz Set 1, inform frontend to load Quiz Set 2
        // Check if they were on Set 1 before (now switched to 2) and failed
        $wasOnSet1 = !$progress || ($progress->current_quiz_set ?? 1) == 1;
        if (!$passed && $wasOnSet1) {
            $response['switch_to_quiz_set'] = 2;
            $response['message'] = 'Quiz Set 1 failed. You will now see Quiz Set 2 questions.';
        }
        
        return response()->json($response);
    }

    public function uploadTinyMceImage(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|max:5120',
            ]);

            $file = $request->file('file');
            
            // Get extension, default to png for pasted images without extension
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                // Try to determine extension from mime type
                $mimeType = $file->getMimeType();
                $mimeToExt = [
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'image/bmp' => 'bmp',
                ];
                $extension = $mimeToExt[$mimeType] ?? 'png';
            }
            
            $filename = time() . '_' . uniqid() . '.' . $extension;

            if (!file_exists(storage_path('app/public/course-media'))) {
                mkdir(storage_path('app/public/course-media'), 0755, true);
            }

            $file->storeAs('course-media', $filename, 'public');
            $fileUrl = '/storage/course-media/' . $filename;

            \Log::info('TinyMCE image uploaded', ['filename' => $filename, 'url' => $fileUrl]);

            return response()->json(['location' => $fileUrl], 200);
        } catch (\Exception $e) {
            \Log::error('TinyMCE image upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Import content from DOCX file and convert to HTML with embedded images
     */
    public function importDocx(Request $request)
    {
        try {
            // Enhanced validation with better error messages
            $request->validate([
                'file' => 'required|file|mimes:docx|max:51200', // Increased to 50MB
            ], [
                'file.required' => 'Please select a DOCX file to import.',
                'file.file' => 'The uploaded item must be a valid file.',
                'file.mimes' => 'Only DOCX files are supported for import.',
                'file.max' => 'The DOCX file must be smaller than 50MB.',
            ]);

            $file = $request->file('file');
            
            \Log::info('Starting DOCX import', ['filename' => $file->getClientOriginalName()]);
            
            // Parse numbering.xml to get list format types BEFORE loading with PHPWord
            $numberingFormats = $this->parseDocxNumbering($file->getPathname());
            \Log::info('Parsed numbering formats', ['formats' => $numberingFormats]);
            
            // Try to load the DOCX file with a custom reader that skips problematic images
            try {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getPathname());
                \Log::info('DOCX loaded successfully with PHPWord');
            } catch (\Exception $loadException) {
                \Log::warning('PHPWord loading failed, trying fallback method', ['error' => $loadException->getMessage()]);
                // If loading fails completely, try to provide helpful error
                if (strpos($loadException->getMessage(), 'Invalid image') !== false) {
                    // Even if loading fails, we can still try to extract text content
                    // by using a more permissive approach
                    return $this->importDocxWithImageSkipping($file);
                }
                throw $loadException;
            }

            $html = '';
            $imageCount = 0;
            $unsupportedImages = [];

            // Ensure upload directory exists
            if (!file_exists(storage_path('app/public/course-media'))) {
                mkdir(storage_path('app/public/course-media'), 0755, true);
            }

            foreach ($phpWord->getSections() as $sectionIndex => $section) {
                \Log::info('Processing section', ['section' => $sectionIndex]);
                $html .= $this->processDocxElements($section->getElements(), $imageCount, false, $unsupportedImages, $phpWord, $numberingFormats);
            }
            
            // Decode any HTML entities that might have been double-encoded
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $response = [
                'success' => true,
                'html' => $html,
                'images_imported' => $imageCount,
            ];

            // Include unsupported images info if any were found
            if (!empty($unsupportedImages)) {
                $response['unsupported_images'] = $unsupportedImages;
                $response['has_unsupported_images'] = true;
            }

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('DOCX import validation error', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed',
                'validation_errors' => $e->errors(),
                'message' => 'Please check your file and try again.'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('DOCX import error: ' . $e->getMessage());
            \Log::error('DOCX import error trace: ' . $e->getTraceAsString());
            
            // If there's still an error, try the fallback method
            if (strpos($e->getMessage(), 'Invalid image') !== false || 
                strpos($e->getMessage(), 'wmf') !== false ||
                strpos($e->getMessage(), 'emf') !== false) {
                
                return $this->importDocxWithImageSkipping($request->file('file'));
            }
            
            return response()->json([
                'error' => 'Failed to import DOCX: ' . $e->getMessage(),
                'message' => 'Please try with a different file or contact support if the issue persists.',
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'type' => get_class($e)
                ]
            ], 500);
        }
    }

    /**
     * Fallback method to import DOCX by extracting text and skipping problematic images
     */
    private function importDocxWithImageSkipping($file)
    {
        try {
            // Use ZipArchive to manually extract content
            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) !== TRUE) {
                throw new \Exception('Could not open DOCX file');
            }

            // Extract document.xml which contains the main content
            $documentXml = $zip->getFromName('word/document.xml');
            if (!$documentXml) {
                throw new \Exception('Could not find document content');
            }

            $zip->close();

            // Parse the XML and extract text content
            $dom = new \DOMDocument();
            $dom->loadXML($documentXml);
            
            $html = $this->extractTextFromDocumentXml($dom);
            
            // Try to extract and process supported images separately
            $imageCount = 0;
            $unsupportedImages = [];
            
            // Re-open zip to check for images
            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) === TRUE) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (strpos($filename, 'word/media/') === 0) {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        
                        if (in_array($ext, ['wmf', 'emf', 'eps', 'tiff', 'tif', 'bmp'])) {
                            $unsupportedImages[] = [
                                'filename' => basename($filename),
                                'format' => strtoupper($ext),
                                'reason' => $this->getUnsupportedFormatReason($ext)
                            ];
                        } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                            // Try to extract supported images
                            $imageData = $zip->getFromName($filename);
                            if ($imageData) {
                                $imageCount++;
                                $newFilename = 'docx_fallback_' . time() . '_' . $imageCount . '.' . $ext;
                                
                                if (!file_exists(storage_path('app/public/course-media'))) {
                                    mkdir(storage_path('app/public/course-media'), 0755, true);
                                }
                                
                                file_put_contents(storage_path('app/public/course-media/' . $newFilename), $imageData);
                                $fileUrl = '/storage/course-media/' . $newFilename;
                                
                                // Add image to HTML content
                                $html .= '<div class="chapter-media"><img src="' . $fileUrl . '" alt="Imported image ' . $imageCount . '" class="img-fluid" style="max-width: 100%; margin: 10px 0;"></div>';
                            }
                        }
                    }
                }
                $zip->close();
            }
            
            // Decode any HTML entities that might have been encoded
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $response = [
                'success' => true,
                'html' => $html,
                'images_imported' => $imageCount,
                'fallback_mode' => true,
                'message' => 'Content imported using fallback mode due to unsupported images'
            ];

            if (!empty($unsupportedImages)) {
                $response['unsupported_images'] = $unsupportedImages;
                $response['has_unsupported_images'] = true;
            }

            return response()->json($response);
            
        } catch (\Exception $e) {
            \Log::error('DOCX fallback import error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Could not import DOCX content. Please try converting the file to a simpler format or removing problematic images.',
                'fallback_failed' => true
            ], 422);
        }
    }

    /**
     * Extract text content from document.xml DOM with better formatting preservation
     */
    private function extractTextFromDocumentXml($dom)
    {
        $html = '';
        $xpath = new \DOMXPath($dom);
        
        // Register namespaces
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Track list state
        $inList = false;
        $listType = 'ul';
        
        // Extract paragraphs with formatting
        $paragraphs = $xpath->query('//w:p');
        
        foreach ($paragraphs as $paragraph) {
            $paragraphText = '';
            $paragraphStyle = '';
            
            // Check for paragraph properties (alignment, spacing, etc.)
            $pPr = $xpath->query('.//w:pPr', $paragraph)->item(0);
            if ($pPr) {
                // Check for text alignment
                $jc = $xpath->query('.//w:jc', $pPr)->item(0);
                if ($jc) {
                    $alignment = $jc->getAttribute('w:val');
                    switch ($alignment) {
                        case 'center':
                            $paragraphStyle .= 'text-align: center; ';
                            break;
                        case 'right':
                            $paragraphStyle .= 'text-align: right; ';
                            break;
                        case 'justify':
                            $paragraphStyle .= 'text-align: justify; ';
                            break;
                        case 'left':
                        default:
                            $paragraphStyle .= 'text-align: left; ';
                            break;
                    }
                }
                
                // Check for indentation
                $ind = $xpath->query('.//w:ind', $pPr)->item(0);
                if ($ind) {
                    $leftIndent = $ind->getAttribute('w:left');
                    if ($leftIndent) {
                        // Convert twips to pixels (1 twip = 1/20 point, 1 point = 1.33 pixels)
                        $leftIndentPx = round($leftIndent / 20 * 1.33);
                        if ($leftIndentPx > 0) {
                            $paragraphStyle .= "margin-left: {$leftIndentPx}px; ";
                        }
                    }
                }
            }
            
            // Extract text with formatting
            $runs = $xpath->query('.//w:r', $paragraph);
            foreach ($runs as $run) {
                $runText = '';
                $runStyle = '';
                
                // Check for run properties (bold, italic, etc.)
                $rPr = $xpath->query('.//w:rPr', $run)->item(0);
                if ($rPr) {
                    if ($xpath->query('.//w:b', $rPr)->length > 0) {
                        $runStyle .= 'font-weight: bold; ';
                    }
                    if ($xpath->query('.//w:i', $rPr)->length > 0) {
                        $runStyle .= 'font-style: italic; ';
                    }
                    if ($xpath->query('.//w:u', $rPr)->length > 0) {
                        $runStyle .= 'text-decoration: underline; ';
                    }
                }
                
                // Get text content
                $textNodes = $xpath->query('.//w:t', $run);
                foreach ($textNodes as $textNode) {
                    $runText .= $textNode->textContent;
                }
                
                // Apply run formatting if any
                if (!empty($runStyle) && !empty($runText)) {
                    $runText = '<span style="' . trim($runStyle) . '">' . $runText . '</span>';
                }
                
                $paragraphText .= $runText;
            }
            
            $paragraphText = trim($paragraphText);
            if (!empty($paragraphText)) {
                // Enhanced list detection for Word documents
                $isListItem = false;
                $currentListType = 'ul'; // default to bullet list
                
                // Check for paragraph properties first (most reliable)
                if ($pPr) {
                    // Check for numbering properties (w:numPr)
                    $numPr = $xpath->query('.//w:numPr', $pPr)->item(0);
                    if ($numPr) {
                        $isListItem = true;
                        
                        // Try to determine if it's numbered or bulleted
                        $numId = $xpath->query('.//w:numId', $numPr)->item(0);
                        $ilvl = $xpath->query('.//w:ilvl', $numPr)->item(0);
                        
                        if ($numId) {
                            $numIdVal = $numId->getAttribute('w:val');
                            // In Word, certain numId values typically indicate numbered lists
                            // This is a heuristic - numbered lists often have higher numId values
                            if ($numIdVal && intval($numIdVal) > 0) {
                                // Additional check: look for numbers in the text
                                if (preg_match('/^(\d+|[ivxlcdm]+|[a-z]|[A-Z])[\.\)]\s*/', $paragraphText)) {
                                    $currentListType = 'ol';
                                }
                            }
                        }
                        
                        \Log::debug('Found list item via numPr', [
                            'text' => substr($paragraphText, 0, 50),
                            'numId' => $numId ? $numId->getAttribute('w:val') : 'none',
                            'ilvl' => $ilvl ? $ilvl->getAttribute('w:val') : 'none',
                            'listType' => $currentListType
                        ]);
                    }
                    
                    // Also check for list-style indentation (fallback method)
                    if (!$isListItem) {
                        $ind = $xpath->query('.//w:ind', $pPr)->item(0);
                        if ($ind) {
                            $hanging = $ind->getAttribute('w:hanging');
                            $firstLine = $ind->getAttribute('w:firstLine');
                            
                            // Hanging indent often indicates a list item
                            if ($hanging && intval($hanging) > 0) {
                                $isListItem = true;
                                \Log::debug('Found list item via hanging indent', [
                                    'text' => substr($paragraphText, 0, 50),
                                    'hanging' => $hanging
                                ]);
                            }
                        }
                    }
                }
                
                // Fallback: Pattern-based detection for manually formatted lists
                if (!$isListItem) {
                    // More comprehensive pattern matching for different list formats
                    
                    // Numbered lists: 1., 1), (1), 1-, a., a), A., A), i., i), I., I), etc.
                    $isNumberedPattern = preg_match('/^(\s*)?(\(?(\d+|[a-z]|[A-Z]|[ivxlcdm]+|[IVXLCDM]+)[\.\)\-]\s*|\(\d+\)\s*)/', $paragraphText);
                    
                    // Bullet points: •, -, *, >, ◦, ▪, ►, ·, etc.
                    $isBulletPattern = preg_match('/^(\s*)?[\•\-\*\>\◦\▪\►\·\x{2022}\x{25CF}\x{25E6}\x{2219}\x{25AA}\x{25AB}\x{2023}\x{204C}\x{204D}]\s+/', $paragraphText);
                    
                    // Word-specific bullet characters (Symbol font and Wingdings)
                    $hasWordBullet = preg_match('/^(\s*)?[\x{F0B7}\x{F0A7}\x{F076}\x{F0D8}\x{F0FC}\x{F0D2}\x{F0A8}]/u', $paragraphText);
                    
                    // Check for tab-indented items (common in Word)
                    $hasTabIndent = preg_match('/^\t+[^\t]/', $paragraphText);
                    
                    // Check for space-indented items with list-like content
                    $hasSpaceIndent = preg_match('/^    +(\d+[\.\)]\s*|[\•\-\*]\s*)/', $paragraphText);
                    
                    if ($isNumberedPattern || $isBulletPattern || $hasWordBullet || $hasTabIndent || $hasSpaceIndent) {
                        $isListItem = true;
                        $currentListType = ($isNumberedPattern) ? 'ol' : 'ul';
                        
                        \Log::debug('Found list item via enhanced pattern matching', [
                            'text' => substr($paragraphText, 0, 50),
                            'numbered' => $isNumberedPattern,
                            'bullet' => $isBulletPattern,
                            'wordBullet' => $hasWordBullet,
                            'tabIndent' => $hasTabIndent,
                            'spaceIndent' => $hasSpaceIndent,
                            'listType' => $currentListType
                        ]);
                    }
                }
                
                if ($isListItem) {
                    // If list type changed, close previous list
                    if ($inList && $listType !== $currentListType) {
                        $html .= "</{$listType}>";
                        $inList = false;
                    }
                    
                    if (!$inList) {
                        $listType = $currentListType;
                        $html .= "<{$listType}>";
                        $inList = true;
                    }
                    
                    // Clean the text by removing list markers
                    $cleanText = $paragraphText;
                    
                    // Remove numbered list markers (more comprehensive)
                    $cleanText = preg_replace('/^(\s*)?(\(?(\d+|[a-z]|[A-Z]|[ivxlcdm]+|[IVXLCDM]+)[\.\)\-]\s*|\(\d+\)\s*)/', '', $cleanText);
                    
                    // Remove bullet markers (more comprehensive)
                    $cleanText = preg_replace('/^(\s*)?[\•\-\*\>\◦\▪\►\·\x{2022}\x{25CF}\x{25E6}\x{F0B7}\x{F0A7}\x{F076}\x{F0D8}\x{2219}\x{25AA}\x{25AB}\x{2023}\x{204C}\x{204D}\x{F0FC}\x{F0D2}\x{F0A8}]\s*/u', '', $cleanText);
                    
                    // Remove tab indentation
                    $cleanText = preg_replace('/^\t+/', '', $cleanText);
                    
                    // Remove excessive space indentation
                    $cleanText = preg_replace('/^    +/', '', $cleanText);
                    
                    // Trim any remaining whitespace
                    $cleanText = trim($cleanText);
                    
                    $html .= '<li>' . $this->cleanTextForHtml($cleanText) . '</li>';
                } else {
                    // Close list if we were in one
                    if ($inList) {
                        $html .= "</{$listType}>";
                        $inList = false;
                    }
                    
                    // Apply paragraph styling if any
                    if (!empty($paragraphStyle)) {
                        $html .= '<p style="' . trim($paragraphStyle) . '">' . $this->cleanTextForHtml($paragraphText) . '</p>';
                    } else {
                        $html .= '<p>' . $this->cleanTextForHtml($paragraphText) . '</p>';
                    }
                }
            }
        }
        
        // Close any open list
        if ($inList) {
            $html .= "</{$listType}>";
        }
        
        // Extract tables with better formatting
        $tables = $xpath->query('//w:tbl');
        foreach ($tables as $table) {
            $html .= '<table class="table table-bordered" style="width: 100%; margin: 15px 0;">';
            $rows = $xpath->query('.//w:tr', $table);
            
            foreach ($rows as $row) {
                $html .= '<tr>';
                $cells = $xpath->query('.//w:tc', $row);
                
                foreach ($cells as $cell) {
                    $cellText = '';
                    $cellStyle = '';
                    
                    // Check for cell alignment
                    $tcPr = $xpath->query('.//w:tcPr', $cell)->item(0);
                    if ($tcPr) {
                        $vAlign = $xpath->query('.//w:vAlign', $tcPr)->item(0);
                        if ($vAlign) {
                            $alignment = $vAlign->getAttribute('w:val');
                            switch ($alignment) {
                                case 'center':
                                    $cellStyle .= 'vertical-align: middle; ';
                                    break;
                                case 'bottom':
                                    $cellStyle .= 'vertical-align: bottom; ';
                                    break;
                                default:
                                    $cellStyle .= 'vertical-align: top; ';
                                    break;
                            }
                        }
                    }
                    
                    $cellTextNodes = $xpath->query('.//w:t', $cell);
                    foreach ($cellTextNodes as $textNode) {
                        $cellText .= $textNode->textContent;
                    }
                    
                    if (!empty($cellStyle)) {
                        $html .= '<td style="' . trim($cellStyle) . '">' . $this->cleanTextForHtml(trim($cellText)) . '</td>';
                    } else {
                        $html .= '<td>' . $this->cleanTextForHtml(trim($cellText)) . '</td>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        
        return $html;
    }
    
    /**
     * Clean text for HTML output - preserve special characters properly
     */
    private function cleanTextForHtml($text)
    {
        if (empty($text)) {
            return '';
        }
        
        // Decode any HTML entities first (in case PHPWord encoded them)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Convert smart quotes/typography to regular characters
        $replacements = [
            // Smart quotes (UTF-8 hex sequences)
            "\xe2\x80\x9c" => '"',   // Left double quote
            "\xe2\x80\x9d" => '"',   // Right double quote
            "\xe2\x80\x98" => "'",   // Left single quote
            "\xe2\x80\x99" => "'",   // Right single quote/apostrophe
            // Dashes
            "\xe2\x80\x93" => '-',   // En dash
            "\xe2\x80\x94" => '--',  // Em dash
            // Other typography
            "\xe2\x80\xa6" => '...', // Ellipsis
            "\xc2\xa0" => ' ',       // Non-breaking space
            // Additional smart quote variants (Windows-1252)
            "\x93" => '"',
            "\x94" => '"',
            "\x91" => "'",
            "\x92" => "'",
            "\x96" => '-',
            "\x97" => '--',
        ];
        $text = strtr($text, $replacements);
        
        // Only escape < and > to prevent HTML injection, but keep quotes/apostrophes as-is
        $text = str_replace(['<', '>'], ['&lt;', '&gt;'], $text);
        
        return $text;
    }

    /**
     * Process DOCX elements recursively and convert to HTML
     * @param bool $isInline - true when processing elements inside a TextRun (don't wrap in <p>)
     * @param array &$unsupportedImages - array to collect unsupported image info
     */
    private function processDocxElements($elements, &$imageCount, $isInline = false, &$unsupportedImages = [], $phpWord = null, $numberingFormats = [])
    {
        $html = '';
        $listItems = [];
        $currentListType = null;

        foreach ($elements as $element) {
            $className = get_class($element);
            
            // Log what elements we're processing
            \Log::info('Processing DOCX element', ['class' => $className]);

            // Handle Images - inline when inside TextRun, block otherwise
            if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
                // Flush any pending list items before adding image
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $imageHtml = $this->processDocxImage($element, $imageCount, $isInline, $unsupportedImages);
                if ($imageHtml) {
                    $html .= $imageHtml;
                }
                continue;
            }

            // Handle ListItem and ListItemRun - collect them to wrap in ul/ol
            // IMPORTANT: This must come BEFORE TextRun because ListItemRun extends TextRun
            if ($element instanceof \PhpOffice\PhpWord\Element\ListItem || 
                get_class($element) === 'PhpOffice\\PhpWord\\Element\\ListItemRun') {
                
                \Log::info('=== ENTERING LIST HANDLING ===', ['class' => get_class($element)]);
                
                $listText = '';
                
                if (get_class($element) === 'PhpOffice\\PhpWord\\Element\\ListItemRun') {
                    // For ListItemRun, process its elements directly
                    if (method_exists($element, 'getElements')) {
                        $listText = $this->processDocxElements($element->getElements(), $imageCount, true, $unsupportedImages, $phpWord, $numberingFormats);
                    } else {
                        // Fallback - try to get text content
                        $listText = method_exists($element, 'getText') ? $this->cleanTextForHtml($element->getText()) : '';
                    }
                } else {
                    // For ListItem, get the text object
                    $textObj = $element->getTextObject();
                    if ($textObj instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        $listText = $this->processDocxElements($textObj->getElements(), $imageCount, true, $unsupportedImages, $phpWord, $numberingFormats);
                    } else {
                        $listText = $this->cleanTextForHtml($textObj->getText());
                    }
                }
                
                // Determine list type (numbered or bullet) by checking numbering definitions
                $listType = 'ul'; // default to bullet
                $depth = method_exists($element, 'getDepth') ? $element->getDepth() : 0;
                
                // Try to get the numbering format from the parsed numbering.xml
                try {
                    $listStyle = method_exists($element, 'getStyle') ? $element->getStyle() : null;
                    
                    if ($listStyle && is_object($listStyle)) {
                        // Get the numbering ID
                        $numId = method_exists($listStyle, 'getNumId') ? $listStyle->getNumId() : null;
                        
                        \Log::info('List style info', [
                            'numId' => $numId,
                            'depth' => $depth,
                            'hasNumId' => $numId !== null
                        ]);
                        
                        // Method 1: Use the parsed numbering formats from numbering.xml
                        if ($numId !== null && !empty($numberingFormats)) {
                            $numIdStr = (string)$numId;
                            if (isset($numberingFormats[$numIdStr])) {
                                $format = $numberingFormats[$numIdStr];
                                \Log::info('Using parsed numbering format', [
                                    'numId' => $numIdStr,
                                    'format' => $format
                                ]);
                                
                                // Check if it's a numbered format
                                // Numbered formats: decimal, upperRoman, lowerRoman, upperLetter, lowerLetter, etc.
                                // Bullet format: bullet
                                if ($format && $format !== 'bullet') {
                                    $listType = 'ol';
                                }
                            }
                        }
                        
                        // Method 2: Check getListType() which returns PHPWord constants
                        if ($listType === 'ul' && method_exists($listStyle, 'getListType')) {
                            $listTypeValue = $listStyle->getListType();
                            // TYPE_NUMBER = 7, TYPE_ALPHANUM = 9, TYPE_NUMBER_NESTED = 8
                            if (in_array($listTypeValue, [7, 8, 9])) {
                                $listType = 'ol';
                            }
                            \Log::info('List type from getListType()', ['value' => $listTypeValue, 'determined' => $listType]);
                        }
                        
                        // Method 3: Check getNumStyle() for additional detection
                        if ($listType === 'ul' && method_exists($listStyle, 'getNumStyle')) {
                            $numStyleName = $listStyle->getNumStyle();
                            if ($numStyleName) {
                                \Log::info('List type from getNumStyle()', ['numStyle' => $numStyleName, 'determined' => $listType]);
                            }
                        }
                        
                        // Method 4: Check the text content for numbering patterns
                        // If the list item text starts with a number pattern, it's likely a numbered list
                        if ($listType === 'ul' && !empty($listText)) {
                            $trimmedText = trim(strip_tags($listText));
                            // Check if text starts with common numbering patterns
                            if (preg_match('/^(\d+[\.\)]\s|[a-zA-Z][\.\)]\s|[ivxIVX]+[\.\)]\s)/', $trimmedText)) {
                                $listType = 'ol';
                                \Log::info('List type detected from text pattern', ['text' => substr($trimmedText, 0, 20)]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error detecting list type', ['error' => $e->getMessage()]);
                }
                
                // Log for debugging
                \Log::info('Found List Element', [
                    'type' => get_class($element),
                    'text' => substr($listText, 0, 50),
                    'depth' => $depth,
                    'listType' => $listType
                ]);
                
                // If list type changed, flush previous list
                if ($currentListType !== null && $currentListType !== $listType) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                }
                
                $currentListType = $listType;
                
                // Clean the list text by removing manual numbering
                $cleanedText = $this->cleanListItemText($listText);
                $listItems[] = '<li>' . $cleanedText . '</li>';
                continue;
            }

            // Handle TextRun (inline elements like bold, italic, images)
            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                // Flush any pending list items
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $innerContent = $this->processDocxElements($element->getElements(), $imageCount, true, $unsupportedImages, $phpWord, $numberingFormats);
                $hasImage = strpos($innerContent, '<img') !== false;
                $hasText = !empty(trim(strip_tags($innerContent, '<img>')));
                
                if ($hasImage && $hasText) {
                    // Image with text - wrap in div with clearfix for float
                    $html .= '<div style="overflow: hidden; margin-bottom: 15px;">' . $innerContent . '</div>';
                } else if ($hasImage) {
                    // Just image(s)
                    $html .= $innerContent;
                } else if ($hasText) {
                    // Just text
                    $html .= '<p>' . $innerContent . '</p>';
                }
                continue;
            }

            // Handle Text
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $text = $this->cleanTextForHtml($element->getText());
                $fontStyle = $element->getFontStyle();
                
                if ($fontStyle && is_object($fontStyle)) {
                    if (method_exists($fontStyle, 'isBold') && $fontStyle->isBold()) {
                        $text = '<strong>' . $text . '</strong>';
                    }
                    if (method_exists($fontStyle, 'isItalic') && $fontStyle->isItalic()) {
                        $text = '<em>' . $text . '</em>';
                    }
                    if (method_exists($fontStyle, 'getUnderline') && $fontStyle->getUnderline() && $fontStyle->getUnderline() !== 'none') {
                        $text = '<u>' . $text . '</u>';
                    }
                }
                $html .= $text;
                continue;
            }

            // Handle Tables
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                // Flush any pending list items
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $html .= '<table class="table table-bordered">';
                foreach ($element->getRows() as $row) {
                    $html .= '<tr>';
                    foreach ($row->getCells() as $cell) {
                        $html .= '<td>' . $this->processDocxElements($cell->getElements(), $imageCount, false, $unsupportedImages, $phpWord, $numberingFormats) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
                continue;
            }

            // Handle TextBreak (line breaks)
            if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                // Don't flush lists on text breaks - they might be within list items
                if (empty($listItems)) {
                    $html .= '<br>';
                }
                continue;
            }
            if ($element instanceof \PhpOffice\PhpWord\Element\Title) {
                // Flush any pending list items
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $depth = $element->getDepth() ?: 1;
                $tag = 'h' . min($depth + 1, 6);
                $titleText = $element->getText();
                if (is_object($titleText) && method_exists($titleText, 'getElements')) {
                    $titleText = $this->processDocxElements($titleText->getElements(), $imageCount, true, $unsupportedImages, $phpWord, $numberingFormats);
                } else {
                    $titleText = $this->cleanTextForHtml($titleText);
                }
                $html .= "<{$tag}>" . $titleText . "</{$tag}>";
                continue;
            }

            // Handle generic text getter
            if (method_exists($element, 'getText')) {
                // Flush any pending list items
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $text = $element->getText();
                if (!empty($text) && is_string($text)) {
                    if ($isInline) {
                        $html .= $this->cleanTextForHtml($text);
                    } else {
                        $html .= '<p>' . $this->cleanTextForHtml($text) . '</p>';
                    }
                }
            }

            // Handle nested elements
            if (method_exists($element, 'getElements')) {
                // Flush any pending list items
                if (!empty($listItems)) {
                    $html .= $this->wrapListItems($listItems, $currentListType);
                    $listItems = [];
                    $currentListType = null;
                }
                
                $nested = $this->processDocxElements($element->getElements(), $imageCount, $isInline, $unsupportedImages, $phpWord, $numberingFormats);
                if (!empty($nested)) {
                    $html .= $nested;
                }
            }
        }
        
        // Flush any remaining list items at the end
        if (!empty($listItems)) {
            $html .= $this->wrapListItems($listItems, $currentListType);
        }

        return $html;
    }
    
    /**
     * Parse numbering.xml from DOCX to get list format types
     * Returns an array mapping numId => format (e.g., 'decimal', 'bullet')
     */
    private function parseDocxNumbering($docxPath)
    {
        $numberingFormats = [];
        
        try {
            $zip = new \ZipArchive();
            if ($zip->open($docxPath) !== TRUE) {
                \Log::warning('Could not open DOCX as ZIP', ['path' => $docxPath]);
                return $numberingFormats;
            }
            
            // Read numbering.xml
            $numberingXml = $zip->getFromName('word/numbering.xml');
            
            if (!$numberingXml) {
                \Log::info('No numbering.xml found in DOCX - this document may not have lists defined');
                $zip->close();
                return $numberingFormats;
            }
            
            \Log::info('Found numbering.xml', ['length' => strlen($numberingXml)]);
            $zip->close();
            
            // Use DOMDocument for more robust XML parsing with namespaces
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            
            // Suppress warnings during load and check result
            libxml_use_internal_errors(true);
            $loaded = $dom->loadXML($numberingXml);
            
            if (!$loaded) {
                $errors = libxml_get_errors();
                \Log::warning('Failed to parse numbering.xml with DOMDocument', [
                    'errors' => array_map(function($e) { return $e->message; }, $errors)
                ]);
                libxml_clear_errors();
                return $numberingFormats;
            }
            libxml_clear_errors();
            
            \Log::info('Successfully parsed numbering.xml with DOMDocument');
            
            // Create XPath with namespace
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            
            // First, get abstract numbering definitions (these define the actual format)
            $abstractFormats = [];
            $abstractNums = $xpath->query('//w:abstractNum');
            
            \Log::info('Found abstractNum elements', ['count' => $abstractNums->length]);
            
            foreach ($abstractNums as $abstractNum) {
                $abstractNumId = $abstractNum->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'abstractNumId');
                
                if (!$abstractNumId) {
                    $abstractNumId = $abstractNum->getAttribute('w:abstractNumId');
                }
                
                \Log::info('Processing abstractNum', ['abstractNumId' => $abstractNumId]);
                
                // Get level 0's format (first level)
                $lvlNodes = $xpath->query('.//w:lvl[@w:ilvl="0"]', $abstractNum);
                
                if ($lvlNodes->length === 0) {
                    // Try without ilvl filter - get first lvl
                    $lvlNodes = $xpath->query('.//w:lvl', $abstractNum);
                }
                
                if ($lvlNodes->length > 0) {
                    $lvl = $lvlNodes->item(0);
                    
                    // Get numFmt value
                    $numFmtNodes = $xpath->query('.//w:numFmt/@w:val', $lvl);
                    
                    if ($numFmtNodes->length > 0) {
                        $format = $numFmtNodes->item(0)->nodeValue;
                        $abstractFormats[$abstractNumId] = $format;
                        \Log::info('Abstract numbering format found', [
                            'abstractNumId' => $abstractNumId,
                            'format' => $format
                        ]);
                    }
                }
            }
            
            // Now map numId to abstractNumId
            $nums = $xpath->query('//w:num');
            
            \Log::info('Found num elements', ['count' => $nums->length]);
            
            foreach ($nums as $num) {
                $numId = $num->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'numId');
                
                if (!$numId) {
                    $numId = $num->getAttribute('w:numId');
                }
                
                // Get abstractNumId reference
                $abstractNumIdNodes = $xpath->query('.//w:abstractNumId/@w:val', $num);
                
                if ($abstractNumIdNodes->length > 0) {
                    $abstractNumId = $abstractNumIdNodes->item(0)->nodeValue;
                    
                    \Log::info('Num mapping', ['numId' => $numId, 'abstractNumId' => $abstractNumId]);
                    
                    if (isset($abstractFormats[$abstractNumId])) {
                        $numberingFormats[$numId] = $abstractFormats[$abstractNumId];
                        \Log::info('Numbering format mapping SUCCESS', [
                            'numId' => $numId,
                            'abstractNumId' => $abstractNumId,
                            'format' => $abstractFormats[$abstractNumId]
                        ]);
                    }
                }
            }
            
            \Log::info('Final numbering formats', ['formats' => $numberingFormats]);
            
        } catch (\Exception $e) {
            \Log::warning('Error parsing numbering.xml', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
        
        return $numberingFormats;
    }

    /**
     * Clean list item text by removing manual numbering
     */
    private function cleanListItemText($text)
    {
        if (empty($text)) {
            return '';
        }
        
        // Remove HTML tags temporarily to work with plain text
        $plainText = strip_tags($text);
        $plainText = trim($plainText);
        
        // Remove various numbering patterns at the start of text
        $patterns = [
            '/^\d+\.\s*/',           // 1. 2. 3. etc.
            '/^\d+\)\s*/',           // 1) 2) 3) etc.
            '/^\(\d+\)\s*/',         // (1) (2) (3) etc.
            '/^[a-z]\.\s*/',         // a. b. c. etc.
            '/^[A-Z]\.\s*/',         // A. B. C. etc.
            '/^[a-z]\)\s*/',         // a) b) c) etc.
            '/^[A-Z]\)\s*/',         // A) B) C) etc.
            '/^[ivxlcdm]+\.\s*/i',   // i. ii. iii. iv. etc. (Roman numerals)
            '/^[IVXLCDM]+\.\s*/',    // I. II. III. IV. etc.
            '/^•\s*/',               // Bullet points
            '/^-\s*/',               // Dash bullets
            '/^→\s*/',               // Arrow bullets
            '/^▪\s*/',               // Square bullets
        ];
        
        $cleanedText = $plainText;
        foreach ($patterns as $pattern) {
            $cleanedText = preg_replace($pattern, '', $cleanedText);
            if ($cleanedText !== $plainText) {
                break; // Stop after first match
            }
        }
        
        // If we removed numbering from plain text, apply the same to HTML
        if ($cleanedText !== $plainText && !empty($cleanedText)) {
            // Try to preserve HTML formatting while removing the numbering
            $htmlText = $text;
            foreach ($patterns as $pattern) {
                $htmlText = preg_replace($pattern, '', $htmlText);
                if (strip_tags($htmlText) !== strip_tags($text)) {
                    break;
                }
            }
            return trim($htmlText);
        }
        
        return $text;
    }

    /**
     * Wrap list items in appropriate ul/ol tags
     */
    private function wrapListItems($items, $listType = 'ul')
    {
        if (empty($items)) {
            return '';
        }
        $tag = ($listType === 'ol') ? 'ol' : 'ul';
        return '<' . $tag . '>' . implode('', $items) . '</' . $tag . '>';
    }

    /**
     * Process and save a DOCX image, return HTML img tag with proper alignment
     * @param bool $isInline - if true, don't wrap in <p> tags
     * @param array &$unsupportedImages - array to collect unsupported image info
     */
    private function processDocxImage($imageElement, &$imageCount, $isInline = false, &$unsupportedImages = [])
    {
        try {
            $source = $imageElement->getSource();
            $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION) ?: '');
            
            // List of unsupported image formats
            $unsupportedFormats = ['wmf', 'emf', 'eps', 'tiff', 'tif', 'bmp', 'svg'];
            
            // Check if format is unsupported BEFORE trying to get image data
            if (in_array($ext, $unsupportedFormats)) {
                $unsupportedImages[] = [
                    'filename' => basename($source),
                    'format' => strtoupper($ext),
                    'reason' => $this->getUnsupportedFormatReason($ext)
                ];
                \Log::warning('Unsupported image format in DOCX: ' . $source);
                
                // Return a placeholder for the unsupported image
                return '<div class="unsupported-image-placeholder" style="background: #f8d7da; border: 1px dashed #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px; color: #721c24; text-align: center;"><i class="fas fa-exclamation-triangle"></i> Unsupported image format: ' . strtoupper($ext) . '</div>';
            }
            
            // Also check for WMF in the source path (sometimes extension might be missing)
            if (strpos(strtolower($source), '.wmf') !== false || strpos(strtolower($source), 'image') !== false) {
                // Try to get image data to test if it's actually supported
                try {
                    $testData = $imageElement->getImageStringData(false); // Don't base64 encode for test
                    if (!$testData) {
                        throw new \Exception('No image data available');
                    }
                } catch (\Exception $testException) {
                    // If we can't get the image data, it's likely unsupported
                    if (strpos($testException->getMessage(), 'Invalid image') !== false || 
                        strpos($testException->getMessage(), 'wmf') !== false ||
                        strpos($testException->getMessage(), 'emf') !== false) {
                        
                        $detectedFormat = 'WMF';
                        if (strpos($testException->getMessage(), 'emf') !== false) {
                            $detectedFormat = 'EMF';
                        }
                        
                        $unsupportedImages[] = [
                            'filename' => basename($source),
                            'format' => $detectedFormat,
                            'reason' => $this->getUnsupportedFormatReason(strtolower($detectedFormat))
                        ];
                        \Log::warning('Detected unsupported image format in DOCX: ' . $source . ' - ' . $testException->getMessage());
                        
                        return '<div class="unsupported-image-placeholder" style="background: #f8d7da; border: 1px dashed #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px; color: #721c24; text-align: center;"><i class="fas fa-exclamation-triangle"></i> Unsupported image format: ' . $detectedFormat . '</div>';
                    }
                    // Re-throw if it's a different kind of error
                    throw $testException;
                }
            }
            
            // Now try to get the actual image data
            $imageData = $imageElement->getImageStringData(true);
            
            if (!$imageData) {
                return '';
            }

            $imageCount++;
            
            // Validate extension for supported formats
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                $ext = 'png';
            }

            $filename = 'docx_' . time() . '_' . $imageCount . '.' . $ext;
            $filepath = storage_path('app/public/course-media/' . $filename);
            
            file_put_contents($filepath, base64_decode($imageData));
            
            $fileUrl = '/storage/course-media/' . $filename;
            
            // Get image dimensions and positioning
            $imgStyle = $imageElement->getStyle();
            $width = null;
            $height = null;
            
            // Safely get dimensions
            try {
                $width = $imgStyle->getWidth();
                $height = $imgStyle->getHeight();
            } catch (\Exception $e) {
                \Log::debug('Could not get image dimensions: ' . $e->getMessage());
            }
            
            // Build style with better alignment handling
            $styleArr = [];
            $shouldFloat = false;
            
            if ($width && $height) {
                // Convert EMUs to pixels (1 inch = 914400 EMUs, 1 inch = 96 pixels)
                $widthPx = round($width / 9525);
                $heightPx = round($height / 9525);
                if ($widthPx > 0 && $heightPx > 0) {
                    // Limit maximum width to prevent layout issues
                    if ($widthPx > 800) {
                        $ratio = 800 / $widthPx;
                        $widthPx = 800;
                        $heightPx = round($heightPx * $ratio);
                    }
                    $styleArr[] = "width: {$widthPx}px";
                    $styleArr[] = "height: {$heightPx}px";
                    
                    // If image is smaller than 400px wide, it's likely meant to float
                    if ($widthPx < 400) {
                        $shouldFloat = true;
                    }
                }
            }
            
            // Check wrapping style - Word uses different wrapping modes
            try {
                if (method_exists($imgStyle, 'getWrappingStyle')) {
                    $wrappingStyle = $imgStyle->getWrappingStyle();
                    if ($wrappingStyle) {
                        // Different wrapping styles in Word can give us hints about positioning
                        switch ($wrappingStyle) {
                            case 'inline':
                                // Inline images should flow with text
                                $styleArr[] = "display: inline-block";
                                $styleArr[] = "vertical-align: middle";
                                $styleArr[] = "max-width: 300px";
                                $styleArr[] = "height: auto";
                                break;
                            case 'square':
                            case 'tight':
                            case 'through':
                                // These typically indicate text wrapping, so float left
                                $shouldFloat = true;
                                break;
                            default:
                                // Keep default centered styling for other cases
                                break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // If wrapping style detection fails, use heuristics
                \Log::debug('Could not get wrapping style for image: ' . $e->getMessage());
            }
            
            // Apply floating or default styling
            if ($shouldFloat) {
                $styleArr[] = "float: left";
                $styleArr[] = "margin: 0 15px 10px 0";
                $styleArr[] = "max-width: 300px";
                $styleArr[] = "height: auto";
            } else {
                // Default centered styling
                $styleArr[] = "max-width: 100%";
                $styleArr[] = "height: auto";
                $styleArr[] = "display: block";
                $styleArr[] = "margin: 10px auto";
            }
            
            $style = implode('; ', $styleArr);
            
            $imgTag = '<img src="' . $fileUrl . '" alt="Imported image ' . $imageCount . '" style="' . $style . '" class="img-fluid" />';
            
            // Only wrap in div if it's not meant to be inline/floating
            if (!$isInline && !$shouldFloat) {
                $imgTag = '<div class="chapter-media" style="text-align: center; margin: 15px 0;">' . $imgTag . '</div>';
            }
            
            return $imgTag;
        } catch (\Exception $e) {
            \Log::error('Failed to process DOCX image: ' . $e->getMessage());
            
            // Try to extract format info from error message or source
            $source = '';
            $detectedFormat = 'UNKNOWN';
            
            try {
                $source = $imageElement->getSource() ?? 'unknown';
                $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION) ?: '');
                
                // Check error message for format clues
                $errorMsg = strtolower($e->getMessage());
                if (strpos($errorMsg, 'wmf') !== false) {
                    $detectedFormat = 'WMF';
                } elseif (strpos($errorMsg, 'emf') !== false) {
                    $detectedFormat = 'EMF';
                } elseif (strpos($errorMsg, 'eps') !== false) {
                    $detectedFormat = 'EPS';
                } elseif (strpos($errorMsg, 'tiff') !== false || strpos($errorMsg, 'tif') !== false) {
                    $detectedFormat = 'TIFF';
                } elseif ($ext) {
                    $detectedFormat = strtoupper($ext);
                }
            } catch (\Exception $sourceException) {
                // If we can't even get the source, use generic info
                $source = 'unknown';
            }
            
            $unsupportedImages[] = [
                'filename' => basename($source),
                'format' => $detectedFormat,
                'reason' => 'Failed to process: ' . $e->getMessage()
            ];
            
            return '<div class="unsupported-image-placeholder" style="background: #f8d7da; border: 1px dashed #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 4px; color: #721c24; text-align: center;"><i class="fas fa-exclamation-triangle"></i> Image could not be imported (' . $detectedFormat . ')</div>';
        }
    }

    /**
     * Get human-readable reason for unsupported format
     */
    private function getUnsupportedFormatReason($format)
    {
        $reasons = [
            'wmf' => 'Windows Metafile (WMF) is a legacy vector format not supported by web browsers',
            'emf' => 'Enhanced Metafile (EMF) is a Windows-specific format not supported by web browsers',
            'eps' => 'Encapsulated PostScript (EPS) requires conversion to a web-compatible format',
            'tiff' => 'TIFF format is not natively supported by web browsers',
            'tif' => 'TIFF format is not natively supported by web browsers',
            'bmp' => 'BMP format should be converted to PNG or JPEG for better web compatibility',
            'svg' => 'SVG embedded in DOCX requires special handling',
        ];
        
        return $reasons[$format] ?? 'This format is not supported for web display';
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\FloridaCourse;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FloridaCourseController extends Controller
{
    public function indexWeb()
    {
        // Fetch from both tables like CourseController does
        $floridaCourses = DB::table('florida_courses')
            ->where('is_active', true)
            ->get();

        $regularCourses = DB::table('courses')
            ->where('is_active', true)
            ->get();

        $allCourses = collect();

        // Add Florida courses
        foreach ($floridaCourses as $course) {
            $allCourses->push((object) [
                'id' => $course->id,
                'real_id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'state_code' => $course->state_code ?? 'FL',
                'total_duration' => $course->duration ?? 0,
                'duration' => $course->duration ?? 0,
                'price' => $course->price ?? 0,
                'passing_score' => $course->passing_score ?? 80,
                'min_pass_score' => $course->min_pass_score ?? 80,
                'is_active' => $course->is_active ?? true,
                'course_type' => $course->course_type ?? 'Regular',
                'certificate_type' => $course->certificate_type ?? null,
                'table' => 'florida_courses',
            ]);
        }

        // Add regular courses
        foreach ($regularCourses as $course) {
            $allCourses->push((object) [
                'id' => $course->id,
                'real_id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'state_code' => $course->state_code ?? $course->state ?? 'MO',
                'total_duration' => $course->duration ?? 0,
                'duration' => $course->duration ?? 0,
                'price' => $course->price ?? 0,
                'passing_score' => $course->passing_score ?? 80,
                'min_pass_score' => $course->passing_score ?? 80,
                'is_active' => $course->is_active ?? true,
                'course_type' => $course->course_type ?? 'Regular',
                'certificate_type' => $course->certificate_type ?? null,
                'table' => 'courses',
            ]);
        }

        return response()->json($allCourses);
    }



    public function destroyWeb($id)
    {
        try {
            // First check which table the course exists in
            $floridaCourse = DB::table('florida_courses')->where('id', $id)->first();
            $regularCourse = DB::table('courses')->where('id', $id)->first();
            
            if (!$floridaCourse && !$regularCourse) {
                return response()->json([
                    'error' => 'Course not found in either table'
                ], 404);
            }
            
            // Determine which table to delete from
            $courseTable = $floridaCourse ? 'florida_courses' : 'courses';
            $course = $floridaCourse ?: $regularCourse;
            
            \Log::info("Deleting course {$id} from {$courseTable} table");
            
            // Check if course has any enrollments
            $enrollmentCount = DB::table('user_course_enrollments')
                ->where('course_id', $id)
                ->where('course_table', $courseTable)
                ->count();
            
            // Check if course has any chapters
            $chapterCount = DB::table('chapters')
                ->where('course_id', $id)
                ->where('course_table', $courseTable)
                ->count();
            
            \Log::info("Course {$id} has {$enrollmentCount} enrollments and {$chapterCount} chapters");
            
            // Start transaction for safe deletion
            DB::beginTransaction();
            
            try {
                // Delete related data in proper order to avoid foreign key constraints
                
                // 1. Delete final exam questions
                DB::table('final_exam_questions')
                    ->where('course_id', $id)
                    ->delete();
                
                // 2. Delete chapter questions and chapters
                if ($chapterCount > 0) {
                    $chapters = DB::table('chapters')
                        ->where('course_id', $id)
                        ->where('course_table', $courseTable)
                        ->pluck('id');
                    
                    // Delete questions for each chapter
                    foreach ($chapters as $chapterId) {
                        DB::table('questions')
                            ->where('chapter_id', $chapterId)
                            ->delete();
                            
                        DB::table('chapter_questions')
                            ->where('chapter_id', $chapterId)
                            ->delete();
                    }
                    
                    // Delete chapters
                    DB::table('chapters')
                        ->where('course_id', $id)
                        ->where('course_table', $courseTable)
                        ->delete();
                }
                
                // 3. Delete user progress and enrollments
                if ($enrollmentCount > 0) {
                    // Get enrollment IDs and user IDs first
                    $enrollments = DB::table('user_course_enrollments')
                        ->where('course_id', $id)
                        ->where('course_table', $courseTable)
                        ->select('id', 'user_id')
                        ->get();
                    
                    $enrollmentIds = $enrollments->pluck('id');
                    $userIds = $enrollments->pluck('user_id');
                    
                    // Delete user course progress (uses enrollment_id)
                    try {
                        DB::table('user_course_progress')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete user_course_progress: ' . $e->getMessage());
                    }
                    
                    // Delete chapter progress (uses user_id and chapter_id)
                    if ($chapterCount > 0) {
                        try {
                            $chapters = DB::table('chapters')
                                ->where('course_id', $id)
                                ->where('course_table', $courseTable)
                                ->pluck('id');
                            
                            DB::table('chapter_progress')
                                ->whereIn('user_id', $userIds)
                                ->whereIn('chapter_id', $chapters)
                                ->delete();
                        } catch (\Exception $e) {
                            \Log::warning('Could not delete chapter_progress: ' . $e->getMessage());
                        }
                    }
                    
                    // Delete quiz attempts (uses enrollment_id)
                    try {
                        DB::table('quiz_attempts')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete quiz_attempts: ' . $e->getMessage());
                    }
                    
                    // Delete chapter quiz results (uses enrollment_id)
                    try {
                        DB::table('chapter_quiz_results')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete chapter_quiz_results: ' . $e->getMessage());
                    }
                    
                    // Delete final exam results (uses enrollment_id)
                    try {
                        DB::table('final_exam_results')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete final_exam_results: ' . $e->getMessage());
                    }
                    
                    // Delete timer sessions (uses user_id)
                    try {
                        DB::table('timer_sessions')
                            ->whereIn('user_id', $userIds)
                            ->where('course_id', $id)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete timer_sessions: ' . $e->getMessage());
                    }
                    
                    // Delete timer violations (uses enrollment_id if it exists)
                    try {
                        DB::table('timer_violations')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete timer_violations: ' . $e->getMessage());
                    }
                    
                    // Delete course timers (uses user_id)
                    try {
                        DB::table('course_timers')
                            ->whereIn('user_id', $userIds)
                            ->where('course_id', $id)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete course_timers: ' . $e->getMessage());
                    }
                    
                    // Delete certificates (uses enrollment_id)
                    try {
                        DB::table('florida_certificates')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete florida_certificates: ' . $e->getMessage());
                    }
                    
                    // Delete state transmissions (uses enrollment_id)
                    try {
                        DB::table('state_transmissions')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete state_transmissions: ' . $e->getMessage());
                    }
                    
                    // Delete payments related to these enrollments (uses enrollment_id)
                    try {
                        DB::table('payments')
                            ->whereIn('enrollment_id', $enrollmentIds)
                            ->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Could not delete payments: ' . $e->getMessage());
                    }
                    
                    // Delete enrollments (this should always work)
                    DB::table('user_course_enrollments')
                        ->where('course_id', $id)
                        ->where('course_table', $courseTable)
                        ->delete();
                }
                
                // 4. Finally delete the course from the appropriate table
                if ($courseTable === 'florida_courses') {
                    DB::table('florida_courses')->where('id', $id)->delete();
                } else {
                    DB::table('courses')->where('id', $id)->delete();
                }
                
                DB::commit();
                
                \Log::info("Successfully deleted course {$id} from {$courseTable}");
                
                return response()->json([
                    'message' => "Course deleted successfully from {$courseTable} table",
                    'deleted_enrollments' => $enrollmentCount,
                    'deleted_chapters' => $chapterCount,
                    'course_table' => $courseTable
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Failed to delete course {$id}: " . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error("Course deletion error: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeWeb(Request $request)
    {
        try {
            \Log::info('FloridaCourseController storeWeb called', ['request_data' => $request->all()]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'state_code' => 'nullable|string|max:10',
                'total_duration' => 'required|integer|min:1',
                'min_pass_score' => 'required|integer|min:0|max:100',
                'price' => 'required|numeric|min:0',
                'certificate_template' => 'nullable|string',
                'is_active' => 'boolean',
                // Optional fields with defaults
                'course_type' => 'nullable|string',
                'delivery_type' => 'nullable|string',
                'dicds_course_id' => 'nullable|string|max:255',
            ]);

            // Map form fields to database fields with proper defaults
            $courseData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['total_duration'],
                'passing_score' => $validated['min_pass_score'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? true,
                'course_type' => $validated['course_type'] ?? 'BDI',
                'delivery_type' => $validated['delivery_type'] ?? 'Online',
                'dicds_course_id' => $validated['dicds_course_id'] ?? 'AUTO_' . time(),
                'certificate_type' => $validated['certificate_template'] ?? null,
            ];

            // Handle state field (the table uses 'state', not 'state_code')
            if (isset($validated['state_code'])) {
                $courseData['state'] = $validated['state_code'];
            } else {
                $courseData['state'] = 'FL';
            }

            \Log::info('Creating Florida course with data', ['course_data' => $courseData]);

            $course = FloridaCourse::create($courseData);

            \Log::info('Florida course created successfully', ['course_id' => $course->id]);

            return response()->json($course, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Florida course validation error', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed',
                'validation_errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Florida course creation error: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            
            return response()->json([
                'error' => 'Failed to create course: ' . $e->getMessage(),
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function updateWeb(Request $request, $id)
    {
        try {
            $course = FloridaCourse::findOrFail($id);
            
            $validated = $request->validate([
                'course_type' => 'required|string',
                'delivery_type' => 'required|string',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'total_duration' => 'required|integer|min:1',
                'min_pass_score' => 'required|integer|min:0|max:100',
                'price' => 'required|numeric|min:0',
                'dicds_course_id' => 'required|string|max:255',
                'is_active' => 'boolean',
            ]);

            $course->update([
                'course_type' => $validated['course_type'],
                'delivery_type' => $validated['delivery_type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['total_duration'],
                'min_pass_score' => $validated['min_pass_score'],
                'price' => $validated['price'],
                'dicds_course_id' => $validated['dicds_course_id'],
                'is_active' => $validated['is_active'] ?? true,
                'passing_score' => $validated['min_pass_score'],
            ]);

            return response()->json($course);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function copy(Request $request)
    {
        try {
            $validated = $request->validate([
                'source_course_id' => 'required|integer',
                'source_table' => 'required|string|in:courses,florida_courses',
                'course_type' => 'sometimes|string', // Made optional since it might not be provided
                'delivery_type' => 'sometimes|string', // Made optional since it might not be provided
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'total_duration' => 'required|integer|min:1',
                'min_pass_score' => 'required|integer|min:0|max:100',
                'price' => 'required|numeric|min:0',
                'dicds_course_id' => 'sometimes|string|max:255', // Made optional since it might not be provided
                'is_active' => 'sometimes|boolean',
                'copy_options' => 'required|array',
                'copy_options.chapters' => 'required|boolean',
                'copy_options.questions' => 'required|boolean',
                'copy_options.final_exam' => 'required|boolean',
            ]);

            DB::beginTransaction();

            // Get source course from appropriate table
            if ($validated['source_table'] === 'florida_courses') {
                $sourceCourse = DB::table('florida_courses')->where('id', $validated['source_course_id'])->first();
                if (!$sourceCourse) {
                    throw new \Exception('Source course not found in florida_courses table');
                }
                $sourceChapterTable = 'chapters'; // All courses use the same chapters table
                $sourceCourseTable = 'florida_courses';
            } else {
                $sourceCourse = DB::table('courses')->where('id', $validated['source_course_id'])->first();
                if (!$sourceCourse) {
                    throw new \Exception('Source course not found in courses table');
                }
                $sourceChapterTable = 'chapters'; // All courses use the same chapters table
                $sourceCourseTable = 'courses';
            }

            // Always create new course in florida_courses table
            $newCourse = FloridaCourse::create([
                'course_type' => $validated['course_type'] ?? 'BDI',
                'delivery_type' => $validated['delivery_type'] ?? 'Online',
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['total_duration'],
                'min_pass_score' => $validated['min_pass_score'],
                'passing_score' => $validated['min_pass_score'],
                'price' => $validated['price'],
                'dicds_course_id' => $validated['dicds_course_id'] ?? ('COPY_' . time() . '_' . $validated['source_course_id']),
                'is_active' => $validated['is_active'] ?? true,
                'state_code' => 'FL',
                'certificate_type' => $sourceCourse->certificate_type ?? null,
                'certificate_template' => $sourceCourse->certificate_template ?? null,
                'copyright_protected' => $sourceCourse->copyright_protected ?? false,
            ]);

            // Copy chapters if requested
            if ($validated['copy_options']['chapters']) {
                $sourceChapters = DB::table($sourceChapterTable)
                    ->where('course_id', $sourceCourse->id)
                    ->where('course_table', $sourceCourseTable)
                    ->orderBy('order_index')
                    ->get();

                foreach ($sourceChapters as $chapter) {
                    // Insert into chapters table with proper course_table field
                    $newChapterId = DB::table('chapters')->insertGetId([
                        'course_id' => $newCourse->id,
                        'title' => $chapter->title,
                        'content' => $chapter->content,
                        'duration' => $chapter->duration,
                        'required_min_time' => $chapter->required_min_time ?? $chapter->duration ?? 0,
                        'order_index' => $chapter->order_index,
                        'video_url' => $chapter->video_url,
                        'is_active' => $chapter->is_active ?? true,
                        'course_table' => 'florida_courses', // Always set to florida_courses since we're copying to florida_courses table
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Copy chapter questions if requested
                    if ($validated['copy_options']['questions']) {
                        $chapterQuestions = DB::table('questions')
                            ->where('chapter_id', $chapter->id)
                            ->get();

                        foreach ($chapterQuestions as $question) {
                            DB::table('questions')->insert([
                                'chapter_id' => $newChapterId,
                                'course_id' => $newCourse->id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type,
                                'options' => $question->options,
                                'correct_answer' => $question->correct_answer,
                                'explanation' => $question->explanation,
                                'points' => $question->points,
                                'order_index' => $question->order_index,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // Copy final exam questions if requested
            if ($validated['copy_options']['final_exam']) {
                $finalExamQuestions = DB::table('final_exam_questions')
                    ->where('course_id', $sourceCourse->id)
                    ->get();

                foreach ($finalExamQuestions as $question) {
                    DB::table('final_exam_questions')->insert([
                        'course_id' => $newCourse->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'options' => $question->options,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'points' => $question->points,
                        'order_index' => $question->order_index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Course copied successfully to Florida courses table',
                'course' => $newCourse,
                'table' => 'florida_courses'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Florida course copy error: ' . $e->getMessage());
            \Log::error('Florida course copy error file: ' . $e->getFile());
            \Log::error('Florida course copy error line: ' . $e->getLine());
            \Log::error('Florida course copy error trace: ' . $e->getTraceAsString());
            \Log::error('Florida course copy request data: ' . json_encode($request->all()));
            
            return response()->json([
                'error' => 'Failed to copy course: ' . $e->getMessage(),
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }


}

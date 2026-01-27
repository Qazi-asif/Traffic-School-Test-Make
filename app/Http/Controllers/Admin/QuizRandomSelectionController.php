<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\Chapter;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizRandomSelectionController extends Controller
{
    /**
     * Display quiz random selection management
     */
    public function index(Request $request)
    {
        try {
            // Get courses from both tables
            $regularCourses = Course::select('id', 'title', 'state')
                ->selectRaw("'courses' as table_type")
                ->orderBy('title')
                ->get();

            $floridaCourses = FloridaCourse::select('id', 'title', 'state_code as state')
                ->selectRaw("'florida_courses' as table_type")
                ->orderBy('title')
                ->get();

            // Combine both collections and add prefixes to distinguish them
            $courses = collect();
            
            foreach ($regularCourses as $course) {
                $course->display_id = $course->id;
                $course->display_title = $course->title . ' (' . ($course->state ?? 'No State') . ') - Regular';
                $courses->push($course);
            }
            
            foreach ($floridaCourses as $course) {
                $course->display_id = 'florida_' . $course->id;
                $course->display_title = $course->title . ' (' . ($course->state ?? 'FL') . ') - Florida';
                $courses->push($course);
            }

            // Debug: Log the number of courses found
            Log::info('Quiz Random Selection: Found ' . $regularCourses->count() . ' regular courses and ' . $floridaCourses->count() . ' Florida courses');

            $selectedCourse = null;
            $quizData = [];

            if ($request->has('course_id') && $request->course_id) {
                // Parse course_id to determine table and actual ID
                $courseInfo = $this->parseCourseId($request->course_id);
                
                if ($courseInfo['table'] === 'courses') {
                    $selectedCourse = Course::find($courseInfo['id']);
                } else {
                    $selectedCourse = FloridaCourse::find($courseInfo['id']);
                    $selectedCourse->table_type = 'florida_courses';
                }
                
                if ($selectedCourse) {
                    Log::info('Quiz Random Selection: Selected course - ' . $selectedCourse->title);
                    
                    // Get chapters with questions for this course
                    $chapters = Chapter::where('course_id', $courseInfo['id'])
                        ->where('course_table', $courseInfo['table'])
                        ->withCount('questions')
                        ->having('questions_count', '>', 0)
                        ->orderBy('order_index')
                        ->get();

                    Log::info('Quiz Random Selection: Found ' . $chapters->count() . ' chapters with questions');

                    // Get quiz settings for each chapter
                    foreach ($chapters as $chapter) {
                        $totalQuestions = Question::where('chapter_id', $chapter->id)->count();
                        
                        // Get current random selection settings
                        $quizSettings = null;
                        try {
                            $quizSettings = DB::table('quiz_random_settings')
                                ->where('course_id', $courseInfo['id'])
                                ->where('chapter_id', $chapter->id)
                                ->where('course_table', $courseInfo['table'])
                                ->first();
                        } catch (\Exception $e) {
                            Log::warning('Quiz Random Selection: quiz_random_settings table not found - ' . $e->getMessage());
                        }

                        $quizData[] = [
                            'chapter' => $chapter,
                            'total_questions' => $totalQuestions,
                            'questions_to_select' => $quizSettings->questions_to_select ?? $totalQuestions,
                            'use_random_selection' => $quizSettings->use_random_selection ?? false,
                            'settings_id' => $quizSettings->id ?? null,
                        ];
                    }

                    // Also check for final exam
                    $finalExamQuestions = Question::where('course_id', $courseInfo['id'])
                        ->whereNull('chapter_id')
                        ->count();

                    Log::info('Quiz Random Selection: Found ' . $finalExamQuestions . ' final exam questions');

                    if ($finalExamQuestions > 0) {
                        $finalExamSettings = null;
                        try {
                            $finalExamSettings = DB::table('quiz_random_settings')
                                ->where('course_id', $courseInfo['id'])
                                ->whereNull('chapter_id')
                                ->where('course_table', $courseInfo['table'])
                                ->first();
                        } catch (\Exception $e) {
                            Log::warning('Quiz Random Selection: quiz_random_settings table not found for final exam - ' . $e->getMessage());
                        }

                        $quizData[] = [
                            'chapter' => (object) [
                                'id' => null,
                                'title' => 'Final Exam',
                                'order_index' => 999,
                            ],
                            'total_questions' => $finalExamQuestions,
                            'questions_to_select' => $finalExamSettings->questions_to_select ?? $finalExamQuestions,
                            'use_random_selection' => $finalExamSettings->use_random_selection ?? false,
                            'settings_id' => $finalExamSettings->id ?? null,
                        ];
                    }
                }
            }

            return view('admin.quiz-random-selection.index', compact(
                'courses', 
                'selectedCourse', 
                'quizData'
            ));

        } catch (\Exception $e) {
            Log::error('Quiz Random Selection Error: ' . $e->getMessage());
            
            // Return view with empty data and error message
            return view('admin.quiz-random-selection.index', [
                'courses' => collect([]),
                'selectedCourse' => null,
                'quizData' => [],
                'error' => 'Error loading courses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Parse course ID to determine table and actual ID
     */
    private function parseCourseId($courseId)
    {
        if (strpos($courseId, 'florida_') === 0) {
            return [
                'table' => 'florida_courses',
                'id' => (int) str_replace('florida_', '', $courseId)
            ];
        }
        
        return [
            'table' => 'courses',
            'id' => (int) $courseId
        ];
    }

    /**
     * Update quiz random selection settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'chapter_id' => 'nullable|integer|exists:chapters,id',
            'use_random_selection' => 'boolean',
            'questions_to_select' => 'required|integer|min:1|max:500',
        ]);

        try {
            // Insert or update quiz random settings
            DB::table('quiz_random_settings')->updateOrInsert(
                [
                    'course_id' => $validated['course_id'],
                    'chapter_id' => $validated['chapter_id'],
                ],
                [
                    'use_random_selection' => $request->has('use_random_selection'),
                    'questions_to_select' => $validated['questions_to_select'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $chapterName = $validated['chapter_id'] 
                ? Chapter::find($validated['chapter_id'])->title 
                : 'Final Exam';

            return response()->json([
                'success' => true,
                'message' => "Quiz settings updated for {$chapterName}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating quiz random selection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quiz settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quiz statistics for a course
     */
    public function getStats(Request $request)
    {
        $courseId = $request->get('course_id');
        
        if (!$courseId) {
            return response()->json(['error' => 'Course ID required'], 400);
        }

        // Parse course ID to get actual ID and table
        $courseInfo = $this->parseCourseId($courseId);

        $stats = [
            'total_chapters_with_quizzes' => Chapter::where('course_id', $courseInfo['id'])
                ->where('course_table', $courseInfo['table'])
                ->whereHas('questions')
                ->count(),
            'total_questions' => Question::where('course_id', $courseInfo['id'])->count(),
            'chapters_with_random_selection' => DB::table('quiz_random_settings')
                ->where('course_id', $courseInfo['id'])
                ->where('course_table', $courseInfo['table'])
                ->where('use_random_selection', true)
                ->count(),
        ];

        return response()->json($stats);
    }
}
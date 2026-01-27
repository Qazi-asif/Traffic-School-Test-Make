<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChapterQuizSettingsController extends Controller
{
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

            // Combine and format courses
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

            $selectedCourse = null;
            $chapterSettings = [];

            if ($request->has('course_id') && $request->course_id) {
                $courseInfo = $this->parseCourseId($request->course_id);
                
                if ($courseInfo['table'] === 'courses') {
                    $selectedCourse = Course::find($courseInfo['id']);
                } else {
                    $selectedCourse = FloridaCourse::find($courseInfo['id']);
                    $selectedCourse->table_type = 'florida_courses';
                }
                
                if ($selectedCourse) {
                    // Get chapters with questions for this course
                    $chapters = Chapter::where('course_id', $courseInfo['id'])
                        ->where('course_table', $courseInfo['table'])
                        ->withCount(['questions' => function($query) {
                            $query->select(DB::raw('count(*)'));
                        }])
                        ->having('questions_count', '>', 0)
                        ->orderBy('order_index')
                        ->get();

                    foreach ($chapters as $chapter) {
                        // Get total questions available
                        $totalQuestions = DB::table('chapter_questions')
                            ->where('chapter_id', $chapter->id)
                            ->count();

                        // If no questions in chapter_questions, try questions table
                        if ($totalQuestions == 0) {
                            $totalQuestions = DB::table('questions')
                                ->where('chapter_id', $chapter->id)
                                ->count();
                        }

                        // Get current settings
                        $settings = DB::table('chapter_quiz_settings')
                            ->where('course_id', $courseInfo['id'])
                            ->where('chapter_id', $chapter->id)
                            ->where('course_table', $courseInfo['table'])
                            ->first();

                        $chapterSettings[] = [
                            'chapter' => $chapter,
                            'course_id' => $courseInfo['id'],
                            'course_table' => $courseInfo['table'],
                            'total_questions' => $totalQuestions,
                            'questions_to_select' => $settings->questions_to_select ?? $totalQuestions,
                            'use_random_selection' => $settings->use_random_selection ?? false,
                        ];
                    }
                }
            }

            return view('admin.chapter-quiz-settings.index', compact(
                'courses', 
                'selectedCourse', 
                'chapterSettings'
            ));

        } catch (\Exception $e) {
            Log::error('Chapter Quiz Settings Error: ' . $e->getMessage());
            
            return view('admin.chapter-quiz-settings.index', [
                'courses' => collect([]),
                'selectedCourse' => null,
                'chapterSettings' => [],
                'error' => 'Error loading courses: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'course_table' => 'required|string|in:courses,florida_courses',
            'questions_to_select' => 'required|integer|min:1|max:500',
            'use_random_selection' => 'boolean',
        ]);

        try {
            // Get total questions available
            $totalQuestions = DB::table('chapter_questions')
                ->where('chapter_id', $validated['chapter_id'])
                ->count();

            // If no questions in chapter_questions, try questions table
            if ($totalQuestions == 0) {
                $totalQuestions = DB::table('questions')
                    ->where('chapter_id', $validated['chapter_id'])
                    ->count();
            }

            // Insert or update settings
            DB::table('chapter_quiz_settings')->updateOrInsert(
                [
                    'course_id' => $validated['course_id'],
                    'chapter_id' => $validated['chapter_id'],
                    'course_table' => $validated['course_table'],
                ],
                [
                    'questions_to_select' => $validated['questions_to_select'],
                    'total_questions_in_pool' => $totalQuestions,
                    'use_random_selection' => $request->has('use_random_selection'),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $chapterName = Chapter::find($validated['chapter_id'])->title ?? 'Chapter';

            return response()->json([
                'success' => true,
                'message' => "Quiz settings updated for {$chapterName} - now selecting {$validated['questions_to_select']} questions from {$totalQuestions} available"
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating chapter quiz settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

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
}
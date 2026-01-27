<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'course']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $course = Course::findOrFail($request->course_id);

        // Check if user is already enrolled
        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['error' => 'Already enrolled in this course'], 400);
        }

        $enrollment = UserCourseEnrollment::create([
            'user_id' => auth()->id(),
            'course_id' => $request->course_id,
            'amount_paid' => $course->price,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'enrolled_at' => now(),
        ]);

        return response()->json($enrollment->load('course'), 201);
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        return response()->json($enrollment->load(['user', 'course', 'progress', 'quizAttempts']));
    }

    public function update(Request $request, UserCourseEnrollment $enrollment)
    {
        $validated = $request->validate([
            'payment_status' => 'in:pending,paid,failed,refunded',
            'status' => 'in:active,completed,expired,cancelled',
            'court_date' => 'nullable|date',
        ]);

        $enrollment->update($validated);

        return response()->json($enrollment);
    }

    public function destroy(UserCourseEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Enrollment cancelled successfully']);
    }

    public function myEnrollments()
    {
        $enrollments = UserCourseEnrollment::with(['course', 'progress'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($enrollments);
    }

    // Web-specific methods for session authentication
    public function storeWeb(Request $request)
    {
        $courseId = $request->course_id;
        $course = null;
        $realCourseId = null;

        // Handle prefixed course IDs
        if (str_starts_with($courseId, 'florida_')) {
            $realCourseId = str_replace('florida_', '', $courseId);
            $course = DB::table('florida_courses')->where('id', $realCourseId)->first();
        } elseif (str_starts_with($courseId, 'courses_')) {
            $realCourseId = str_replace('courses_', '', $courseId);
            $course = DB::table('courses')->where('id', $realCourseId)->first();
        } else {
            // Fallback for numeric IDs - try courses table first
            $course = DB::table('courses')->where('id', $courseId)->first();
            $realCourseId = $courseId;

            if (! $course) {
                $course = DB::table('florida_courses')->where('id', $courseId)->first();
            }
        }

        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        // Determine course table
        $courseTable = 'courses';
        if (str_starts_with($courseId, 'florida_') || DB::table('florida_courses')->where('id', $realCourseId)->exists()) {
            $courseTable = 'florida_courses';
        }

        // Check if user is already enrolled
        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $realCourseId)
            ->where('course_table', $courseTable)
            ->first();

        if ($existingEnrollment) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Already enrolled in this course'], 400);
            }

            return redirect()->back()->with('error', 'Already enrolled in this course');
        }

        // Get user's personal information to copy to enrollment
        $user = auth()->user();

        $enrollment = UserCourseEnrollment::create([
            'user_id' => auth()->id(),
            'course_id' => $realCourseId,
            'course_table' => $courseTable,
            'amount_paid' => $course->price ?? 0,
            'payment_status' => 'pending',
            
            // Copy citation and court information from user profile
            'citation_number' => $request->citation_number ?? $user->citation_number,
            'case_number' => $user->case_number ?? null,
            'court_date' => $request->court_date ?? null,
            'court_state' => $user->state ?? null,
            'court_county' => $user->court_county ?? null,
            'court_selected' => $user->court_selected ?? null,
            
            'enrolled_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($enrollment, 201);
        }

        return redirect('/my-enrollments')->with('success', 'Successfully enrolled in course!');
    }

    public function myEnrollmentsWeb()
    {
        try {
            $enrollments = UserCourseEnrollment::where('user_id', auth()->id())
                ->where('status', '!=', 'cancelled') // Don't show cancelled enrollments
                ->orderBy('created_at', 'desc')
                ->get();

            $enrollmentsWithCourses = $enrollments->map(function ($enrollment) {
                // Use course_table column to determine which table to fetch from
                $courseTable = $enrollment->course_table ?? 'florida_courses';
                
                $course = DB::table($courseTable)->where('id', $enrollment->course_id)->first();

                // Fallback to other table if not found
                if (! $course) {
                    $fallbackTable = $courseTable === 'courses' ? 'florida_courses' : 'courses';
                    $course = DB::table($fallbackTable)->where('id', $enrollment->course_id)->first();
                    
                    // Update the course_table if we found it in the fallback table
                    if ($course) {
                        $enrollment->update(['course_table' => $fallbackTable]);
                    }
                }

                $enrollmentData = $enrollment->toArray();
                $enrollmentData['course'] = $course;

                // Add computed fields for better UI
                $enrollmentData['can_access_course'] = $enrollment->payment_status === 'paid';
                $enrollmentData['needs_payment'] = in_array($enrollment->payment_status, ['pending', 'failed']);
                $enrollmentData['is_completed'] = $enrollment->status === 'completed';
                $enrollmentData['progress_percentage'] = $enrollment->progress_percentage ?? 0;

                return $enrollmentData;
            });

            return response()->json($enrollmentsWithCourses);
            
        } catch (\Exception $e) {
            \Log::error('Error in myEnrollmentsWeb: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Unable to load enrollments'], 500);
        }
    }

    public function checkEnrollment(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer',
            'table' => 'required|in:courses,florida_courses'
        ]);

        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->where('course_table', $request->table)
            ->first();

        return response()->json([
            'already_enrolled' => $existingEnrollment !== null,
            'enrollment_id' => $existingEnrollment ? $existingEnrollment->id : null
        ]);
    }

    public function showWeb(UserCourseEnrollment $enrollment)
    {
        \Log::info('=== showWeb START ===', ['enrollment_id' => $enrollment->id]);
        
        // Auto-fix: Check if final exam is passed but not marked complete
        $this->autoFixFinalExamProgress($enrollment);
        
        // Enable query logging
        \DB::enableQueryLog();
        
        // Ensure user can only access their own enrollments
        if ($enrollment->user_id !== auth()->id()) {
            \Log::error('Unauthorized access attempt', ['enrollment_id' => $enrollment->id, 'user_id' => auth()->id(), 'enrollment_user_id' => $enrollment->user_id]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Cache course data for 10 minutes to improve performance
        $cacheKey = "enrollment_course_data_{$enrollment->id}_{$enrollment->course_id}_{$enrollment->course_table}";
        \Log::info('Cache key', ['key' => $cacheKey]);
        
        $courseData = cache()->remember($cacheKey, 600, function () use ($enrollment) {
            \Log::info('Cache miss, fetching course data', ['course_id' => $enrollment->course_id, 'course_table' => $enrollment->course_table]);
            
            $courseTable = $enrollment->course_table ?? 'courses';
            \Log::info('Course table determined', ['table' => $courseTable]);
            
            // Use Eloquent for better performance and relationships
            if ($courseTable === 'florida_courses') {
                \Log::info('Loading florida_courses');
                $course = \App\Models\FloridaCourse::with(['chapters' => function($query) {
                    \Log::info('Loading chapters for florida_courses');
                    $query->orderBy('order_index')->select('id', 'course_id', 'title', 'order_index', 'content', 'duration', 'video_url');
                }])->find($enrollment->course_id);
                \Log::info('Florida course loaded', ['course' => $course ? 'found' : 'not found', 'chapters_count' => $course ? $course->chapters->count() : 0]);
                
                // Log the query
                $queries = \DB::getQueryLog();
                foreach ($queries as $query) {
                    \Log::info('Query executed', ['sql' => $query['query'], 'bindings' => $query['bindings']]);
                }
            } else {
                \Log::info('Loading regular courses');
                $course = \App\Models\Course::with(['chapters' => function($query) {
                    \Log::info('Loading chapters for regular courses');
                    $query->orderBy('order_index')->select('id', 'course_id', 'title', 'order_index', 'content', 'duration', 'video_url');
                }])->find($enrollment->course_id);
                \Log::info('Regular course loaded', ['course' => $course ? 'found' : 'not found', 'chapters_count' => $course ? $course->chapters->count() : 0]);
                
                // Log the query
                $queries = \DB::getQueryLog();
                foreach ($queries as $query) {
                    \Log::info('Query executed', ['sql' => $query['query'], 'bindings' => $query['bindings']]);
                }
            }
            
            return $course;
        });
        
        if (!$courseData) {
            \Log::error('Course data not found', ['course_id' => $enrollment->course_id, 'course_table' => $enrollment->course_table]);
            return response()->json(['error' => 'Course not found'], 404);
        }
        
        \Log::info('Course data retrieved', ['course_id' => $courseData->id, 'chapters' => $courseData->chapters ? $courseData->chapters->count() : 0]);
        
        // Add is_completed status to chapters based on user progress
        if ($courseData->chapters) {
            $progress = \App\Models\UserCourseProgress::where('enrollment_id', $enrollment->id)->get();
            \Log::info("Progress records found: {$progress->count()}");
            
            $courseData->chapters = $courseData->chapters->map(function ($chapter) use ($progress) {
                $chapterProgress = $progress->firstWhere('chapter_id', $chapter->id);
                $chapter->is_completed = $chapterProgress && $chapterProgress->is_completed ? true : false;
                $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
                return $chapter;
            });
        }
        
        $enrollmentData = $enrollment->toArray();
        $enrollmentData['course'] = $courseData->toArray();
        
        \Log::info('Enrollment data prepared', ['enrollment_id' => $enrollment->id, 'course_id' => $courseData->id]);
        
        // Ensure strict_duration_enabled is included
        if (!isset($enrollmentData['course']['strict_duration_enabled'])) {
            $enrollmentData['course']['strict_duration_enabled'] = $courseData->strict_duration_enabled ?? false;
        }

        \Log::info('=== showWeb END ===', ['enrollment_id' => $enrollment->id]);
        
        // Disable query logging
        \DB::disableQueryLog();
        
        return response()->json($enrollmentData);
    }

    public function indexWeb(Request $request)
    {
        try {
            $query = UserCourseEnrollment::with(['user', 'course']);

            // Search functionality
            if ($request->search) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            }

            // Filter by status
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Filter by payment status
            if ($request->payment_status) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by specific user
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // Order by most recent first
            $query->orderBy('created_at', 'desc');

            // Allow custom per_page parameter, default to 15, max 100
            $perPage = min($request->get('per_page', 15), 100);
            $result = $query->paginate($perPage);

            // Transform the data to include course information from the correct table
            $transformedData = $result->getCollection()->map(function ($enrollment) {
                $enrollmentData = $enrollment->toArray();
                
                // Get course data from the correct table
                $courseTable = $enrollment->course_table ?? 'florida_courses';
                
                if ($courseTable === 'florida_courses') {
                    $course = \App\Models\FloridaCourse::find($enrollment->course_id);
                } else {
                    $course = \App\Models\Course::find($enrollment->course_id);
                }
                
                $enrollmentData['course'] = $course ? $course->toArray() : null;
                $enrollmentData['course_table'] = $courseTable;
                
                return $enrollmentData;
            });

            return response()->json([
                'data' => $transformedData,
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'total' => $result->total(),
                'per_page' => $result->perPage(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('EnrollmentController indexWeb error: '.$e->getMessage());

            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
                'error' => 'Failed to load enrollments',
            ], 500);
        }
    }

    public function cancelEnrollmentWeb(UserCourseEnrollment $enrollment)
    {
        try {
            // Ensure user can only cancel their own enrollments
            if ($enrollment->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Only allow cancellation of pending payments
            if ($enrollment->payment_status === 'paid') {
                return response()->json(['error' => 'Cannot cancel paid enrollments'], 400);
            }

            // Update enrollment status to cancelled (keep payment_status as is since 'cancelled' is not valid)
            $enrollment->update([
                'status' => 'cancelled'
                // Don't update payment_status to 'cancelled' as it's not a valid enum value
            ]);

            return response()->json(['message' => 'Enrollment cancelled successfully']);
            
        } catch (\Exception $e) {
            \Log::error('Error cancelling enrollment: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'enrollment_id' => $enrollment->id
            ]);
            
            return response()->json(['error' => 'Unable to cancel enrollment'], 500);
        }
    }

    /**
     * Get feedback for a student's enrollment
     */
    public function getFeedback($enrollmentId)
    {
        try {
            // Get enrollment and verify ownership
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$enrollment) {
                return response()->json(['error' => 'Enrollment not found'], 404);
            }

            $feedback = [
                'overall_feedback' => null,
                'quiz_feedback' => [],
                'free_response_feedback' => [],
            ];

            // Get overall feedback
            try {
                $overallFeedback = DB::table('student_feedback')
                    ->where('enrollment_id', $enrollmentId)
                    ->first();

                if ($overallFeedback) {
                    $feedback['overall_feedback'] = [
                        'instructor_feedback' => $overallFeedback->instructor_feedback,
                        'status' => $overallFeedback->status,
                        'feedback_given_at' => $overallFeedback->feedback_given_at,
                        'can_take_final_exam' => $overallFeedback->can_take_final_exam,
                    ];
                }
            } catch (\Exception $e) {
                \Log::info('Student feedback table not found');
            }

            // Get quiz feedback
            try {
                $quizFeedback = DB::table('quiz_feedback as qf')
                    ->leftJoin('chapters as c', 'qf.chapter_id', '=', 'c.id')
                    ->select(
                        'qf.*',
                        'c.title as chapter_title'
                    )
                    ->where('qf.enrollment_id', $enrollmentId)
                    ->whereNotNull('qf.instructor_feedback')
                    ->get();

                $feedback['quiz_feedback'] = $quizFeedback->map(function ($quiz) {
                    return [
                        'chapter_id' => $quiz->chapter_id,
                        'chapter_title' => $quiz->chapter_title,
                        'score' => $quiz->score,
                        'correct_answers' => $quiz->correct_answers,
                        'total_questions' => $quiz->total_questions,
                        'instructor_feedback' => $quiz->instructor_feedback,
                        'status' => $quiz->status,
                        'feedback_given_at' => $quiz->feedback_given_at,
                    ];
                })->toArray();
            } catch (\Exception $e) {
                \Log::info('Quiz feedback table not found');
            }

            // Get free response feedback
            try {
                $freeResponseFeedback = DB::table('free_response_answers as fra')
                    ->leftJoin('free_response_questions as frq', 'fra.question_id', '=', 'frq.id')
                    ->select(
                        'fra.id',
                        'fra.question_id',
                        'fra.answer_text',
                        'fra.score',
                        'fra.feedback',
                        'fra.status',
                        'frq.question_text',
                        'frq.points'
                    )
                    ->where('fra.user_id', $enrollment->user_id)
                    ->whereNotNull('fra.feedback')
                    ->get();

                $feedback['free_response_feedback'] = $freeResponseFeedback->map(function ($answer) {
                    return [
                        'question_id' => $answer->question_id,
                        'question_text' => $answer->question_text,
                        'answer_text' => $answer->answer_text,
                        'score' => $answer->score,
                        'points' => $answer->points,
                        'feedback' => $answer->feedback,
                        'status' => $answer->status,
                    ];
                })->toArray();
            } catch (\Exception $e) {
                \Log::info('Free response answers table not found');
            }

            return response()->json($feedback);

        } catch (\Exception $e) {
            \Log::error('Error loading feedback: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load feedback'], 500);
        }
    }
    
    /**
     * Auto-fix enrollments with passed final exams but incomplete status
     */
    private function autoFixFinalExamProgress(UserCourseEnrollment $enrollment)
    {
        try {
            // Check if already completed
            if ($enrollment->status === 'completed' && $enrollment->progress_percentage == 100) {
                return;
            }
            
            // Check for passed final exam
            $passedResult = \DB::table('final_exam_results')
                ->where('enrollment_id', $enrollment->id)
                ->where('passed', true)
                ->where('score', '>=', 70)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$passedResult) {
                return;
            }
            
            // Check if needs fixing
            if (!$enrollment->final_exam_completed || $enrollment->status !== 'completed' || $enrollment->progress_percentage < 100) {
                \Log::info('Auto-fixing final exam progress', ['enrollment_id' => $enrollment->id]);
                
                // Update flags
                $enrollment->final_exam_completed = true;
                $enrollment->final_exam_result_id = $passedResult->id;
                $enrollment->save();
                
                // Recalculate progress
                $progressController = new \App\Http\Controllers\ProgressController();
                $progressController->updateEnrollmentProgressPublic($enrollment);
                
                $enrollment->refresh();
                
                \Log::info('Auto-fix completed', [
                    'enrollment_id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'progress' => $enrollment->progress_percentage
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Auto-fix error', ['enrollment_id' => $enrollment->id, 'error' => $e->getMessage()]);
        }
    }
}

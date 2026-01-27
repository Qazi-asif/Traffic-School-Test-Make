<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentFeedbackController extends Controller
{
    /**
     * Display list of students needing feedback
     * ONLY for Florida 12 Hour course with free response quizzes
     */
    public function index(Request $request)
    {
        // IMPORTANT: Only Florida 12 Hour course requires free response feedback
        // Other courses with multiple choice quizzes do NOT need feedback to continue
        
        $query = DB::table('user_course_enrollments as e')
            ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
            ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
            ->select(
                'e.id as enrollment_id',
                'e.user_id',
                'e.course_id',
                'e.progress_percentage',
                'e.quiz_average',
                'e.created_at as enrolled_at',
                'u.first_name',
                'u.last_name',
                'u.email',
                DB::raw('COALESCE(c.title, fc.title) as course_title'),
                DB::raw('COALESCE(c.state_code, fc.state_code) as course_state_code'),
                DB::raw('NULL as feedback_status'),
                DB::raw('NULL as feedback_given_at'),
                DB::raw('NULL as can_take_final_exam')
            )
            ->where('e.progress_percentage', '>=', 80)
            // FILTER: Only show Florida 12 Hour course
            ->where(function($q) {
                $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                  ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
            });

        // Try to join with student_feedback if table exists
        try {
            $query = DB::table('user_course_enrollments as e')
                ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
                ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
                ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
                ->leftJoin('student_feedback as sf', 'e.id', '=', 'sf.enrollment_id')
                ->select(
                    'e.id as enrollment_id',
                    'e.user_id',
                    'e.course_id',
                    'e.progress_percentage',
                    'e.quiz_average',
                    'e.created_at as enrolled_at',
                    'u.first_name',
                    'u.last_name',
                    'u.email',
                    DB::raw('COALESCE(c.title, fc.title) as course_title'),
                    DB::raw('COALESCE(c.state_code, fc.state_code) as course_state_code'),
                    'sf.status as feedback_status',
                    'sf.feedback_given_at',
                    'sf.can_take_final_exam'
                )
                ->where('e.progress_percentage', '>=', 80)
                // FILTER: Only show Florida 12 Hour course
                ->where(function($q) {
                    $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                      ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
                });
        } catch (\Exception $e) {
            // Student feedback table doesn't exist yet
            \Log::info('Student feedback table not found, using basic query');
        }

        // Filter by feedback status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'pending') {
                try {
                    $query->whereNull('sf.id');
                } catch (\Exception $e) {
                    // Can't filter by feedback status if table doesn't exist
                }
            } else {
                try {
                    $query->where('sf.status', $request->status);
                } catch (\Exception $e) {
                    // Can't filter by feedback status if table doesn't exist
                }
            }
        }

        // Search by student name or email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('u.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('u.email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by course
        if ($request->has('course_id') && !empty($request->course_id)) {
            $query->where('e.course_id', $request->course_id);
        }

        $students = $query->orderBy('e.created_at', 'desc')->paginate(20);

        // Get courses for filter dropdown
        $courses = DB::table('courses')
            ->select('id', 'title', 'state_code')
            ->union(
                DB::table('florida_courses')
                    ->select('id', 'title', 'state_code')
            )
            ->orderBy('title')
            ->get();

        // Get statistics - ONLY for Florida 12 Hour course
        $stats = [
            'pending_feedback' => 0,
            'completed_feedback' => 0,
            'approved_students' => 0,
            'needs_improvement' => 0,
        ];

        try {
            $stats = [
                'pending_feedback' => DB::table('user_course_enrollments as e')
                    ->leftJoin('student_feedback as sf', 'e.id', '=', 'sf.enrollment_id')
                    ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
                    ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
                    ->where('e.progress_percentage', '>=', 80)
                    ->where(function($q) {
                        $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                          ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
                    })
                    ->whereNull('sf.id')
                    ->count(),
                'completed_feedback' => DB::table('student_feedback as sf')
                    ->join('user_course_enrollments as e', 'sf.enrollment_id', '=', 'e.id')
                    ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
                    ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
                    ->where(function($q) {
                        $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                          ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
                    })
                    ->count(),
                'approved_students' => DB::table('student_feedback as sf')
                    ->join('user_course_enrollments as e', 'sf.enrollment_id', '=', 'e.id')
                    ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
                    ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
                    ->where('sf.status', 'approved')
                    ->where(function($q) {
                        $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                          ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
                    })
                    ->count(),
                'needs_improvement' => DB::table('student_feedback as sf')
                    ->join('user_course_enrollments as e', 'sf.enrollment_id', '=', 'e.id')
                    ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
                    ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
                    ->where('sf.status', 'needs_improvement')
                    ->where(function($q) {
                        $q->where('c.title', 'LIKE', '%Florida%12%Hour%')
                          ->orWhere('fc.title', 'LIKE', '%Florida%12%Hour%');
                    })
                    ->count(),
            ];
        } catch (\Exception $e) {
            // Tables don't exist yet
            \Log::info('Student feedback tables not found, using default stats');
        }

        return view('admin.student-feedback.index', compact('students', 'courses', 'stats'));
    }

    /**
     * Show detailed student review page
     */
    public function show($enrollmentId)
    {
        // Get enrollment with course info and user data
        $enrollment = DB::table('user_course_enrollments as e')
            ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
            ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
            ->select(
                'e.*',
                'u.first_name',
                'u.last_name',
                'u.email',
                DB::raw('COALESCE(c.title, fc.title) as course_title'),
                DB::raw('COALESCE(c.state_code, fc.state_code) as course_state_code')
            )
            ->where('e.id', $enrollmentId)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found.');
        }

        // Get existing feedback
        $feedback = null;
        try {
            $feedback = DB::table('student_feedback')
                ->where('enrollment_id', $enrollmentId)
                ->first();
        } catch (\Exception $e) {
            // Student feedback table doesn't exist yet
            \Log::info('Student feedback table not found');
        }

        // Get chapter quiz results with detailed question data
        $chapterQuizzes = DB::table('chapter_quiz_results as cqr')
            ->leftJoin('chapters as c', 'cqr.chapter_id', '=', 'c.id')
            ->select(
                'cqr.*', 
                'c.title as chapter_title',
                DB::raw('NULL as quiz_feedback'),
                DB::raw('NULL as feedback_status'),
                DB::raw('NULL as quiz_feedback_id'),
                DB::raw('cqr.percentage as score'), // Use percentage as score
                DB::raw('cqr.correct_answers'),
                DB::raw('cqr.total_questions')
            )
            ->where('cqr.user_id', $enrollment->user_id) // Use user_id instead of enrollment_id
            ->orderBy('cqr.chapter_id')
            ->get();

        // Try to get quiz feedback if the table exists
        try {
            $quizFeedbackData = DB::table('quiz_feedback as qf')
                ->select('qf.*')
                ->where('qf.enrollment_id', $enrollmentId)
                ->get()
                ->keyBy('chapter_id');

            // Merge quiz feedback data with chapter quizzes
            foreach ($chapterQuizzes as $quiz) {
                if (isset($quizFeedbackData[$quiz->chapter_id])) {
                    $feedback = $quizFeedbackData[$quiz->chapter_id];
                    $quiz->quiz_feedback = $feedback->instructor_feedback;
                    $quiz->feedback_status = $feedback->status;
                    $quiz->quiz_feedback_id = $feedback->id;
                }
            }
        } catch (\Exception $e) {
            // Quiz feedback table doesn't exist yet, that's okay
            \Log::info('Quiz feedback table not found, using default values');
        }

        // Get detailed quiz attempts with questions and answers
        $quizDetails = [];
        
        foreach ($chapterQuizzes as $quiz) {
            try {
                // Get quiz attempts for this chapter
                $quizAttempts = DB::table('quiz_attempts')
                    ->where('enrollment_id', $enrollmentId)
                    ->where('chapter_id', $quiz->chapter_id)
                    ->orderBy('created_at', 'desc')
                    ->first(); // Get the most recent attempt

                if ($quizAttempts && $quizAttempts->questions_attempted) {
                    $questionsData = json_decode($quizAttempts->questions_attempted, true);
                    $detailedQuestions = [];

                    if (is_array($questionsData)) {
                        foreach ($questionsData as $questionData) {
                            // Get the full question details
                            $questionId = $questionData['question_id'] ?? $questionData['id'] ?? null;
                            $question = DB::table('questions')
                                ->where('id', $questionId)
                                ->first();

                            if ($question) {
                                $detailedQuestions[] = (object) [
                                    'id' => $question->id,
                                    'question_text' => $question->question_text,
                                    'option_a' => $question->option_a,
                                    'option_b' => $question->option_b,
                                    'option_c' => $question->option_c,
                                    'option_d' => $question->option_d,
                                    'option_e' => $question->option_e,
                                    'correct_answer' => $question->correct_answer,
                                    'explanation' => $question->explanation,
                                    'student_answer' => $questionData['selected_answer'] ?? $questionData['answer'] ?? 'Not answered',
                                    'is_correct' => ($questionData['selected_answer'] ?? $questionData['answer'] ?? '') === $question->correct_answer,
                                    'time_spent' => $questionData['time_spent'] ?? 0
                                ];
                            }
                        }
                    }
                    
                    $quizDetails[$quiz->chapter_id] = collect($detailedQuestions);
                } else {
                    $quizDetails[$quiz->chapter_id] = collect();
                }
            } catch (\Exception $e) {
                $quizDetails[$quiz->chapter_id] = collect();
            }
        }

        // Get free response answers
        $freeResponseAnswers = DB::table('free_response_answers as fra')
            ->leftJoin('free_response_questions as frq', 'fra.question_id', '=', 'frq.id')
            ->select(
                'fra.*',
                'frq.question_text',
                'frq.sample_answer',
                'frq.points',
                DB::raw('LENGTH(fra.answer_text) - LENGTH(REPLACE(fra.answer_text, " ", "")) + 1 as word_count')
            )
            ->where('fra.user_id', $enrollment->user_id)
            ->whereExists(function($query) use ($enrollment) {
                $query->select(DB::raw(1))
                    ->from('free_response_questions')
                    ->whereRaw('free_response_questions.id = fra.question_id')
                    ->where('course_id', $enrollment->course_id);
            })
            ->get();

        return view('admin.student-feedback.show', compact(
            'enrollment',
            'feedback',
            'chapterQuizzes',
            'quizDetails',
            'freeResponseAnswers'
        ));
    }

    /**
     * Store instructor feedback
     */
    public function storeFeedback(Request $request, $enrollmentId)
    {
        $request->validate([
            'instructor_feedback' => 'required|string|max:5000',
            'allow_final_exam' => 'boolean',
            'requires_improvement' => 'boolean',
        ]);

        $status = 'pending';
        $canTakeFinalExam = false;

        if ($request->has('allow_final_exam') && $request->allow_final_exam) {
            $status = 'approved';
            $canTakeFinalExam = true;
        } elseif ($request->has('requires_improvement') && $request->requires_improvement) {
            $status = 'needs_improvement';
            $canTakeFinalExam = false;
        }

        try {
            // Update or create feedback record
            DB::table('student_feedback')->updateOrInsert(
                ['enrollment_id' => $enrollmentId],
                [
                    'instructor_feedback' => $request->instructor_feedback,
                    'status' => $status,
                    'can_take_final_exam' => $canTakeFinalExam,
                    'feedback_given_at' => now(),
                    'instructor_id' => Auth::id(),
                    'updated_at' => now(),
                ]
            );

            // Update enrollment record
            DB::table('user_course_enrollments')
                ->where('id', $enrollmentId)
                ->update([
                    'can_take_final_exam' => $canTakeFinalExam,
                    'quiz_feedback_completed' => true,
                    'updated_at' => now(),
                ]);

            return redirect()->route('admin.student-feedback.index')
                ->with('success', 'Feedback submitted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error storing feedback: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database tables not ready. Please run migrations first: php artisan migrate');
        }
    }

    /**
     * Grade a free response answer
     */
    public function gradeFreeResponse(Request $request, $answerId)
    {
        $request->validate([
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $updated = DB::table('free_response_answers')
            ->where('id', $answerId)
            ->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'status' => 'graded',
                'updated_at' => now(),
            ]);

        if ($updated) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Failed to update grade']);
    }

    /**
     * Store quiz feedback for multiple choice questions
     */
    public function storeQuizFeedback(Request $request, $enrollmentId, $chapterId)
    {
        $request->validate([
            'quiz_feedback' => 'required|string|max:2000',
            'status' => 'required|in:reviewed,needs_improvement,approved',
            'question_feedback' => 'array',
            'question_feedback.*.question_id' => 'required|integer',
            'question_feedback.*.feedback' => 'nullable|string|max:500',
        ]);

        try {
            // Get quiz result - need to find it by user_id and chapter_id since that's the table structure
            $quizResult = DB::table('chapter_quiz_results as cqr')
                ->join('user_course_enrollments as e', 'cqr.user_id', '=', 'e.user_id')
                ->where('e.id', $enrollmentId)
                ->where('cqr.chapter_id', $chapterId)
                ->select('cqr.*')
                ->first();

            if (!$quizResult) {
                return response()->json(['success' => false, 'error' => 'Quiz result not found']);
            }

            // Create or update quiz feedback
            $quizFeedbackId = DB::table('quiz_feedback')->updateOrInsert(
                [
                    'enrollment_id' => $enrollmentId,
                    'chapter_id' => $chapterId,
                ],
                [
                    'score' => $quizResult->percentage, // Use percentage as score
                    'correct_answers' => $quizResult->correct_answers,
                    'total_questions' => $quizResult->total_questions,
                    'instructor_feedback' => $request->quiz_feedback,
                    'status' => $request->status,
                    'feedback_given_at' => now(),
                    'instructor_id' => Auth::id(),
                    'updated_at' => now(),
                ]
            );

            // Get the quiz feedback ID
            if (!is_numeric($quizFeedbackId)) {
                $quizFeedbackRecord = DB::table('quiz_feedback')
                    ->where('enrollment_id', $enrollmentId)
                    ->where('chapter_id', $chapterId)
                    ->first();
                $quizFeedbackId = $quizFeedbackRecord->id;
            }

            // Store individual question feedback
            if ($request->has('question_feedback')) {
                foreach ($request->question_feedback as $questionFeedback) {
                    if (!empty($questionFeedback['feedback'])) {
                        DB::table('quiz_question_feedback')->updateOrInsert(
                            [
                                'quiz_feedback_id' => $quizFeedbackId,
                                'question_id' => $questionFeedback['question_id'],
                            ],
                            [
                                'question_feedback' => $questionFeedback['feedback'],
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error storing quiz feedback: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database tables not ready. Please run migrations first.']);
        }
    }

    /**
     * Save feedback for individual question
     */
    public function saveQuestionFeedback(Request $request, $questionId)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        try {
            // For now, we'll store this in a simple way
            // In a full implementation, you might want a separate table for question feedback
            \Log::info("Question feedback for question {$questionId}: " . $request->feedback);
            
            return response()->json(['success' => true, 'message' => 'Question feedback saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Error saving question feedback: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to save question feedback']);
        }
    }
}
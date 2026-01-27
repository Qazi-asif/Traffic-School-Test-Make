<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreeResponseQuestion;
use App\Models\FreeResponseAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreeResponseQuizController extends Controller
{
    /**
     * Display a listing of free response quiz questions
     */
    public function index(Request $request)
    {
        $courseId = $request->get('course_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);

        // Get available courses from both tables
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        // Combine both course collections
        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // If no course selected, default to first available course
        if (!$courseId && $courses->isNotEmpty()) {
            $courseId = $courses->first()->id;
        }

        // Build query for free response questions with course information
        $query = DB::table('free_response_questions')
            ->leftJoin('courses', 'free_response_questions.course_id', '=', 'courses.id')
            ->leftJoin('florida_courses', 'free_response_questions.course_id', '=', 'florida_courses.id')
            ->select(
                'free_response_questions.*',
                DB::raw('COALESCE(courses.title, florida_courses.title) as course_title'),
                DB::raw('COALESCE(courses.state_code, florida_courses.state_code) as course_state_code'),
                DB::raw('CASE 
                    WHEN courses.id IS NOT NULL THEN "Regular" 
                    WHEN florida_courses.id IS NOT NULL THEN "Florida" 
                    ELSE "Unknown" 
                END as course_type')
            );
        
        if ($courseId) {
            $query->where('free_response_questions.course_id', $courseId);
        }

        if ($search) {
            $query->where('free_response_questions.question_text', 'LIKE', "%{$search}%");
        }

        $questions = $query->orderBy('free_response_questions.order_index')
            ->paginate($perPage);

        $totalQuestions = DB::table('free_response_questions')
            ->when($courseId, function($q) use ($courseId) {
                return $q->where('course_id', $courseId);
            })
            ->count();

        // Get total questions across all courses
        $totalAllQuestions = DB::table('free_response_questions')->count();

        return view('admin.free-response-quiz.index', compact(
            'questions', 
            'courses', 
            'courseId', 
            'search', 
            'totalQuestions',
            'totalAllQuestions',
            'perPage'
        ));
    }

    /**
     * Show create form for free response question
     */
    public function create(Request $request)
    {
        $courseId = $request->get('course_id');

        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // Get available placements for the selected course
        $placements = [];
        if ($courseId) {
            $placements = DB::table('free_response_quiz_placements')
                ->where('course_id', $courseId)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();
        }

        // Get next order index for the course
        $nextOrder = DB::table('free_response_questions')
            ->where('course_id', $courseId)
            ->max('order_index') + 1 ?? 1;

        return view('admin.free-response-quiz.create', compact('courses', 'courseId', 'nextOrder', 'placements'));
    }

    /**
     * Store a new free response question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'placement_id' => 'required|integer|exists:free_response_quiz_placements,id',
            'question_text' => 'required|string|max:1000',
            'order_index' => 'required|integer|min:1',
            'sample_answer' => 'nullable|string|max:2000',
            'grading_rubric' => 'nullable|string|max:2000',
            'points' => 'nullable|integer|min:1|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            // Set defaults for optional fields
            $validated['points'] = $validated['points'] ?? 5;
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $question = FreeResponseQuestion::create($validated);

            return redirect()->route('admin.free-response-quiz.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Free response question created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating free response question: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create question: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form for free response question
     */
    public function edit($id)
    {
        $question = FreeResponseQuestion::find($id);

        if (!$question) {
            return redirect()->route('admin.free-response-quiz.index')
                ->withErrors(['error' => 'Question not found']);
        }

        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        return view('admin.free-response-quiz.edit', compact('question', 'courses'));
    }

    /**
     * Update a free response question
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'question_text' => 'required|string|max:1000',
            'order_index' => 'required|integer|min:1',
            'sample_answer' => 'nullable|string|max:2000',
            'grading_rubric' => 'nullable|string|max:2000',
            'points' => 'nullable|integer|min:1|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $question = FreeResponseQuestion::findOrFail($id);
            
            // Set defaults for optional fields
            $validated['points'] = $validated['points'] ?? 5;
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $question->update($validated);

            return redirect()->route('admin.free-response-quiz.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Free response question updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating free response question: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update question: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a free response question
     */
    public function destroy($id)
    {
        try {
            $question = FreeResponseQuestion::findOrFail($id);
            $question->delete();
            
            return redirect()->route('admin.free-response-quiz.index')
                ->with('success', 'Free response question deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting free response question: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete question: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form for free response answer
     */
    public function editAnswer($id)
    {
        $answer = DB::table('free_response_answers')
            ->join('free_response_questions', 'free_response_answers.question_id', '=', 'free_response_questions.id')
            ->join('users', 'free_response_answers.user_id', '=', 'users.id')
            ->select(
                'free_response_answers.*',
                'free_response_questions.question_text',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name')
            )
            ->find($id);

        if (!$answer) {
            return redirect()->route('admin.free-response-quiz.submissions')
                ->withErrors(['error' => 'Answer not found']);
        }

        return view('admin.free-response-quiz.edit-answer', compact('answer'));
    }

    /**
     * Update sample answer for a question
     */
    public function storeSampleAnswer(Request $request, $id)
    {
        $validated = $request->validate([
            'sample_answer' => 'required|string|max:2000',
        ]);

        try {
            DB::table('free_response_questions')
                ->where('id', $id)
                ->update([
                    'sample_answer' => $validated['sample_answer'],
                    'updated_at' => now(),
                ]);

            return back()->with('success', 'Sample answer saved successfully');
        } catch (\Exception $e) {
            Log::error('Error saving sample answer: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to save sample answer: ' . $e->getMessage()]);
        }
    }

    /**
     * Display student submissions for free response questions
     */
    public function submissions(Request $request)
    {
        $courseId = $request->get('course_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);

        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code')
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code')
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        try {
            // Get submissions with user and question details - show ALL by default
            $query = DB::table('free_response_answers')
                ->join('free_response_questions', 'free_response_answers.question_id', '=', 'free_response_questions.id')
                ->join('users', 'free_response_answers.user_id', '=', 'users.id')
                ->join('user_course_enrollments', 'free_response_answers.enrollment_id', '=', 'user_course_enrollments.id')
                ->leftJoin('courses', 'free_response_questions.course_id', '=', 'courses.id')
                ->leftJoin('florida_courses', 'free_response_questions.course_id', '=', 'florida_courses.id')
                ->select(
                    'free_response_answers.*',
                    'free_response_questions.question_text',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name'),
                    'users.email as student_email',
                    'user_course_enrollments.id as enrollment_id',
                    DB::raw('COALESCE(courses.title, florida_courses.title) as course_title'),
                    DB::raw('COALESCE(courses.state_code, florida_courses.state_code) as course_state')
                );

            // Only filter by course if specified
            if ($courseId) {
                $query->where('free_response_questions.course_id', $courseId);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('users.first_name', 'LIKE', "%{$search}%")
                      ->orWhere('users.last_name', 'LIKE', "%{$search}%")
                      ->orWhere('users.email', 'LIKE', "%{$search}%")
                      ->orWhere('free_response_answers.answer_text', 'LIKE', "%{$search}%")
                      ->orWhere(DB::raw('CONCAT(users.first_name, " ", users.last_name)'), 'LIKE', "%{$search}%");
                });
            }

            $submissions = $query->orderBy('free_response_answers.submitted_at', 'desc')
                ->paginate($perPage);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error loading free response submissions: ' . $e->getMessage());
            
            // If tables don't exist, return empty paginated collection
            $submissions = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 
                0, 
                $perPage, 
                1, 
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        return view('admin.free-response-quiz.submissions', compact(
            'submissions',
            'courses',
            'courseId',
            'search',
            'perPage'
        ));
    }

    /**
     * Grade a free response answer
     */
    public function gradeAnswer(Request $request, $answerId)
    {
        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
            'sample_answer' => 'nullable|string|max:2000'
        ]);

        Log::info('gradeAnswer called', [
            'answerId' => $answerId,
            'validated' => $validated,
            'request_all' => $request->all()
        ]);

        try {
            // Get the answer
            $answer = FreeResponseAnswer::find($answerId);
            
            Log::info('Answer found', [
                'answer' => $answer ? $answer->toArray() : 'NOT FOUND',
                'answerId' => $answerId
            ]);
            
            if (!$answer) {
                Log::error('Answer not found', ['answerId' => $answerId]);
                return response()->json(['success' => false, 'error' => 'Answer not found']);
            }
            
            // Update the answer
            $updateResult = $answer->update([
                'score' => $validated['grade'],
                'feedback' => $validated['feedback'],
                'status' => 'graded', // Mark as graded
                'graded_at' => now(),
                'graded_by' => auth()->id(),
            ]);
            
            Log::info('Answer updated', [
                'updateResult' => $updateResult,
                'answerId' => $answerId,
                'score' => $validated['grade']
            ]);

            // If sample_answer is provided, save it to the questions table
            if ($validated['sample_answer']) {
                Log::info('Saving sample answer', [
                    'question_id' => $answer->question_id,
                    'sample_answer' => $validated['sample_answer']
                ]);
                
                $updateQuestionResult = FreeResponseQuestion::where('id', $answer->question_id)
                    ->update([
                        'sample_answer' => $validated['sample_answer'],
                    ]);
                
                Log::info('Sample answer update result', [
                    'updateQuestionResult' => $updateQuestionResult,
                    'question_id' => $answer->question_id
                ]);
            }

            // Check if all answers for this enrollment are now graded
            $this->checkAndNotifyGradingComplete($answer->enrollment_id);

            return response()->json(['success' => true, 'message' => 'Answer graded successfully']);
        } catch (\Exception $e) {
            Log::error('Error grading answer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'answerId' => $answerId
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to grade answer: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit free response answers from course player
     */
    public function submitAnswers(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|integer',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer_text' => 'required|string',
            'answers.*.word_count' => 'required|integer',
        ]);

        try {
            $enrollmentId = $validated['enrollment_id'];
            $answers = $validated['answers'];

            // Verify enrollment belongs to current user
            $enrollment = DB::table('user_course_enrollments')
                ->where('id', $enrollmentId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enrollment not found or access denied'
                ], 403);
            }

            // Process each answer
            foreach ($answers as $answerData) {
                $questionId = $answerData['question_id'];
                $answerText = trim($answerData['answer_text']);
                $wordCount = $answerData['word_count'];

                // Validate word count (50-100 words required, strict limit)
                if ($wordCount < 50) {
                    return response()->json([
                        'success' => false,
                        'message' => "Answer for question {$questionId} must be at least 50 words (current: {$wordCount} words)"
                    ], 400);
                }

                if ($wordCount > 100) {
                    return response()->json([
                        'success' => false,
                        'message' => "Answer for question {$questionId} exceeds 100-word limit (current: {$wordCount} words). Please shorten your answer."
                    ], 400);
                }

                // Check if answer already exists
                $existingAnswer = DB::table('free_response_answers')
                    ->where('user_id', auth()->id())
                    ->where('question_id', $questionId)
                    ->where('enrollment_id', $enrollmentId)
                    ->first();

                if ($existingAnswer) {
                    // Update existing answer
                    DB::table('free_response_answers')
                        ->where('id', $existingAnswer->id)
                        ->update([
                            'answer_text' => $answerText,
                            'word_count' => $wordCount,
                            'submitted_at' => now(),
                            'updated_at' => now(),
                        ]);
                } else {
                    // Create new answer
                    DB::table('free_response_answers')->insert([
                        'user_id' => auth()->id(),
                        'question_id' => $questionId,
                        'enrollment_id' => $enrollmentId,
                        'answer_text' => $answerText,
                        'word_count' => $wordCount,
                        'submitted_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Answers submitted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting free response answers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status of a free response question
     */
    public function toggleActive($id)
    {
        try {
            $question = FreeResponseQuestion::findOrFail($id);
            $question->update(['is_active' => !$question->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Question status updated successfully',
                'is_active' => $question->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling question status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update question status'
            ], 500);
        }
    }

    /**
     * Check if all answers for an enrollment are graded and notify if complete
     */
    private function checkAndNotifyGradingComplete($enrollmentId)
    {
        try {
            // Get all answers for this enrollment
            $answers = DB::table('free_response_answers')
                ->where('enrollment_id', $enrollmentId)
                ->get();

            // Check if all answers are graded
            $allGraded = $answers->every(function($answer) {
                return $answer->status === 'graded' && 
                       (!is_null($answer->feedback) || !is_null($answer->score));
            });

            if ($allGraded) {
                // Update enrollment to allow progression
                DB::table('user_course_enrollments')
                    ->where('id', $enrollmentId)
                    ->update([
                        'free_response_graded' => true,
                        'grading_completed_at' => now(),
                        'updated_at' => now()
                    ]);

                Log::info('All free response answers graded for enrollment', [
                    'enrollment_id' => $enrollmentId,
                    'total_answers' => $answers->count()
                ]);

                // TODO: Send email notification to student that grading is complete
                // $this->sendGradingCompleteNotification($enrollmentId);
            }

        } catch (\Exception $e) {
            Log::error('Error checking grading completion: ' . $e->getMessage());
        }
    }
}

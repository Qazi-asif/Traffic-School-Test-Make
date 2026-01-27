<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentFreeResponseQuizController extends Controller
{
    /**
     * Show free response questions for a course
     */
    public function show(Request $request)
    {
        $enrollmentId = $request->get('enrollment_id');
        
        if (!$enrollmentId) {
            return redirect()->back()->with('error', 'Enrollment ID is required.');
        }

        // Get enrollment and course information
        $enrollment = DB::table('user_course_enrollments')
            ->where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found.');
        }

        // Get course information
        $course = DB::table('courses')->where('id', $enrollment->course_id)->first();
        if (!$course) {
            $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
        }

        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        // Get active free response questions for this course
        $questions = DB::table('free_response_questions')
            ->where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();

        // Get user's existing answers
        $existingAnswers = DB::table('free_response_answers')
            ->where('user_id', Auth::id())
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        return view('free-response-quiz.show', compact(
            'questions', 
            'course', 
            'enrollment', 
            'existingAnswers'
        ));
    }

    /**
     * Submit free response answers
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|integer',
            'answers' => 'required|array',
            'answers.*' => 'required|string|max:1000', // Allow buffer for 100 words
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['answers'] as $questionId => $answerText) {
                // Count words (simple word count)
                $wordCount = str_word_count(trim($answerText));
                
                // Enforce 50-100 word range (strict)
                if ($wordCount < 50) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => "Answer for question {$questionId} must be at least 50 words (current: {$wordCount} words)."
                    ], 422);
                }
                
                if ($wordCount > 100) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => "Answer for question {$questionId} exceeds 100-word limit (current: {$wordCount} words). Please shorten your answer."
                    ], 422);
                }

                // Check if answer already exists
                $existingAnswer = DB::table('free_response_answers')
                    ->where('question_id', $questionId)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($existingAnswer) {
                    // Update existing answer
                    DB::table('free_response_answers')
                        ->where('id', $existingAnswer->id)
                        ->update([
                            'answer_text' => trim($answerText),
                            'word_count' => $wordCount,
                            'status' => 'submitted',
                            'submitted_at' => now(),
                            'updated_at' => now()
                        ]);
                } else {
                    // Create new answer
                    DB::table('free_response_answers')->insert([
                        'question_id' => $questionId,
                        'user_id' => Auth::id(),
                        'enrollment_id' => $validated['enrollment_id'],
                        'answer_text' => trim($answerText),
                        'word_count' => $wordCount,
                        'status' => 'submitted',
                        'submitted_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            Log::info('Free response quiz submitted', [
                'user_id' => Auth::id(),
                'enrollment_id' => $validated['enrollment_id'],
                'questions_answered' => count($validated['answers'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your answers have been submitted successfully!',
                'grading_period' => true,
                'grading_message' => 'Your answers are now under instructor review. You will be able to continue the course within 24 hours after the instructor provides feedback.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting free response quiz: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit answers. Please try again.'
            ], 500);
        }
    }

    /**
     * Check grading status for free response questions
     */
    public function getGradingStatus(Request $request)
    {
        $enrollmentId = $request->get('enrollment_id');
        
        if (!$enrollmentId) {
            return response()->json(['error' => 'Enrollment ID is required.'], 400);
        }

        // Get enrollment and verify ownership
        $enrollment = DB::table('user_course_enrollments')
            ->where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found.'], 404);
        }

        try {
            // Get all free response answers for this enrollment
            $answers = DB::table('free_response_answers')
                ->where('user_id', Auth::id())
                ->where('enrollment_id', $enrollmentId)
                ->get();

            if ($answers->isEmpty()) {
                return response()->json([
                    'all_graded' => false,
                    'message' => 'No answers found.'
                ]);
            }

            // Check if all answers have been graded (have feedback or score)
            $gradedCount = $answers->filter(function($answer) {
                return !is_null($answer->feedback) || !is_null($answer->score);
            })->count();

            $allGraded = $gradedCount === $answers->count();

            return response()->json([
                'all_graded' => $allGraded,
                'total_answers' => $answers->count(),
                'graded_answers' => $gradedCount,
                'pending_answers' => $answers->count() - $gradedCount,
                'message' => $allGraded 
                    ? 'All answers have been graded by your instructor.'
                    : 'Some answers are still under review.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking grading status: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to check grading status.'
            ], 500);
        }
    }

    /**
     * API endpoint to get questions for course player
     */
    public function getQuestionsApi(Request $request)
    {
        $enrollmentId = $request->get('enrollment_id');
        $placementId = $request->get('placement_id');
        
        if (!$enrollmentId) {
            return response()->json(['error' => 'Enrollment ID is required.'], 400);
        }

        // Get enrollment and course information
        $enrollment = DB::table('user_course_enrollments')
            ->where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found.'], 404);
        }

        // Get quiz placement settings if placement_id is provided
        $placement = null;
        if ($placementId) {
            $placement = DB::table('free_response_quiz_placements')
                ->where('id', $placementId)
                ->where('course_id', $enrollment->course_id)
                ->first();
        }

        // Check if free_response_questions table exists
        try {
            // Get questions based on placement settings
            $questionsQuery = DB::table('free_response_questions')
                ->where('course_id', $enrollment->course_id)
                ->where('is_active', true);
            
            if ($placementId) {
                $questionsQuery->where('placement_id', $placementId);
            }
            
            $allQuestions = $questionsQuery->orderBy('order_index')->get();
            
            // Apply random selection if configured
            $questions = $allQuestions;
            if ($placement && $placement->use_random_selection && $placement->questions_to_select > 0) {
                // Check if user already has a selected set of questions
                $userQuestionSelection = DB::table('user_quiz_question_selections')
                    ->where('user_id', Auth::id())
                    ->where('enrollment_id', $enrollmentId)
                    ->where('placement_id', $placementId)
                    ->first();
                
                if ($userQuestionSelection) {
                    // Use previously selected questions
                    $selectedQuestionIds = json_decode($userQuestionSelection->selected_question_ids, true);
                    $questions = $allQuestions->whereIn('id', $selectedQuestionIds);
                } else {
                    // Randomly select questions and save the selection
                    $shuffled = $allQuestions->shuffle();
                    $selected = $shuffled->take($placement->questions_to_select);
                    $selectedIds = $selected->pluck('id')->toArray();
                    
                    // Save the selection for consistency
                    DB::table('user_quiz_question_selections')->insert([
                        'user_id' => Auth::id(),
                        'enrollment_id' => $enrollmentId,
                        'placement_id' => $placementId,
                        'selected_question_ids' => json_encode($selectedIds),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $questions = $selected;
                }
            }

            // Get user's existing answers with submission status
            $existingAnswers = DB::table('free_response_answers')
                ->where('user_id', Auth::id())
                ->whereIn('question_id', $questions->pluck('id'))
                ->get()
                ->keyBy('question_id');

            // Format answers and check if already submitted
            $formattedAnswers = [];
            $isSubmitted = false;
            
            foreach ($existingAnswers as $questionId => $answer) {
                $formattedAnswers[$questionId] = $answer->answer_text;
                if ($answer->status === 'submitted') {
                    $isSubmitted = true;
                }
            }

            return response()->json([
                'questions' => $questions->values(), // Reset array keys
                'existingAnswers' => $formattedAnswers,
                'isSubmitted' => $isSubmitted,
                'submittedAt' => $isSubmitted ? $existingAnswers->first()->submitted_at : null,
                'placement' => $placement,
                'totalQuestionsInPool' => $allQuestions->count(),
                'questionsSelected' => $questions->count(),
            ]);

        } catch (\Exception $e) {
            // If table doesn't exist or other error, return empty questions
            Log::warning('Free response questions table may not exist: ' . $e->getMessage());
            
            return response()->json([
                'questions' => [],
                'existingAnswers' => [],
                'isSubmitted' => false,
                'submittedAt' => null,
                'placement' => $placement,
            ]);
        }
    }
}
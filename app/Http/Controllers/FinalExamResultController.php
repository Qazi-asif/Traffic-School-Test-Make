<?php

namespace App\Http\Controllers;

use App\Models\FinalExamResult;
use App\Models\FinalExamQuestionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinalExamResultController extends Controller
{
    /**
     * Show final exam result to student
     */
    public function show($resultId)
    {
        $result = FinalExamResult::with(['user', 'questionResults.question'])
            ->where('id', $resultId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Get course details
        $courseDetails = $this->getCourseDetails($result);
        
        // Get component scores
        $componentScores = $this->getComponentScores($result);

        return view('student.final-exam-result', compact(
            'result',
            'courseDetails',
            'componentScores'
        ));
    }

    /**
     * Submit student feedback
     */
    public function submitFeedback(Request $request, $resultId)
    {
        $request->validate([
            'student_feedback' => 'required|string|max:2000',
            'student_rating' => 'required|integer|min:1|max:5',
        ]);

        $result = FinalExamResult::where('id', $resultId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if feedback already submitted
        if ($result->student_feedback) {
            return redirect()->back()->with('error', 'Feedback has already been submitted.');
        }

        $result->update([
            'student_feedback' => $request->student_feedback,
            'student_rating' => $request->student_rating,
            'student_feedback_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Thank you for your feedback!');
    }

    /**
     * Process final exam completion and create result
     */
    public function processExamCompletion(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|integer',
            'exam_answers' => 'required|array',
            'exam_duration' => 'required|integer',
        ]);

        $enrollmentId = $request->enrollment_id;
        
        // Get enrollment details
        $enrollment = DB::table('user_course_enrollments as e')
            ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
            ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
            ->select(
                'e.*',
                DB::raw('COALESCE(c.id, fc.id) as course_id'),
                DB::raw('CASE WHEN c.id IS NOT NULL THEN "courses" ELSE "florida_courses" END as course_type')
            )
            ->where('e.id', $enrollmentId)
            ->where('e.user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }

        // Check if student has already passed the final exam
        $existingPassedResult = FinalExamResult::where('enrollment_id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->where('passed', true)  // Use 'passed' column
            ->first();

        if ($existingPassedResult) {
            return response()->json([
                'error' => 'You have already passed the final exam',
                'redirect_url' => '/generate-certificates'
            ], 400);
        }

        // Check if course is already completed
        if ($enrollment->status === 'completed' && $enrollment->completed_at) {
            return response()->json([
                'error' => 'Course is already completed',
                'redirect_url' => '/generate-certificates'
            ], 400);
        }

        // Calculate exam score
        $examResults = $this->calculateExamScore($request->exam_answers);
        
        // Get component scores
        $quizAverage = $this->getQuizAverage($enrollment);
        $freeResponseScore = $this->getFreeResponseScore($enrollment);

        // Create final exam result
        $result = FinalExamResult::create([
            'user_id' => Auth::id(),
            'enrollment_id' => $enrollmentId,
            'course_id' => $enrollment->course_id,
            'course_type' => $enrollment->course_type,
            'score' => $examResults['percentage'],  // Use 'score' column
            'final_exam_correct' => $examResults['correct'],
            'final_exam_total' => $examResults['total'],
            'exam_completed_at' => Carbon::now(),
            'exam_duration_minutes' => $request->exam_duration,
            'quiz_average' => $quizAverage,
            'free_response_score' => $freeResponseScore,
            'grading_period_ends_at' => Carbon::now()->addHours(24),
        ]);

        // Calculate overall score
        $result->overall_score = $result->calculateOverallScore();
        $result->grade_letter = $result->getGradeLetter();
        $result->passed = $result->overall_score >= $result->passing_threshold;  // Use 'passed' column
        $result->status = $result->passed ? 'passed' : 'failed';
        $result->save();

        // Save detailed question results
        $this->saveQuestionResults($result, $request->exam_answers);

        // Update enrollment
        DB::table('user_course_enrollments')
            ->where('id', $enrollmentId)
            ->update([
                'final_exam_completed' => true,
                'final_exam_result_id' => $result->id,
                'updated_at' => Carbon::now(),
            ]);

        // Update enrollment progress now that final exam is completed
        $enrollmentModel = \App\Models\UserCourseEnrollment::find($enrollmentId);
        if ($enrollmentModel) {
            \Log::info("Final exam completed - Before progress update", [
                'enrollment_id' => $enrollmentId,
                'status' => $enrollmentModel->status,
                'progress' => $enrollmentModel->progress_percentage,
                'final_exam_completed' => $enrollmentModel->final_exam_completed,
                'is_passing' => $result->is_passing
            ]);
            
            // Use the ProgressController method to update progress
            $progressController = new \App\Http\Controllers\ProgressController();
            $progressController->updateEnrollmentProgressPublic($enrollmentModel);
            
            // Refresh the enrollment to get updated status
            $enrollmentModel->refresh();
            
            \Log::info("Final exam completed - After progress update", [
                'enrollment_id' => $enrollmentId,
                'status' => $enrollmentModel->status,
                'progress' => $enrollmentModel->progress_percentage,
                'completed_at' => $enrollmentModel->completed_at
            ]);
        }

        // Determine redirect URL based on result
        if ($result->passed && $enrollmentModel && $enrollmentModel->status === 'completed') {  // Use 'passed' column
            // Student passed and course is completed - redirect to certificate generation
            $redirectUrl = '/generate-certificates';
            \Log::info("Redirecting to certificate generation", ['enrollment_id' => $enrollmentId]);
        } else {
            // Student failed or course not completed - show result page
            $redirectUrl = route('final-exam.result', $result->id);
            \Log::info("Redirecting to result page", [
                'enrollment_id' => $enrollmentId,
                'passed' => $result->is_passing,
                'status' => $enrollmentModel ? $enrollmentModel->status : 'unknown'
            ]);
        }

        return response()->json([
            'success' => true,
            'result_id' => $result->id,
            'passed' => $result->passed,  // Use 'passed' column
            'course_completed' => $enrollmentModel ? $enrollmentModel->status === 'completed' : false,
            'progress_percentage' => $enrollmentModel ? $enrollmentModel->progress_percentage : 0,
            'redirect_url' => $redirectUrl
        ]);
    }

    /**
     * Get course details
     */
    private function getCourseDetails($result)
    {
        if ($result->course_type === 'florida_courses') {
            return DB::table('florida_courses')->where('id', $result->course_id)->first();
        } else {
            return DB::table('courses')->where('id', $result->course_id)->first();
        }
    }

    /**
     * Get component scores breakdown
     */
    private function getComponentScores($result)
    {
        return [
            'quiz_average' => [
                'score' => $result->quiz_average ?? 0,
                'weight' => 30,
                'weighted_score' => ($result->quiz_average ?? 0) * 0.3
            ],
            'free_response' => [
                'score' => $result->free_response_score,
                'weight' => 20,
                'weighted_score' => ($result->free_response_score ?? 0) * 0.2
            ],
            'final_exam' => [
                'score' => $result->final_exam_score ?? 0,
                'weight' => 50,
                'weighted_score' => ($result->final_exam_score ?? 0) * 0.5
            ]
        ];
    }

    /**
     * Calculate exam score from answers
     */
    private function calculateExamScore($answers)
    {
        $correct = 0;
        $total = count($answers);

        foreach ($answers as $index => $answer) {
            $isCorrect = isset($answer['is_correct']) && $answer['is_correct'];
            
            if ($isCorrect) {
                $correct++;
            }
        }

        $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        return [
            'correct' => $correct,
            'total' => $total,
            'percentage' => $percentage
        ];
    }

    /**
     * Get quiz average for enrollment
     */
    private function getQuizAverage($enrollment)
    {
        $average = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->avg('percentage');

        return $average ? round($average, 2) : 0;
    }

    /**
     * Get free response score for enrollment
     */
    private function getFreeResponseScore($enrollment)
    {
        $totalScore = DB::table('free_response_answers as fra')
            ->join('free_response_questions as frq', 'fra.question_id', '=', 'frq.id')
            ->where('fra.user_id', $enrollment->user_id)
            ->where('frq.course_id', $enrollment->course_id)
            ->sum('fra.score');

        $totalPossible = DB::table('free_response_answers as fra')
            ->join('free_response_questions as frq', 'fra.question_id', '=', 'frq.id')
            ->where('fra.user_id', $enrollment->user_id)
            ->where('frq.course_id', $enrollment->course_id)
            ->sum('frq.points');

        if ($totalPossible > 0) {
            return round(($totalScore / $totalPossible) * 100, 2);
        }

        return null;
    }

    /**
     * Save detailed question results
     */
    private function saveQuestionResults($result, $answers)
    {
        foreach ($answers as $answer) {
            FinalExamQuestionResult::create([
                'final_exam_result_id' => $result->id,
                'question_id' => $answer['question_id'],
                'student_answer' => $answer['student_answer'] ?? '',
                'correct_answer' => $answer['correct_answer'] ?? '',
                'is_correct' => $answer['is_correct'] ?? false,
                'points_earned' => $answer['is_correct'] ? 1 : 0,
                'points_possible' => 1,
                'time_spent_seconds' => $answer['time_spent'] ?? 0,
            ]);
        }
    }
}
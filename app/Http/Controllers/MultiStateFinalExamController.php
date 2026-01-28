<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserCourseEnrollment;
use App\Models\FinalExamResult;
use App\Models\FinalExamQuestion;
use App\Events\CourseCompleted;
use App\Services\MultiStateCertificateService;

class MultiStateFinalExamController extends Controller
{
    protected $certificateService;
    
    public function __construct(MultiStateCertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }
    
    /**
     * Get final exam questions based on state requirements
     */
    public function getExamQuestions(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        // Check if all chapters are completed
        if (!$this->allChaptersCompleted($enrollment)) {
            return response()->json(['error' => 'Please complete all chapters before taking the final exam'], 400);
        }
        
        // Get course and state information
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        $stateSettings = $this->getStateSpecificSettings($stateCode);
        
        // Check if final exam is already passed
        $existingResult = FinalExamResult::where('enrollment_id', $enrollment->id)
            ->where('passed', true)
            ->first();
        
        if ($existingResult) {
            return response()->json([
                'already_passed' => true,
                'result' => $existingResult,
                'message' => 'You have already passed the final exam for this course.'
            ]);
        }
        
        // Get state-specific questions
        $questions = $this->getStateSpecificExamQuestions($enrollment, $stateCode, $stateSettings);
        
        if ($questions->isEmpty()) {
            return response()->json(['error' => 'No final exam questions available'], 404);
        }
        
        return response()->json([
            'questions' => $questions,
            'state_code' => $stateCode,
            'exam_settings' => [
                'total_questions' => $stateSettings['final_exam_questions'],
                'passing_score' => $stateSettings['final_exam_passing_score'],
                'time_limit' => $this->getExamTimeLimit($stateCode),
                'attempts_allowed' => $this->getAttemptsAllowed($stateCode),
                'state_requirements' => $stateSettings
            ]
        ]);
    }
    
    /**
     * Submit final exam answers
     */
    public function submitExam(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        $answers = $request->input('answers', []);
        $timeSpent = $request->input('time_spent', 0);
        
        // Get course and state information
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        $stateSettings = $this->getStateSpecificSettings($stateCode);
        
        // Get questions for validation
        $questions = $this->getStateSpecificExamQuestions($enrollment, $stateCode, $stateSettings);
        
        // Calculate score
        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $questionResults = [];
        
        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $this->validateAnswer($question, $userAnswer, $stateCode);
            
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
        $passingScore = $stateSettings['final_exam_passing_score'];
        $passed = $percentage >= $passingScore;
        
        // Save exam result
        $examResult = FinalExamResult::create([
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $totalQuestions - $correctAnswers,
            'score' => $percentage,
            'passed' => $passed,
            'passing_score_required' => $passingScore,
            'time_spent' => $timeSpent,
            'state_code' => $stateCode,
            'answers' => json_encode($answers),
            'question_results' => json_encode($questionResults),
            'exam_settings' => json_encode($stateSettings)
        ]);
        
        // Update enrollment if passed
        if ($passed) {
            $enrollment->update([
                'final_exam_completed' => true,
                'final_exam_result_id' => $examResult->id,
                'completed_at' => now(),
                'status' => 'completed'
            ]);
            
            // Generate state-specific certificate
            try {
                $certificateResult = $this->certificateService->generateCertificate($enrollment);
                
                if ($certificateResult['success']) {
                    // Store certificate data
                    DB::table('certificates')->updateOrInsert(
                        ['enrollment_id' => $enrollment->id],
                        [
                            'user_id' => $user->id,
                            'certificate_number' => $certificateResult['certificate_data']['certificate_number'],
                            'certificate_type' => $stateSettings['certificate_type'],
                            'state_code' => $stateCode,
                            'issued_at' => now(),
                            'pdf_path' => $this->storeCertificatePDF($certificateResult),
                            'certificate_data' => json_encode($certificateResult['certificate_data']),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Certificate generation failed: ' . $e->getMessage());
            }
            
            // Fire course completion event
            event(new CourseCompleted($enrollment));
        }
        
        return response()->json([
            'success' => true,
            'passed' => $passed,
            'score' => round($percentage, 2),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'passing_score_required' => $passingScore,
            'state_code' => $stateCode,
            'course_completed' => $passed,
            'exam_result_id' => $examResult->id,
            'question_results' => $questionResults
        ]);
    }
    
    /**
     * Get state-specific exam questions
     */
    private function getStateSpecificExamQuestions($enrollment, $stateCode, $stateSettings)
    {
        $questionsNeeded = $stateSettings['final_exam_questions'];
        
        // Get questions with state preference
        $questions = FinalExamQuestion::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table)
            ->where('is_active', true)
            ->where(function($query) use ($stateCode) {
                $query->where('state_specific', $stateCode)
                      ->orWhereNull('state_specific')
                      ->orWhere('state_specific', '');
            })
            ->orderBy('state_specific', 'desc') // Prioritize state-specific questions
            ->orderBy('difficulty_level')
            ->inRandomOrder()
            ->limit($questionsNeeded)
            ->get();
        
        // If not enough state-specific questions, get generic ones
        if ($questions->count() < $questionsNeeded) {
            $additionalQuestions = FinalExamQuestion::where('course_id', $enrollment->course_id)
                ->where('is_active', true)
                ->whereNotIn('id', $questions->pluck('id'))
                ->inRandomOrder()
                ->limit($questionsNeeded - $questions->count())
                ->get();
            
            $questions = $questions->merge($additionalQuestions);
        }
        
        return $questions;
    }
    
    /**
     * Get state-specific settings
     */
    private function getStateSpecificSettings($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                return [
                    'final_exam_questions' => 40,
                    'final_exam_passing_score' => 80,
                    'certificate_type' => 'florida_certificate',
                    'requires_dicds_submission' => true
                ];
            case 'MO':
                return [
                    'final_exam_questions' => 25,
                    'final_exam_passing_score' => 70,
                    'certificate_type' => 'missouri_certificate',
                    'requires_form_4444' => true
                ];
            case 'TX':
                return [
                    'final_exam_questions' => 30,
                    'final_exam_passing_score' => 75,
                    'certificate_type' => 'texas_certificate'
                ];
            case 'DE':
                return [
                    'final_exam_questions' => 20,
                    'final_exam_passing_score' => 80,
                    'certificate_type' => 'delaware_certificate'
                ];
            default:
                return [
                    'final_exam_questions' => 25,
                    'final_exam_passing_score' => 80,
                    'certificate_type' => 'generic_certificate'
                ];
        }
    }
    
    /**
     * Get exam time limit based on state
     */
    private function getExamTimeLimit($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                return 120; // 2 hours
            case 'MO':
                return 90;  // 1.5 hours
            case 'TX':
                return 90;  // 1.5 hours
            case 'DE':
                return 60;  // 1 hour
            default:
                return 90;  // 1.5 hours
        }
    }
    
    /**
     * Get attempts allowed based on state
     */
    private function getAttemptsAllowed($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                return 3;
            case 'MO':
                return 5;
            case 'TX':
                return 3;
            case 'DE':
                return 3;
            default:
                return 3;
        }
    }
    
    /**
     * Validate answer with state-specific logic
     */
    private function validateAnswer($question, $userAnswer, $stateCode)
    {
        if (!$userAnswer || !$question->correct_answer) {
            return false;
        }
        
        // Normalize answers
        $userNorm = strtoupper(trim($userAnswer));
        $correctNorm = strtoupper(trim($question->correct_answer));
        
        return $userNorm === $correctNorm;
    }
    
    /**
     * Check if all chapters are completed
     */
    private function allChaptersCompleted($enrollment)
    {
        $totalChapters = DB::table('chapters')
            ->where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table)
            ->where('is_active', true)
            ->count();
        
        $completedChapters = DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();
        
        return $completedChapters >= $totalChapters;
    }
    
    /**
     * Get course data based on enrollment
     */
    private function getCourseData($enrollment)
    {
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return \App\Models\FloridaCourse::find($enrollment->course_id);
            case 'missouri_courses':
                return \App\Models\Missouri\Course::find($enrollment->course_id);
            case 'texas_courses':
                return \App\Models\Texas\Course::find($enrollment->course_id);
            case 'delaware_courses':
                return \App\Models\Delaware\Course::find($enrollment->course_id);
            default:
                return \App\Models\Course::find($enrollment->course_id);
        }
    }
    
    /**
     * Get state code from enrollment and course
     */
    private function getStateCode($enrollment, $course)
    {
        if (isset($course->state_code)) {
            return $course->state_code;
        }
        
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return 'FL';
            case 'missouri_courses':
                return 'MO';
            case 'texas_courses':
                return 'TX';
            case 'delaware_courses':
                return 'DE';
            default:
                return 'GENERIC';
        }
    }
    
    /**
     * Store certificate PDF file
     */
    private function storeCertificatePDF($certificateResult)
    {
        $filename = $certificateResult['filename'];
        $pdfContent = $certificateResult['pdf_content'];
        
        $path = "certificates/{$filename}";
        \Storage::disk('public')->put($path, $pdfContent);
        
        return $path;
    }
}
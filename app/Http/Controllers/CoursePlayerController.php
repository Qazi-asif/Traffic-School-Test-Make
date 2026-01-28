<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCourseEnrollment;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\MissouriCourse;
use App\Models\TexasCourse;
use App\Models\DelawareCourse;
use App\Models\FinalExamResult;
use App\Models\FinalExamQuestion;
use App\Models\UserCourseProgress;
use App\Events\ChapterCompleted;
use App\Events\CourseCompleted;

class CoursePlayerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $enrollmentId = $request->enrollment_id;
        
        if (!$enrollmentId) {
            return redirect()->back()->with('error', 'No enrollment specified');
        }
        
        // Get enrollment with course data
        $enrollment = UserCourseEnrollment::with(['user'])
            ->where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found');
        }
        
        // Get course data based on state
        $course = $this->getCourseData($enrollment);
        
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found');
        }
        
        // Check if course is already completed
        if ($enrollment->status === 'completed' && $enrollment->completed_at) {
            // Get final exam result for display
            $examResult = FinalExamResult::where('enrollment_id', $enrollment->id)
                ->where('passed', true)
                ->orderBy('created_at', 'desc')
                ->first();
            
            return view('course-player', [
                'enrollment' => $enrollment,
                'course' => $course,
                'examResult' => $examResult,
                'showCompletionOnly' => true
            ]);
        }
        
        // Get chapters based on state and course structure
        $chapters = $this->getStateSpecificChapters($enrollment, $course);
        
        // Get user progress
        $progress = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');
        
        // Add progress information to chapters
        foreach ($chapters as $chapter) {
            $chapterProgress = $progress->get($chapter->id);
            $chapter->is_completed = $chapterProgress ? $chapterProgress->is_completed : false;
            $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
            $chapter->started_at = $chapterProgress ? $chapterProgress->started_at : null;
            $chapter->completed_at = $chapterProgress ? $chapterProgress->completed_at : null;
        }
        
        return view('course-player', compact('enrollment', 'course', 'chapters', 'progress'));
    }
    
    /**
     * Get course data based on enrollment table
     */
    private function getCourseData($enrollment)
    {
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return FloridaCourse::find($enrollment->course_id);
            case 'missouri_courses':
                return MissouriCourse::find($enrollment->course_id);
            case 'texas_courses':
                return TexasCourse::find($enrollment->course_id);
            case 'delaware_courses':
                return DelawareCourse::find($enrollment->course_id);
            default:
                return Course::find($enrollment->course_id);
        }
    }
    
    /**
     * Get chapter with state-specific enhancements
     */
    public function getChapter(Request $request, $enrollmentId, $chapterId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        $chapter = Chapter::where('id', $chapterId)
            ->where('course_id', $enrollment->course_id)
            ->first();
        
        if (!$chapter) {
            return response()->json(['error' => 'Chapter not found'], 404);
        }
        
        // Mark chapter as started
        UserCourseProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id
            ],
            [
                'started_at' => now(),
                'last_accessed_at' => now(),
            ]
        );
        
        // Get state-specific questions
        $questions = $this->getStateSpecificQuestions($chapter, $enrollment);
        
        // Get course data for state-specific settings
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        return response()->json([
            'chapter' => $chapter,
            'questions' => $questions,
            'has_quiz' => $questions->count() > 0,
            'state_code' => $stateCode,
            'quiz_passing_score' => $this->getQuizPassingScore($stateCode),
            'timer_required' => $this->isTimerRequired($stateCode),
            'state_specific_settings' => $this->getStateSpecificSettings($stateCode)
        ]);
    }
    
    /**
     * Get state-specific questions for a chapter
     */
    private function getStateSpecificQuestions($chapter, $enrollment)
    {
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        // Get questions with state preference
        $questions = ChapterQuestion::where('chapter_id', $chapter->id)
            ->where('is_active', true)
            ->where(function($query) use ($stateCode) {
                $query->where('state_specific', $stateCode)
                      ->orWhereNull('state_specific')
                      ->orWhere('state_specific', '');
            })
            ->orderBy('state_specific', 'desc') // Prioritize state-specific questions
            ->orderBy('order_index')
            ->get();
        
        // If no state-specific questions found, get generic questions
        if ($questions->isEmpty()) {
            $questions = ChapterQuestion::where('chapter_id', $chapter->id)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();
        }
        
        return $questions;
    }
    
    /**
     * Get quiz passing score based on state
     */
    private function getQuizPassingScore($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
            case 'DE':
                return 80;
            case 'TX':
                return 75;
            case 'MO':
                return 70;
            default:
                return 80;
        }
    }
    
    /**
     * Check if timer is required for state
     */
    private function isTimerRequired($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
            case 'TX':
            case 'DE':
                return true;
            case 'MO':
                return false;
            default:
                return false;
        }
    }
    
    /**
     * Get state-specific settings
     */
    private function getStateSpecificSettings($stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                return [
                    'requires_dicds_submission' => true,
                    'certificate_type' => 'florida_certificate',
                    'final_exam_questions' => 40,
                    'final_exam_passing_score' => 80,
                    'timer_enforcement' => 'strict'
                ];
            case 'MO':
                return [
                    'requires_form_4444' => true,
                    'certificate_type' => 'missouri_certificate',
                    'final_exam_questions' => 25,
                    'final_exam_passing_score' => 70,
                    'timer_enforcement' => 'none'
                ];
            case 'TX':
                return [
                    'certificate_type' => 'texas_certificate',
                    'final_exam_questions' => 30,
                    'final_exam_passing_score' => 75,
                    'timer_enforcement' => 'moderate'
                ];
            case 'DE':
                return [
                    'certificate_type' => 'delaware_certificate',
                    'final_exam_questions' => 20,
                    'final_exam_passing_score' => 80,
                    'timer_enforcement' => 'strict',
                    'course_variations' => ['3hr', '6hr']
                ];
            default:
                return [
                    'certificate_type' => 'generic_certificate',
                    'final_exam_questions' => 25,
                    'final_exam_passing_score' => 80,
                    'timer_enforcement' => 'none'
                ];
        }
    }
    
    /**
     * Submit quiz with state-specific validation
     */
    public function submitQuiz(Request $request, $enrollmentId, $chapterId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        $answers = $request->input('answers', []);
        
        // Get course and state information
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        // Get state-specific questions
        $chapter = Chapter::find($chapterId);
        $questions = $this->getStateSpecificQuestions($chapter, $enrollment);
        
        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $questionResults = [];
        
        // Calculate score with state-specific logic
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
        $passingScore = $this->getQuizPassingScore($stateCode);
        $passed = $percentage >= $passingScore;
        
        // Save quiz result with state information
        $quizResult = DB::table('chapter_quiz_results')->updateOrInsert(
            [
                'user_id' => $user->id,
                'chapter_id' => $chapterId,
                'enrollment_id' => $enrollment->id
            ],
            [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $totalQuestions - $correctAnswers,
                'percentage' => $percentage,
                'passed' => $passed,
                'passing_score_required' => $passingScore,
                'state_code' => $stateCode,
                'answers' => json_encode($answers),
                'question_results' => json_encode($questionResults),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        // Fire state-specific events if needed
        if ($passed) {
            event(new ChapterCompleted($enrollment, $chapter, $stateCode));
        }
        
        return response()->json([
            'passed' => $passed,
            'percentage' => round($percentage, 2),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'wrong_answers' => $totalQuestions - $correctAnswers,
            'passing_score_required' => $passingScore,
            'state_code' => $stateCode,
            'question_results' => $questionResults
        ]);
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
        
        // Direct match
        if ($userNorm === $correctNorm) {
            return true;
        }
        
        // State-specific validation logic
        switch (strtoupper($stateCode)) {
            case 'FL':
                return $this->validateFloridaAnswer($question, $userAnswer);
            case 'MO':
                return $this->validateMissouriAnswer($question, $userAnswer);
            case 'TX':
                return $this->validateTexasAnswer($question, $userAnswer);
            case 'DE':
                return $this->validateDelawareAnswer($question, $userAnswer);
            default:
                return $this->validateGenericAnswer($question, $userAnswer);
        }
    }
    
    /**
     * Florida-specific answer validation
     */
    private function validateFloridaAnswer($question, $userAnswer)
    {
        // Florida may have specific answer formats or requirements
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }
    
    /**
     * Missouri-specific answer validation
     */
    private function validateMissouriAnswer($question, $userAnswer)
    {
        // Missouri may be more lenient with answer formats
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }
    
    /**
     * Texas-specific answer validation
     */
    private function validateTexasAnswer($question, $userAnswer)
    {
        // Texas-specific validation logic
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }
    
    /**
     * Delaware-specific answer validation
     */
    private function validateDelawareAnswer($question, $userAnswer)
    {
        // Delaware-specific validation logic
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }
    
    /**
     * Generic answer validation
     */
    private function validateGenericAnswer($question, $userAnswer)
    {
        return strtoupper(trim($userAnswer)) === strtoupper(trim($question->correct_answer));
    }
    
    /**
     * Complete chapter with state-specific requirements
     */
    public function completeChapter(Request $request, $enrollmentId, $chapterId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        $chapter = Chapter::find($chapterId);
        
        if (!$chapter) {
            return response()->json(['error' => 'Chapter not found'], 404);
        }
        
        // Get course and state information
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        // Check state-specific requirements
        $requirements = $this->checkChapterRequirements($chapter, $enrollment, $stateCode);
        
        if (!$requirements['can_complete']) {
            return response()->json([
                'error' => $requirements['message'],
                'requirements' => $requirements
            ], 400);
        }
        
        // Mark chapter as completed
        $progress = UserCourseProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapterId
            ],
            [
                'completed_at' => now(),
                'is_completed' => true,
                'time_spent' => $request->input('time_spent', 60),
                'last_accessed_at' => now(),
                'state_code' => $stateCode,
                'completion_method' => $request->input('completion_method', 'manual')
            ]
        );
        
        // Update enrollment progress with state-specific logic
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);
        
        $enrollment->refresh();
        
        // Fire state-specific completion events
        event(new ChapterCompleted($enrollment, $chapter, $stateCode));
        
        // Check if course is completed
        if ($enrollment->status === 'completed') {
            event(new CourseCompleted($enrollment));
        }
        
        return response()->json([
            'success' => true,
            'progress_percentage' => $enrollment->progress_percentage,
            'enrollment_completed' => $enrollment->status === 'completed',
            'state_code' => $stateCode,
            'next_chapter_available' => $this->getNextChapterInfo($enrollment, $chapter)
        ]);
    }
    
    /**
     * Check chapter completion requirements based on state
     */
    private function checkChapterRequirements($chapter, $enrollment, $stateCode)
    {
        $requirements = [
            'can_complete' => true,
            'message' => '',
            'quiz_required' => false,
            'quiz_passed' => false,
            'timer_required' => false,
            'timer_satisfied' => false
        ];
        
        // Check if chapter has quiz
        $questions = $this->getStateSpecificQuestions($chapter, $enrollment);
        
        if ($questions->count() > 0) {
            $requirements['quiz_required'] = true;
            
            $passingScore = $this->getQuizPassingScore($stateCode);
            $quizResult = DB::table('chapter_quiz_results')
                ->where('user_id', $enrollment->user_id)
                ->where('chapter_id', $chapter->id)
                ->where('percentage', '>=', $passingScore)
                ->first();
            
            if (!$quizResult) {
                $requirements['can_complete'] = false;
                $requirements['message'] = "You must pass the chapter quiz with {$passingScore}% or higher before completing this chapter.";
                return $requirements;
            }
            
            $requirements['quiz_passed'] = true;
        }
        
        // Check timer requirements based on state
        if ($this->isTimerRequired($stateCode)) {
            $requirements['timer_required'] = true;
            
            // Check if minimum time has been spent (this would integrate with your timer system)
            $progress = UserCourseProgress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapter->id)
                ->first();
            
            $minimumTime = $chapter->duration * 60; // Convert minutes to seconds
            $timeSpent = $progress ? $progress->time_spent : 0;
            
            if ($timeSpent < $minimumTime) {
                $requirements['can_complete'] = false;
                $requirements['message'] = "You must spend at least {$chapter->duration} minutes on this chapter as required by {$stateCode} state regulations.";
                return $requirements;
            }
            
            $requirements['timer_satisfied'] = true;
        }
        
        return $requirements;
    }
    
    /**
     * Get information about the next available chapter
     */
    private function getNextChapterInfo($enrollment, $currentChapter)
    {
        $course = $this->getCourseData($enrollment);
        $chapters = $this->getStateSpecificChapters($enrollment, $course);
        
        $currentIndex = $chapters->search(function($chapter) use ($currentChapter) {
            return $chapter->id === $currentChapter->id;
        });
        
        if ($currentIndex !== false && $currentIndex < $chapters->count() - 1) {
            $nextChapter = $chapters[$currentIndex + 1];
            return [
                'id' => $nextChapter->id,
                'title' => $nextChapter->title,
                'available' => true
            ];
        }
        
        // Check if final exam is available
        if ($this->allChaptersCompleted($enrollment)) {
            return [
                'id' => 'final-exam',
                'title' => 'Final Exam',
                'available' => true
            ];
        }
        
        return null;
    }
    
    /**
     * Check if all chapters are completed
     */
    private function allChaptersCompleted($enrollment)
    {
        $course = $this->getCourseData($enrollment);
        $chapters = $this->getStateSpecificChapters($enrollment, $course);
        
        foreach ($chapters as $chapter) {
            if (!$chapter->is_completed) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getProgress($enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        // Get course and state information
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        $progress = DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');
        
        // Get state-specific progress information
        $stateSpecificProgress = $this->getStateSpecificProgress($enrollment, $stateCode);
        
        return response()->json([
            'enrollment' => $enrollment,
            'progress' => $progress,
            'overall_progress' => $enrollment->progress_percentage,
            'state_code' => $stateCode,
            'state_specific_progress' => $stateSpecificProgress,
            'course_requirements' => $this->getStateSpecificSettings($stateCode)
        ]);
    }
    
    /**
     * Get state-specific progress information
     */
    private function getStateSpecificProgress($enrollment, $stateCode)
    {
        $progress = [
            'chapters_completed' => 0,
            'total_chapters' => 0,
            'quiz_average' => 0,
            'final_exam_status' => 'not_started',
            'certificate_status' => 'not_available',
            'state_submission_status' => 'pending'
        ];
        
        // Get chapters count
        $course = $this->getCourseData($enrollment);
        $chapters = $this->getStateSpecificChapters($enrollment, $course);
        $progress['total_chapters'] = $chapters->count();
        $progress['chapters_completed'] = $chapters->where('is_completed', true)->count();
        
        // Get quiz average
        $quizResults = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->where('enrollment_id', $enrollment->id)
            ->get();
        
        if ($quizResults->count() > 0) {
            $progress['quiz_average'] = $quizResults->avg('percentage');
        }
        
        // Check final exam status
        $finalExamResult = DB::table('final_exam_results')
            ->where('enrollment_id', $enrollment->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($finalExamResult) {
            $progress['final_exam_status'] = $finalExamResult->passed ? 'passed' : 'failed';
        }
        
        // Check certificate status
        if ($enrollment->completed_at) {
            $progress['certificate_status'] = 'available';
        }
        
        // Check state submission status based on state
        switch (strtoupper($stateCode)) {
            case 'FL':
                $transmission = DB::table('state_transmissions')
                    ->where('enrollment_id', $enrollment->id)
                    ->where('state', 'FL')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($transmission) {
                    $progress['state_submission_status'] = $transmission->status;
                }
                break;
                
            case 'MO':
                // Missouri uses Form 4444
                $form4444 = DB::table('missouri_form_4444s')
                    ->where('enrollment_id', $enrollment->id)
                    ->first();
                
                if ($form4444) {
                    $progress['state_submission_status'] = 'completed';
                }
                break;
                
            default:
                $progress['state_submission_status'] = 'not_required';
                break;
        }
        
        return $progress;
    }
    
    /**
     * Get state-specific chapters with proper structure
     */
    private function getStateSpecificChapters($enrollment, $course)
    {
        $stateCode = $this->getStateCode($enrollment, $course);
        
        // Get base chapters
        $chapters = Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table ?? 'courses')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();
        
        // Add state-specific enhancements
        switch (strtoupper($stateCode)) {
            case 'FL':
                return $this->enhanceFloridaChapters($chapters, $enrollment);
            case 'MO':
                return $this->enhanceMissouriChapters($chapters, $enrollment);
            case 'TX':
                return $this->enhanceTexasChapters($chapters, $enrollment);
            case 'DE':
                return $this->enhanceDelawareChapters($chapters, $enrollment);
            default:
                return $this->enhanceGenericChapters($chapters, $enrollment);
        }
    }
    
    /**
     * Get state code from enrollment and course
     */
    private function getStateCode($enrollment, $course)
    {
        // Try to get state from course
        if (isset($course->state_code)) {
            return $course->state_code;
        }
        
        if (isset($course->state)) {
            return $course->state;
        }
        
        // Try to determine from course table
        if ($enrollment->course_table === 'florida_courses') {
            return 'FL';
        } elseif ($enrollment->course_table === 'missouri_courses') {
            return 'MO';
        } elseif ($enrollment->course_table === 'texas_courses') {
            return 'TX';
        } elseif ($enrollment->course_table === 'delaware_courses') {
            return 'DE';
        }
        
        // Default to generic
        return 'GENERIC';
    }
    
    /**
     * Enhance chapters for Florida state requirements
     */
    private function enhanceFloridaChapters($chapters, $enrollment)
    {
        // Florida-specific enhancements
        foreach ($chapters as $chapter) {
            // Add Florida-specific quiz requirements
            $chapter->quiz_passing_score = 80; // Florida requires 80%
            $chapter->timer_required = true; // Florida requires timers
            $chapter->state_compliance = 'FL';
            
            // Check for Florida-specific question types
            $chapter->has_florida_questions = ChapterQuestion::where('chapter_id', $chapter->id)
                ->where('state_specific', 'FL')
                ->exists();
        }
        
        return $chapters;
    }
    
    /**
     * Enhance chapters for Missouri state requirements
     */
    private function enhanceMissouriChapters($chapters, $enrollment)
    {
        // Missouri-specific enhancements
        foreach ($chapters as $chapter) {
            $chapter->quiz_passing_score = 70; // Missouri requires 70%
            $chapter->timer_required = false; // Missouri doesn't require strict timers
            $chapter->state_compliance = 'MO';
            
            // Check for Missouri-specific content
            $chapter->has_missouri_content = strpos(strtolower($chapter->content), 'missouri') !== false;
        }
        
        return $chapters;
    }
    
    /**
     * Enhance chapters for Texas state requirements
     */
    private function enhanceTexasChapters($chapters, $enrollment)
    {
        // Texas-specific enhancements
        foreach ($chapters as $chapter) {
            $chapter->quiz_passing_score = 75; // Texas requires 75%
            $chapter->timer_required = true; // Texas requires timers
            $chapter->state_compliance = 'TX';
            
            // Texas has specific chapter requirements
            $chapter->has_texas_requirements = true;
        }
        
        return $chapters;
    }
    
    /**
     * Enhance chapters for Delaware state requirements
     */
    private function enhanceDelawareChapters($chapters, $enrollment)
    {
        // Delaware-specific enhancements
        foreach ($chapters as $chapter) {
            $chapter->quiz_passing_score = 80; // Delaware requires 80%
            $chapter->timer_required = true; // Delaware requires timers
            $chapter->state_compliance = 'DE';
            
            // Delaware has 3hr and 6hr course variations
            $chapter->course_duration_type = $this->getDelawareCourseType($enrollment);
        }
        
        return $chapters;
    }
    
    /**
     * Enhance chapters for generic courses
     */
    private function enhanceGenericChapters($chapters, $enrollment)
    {
        foreach ($chapters as $chapter) {
            $chapter->quiz_passing_score = 80; // Default 80%
            $chapter->timer_required = false; // Default no timer
            $chapter->state_compliance = 'GENERIC';
        }
        
        return $chapters;
    }
    
    /**
     * Get Delaware course type (3hr or 6hr)
     */
    private function getDelawareCourseType($enrollment)
    {
        $course = $this->getCourseData($enrollment);
        
        if (isset($course->duration_hours)) {
            return $course->duration_hours . 'hr';
        }
        
        // Try to determine from course title
        if (isset($course->title)) {
            if (strpos(strtolower($course->title), '6') !== false) {
                return '6hr';
            } elseif (strpos(strtolower($course->title), '3') !== false) {
                return '3hr';
            }
        }
        
        return '6hr'; // Default to 6hr
    }
}
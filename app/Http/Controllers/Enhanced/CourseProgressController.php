<?php

namespace App\Http\Controllers\Enhanced;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;
use App\Models\FinalExamResult;
use App\Events\CourseCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CourseProgressController extends Controller
{
    /**
     * Get comprehensive progress for an enrollment
     */
    public function getProgress(Request $request, $enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', $user->id)
                ->with(['user', 'progress'])
                ->first();
            
            if (!$enrollment) {
                return response()->json(['error' => 'Enrollment not found'], 404);
            }
            
            $progressData = $this->calculateComprehensiveProgress($enrollment);
            
            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollmentId,
                'progress' => $progressData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Progress API error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get progress'], 500);
        }
    }
    
    /**
     * Complete a chapter with comprehensive validation
     */
    public function completeChapter(Request $request, $enrollmentId, $chapterId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json(['error' => 'Enrollment not found'], 404);
            }
            
            $chapter = Chapter::where('id', $chapterId)
                ->where('course_id', $enrollment->course_id)
                ->where('is_active', true)
                ->first();
            
            if (!$chapter) {
                return response()->json(['error' => 'Chapter not found'], 404);
            }
            
            // Check if chapter has quiz requirements
            $quizValidation = $this->validateChapterQuiz($enrollment, $chapter);
            if (!$quizValidation['passed']) {
                return response()->json([
                    'error' => 'Quiz requirement not met',
                    'message' => $quizValidation['message'],
                    'requires_quiz' => true
                ], 422);
            }
            
            // Mark chapter as completed
            $progress = UserCourseProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'chapter_id' => $chapter->id,
                ],
                [
                    'completed_at' => now(),
                    'is_completed' => true,
                    'time_spent' => $request->input('time_spent', $chapter->duration ?? 60),
                    'last_accessed_at' => now(),
                    'started_at' => DB::raw('COALESCE(started_at, NOW())')
                ]
            );
            
            // Update enrollment start time if not set
            if (!$enrollment->started_at) {
                $enrollment->update(['started_at' => now()]);
            }
            
            // Recalculate progress
            $this->updateEnrollmentProgress($enrollment);
            
            // Refresh enrollment to get updated progress
            $enrollment->refresh();
            
            return response()->json([
                'success' => true,
                'progress' => $progress,
                'enrollment_progress' => round($enrollment->progress_percentage, 2),
                'enrollment_status' => $enrollment->status,
                'is_completed' => $enrollment->status === 'completed',
                'message' => 'Chapter completed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chapter completion error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to complete chapter',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process final exam completion
     */
    public function processFinalExam(Request $request, $enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json(['error' => 'Enrollment not found'], 404);
            }
            
            // Check if all chapters are completed
            $chaptersValidation = $this->validateAllChaptersCompleted($enrollment);
            if (!$chaptersValidation['passed']) {
                return response()->json([
                    'error' => 'Prerequisites not met',
                    'message' => $chaptersValidation['message'],
                    'completed_chapters' => $chaptersValidation['completed'],
                    'total_chapters' => $chaptersValidation['total']
                ], 422);
            }
            
            // Mark final exam as completed
            $enrollment->update([
                'final_exam_completed' => true,
                'final_exam_result_id' => $request->input('result_id')
            ]);
            
            // Recalculate progress
            $this->updateEnrollmentProgress($enrollment);
            
            // Refresh enrollment
            $enrollment->refresh();
            
            return response()->json([
                'success' => true,
                'enrollment_progress' => round($enrollment->progress_percentage, 2),
                'enrollment_status' => $enrollment->status,
                'is_completed' => $enrollment->status === 'completed',
                'message' => 'Final exam processed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Final exam processing error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to process final exam',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate comprehensive progress for an enrollment
     */
    private function calculateComprehensiveProgress(UserCourseEnrollment $enrollment)
    {
        // Get course chapters
        $totalChapters = Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table ?? 'florida_courses')
            ->where('is_active', true)
            ->count();
        
        // Get completed chapters
        $completedChapters = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->distinct('chapter_id')
            ->count('chapter_id');
        
        // Check final exam status
        $finalExamResult = FinalExamResult::where('enrollment_id', $enrollment->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $finalExamCompleted = $enrollment->final_exam_completed ?? false;
        $finalExamPassed = $finalExamResult && $finalExamResult->passed;
        
        // Calculate progress components
        $chapterProgressPercentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;
        
        // Determine overall progress
        if ($finalExamPassed) {
            $overallProgress = 100;
            $status = 'completed';
        } elseif ($chapterProgressPercentage >= 100 && $finalExamCompleted) {
            $overallProgress = 95; // All chapters done, exam attempted but not passed
            $status = 'active';
        } elseif ($chapterProgressPercentage >= 100) {
            $overallProgress = 90; // All chapters done, no exam attempt
            $status = 'active';
        } else {
            $overallProgress = $chapterProgressPercentage * 0.9; // Chapter progress weighted at 90%
            $status = 'active';
        }
        
        return [
            'overall_percentage' => round($overallProgress, 2),
            'status' => $status,
            'chapters' => [
                'completed' => $completedChapters,
                'total' => $totalChapters,
                'percentage' => round($chapterProgressPercentage, 2)
            ],
            'final_exam' => [
                'completed' => $finalExamCompleted,
                'passed' => $finalExamPassed,
                'score' => $finalExamResult->score ?? null,
                'attempts' => FinalExamResult::where('enrollment_id', $enrollment->id)->count()
            ],
            'requirements_met' => [
                'all_chapters_completed' => $chapterProgressPercentage >= 100,
                'final_exam_passed' => $finalExamPassed,
                'course_completed' => $finalExamPassed && $chapterProgressPercentage >= 100
            ]
        ];
    }
    
    /**
     * Update enrollment progress with comprehensive logic
     */
    public function updateEnrollmentProgress(UserCourseEnrollment $enrollment)
    {
        try {
            Log::info("Updating comprehensive progress for enrollment {$enrollment->id}");
            
            $progressData = $this->calculateComprehensiveProgress($enrollment);
            
            $wasCompleted = $enrollment->status === 'completed';
            $isNowCompleted = $progressData['requirements_met']['course_completed'];
            
            // Update enrollment
            $updateData = [
                'progress_percentage' => $progressData['overall_percentage'],
                'status' => $progressData['status']
            ];
            
            // Set completion timestamp if course is now completed
            if ($isNowCompleted && !$wasCompleted) {
                $updateData['completed_at'] = now();
            }
            
            $enrollment->update($updateData);
            
            // Generate certificate and fire events if newly completed
            if ($isNowCompleted && !$wasCompleted) {
                $this->handleCourseCompletion($enrollment);
            }
            
            Log::info("Progress updated for enrollment {$enrollment->id}: {$progressData['overall_percentage']}% ({$progressData['status']})");
            
        } catch (\Exception $e) {
            Log::error("Error updating enrollment progress: " . $e->getMessage());
        }
    }
    
    /**
     * Validate chapter quiz requirements
     */
    private function validateChapterQuiz(UserCourseEnrollment $enrollment, Chapter $chapter)
    {
        // Check if chapter has questions
        $hasQuestions = $chapter->hasQuestions();
        
        if (!$hasQuestions) {
            return ['passed' => true, 'message' => 'No quiz required'];
        }
        
        // Check for passing quiz result
        $quizResult = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->where('chapter_id', $chapter->id)
            ->where('percentage', '>=', 80)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($quizResult) {
            return ['passed' => true, 'message' => 'Quiz passed'];
        }
        
        // Auto-create passing result if configured to do so
        if (config('course.auto_pass_chapter_quizzes', false)) {
            try {
                DB::table('chapter_quiz_results')->insert([
                    'user_id' => $enrollment->user_id,
                    'chapter_id' => $chapter->id,
                    'enrollment_id' => $enrollment->id,
                    'total_questions' => 1,
                    'correct_answers' => 1,
                    'wrong_answers' => 0,
                    'percentage' => 100.00,
                    'answers' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                Log::info("Auto-created passing quiz result for user {$enrollment->user_id}, chapter {$chapter->id}");
                return ['passed' => true, 'message' => 'Quiz auto-passed'];
                
            } catch (\Exception $e) {
                Log::error("Failed to auto-create quiz result: " . $e->getMessage());
            }
        }
        
        return [
            'passed' => false,
            'message' => 'You must pass the chapter quiz with 80% or higher before completing this chapter.'
        ];
    }
    
    /**
     * Validate all chapters are completed
     */
    private function validateAllChaptersCompleted(UserCourseEnrollment $enrollment)
    {
        $totalChapters = Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table ?? 'florida_courses')
            ->where('is_active', true)
            ->count();
        
        $completedChapters = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->distinct('chapter_id')
            ->count('chapter_id');
        
        if ($completedChapters >= $totalChapters && $totalChapters > 0) {
            return [
                'passed' => true,
                'message' => 'All chapters completed',
                'completed' => $completedChapters,
                'total' => $totalChapters
            ];
        }
        
        return [
            'passed' => false,
            'message' => "You must complete all chapters before taking the final exam. Progress: {$completedChapters}/{$totalChapters}",
            'completed' => $completedChapters,
            'total' => $totalChapters
        ];
    }
    
    /**
     * Handle course completion (certificate generation, events, etc.)
     */
    private function handleCourseCompletion(UserCourseEnrollment $enrollment)
    {
        try {
            // Generate certificate
            $this->generateCertificate($enrollment);
            
            // Fire course completed event
            event(new CourseCompleted($enrollment));
            
            Log::info("Course completion handled for enrollment {$enrollment->id}");
            
        } catch (\Exception $e) {
            Log::error("Error handling course completion: " . $e->getMessage());
        }
    }
    
    /**
     * Generate certificate for completed enrollment
     */
    private function generateCertificate(UserCourseEnrollment $enrollment)
    {
        try {
            // Check if certificate already exists
            $existingCertificate = DB::table('florida_certificates')
                ->where('enrollment_id', $enrollment->id)
                ->first();
            
            if ($existingCertificate) {
                Log::info("Certificate already exists for enrollment {$enrollment->id}");
                return;
            }
            
            // Generate certificate number
            $year = date('Y');
            $lastCertificate = DB::table('florida_certificates')
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastCertificate ? 
                (int) substr($lastCertificate->dicds_certificate_number, -6) + 1 : 1;
            
            $certificateNumber = 'FL' . $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            
            // Get course name
            $courseName = 'Florida Traffic School Course';
            if ($enrollment->course_table === 'florida_courses') {
                $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
                $courseName = $course->title ?? $courseName;
            }
            
            // Create certificate
            DB::table('florida_certificates')->insert([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => $certificateNumber,
                'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                'course_name' => $courseName,
                'completion_date' => $enrollment->completed_at ?? now(),
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'status' => 'generated',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Certificate generated for enrollment {$enrollment->id}: {$certificateNumber}");
            
        } catch (\Exception $e) {
            Log::error("Certificate generation error: " . $e->getMessage());
        }
    }
    
    /**
     * Fix all stuck progress (admin function)
     */
    public function fixAllStuckProgress()
    {
        try {
            $fixed = 0;
            
            // Fix enrollments with passed final exam but progress < 100%
            $stuckEnrollments = UserCourseEnrollment::whereHas('finalExamResults', function($query) {
                $query->where('passed', true);
            })->where('progress_percentage', '<', 100)->get();
            
            foreach ($stuckEnrollments as $enrollment) {
                $this->updateEnrollmentProgress($enrollment);
                $fixed++;
            }
            
            Log::info("Fixed {$fixed} stuck progress enrollments");
            
            return response()->json([
                'success' => true,
                'fixed_count' => $fixed,
                'message' => "Fixed {$fixed} enrollments with stuck progress"
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error fixing stuck progress: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fix stuck progress'], 500);
        }
    }
}
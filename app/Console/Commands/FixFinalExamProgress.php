<?php

/**
 * Auto-fix enrollments with passed final exams but incomplete status
 * Run: php artisan fix:final-exam-progress
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCourseEnrollment;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\DB;

class FixFinalExamProgress extends Command
{
    protected $signature = 'fix:final-exam-progress {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Auto-fix enrollments with passed final exams but incomplete progress';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Checking for enrollments with passed final exams ===');
        $this->newLine();
        
        // Find enrollments with passed final exams but not marked as completed
        $enrollments = DB::table('user_course_enrollments as uce')
            ->join('final_exam_results as fer', 'uce.id', '=', 'fer.enrollment_id')
            ->where('fer.passed', true)
            ->where('fer.score', '>=', 70)
            ->where(function($q) {
                $q->where('uce.final_exam_completed', false)
                  ->orWhere('uce.status', '!=', 'completed')
                  ->orWhere('uce.progress_percentage', '<', 100);
            })
            ->select('uce.id', 'uce.user_id', 'uce.status', 'uce.progress_percentage', 
                     'uce.final_exam_completed', 'fer.id as result_id', 'fer.score')
            ->get();
        
        if ($enrollments->isEmpty()) {
            $this->info('✅ No enrollments need fixing!');
            return 0;
        }
        
        $this->warn("Found {$enrollments->count()} enrollments that need fixing:");
        $this->newLine();
        
        $progressController = new ProgressController();
        $fixed = 0;
        
        foreach ($enrollments as $enrollment) {
            $this->line("Enrollment ID: {$enrollment->id}");
            $this->line("  Current Status: {$enrollment->status}");
            $this->line("  Current Progress: {$enrollment->progress_percentage}%");
            $this->line("  Final Exam Score: {$enrollment->score}%");
            $this->line("  Final Exam Completed Flag: " . ($enrollment->final_exam_completed ? 'Yes' : 'No'));
            
            if (!$dryRun) {
                try {
                    // Update flags
                    DB::table('user_course_enrollments')
                        ->where('id', $enrollment->id)
                        ->update([
                            'final_exam_completed' => true,
                            'final_exam_result_id' => $enrollment->result_id,
                            'updated_at' => now()
                        ]);
                    
                    // Recalculate progress
                    $enrollmentModel = UserCourseEnrollment::find($enrollment->id);
                    if ($enrollmentModel) {
                        $progressController->updateEnrollmentProgressPublic($enrollmentModel);
                        $enrollmentModel->refresh();
                        
                        $this->info("  ✅ FIXED - New Status: {$enrollmentModel->status}, Progress: {$enrollmentModel->progress_percentage}%");
                        $fixed++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ ERROR: " . $e->getMessage());
                }
            } else {
                $this->comment("  [DRY RUN] Would fix this enrollment");
            }
            
            $this->newLine();
        }
        
        if ($dryRun) {
            $this->warn("DRY RUN: No changes made. Run without --dry-run to apply fixes.");
        } else {
            $this->info("✅ Fixed {$fixed} out of {$enrollments->count()} enrollments");
        }
        
        return 0;
    }
}

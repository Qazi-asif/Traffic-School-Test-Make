<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Models\MissouriForm4444;
use App\Services\MissouriForm4444PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateMissouriForm4444 implements ShouldQueue
{
    use InteractsWithQueue;

    protected $pdfService;

    public function __construct(MissouriForm4444PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function handle(CourseCompleted $event)
    {
        $enrollment = $event->enrollment;
        
        // Check if this is a Missouri course
        if (!$this->isMissouriCourse($enrollment)) {
            return;
        }

        // Check if Form 4444 already exists
        $existingForm = MissouriForm4444::where('enrollment_id', $enrollment->id)->first();
        if ($existingForm) {
            Log::info('Form 4444 already exists for enrollment', ['enrollment_id' => $enrollment->id]);
            return;
        }

        try {
            // Determine submission method based on enrollment data
            $submissionMethod = $this->determineSubmissionMethod($enrollment);

            // Create Form 4444 record
            $form = MissouriForm4444::create([
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'form_number' => 'MO-4444-' . time() . '-' . $enrollment->id,
                'completion_date' => now(),
                'submission_deadline' => now()->addDays(15),
                'submission_method' => $submissionMethod,
                'court_signature_required' => $submissionMethod === 'point_reduction',
                'status' => 'ready_for_submission',
            ]);

            // Generate PDF
            $this->pdfService->generateForm4444Pdf($form);

            // Send email with Form 4444
            $this->pdfService->emailForm4444($form);

            Log::info('Form 4444 generated and emailed successfully', [
                'enrollment_id' => $enrollment->id,
                'form_id' => $form->id,
                'user_id' => $enrollment->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate Form 4444', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function isMissouriCourse($enrollment)
    {
        // Check if course is in Missouri courses table
        if ($enrollment->course_table === 'missouri_courses') {
            return true;
        }

        // Check if regular course is Missouri-based
        if ($enrollment->course_table === 'courses' || !$enrollment->course_table) {
            $course = \App\Models\Course::find($enrollment->course_id);
            return $course && strtolower($course->state) === 'missouri';
        }

        return false;
    }

    private function determineSubmissionMethod($enrollment)
    {
        $user = $enrollment->user;

        // Check user's reason for taking the course
        if ($user->insurance_discount_only) {
            return 'insurance_discount';
        }

        // Check if there's a citation number (indicates point reduction or court ordered)
        if ($enrollment->citation_number || $user->citation_number) {
            // If there's a court date, it's likely court ordered
            if ($enrollment->court_date || $user->due_month) {
                return 'court_ordered';
            }
            
            // Otherwise, assume point reduction
            return 'point_reduction';
        }

        // Default to voluntary if no specific indicators
        return 'voluntary';
    }
}
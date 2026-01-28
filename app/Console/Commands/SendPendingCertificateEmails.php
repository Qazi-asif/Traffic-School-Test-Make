<?php

namespace App\Console\Commands;

use App\Mail\CertificateGenerated;
use App\Models\FloridaCertificate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPendingCertificateEmails extends Command
{
    protected $signature = 'certificates:send-pending-emails';
    protected $description = 'Send emails for certificates that were generated but not emailed to students';

    public function handle()
    {
        $this->info('🎯 Sending pending certificate emails...');
        
        // Find certificates that weren't emailed
        $pendingCertificates = FloridaCertificate::where('is_sent_to_student', false)
            ->orWhereNull('is_sent_to_student')
            ->with(['enrollment.user'])
            ->get();
        
        $this->info("Found {$pendingCertificates->count()} certificates pending email delivery");
        
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($pendingCertificates as $certificate) {
            $enrollment = $certificate->enrollment;
            
            if (!$enrollment || !$enrollment->user) {
                $this->warn("Certificate {$certificate->id}: Missing enrollment or user data");
                continue;
            }
            
            $user = $enrollment->user;
            $this->line("Processing certificate {$certificate->id} for {$user->email}...");
            
            try {
                // Get course data
                $course = null;
                if ($enrollment->course_table === 'florida_courses') {
                    $course = \App\Models\FloridaCourse::find($enrollment->course_id);
                } else {
                    $course = \App\Models\Course::find($enrollment->course_id);
                }
                
                if (!$course) {
                    $this->warn("  Course not found for enrollment {$enrollment->id}");
                    continue;
                }
                
                // Generate PDF for email attachment
                $certificatePdf = $this->generateCertificatePdf($enrollment, $certificate);
                
                // Send email
                Mail::to($user->email)->send(new CertificateGenerated(
                    $user,
                    $course,
                    $certificate->dicds_certificate_number,
                    $certificatePdf
                ));
                
                // Update certificate as sent
                $certificate->update([
                    'is_sent_to_student' => true,
                    'sent_at' => now()
                ]);
                
                $successCount++;
                $this->info("  ✅ Email sent successfully to {$user->email}");
                
            } catch (\Exception $e) {
                $failureCount++;
                $this->error("  ❌ Email failed for {$user->email}: " . $e->getMessage());
                
                // Mark failure for retry
                $certificate->update([
                    'email_failed_at' => now(),
                    'email_failure_reason' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("\n📊 Certificate Email Summary:");
        $this->info("✅ Emails sent successfully: {$successCount}");
        $this->info("❌ Emails failed: {$failureCount}");
        
        if ($successCount > 0) {
            $this->info("\n🎉 Certificate email delivery system is now working!");
        }
        
        if ($failureCount > 0) {
            $this->warn("\n⚠️  Some emails failed. Check logs for details and email configuration.");
        }
        
        return 0;
    }
    
    /**
     * Generate certificate PDF for email attachment
     */
    private function generateCertificatePdf($enrollment, $certificate)
    {
        try {
            $user = $enrollment->user;
            
            // Build student address
            $addressParts = array_filter([
                $user->mailing_address,
                $user->city,
                $user->state,
                $user->zip,
            ]);
            $student_address = implode(', ', $addressParts);

            // Build phone number
            $phone_parts = array_filter([$user->phone_1, $user->phone_2, $user->phone_3]);
            $phone = implode('-', $phone_parts);

            // Build birth date
            $birth_date = null;
            if ($user->birth_month && $user->birth_day && $user->birth_year) {
                $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
            }

            // Build due date
            $due_date = null;
            if ($user->due_month && $user->due_day && $user->due_year) {
                $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
            }

            // Get state stamp if available
            $stateStamp = null;
            $course = $enrollment->course ?? $enrollment->floridaCourse;
            if ($course) {
                $stateCode = $course->state ?? $course->state_code ?? 'FL';
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }

            $data = [
                'student_name' => $certificate->student_name,
                'student_address' => $student_address,
                'completion_date' => $certificate->completion_date->format('m/d/Y'),
                'course_type' => $certificate->course_name,
                'score' => $enrollment->final_exam_score ? $enrollment->final_exam_score.'%' : 'Passed',
                'license_number' => $user->driver_license,
                'birth_date' => $birth_date,
                'citation_number' => $enrollment->citation_number,
                'due_date' => $due_date,
                'court' => $user->court_selected,
                'county' => $user->state,
                'certificate_number' => $certificate->dicds_certificate_number,
                'phone' => $phone,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'state_stamp' => $stateStamp,
            ];

            // Generate PDF using DomPDF
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $data);
                return $pdf->output();
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Certificate PDF generation error: '.$e->getMessage());
            return null;
        }
    }
}
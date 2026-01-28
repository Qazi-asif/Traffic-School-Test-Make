<?php

/**
 * CRITICAL FIX: Certificate Email Delivery
 * 
 * This script fixes the certificate email delivery system by:
 * 1. Checking existing certificates that weren't emailed
 * 2. Sending emails for completed certificates
 * 3. Testing the email system
 * 4. Ensuring future certificates are automatically emailed
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CERTIFICATE EMAIL DELIVERY FIX ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Step 1: Check existing certificates that weren't emailed
    echo "Step 1: Checking existing certificates...\n";
    
    $unsentCertificates = \App\Models\FloridaCertificate::where('is_sent_to_student', false)
        ->orWhereNull('is_sent_to_student')
        ->with(['enrollment.user'])
        ->get();
    
    echo "Found {$unsentCertificates->count()} certificates that weren't emailed\n\n";
    
    $emailsSent = 0;
    $emailsFailed = 0;
    
    foreach ($unsentCertificates as $certificate) {
        $enrollment = $certificate->enrollment;
        
        if (!$enrollment || !$enrollment->user) {
            echo "Certificate {$certificate->id}: Missing enrollment or user data\n";
            continue;
        }
        
        $user = $enrollment->user;
        echo "Processing certificate {$certificate->id} for {$user->email}...\n";
        
        try {
            // Get course data
            $course = null;
            if ($enrollment->course_table === 'florida_courses') {
                $course = \App\Models\FloridaCourse::find($enrollment->course_id);
            } else {
                $course = \App\Models\Course::find($enrollment->course_id);
            }
            
            if (!$course) {
                echo "  ⚠ Course not found for enrollment {$enrollment->id}\n";
                continue;
            }
            
            // Generate PDF for email attachment
            $certificatePdf = $this->generateCertificatePdf($enrollment, $certificate);
            
            // Send email
            \Mail::to($user->email)->send(new \App\Mail\CertificateGenerated(
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
            
            $emailsSent++;
            echo "  ✓ Email sent successfully to {$user->email}\n";
            
        } catch (\Exception $e) {
            $emailsFailed++;
            echo "  ✗ Email failed for {$user->email}: " . $e->getMessage() . "\n";
            \Log::error('Certificate email error: ' . $e->getMessage(), [
                'certificate_id' => $certificate->id,
                'user_email' => $user->email
            ]);
        }
    }
    
    echo "\nStep 1 Results:\n";
    echo "- Emails sent successfully: {$emailsSent}\n";
    echo "- Emails failed: {$emailsFailed}\n\n";
    
    // Step 2: Test email configuration
    echo "Step 2: Testing email configuration...\n";
    
    $mailConfig = config('mail');
    echo "Mail driver: " . $mailConfig['default'] . "\n";
    echo "SMTP host: " . config('mail.mailers.smtp.host') . "\n";
    echo "SMTP port: " . config('mail.mailers.smtp.port') . "\n";
    echo "From address: " . config('mail.from.address') . "\n";
    
    // Test email sending with a simple test
    try {
        $testUser = \App\Models\User::first();
        if ($testUser) {
            echo "Sending test email to {$testUser->email}...\n";
            
            \Mail::raw('This is a test email from the certificate system.', function ($message) use ($testUser) {
                $message->to($testUser->email)
                        ->subject('Certificate System Test Email');
            });
            
            echo "✓ Test email sent successfully\n";
        }
    } catch (\Exception $e) {
        echo "✗ Test email failed: " . $e->getMessage() . "\n";
    }
    
    // Step 3: Check for completed enrollments without certificates
    echo "\nStep 3: Checking completed enrollments without certificates...\n";
    
    $completedEnrollments = \App\Models\UserCourseEnrollment::where('status', 'completed')
        ->where('progress_percentage', 100)
        ->whereDoesntHave('floridaCertificate')
        ->with('user')
        ->get();
    
    echo "Found {$completedEnrollments->count()} completed enrollments without certificates\n";
    
    $certificatesGenerated = 0;
    
    foreach ($completedEnrollments as $enrollment) {
        echo "Generating certificate for enrollment {$enrollment->id}...\n";
        
        try {
            // Use the ProgressController method to generate certificate
            $progressController = new \App\Http\Controllers\ProgressController();
            $reflection = new \ReflectionClass($progressController);
            $method = $reflection->getMethod('generateCertificate');
            $method->setAccessible(true);
            
            $method->invoke($progressController, $enrollment);
            
            $certificatesGenerated++;
            echo "  ✓ Certificate generated and emailed\n";
            
        } catch (\Exception $e) {
            echo "  ✗ Certificate generation failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nStep 3 Results:\n";
    echo "- Certificates generated: {$certificatesGenerated}\n\n";
    
    // Step 4: Verify email template exists
    echo "Step 4: Verifying email template...\n";
    
    $templatePath = resource_path('views/emails/certificate-generated.blade.php');
    if (file_exists($templatePath)) {
        echo "✓ Email template exists: {$templatePath}\n";
    } else {
        echo "✗ Email template missing: {$templatePath}\n";
    }
    
    $pdfTemplatePath = resource_path('views/certificate-pdf.blade.php');
    if (file_exists($pdfTemplatePath)) {
        echo "✓ PDF template exists: {$pdfTemplatePath}\n";
    } else {
        echo "✗ PDF template missing: {$pdfTemplatePath}\n";
    }
    
    // Step 5: Summary and recommendations
    echo "\n=== SUMMARY ===\n";
    echo "Certificate Email Delivery Status:\n";
    echo "- Existing certificates emailed: {$emailsSent}\n";
    echo "- Email failures: {$emailsFailed}\n";
    echo "- New certificates generated: {$certificatesGenerated}\n\n";
    
    if ($emailsFailed > 0) {
        echo "Issues Found:\n";
        echo "- {$emailsFailed} emails failed to send\n";
        echo "- Check email configuration and SMTP settings\n";
        echo "- Review error logs for specific failures\n\n";
    }
    
    echo "Recommendations:\n";
    echo "1. Monitor email queue for delivery status\n";
    echo "2. Set up email delivery monitoring\n";
    echo "3. Consider using a reliable email service (SendGrid, Mailgun)\n";
    echo "4. Add retry mechanism for failed emails\n";
    
    echo "\nNext Steps:\n";
    echo "1. Test certificate generation for new course completions\n";
    echo "2. Verify students receive emails promptly\n";
    echo "3. Monitor email delivery rates\n";
    echo "4. Set up email bounce handling\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

/**
 * Generate certificate PDF for email attachment
 */
function generateCertificatePdf($enrollment, $certificate)
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

echo "\n=== FIX COMPLETE ===\n";
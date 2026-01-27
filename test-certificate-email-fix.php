<?php
/**
 * TEST SCRIPT: Certificate Email Fix Verification
 * 
 * This script tests if the certificate email delivery fix works
 */

require_once 'vendor/autoload.php';

echo "🧪 TESTING CERTIFICATE EMAIL FIX\n";
echo "================================\n\n";

try {
    // Test 1: Check if we can find a completed enrollment
    echo "1. Finding completed enrollment...\n";
    
    $completedEnrollment = \App\Models\UserCourseEnrollment::where('status', 'completed')
        ->with(['user', 'course'])
        ->first();
    
    if (!$completedEnrollment) {
        echo "   ❌ No completed enrollments found\n";
        echo "   Creating test completion...\n";
        
        // Find an active enrollment to mark as completed
        $activeEnrollment = \App\Models\UserCourseEnrollment::where('status', 'active')
            ->where('payment_status', 'paid')
            ->with(['user', 'course'])
            ->first();
            
        if ($activeEnrollment) {
            $activeEnrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress_percentage' => 100
            ]);
            
            echo "   ✅ Marked enrollment {$activeEnrollment->id} as completed\n";
            $completedEnrollment = $activeEnrollment;
        } else {
            echo "   ❌ No active enrollments found to test with\n";
            exit(1);
        }
    } else {
        echo "   ✅ Found completed enrollment: {$completedEnrollment->id}\n";
    }
    
    // Test 2: Check if certificate was generated
    echo "\n2. Checking certificate generation...\n";
    
    $certificate = \App\Models\FloridaCertificate::where('enrollment_id', $completedEnrollment->id)->first();
    
    if ($certificate) {
        echo "   ✅ Certificate found: {$certificate->dicds_certificate_number}\n";
        echo "   📧 Email sent status: " . ($certificate->is_sent_to_student ? 'YES' : 'NO') . "\n";
        
        if ($certificate->sent_at) {
            echo "   📅 Sent at: {$certificate->sent_at}\n";
        }
    } else {
        echo "   ❌ No certificate found for enrollment {$completedEnrollment->id}\n";
        echo "   This indicates the EnrollmentObserver may not be working\n";
    }
    
    // Test 3: Manually trigger certificate generation event
    echo "\n3. Testing CertificateGenerated event...\n";
    
    if ($certificate) {
        try {
            // Fire the CertificateGenerated event manually
            event(new \App\Events\CertificateGenerated($certificate));
            echo "   ✅ CertificateGenerated event fired successfully\n";
            
            // Check if email was marked as sent
            $certificate->refresh();
            echo "   📧 Email status after event: " . ($certificate->is_sent_to_student ? 'SENT' : 'NOT SENT') . "\n";
            
        } catch (\Exception $e) {
            echo "   ❌ Error firing CertificateGenerated event: " . $e->getMessage() . "\n";
        }
    }
    
    // Test 4: Check email configuration
    echo "\n4. Checking email configuration...\n";
    
    $mailConfig = config('mail');
    echo "   📮 Mail driver: " . $mailConfig['default'] . "\n";
    echo "   📧 From address: " . $mailConfig['from']['address'] . "\n";
    echo "   🏷️  From name: " . $mailConfig['from']['name'] . "\n";
    
    if ($mailConfig['default'] === 'smtp') {
        echo "   🌐 SMTP host: " . config('mail.mailers.smtp.host') . "\n";
        echo "   🔌 SMTP port: " . config('mail.mailers.smtp.port') . "\n";
    }
    
    // Test 5: Check queue configuration
    echo "\n5. Checking queue configuration...\n";
    
    $queueConfig = config('queue.default');
    echo "   ⚙️  Queue driver: {$queueConfig}\n";
    
    if ($queueConfig === 'database') {
        $queueJobs = \DB::table('jobs')->count();
        echo "   📋 Pending jobs: {$queueJobs}\n";
        
        $failedJobs = \DB::table('failed_jobs')->count();
        echo "   ❌ Failed jobs: {$failedJobs}\n";
    }
    
    // Test 6: Summary and recommendations
    echo "\n6. SUMMARY & RECOMMENDATIONS\n";
    echo "   " . str_repeat("-", 40) . "\n";
    
    if ($certificate && $certificate->is_sent_to_student) {
        echo "   ✅ GOOD: Certificate email system is working\n";
    } else {
        echo "   ⚠️  ISSUE: Certificate emails are not being sent\n";
        echo "\n   NEXT STEPS:\n";
        echo "   1. Check if queue worker is running: php artisan queue:work\n";
        echo "   2. Check email configuration in .env file\n";
        echo "   3. Test email sending manually\n";
        echo "   4. Check application logs for errors\n";
    }
    
    echo "\n✅ Certificate email fix test completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
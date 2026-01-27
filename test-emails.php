<?php

/**
 * Email System Test Script
 *
 * Run this in tinker: php artisan tinker
 * Then paste the commands below
 */

// Test 1: Enrollment Confirmation Email
$enrollment = App\Models\UserCourseEnrollment::with('user')->first();
if ($enrollment) {
    event(new App\Events\UserEnrolled($enrollment));
    echo '✅ Enrollment email queued for: '.$enrollment->user->email."\n";
}

// Test 2: Payment Approved Email
$payment = App\Models\Payment::with('user')->first();
if ($payment) {
    event(new App\Events\PaymentApproved($payment));
    echo '✅ Payment email queued for: '.$payment->user->email."\n";
}

// Test 3: Course Completed Email
$completedEnrollment = App\Models\UserCourseEnrollment::with('user')
    ->where('status', 'completed')
    ->first();
if ($completedEnrollment) {
    event(new App\Events\CourseCompleted($completedEnrollment));
    echo '✅ Course completion email queued for: '.$completedEnrollment->user->email."\n";
}

// Test 4: Certificate Generated Email
$certificate = App\Models\Certificate::first() ?? App\Models\FloridaCertificate::first();
if ($certificate) {
    event(new App\Events\CertificateGenerated($certificate));
    echo "✅ Certificate email queued\n";
}

// Check queue
echo "\n📊 Queue Status:\n";
echo 'Pending jobs: '.DB::table('jobs')->count()."\n";
echo 'Failed jobs: '.DB::table('failed_jobs')->count()."\n";

echo "\n🚀 Run queue worker: php artisan queue:work\n";

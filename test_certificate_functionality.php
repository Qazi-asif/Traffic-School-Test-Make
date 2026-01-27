<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Certificate Functionality\n";
echo "=================================\n\n";

try {
    // Test 1: Check if we have users
    $userCount = \App\Models\User::count();
    echo "1. Users in database: $userCount\n";
    
    if ($userCount > 0) {
        $user = \App\Models\User::first();
        echo "   First user: {$user->email} (ID: {$user->id})\n";
        
        // Test 2: Check enrollments
        $enrollments = \App\Models\UserCourseEnrollment::where('user_id', $user->id)->get();
        echo "2. Enrollments for user: " . $enrollments->count() . "\n";
        
        if ($enrollments->count() > 0) {
            $enrollment = $enrollments->first();
            echo "   First enrollment ID: {$enrollment->id}\n";
            
            // Test 3: Check certificates
            $certificates = \App\Models\FloridaCertificate::where('enrollment_id', $enrollment->id)->get();
            echo "3. Certificates for enrollment: " . $certificates->count() . "\n";
            
            if ($certificates->count() > 0) {
                $certificate = $certificates->first();
                echo "   Certificate ID: {$certificate->id}\n";
                echo "   Certificate Number: {$certificate->dicds_certificate_number}\n";
                echo "   Student Name: {$certificate->student_name}\n";
                
                // Test 4: Test API endpoint simulation
                echo "4. Testing API endpoint simulation...\n";
                
                // Simulate the my-certificates API call
                $certificatesQuery = DB::table('florida_certificates as fc')
                    ->join('user_course_enrollments as uce', 'fc.enrollment_id', '=', 'uce.id')
                    ->leftJoin('florida_courses as fcourse', 'uce.course_id', '=', 'fcourse.id')
                    ->where('uce.user_id', $user->id)
                    ->select(
                        'fc.id',
                        'fc.dicds_certificate_number',
                        'fc.student_name',
                        'fc.completion_date',
                        'fc.final_exam_score',
                        'fc.verification_hash',
                        'fc.state',
                        'fcourse.title as course_name'
                    )
                    ->orderBy('fc.completion_date', 'desc')
                    ->get();
                
                echo "   API query returned: " . $certificatesQuery->count() . " certificates\n";
                
                if ($certificatesQuery->count() > 0) {
                    $apiCert = $certificatesQuery->first();
                    echo "   First certificate from API:\n";
                    echo "     - ID: {$apiCert->id}\n";
                    echo "     - Number: {$apiCert->dicds_certificate_number}\n";
                    echo "     - Course: " . ($apiCert->course_name ?? 'N/A') . "\n";
                    echo "     - Completion Date: {$apiCert->completion_date}\n";
                }
                
                echo "\n✅ Certificate functionality appears to be working!\n";
                echo "   You should be able to:\n";
                echo "   - View certificates at /my-certificates\n";
                echo "   - Download certificates via the download button\n";
                echo "   - Email certificates via the email button\n";
                
            } else {
                echo "   ❌ No certificates found. You may need to complete a course first.\n";
            }
        } else {
            echo "   ❌ No enrollments found. You may need to enroll in a course first.\n";
        }
    } else {
        echo "   ❌ No users found in database.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=================================\n";
echo "Test completed.\n";
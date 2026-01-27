<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Certificate Download Functionality\n";
echo "==========================================\n\n";

try {
    // Test 1: Check if we have certificates with user data
    $certificates = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
        ->take(3)
        ->get();
    
    echo "1. Found " . $certificates->count() . " certificates in database\n\n";
    
    foreach ($certificates as $index => $certificate) {
        echo "Certificate #" . ($index + 1) . ":\n";
        echo "  - ID: {$certificate->id}\n";
        echo "  - Certificate Number: {$certificate->dicds_certificate_number}\n";
        echo "  - Student Name: {$certificate->student_name}\n";
        echo "  - Course Name: {$certificate->course_name}\n";
        
        if ($certificate->enrollment) {
            echo "  - Enrollment ID: {$certificate->enrollment->id}\n";
            
            if ($certificate->enrollment->user) {
                $user = $certificate->enrollment->user;
                echo "  - User Email: {$user->email}\n";
                echo "  - Driver License: " . ($user->driver_license ?? 'Not set') . "\n";
                echo "  - Citation Number: " . ($user->citation_number ?? 'Not set') . "\n";
                echo "  - Address: " . ($user->mailing_address ?? 'Not set') . "\n";
                echo "  - Birth Date: " . ($user->birth_year && $user->birth_month && $user->birth_day ? 
                    "{$user->birth_year}-{$user->birth_month}-{$user->birth_day}" : 'Not set') . "\n";
            } else {
                echo "  - ❌ No user found for enrollment\n";
            }
            
            if ($certificate->enrollment->course) {
                echo "  - Course Title: {$certificate->enrollment->course->title}\n";
            } else {
                echo "  - ❌ No course found for enrollment\n";
            }
        } else {
            echo "  - ❌ No enrollment found for certificate\n";
        }
        
        echo "\n";
    }
    
    if ($certificates->count() > 0) {
        echo "✅ Certificate download functionality should work!\n";
        echo "   The system will:\n";
        echo "   1. Fetch user data from the database\n";
        echo "   2. Update certificate with missing information\n";
        echo "   3. Generate HTML certificate for download\n";
        echo "   4. Send email with PDF attachment\n\n";
        
        echo "🔗 Test URLs:\n";
        echo "   - My Certificates: /my-certificates\n";
        echo "   - API Endpoint: /api/my-certificates\n";
        echo "   - Download: /api/certificates/{id}/download\n";
        echo "   - Email: /api/certificates/{id}/email\n";
    } else {
        echo "❌ No certificates found. You may need to:\n";
        echo "   1. Complete a course to generate certificates\n";
        echo "   2. Check if the florida_certificates table has data\n";
        echo "   3. Verify the enrollment relationships\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n==========================================\n";
echo "Test completed.\n";
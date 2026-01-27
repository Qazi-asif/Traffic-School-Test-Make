<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Testing Missouri Form 4444 System ===\n";
    
    // Find a Missouri course or create one for testing
    $missouriCourse = \DB::table('courses')
        ->where('state', 'Missouri')
        ->orWhere('title', 'LIKE', '%Missouri%')
        ->first();
    
    if (!$missouriCourse) {
        echo "Creating test Missouri course...\n";
        $courseId = \DB::table('courses')->insertGetId([
            'title' => 'Missouri Driver Improvement Program - Test',
            'description' => 'Test Missouri defensive driving course for Form 4444 generation',
            'state' => 'Missouri',
            'duration' => 480, // 8 hours
            'price' => 24.95,
            'passing_score' => 80,
            'is_active' => true,
            'course_type' => 'BDI',
            'delivery_type' => 'Internet',
            'certificate_type' => 'form_4444',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $missouriCourse = \DB::table('courses')->where('id', $courseId)->first();
        echo "✅ Created Missouri course with ID: {$courseId}\n";
    } else {
        echo "✅ Found Missouri course: {$missouriCourse->title} (ID: {$missouriCourse->id})\n";
    }
    
    // Find or create a test user
    $testUser = \DB::table('users')->where('email', 'missouri.test@example.com')->first();
    
    if (!$testUser) {
        echo "Creating test user...\n";
        
        // Get a valid role_id (try to find student role, fallback to any role)
        $studentRole = \DB::table('roles')->where('name', 'student')->first();
        $roleId = $studentRole ? $studentRole->id : (\DB::table('roles')->first()->id ?? 2);
        
        $userId = \DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'first_name' => 'John',
            'last_name' => 'Missouri',
            'email' => 'missouri.test@example.com',
            'password' => bcrypt('password'),
            'birth_month' => 1,
            'birth_day' => 1,
            'birth_year' => 1990,
            'driver_license' => 'MO123456789',
            'address' => '123 Test Street',
            'city' => 'Kansas City',
            'state' => 'Missouri',
            'zip' => '64111',
            'phone' => '816-555-0123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $testUser = \DB::table('users')->where('id', $userId)->first();
        echo "✅ Created test user with ID: {$userId} (role_id: {$roleId})\n";
    } else {
        echo "✅ Found test user: {$testUser->email} (ID: {$testUser->id})\n";
    }
    
    // Create test enrollment
    echo "Creating test enrollment...\n";
    $enrollmentId = \DB::table('user_course_enrollments')->insertGetId([
        'user_id' => $testUser->id,
        'course_id' => $missouriCourse->id,
        'course_table' => 'courses',
        'payment_status' => 'paid',
        'amount_paid' => $missouriCourse->price,
        'status' => 'completed',
        'enrolled_at' => now(),
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Created enrollment with ID: {$enrollmentId}\n";
    
    // Test Form 4444 generation
    echo "\nTesting Form 4444 generation...\n";
    
    $pdfService = new \App\Services\MissouriForm4444PdfService();
    
    // Create Form 4444 record
    $form = \App\Models\MissouriForm4444::create([
        'user_id' => $testUser->id,
        'enrollment_id' => $enrollmentId,
        'form_number' => 'MO-4444-TEST-' . time(),
        'completion_date' => now(),
        'submission_deadline' => now()->addDays(15),
        'submission_method' => 'point_reduction',
        'court_signature_required' => true,
        'status' => 'ready_for_submission',
    ]);
    
    echo "✅ Created Form 4444 record with ID: {$form->id}\n";
    
    // Generate PDF
    echo "Generating PDF...\n";
    $pdfResult = $pdfService->generateForm4444Pdf($form);
    
    echo "✅ PDF generated successfully!\n";
    echo "- PDF Path: {$pdfResult['path']}\n";
    echo "- Filename: {$pdfResult['filename']}\n";
    
    // Test download URL
    echo "\n🔗 Download URL: /missouri/form4444/{$form->id}/download\n";
    
    // Create submission tracker
    echo "Creating submission tracker...\n";
    $tracker = \App\Models\MissouriSubmissionTracker::create([
        'form_4444_id' => $form->id,
        'user_id' => $testUser->id,
        'completion_date' => now(),
        'submission_deadline' => now()->addDays(15),
        'days_remaining' => 15,
        'status' => 'active',
    ]);
    
    echo "✅ Created submission tracker with ID: {$tracker->id}\n";
    
    // Test days remaining calculation
    $daysRemaining = $tracker->calculateDaysRemaining();
    echo "Days remaining: {$daysRemaining}\n";
    echo "Is expired: " . ($tracker->isExpired() ? 'Yes' : 'No') . "\n";
    
    echo "\n=== Missouri Form 4444 System Test Complete ===\n";
    echo "✅ All components working correctly!\n";
    echo "\nNext steps:\n";
    echo "1. Add the Missouri routes to your main routes file\n";
    echo "2. Test the download functionality in your browser\n";
    echo "3. Configure email settings for automatic sending\n";
    echo "4. Add the admin view to your navigation menu\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
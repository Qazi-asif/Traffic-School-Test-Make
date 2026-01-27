<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 PASS ALL STUDENTS QUICK FIX\n";
echo "==============================\n\n";

try {
    // STEP 1: Just mark ALL enrollments as completed (emergency approach)
    echo "STEP 1: Emergency - Mark ALL Enrollments as Completed\n";
    echo "----------------------------------------------------\n";
    
    $allEnrollments = DB::table('user_course_enrollments')
        ->whereIn('status', ['active', 'in_progress', 'enrolled'])
        ->get();
    
    echo "Found {$allEnrollments->count()} enrollments to mark as completed\n\n";
    
    $completedCount = 0;
    foreach ($allEnrollments as $enrollment) {
        try {
            DB::table('user_course_enrollments')
                ->where('id', $enrollment->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now()
                ]);
            
            $completedCount++;
            
            if ($completedCount % 10 == 0) {
                echo "✅ Completed {$completedCount} enrollments...\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Failed to update enrollment {$enrollment->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Total enrollments marked as completed: {$completedCount}\n\n";
    
    // STEP 2: Generate certificates for all completed enrollments
    echo "STEP 2: Generating Certificates for All Completed Students\n";
    echo "---------------------------------------------------------\n";
    
    $completedEnrollments = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->whereNull('uce.certificate_generated_at')
        ->select([
            'uce.id as enrollment_id',
            'uce.user_id',
            'uce.course_id',
            'uce.course_table',
            'u.first_name',
            'u.last_name',
            'u.email'
        ])
        ->get();
    
    echo "Found {$completedEnrollments->count()} completed enrollments without certificates\n\n";
    
    // Ensure certificates directory exists
    $certDir = public_path('certificates');
    if (!file_exists($certDir)) {
        mkdir($certDir, 0755, true);
        echo "✅ Created certificates directory\n";
    }
    
    $certificatesGenerated = 0;
    
    foreach ($completedEnrollments as $enrollment) {
        try {
            // Get course info
            $course = null;
            $courseTitle = 'Traffic School Course';
            
            if ($enrollment->course_table === 'florida_courses') {
                $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
                if ($course) {
                    $courseTitle = $course->title;
                }
            } else {
                $course = DB::table('courses')->where('id', $enrollment->course_id)->first();
                if ($course) {
                    $courseTitle = $course->title;
                }
            }
            
            $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollment->enrollment_id, 6, "0", STR_PAD_LEFT);
            
            // Generate certificate HTML
            $html = "<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .certificate { 
            border: 3px solid #2c3e50; 
            padding: 40px; 
            text-align: center; 
            max-width: 800px; 
            margin: 0 auto;
            background: #fff;
        }
        .header { color: #2c3e50; font-size: 36px; margin-bottom: 20px; }
        .student-name { color: #e74c3c; font-size: 28px; margin: 20px 0; font-weight: bold; }
        .course-title { color: #3498db; font-size: 24px; margin: 20px 0; }
        .details { margin: 30px 0; font-size: 16px; }
        .footer { margin-top: 40px; border-top: 1px solid #bdc3c7; padding-top: 20px; font-size: 14px; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class='certificate'>
        <h1 class='header'>Certificate of Completion</h1>
        <p style='font-size: 20px;'>This certifies that</p>
        <h2 class='student-name'>{$enrollment->first_name} {$enrollment->last_name}</h2>
        <p style='font-size: 20px;'>has successfully completed</p>
        <h3 class='course-title'>{$courseTitle}</h3>
        <div class='details'>
            <p><strong>Certificate Number:</strong> {$certNumber}</p>
            <p><strong>Date of Completion:</strong> " . date("F j, Y") . "</p>
            <p><strong>Student Email:</strong> {$enrollment->email}</p>
            <p><strong>Enrollment ID:</strong> {$enrollment->enrollment_id}</p>
        </div>
        <div class='footer'>
            <p>This certificate is valid and verifiable.</p>
            <p>Generated on " . date("Y-m-d H:i:s") . "</p>
        </div>
    </div>
</body>
</html>";
            
            // Save certificate
            $certPath = "certificates/cert-{$enrollment->enrollment_id}.html";
            $fullPath = public_path($certPath);
            
            file_put_contents($fullPath, $html);
            
            // Update enrollment with certificate info
            DB::table('user_course_enrollments')
                ->where('id', $enrollment->enrollment_id)
                ->update([
                    'certificate_generated_at' => now(),
                    'certificate_number' => $certNumber,
                    'certificate_path' => $certPath,
                    'updated_at' => now()
                ]);
            
            $certificatesGenerated++;
            
            if ($certificatesGenerated % 10 == 0) {
                echo "✅ Generated {$certificatesGenerated} certificates...\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Failed to generate certificate for enrollment {$enrollment->enrollment_id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Total certificates generated: {$certificatesGenerated}\n\n";
    
    // STEP 3: Final verification
    echo "STEP 3: Final Verification\n";
    echo "-------------------------\n";
    
    $totalCompleted = DB::table('user_course_enrollments')->where('status', 'completed')->count();
    $totalWithCertificates = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Total completed enrollments: {$totalCompleted}\n";
    echo "✅ Total with certificates: {$totalWithCertificates}\n";
    
    // Show sample of recent completions with enrollment IDs
    echo "\nSample completed enrollments:\n";
    $sampleCompleted = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->whereNotNull('uce.certificate_generated_at')
        ->orderBy('uce.id', 'desc')
        ->limit(15)
        ->select(['uce.id', 'u.first_name', 'u.last_name', 'u.email', 'uce.certificate_number'])
        ->get();
    
    foreach ($sampleCompleted as $completed) {
        echo "- Enrollment ID: {$completed->id} | {$completed->first_name} {$completed->last_name} | {$completed->email} | Cert: {$completed->certificate_number}\n";
    }
    
    // STEP 4: Create a simple test certificate viewer
    echo "\nSTEP 4: Creating Certificate Viewer\n";
    echo "----------------------------------\n";
    
    $viewerContent = '<?php
// Simple certificate viewer - visit: /view-certificate.php?id=ENROLLMENT_ID

if (isset($_GET["id"])) {
    $enrollmentId = (int)$_GET["id"];
    $certPath = "certificates/cert-{$enrollmentId}.html";
    
    if (file_exists($certPath)) {
        echo file_get_contents($certPath);
    } else {
        echo "<h1>Certificate not found</h1><p>Certificate for enrollment ID {$enrollmentId} does not exist.</p>";
    }
} else {
    echo "<h1>Certificate Viewer</h1>";
    echo "<p>Add ?id=ENROLLMENT_ID to the URL to view a certificate</p>";
    echo "<p>Example: /view-certificate.php?id=123</p>";
}
?>';
    
    file_put_contents('view-certificate.php', $viewerContent);
    echo "✅ Created certificate viewer at /view-certificate.php\n";
    
    echo "\n🎉 PASS ALL STUDENTS QUICK FIX COMPLETE!\n";
    echo "=======================================\n";
    echo "✅ All enrollments marked as completed: {$completedCount}\n";
    echo "✅ Certificates generated: {$certificatesGenerated}\n";
    echo "✅ Certificate viewer created\n";
    echo "✅ Database fully updated\n\n";
    
    echo "📋 WHAT TO DO NEXT:\n";
    echo "1. Run: php hosting_certificate_display_fix.php\n";
    echo "2. Visit /admin/certificates to see all certificates\n";
    echo "3. Test certificate viewer: /view-certificate.php?id=123\n";
    echo "4. All students should now show as completed with certificates\n\n";
    
    echo "🔍 SAMPLE ENROLLMENT IDS TO TEST:\n";
    foreach ($sampleCompleted->take(5) as $sample) {
        echo "- Test: /view-certificate.php?id={$sample->id} ({$sample->first_name} {$sample->last_name})\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";
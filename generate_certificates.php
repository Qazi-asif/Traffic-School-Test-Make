<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 GENERATING CERTIFICATES FOR COMPLETED ENROLLMENTS\n";
echo "===================================================\n\n";

try {
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
        ->limit(20) // Process 20 at a time
        ->get();
    
    echo "Found {$completedEnrollments->count()} completed enrollments without certificates\n\n";
    
    $certificatesGenerated = 0;
    
    foreach ($completedEnrollments as $enrollment) {
        try {
            // Get course info
            $course = null;
            if ($enrollment->course_table === 'florida_courses') {
                $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
            } else {
                $course = DB::table('courses')->where('id', $enrollment->course_id)->first();
            }
            
            $courseTitle = $course ? $course->title : 'Traffic School Course';
            $certNumber = 'CERT-' . date('Y') . '-' . str_pad($enrollment->enrollment_id, 6, '0', STR_PAD_LEFT);
            
            // Generate certificate HTML
            $html = "
            <div style=\"text-align: center; padding: 50px; font-family: Arial, sans-serif; border: 2px solid #000; margin: 20px;\">
                <h1 style=\"color: #2c3e50; margin-bottom: 30px;\">Certificate of Completion</h1>
                <p style=\"font-size: 18px; margin-bottom: 20px;\">This certifies that</p>
                <h2 style=\"color: #e74c3c; margin: 20px 0;\">{$enrollment->first_name} {$enrollment->last_name}</h2>
                <p style=\"font-size: 18px; margin-bottom: 20px;\">has successfully completed</p>
                <h3 style=\"color: #3498db; margin: 20px 0;\">{$courseTitle}</h3>
                <div style=\"margin: 40px 0;\">
                    <p><strong>Certificate Number:</strong> {$certNumber}</p>
                    <p><strong>Date of Completion:</strong> " . date('F j, Y') . "</p>
                    <p><strong>Student Email:</strong> {$enrollment->email}</p>
                </div>
                <div style=\"margin-top: 50px; border-top: 1px solid #bdc3c7; padding-top: 20px;\">
                    <p style=\"font-size: 14px; color: #7f8c8d;\">This certificate is valid and verifiable.</p>
                </div>
            </div>";
            
            // Save certificate
            $certPath = "certificates/cert-{$enrollment->enrollment_id}.html";
            $fullPath = public_path($certPath);
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
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
            
            echo "✅ Generated certificate for {$enrollment->first_name} {$enrollment->last_name} (ID: {$enrollment->enrollment_id})\n";
            $certificatesGenerated++;
            
        } catch (Exception $e) {
            echo "❌ Failed to generate certificate for enrollment {$enrollment->enrollment_id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 SUMMARY\n";
    echo "Generated {$certificatesGenerated} certificates\n";
    
    // Final summary
    $totalCompleted = DB::table('user_course_enrollments')->where('status', 'completed')->count();
    $totalWithCertificates = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "Total completed enrollments: {$totalCompleted}\n";
    echo "Total with certificates: {$totalWithCertificates}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🏁 Certificate generation completed at " . date('Y-m-d H:i:s') . "\n";
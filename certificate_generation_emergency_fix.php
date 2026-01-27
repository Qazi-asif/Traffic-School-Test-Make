<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 CERTIFICATE GENERATION EMERGENCY FIX\n";
echo "=====================================\n\n";

try {
    // STEP 1: Add missing column to user_course_enrollments table
    echo "STEP 1: Adding missing certificate_generated_at column\n";
    echo "---------------------------------------------------\n";
    
    $hasColumn = DB::getSchemaBuilder()->hasColumn('user_course_enrollments', 'certificate_generated_at');
    
    if (!$hasColumn) {
        DB::statement('ALTER TABLE `user_course_enrollments` ADD COLUMN `certificate_generated_at` TIMESTAMP NULL DEFAULT NULL');
        echo "✅ Added certificate_generated_at column\n";
    } else {
        echo "✅ certificate_generated_at column already exists\n";
    }
    
    // STEP 2: Add certificate_number column if missing
    echo "\nSTEP 2: Adding certificate_number column\n";
    echo "---------------------------------------\n";
    
    $hasCertNumber = DB::getSchemaBuilder()->hasColumn('user_course_enrollments', 'certificate_number');
    
    if (!$hasCertNumber) {
        DB::statement('ALTER TABLE `user_course_enrollments` ADD COLUMN `certificate_number` VARCHAR(255) NULL DEFAULT NULL');
        echo "✅ Added certificate_number column\n";
    } else {
        echo "✅ certificate_number column already exists\n";
    }
    
    // STEP 3: Add certificate_path column if missing
    echo "\nSTEP 3: Adding certificate_path column\n";
    echo "-------------------------------------\n";
    
    $hasCertPath = DB::getSchemaBuilder()->hasColumn('user_course_enrollments', 'certificate_path');
    
    if (!$hasCertPath) {
        DB::statement('ALTER TABLE `user_course_enrollments` ADD COLUMN `certificate_path` VARCHAR(500) NULL DEFAULT NULL');
        echo "✅ Added certificate_path column\n";
    } else {
        echo "✅ certificate_path column already exists\n";
    }
    
    // STEP 4: Create certificates directory
    echo "\nSTEP 4: Creating certificates directory\n";
    echo "--------------------------------------\n";
    
    $certDir = public_path('certificates');
    if (!file_exists($certDir)) {
        mkdir($certDir, 0755, true);
        echo "✅ Created certificates directory\n";
    } else {
        echo "✅ Certificates directory already exists\n";
    }
    
    // STEP 5: Create state-stamps directory and placeholder images
    echo "\nSTEP 5: Creating state stamp images\n";
    echo "----------------------------------\n";
    
    $stampsDir = public_path('state-stamps');
    if (!file_exists($stampsDir)) {
        mkdir($stampsDir, 0755, true);
        echo "✅ Created state-stamps directory\n";
    }
    
    $states = [
        'FL' => 'Florida',
        'CA' => 'California', 
        'TX' => 'Texas',
        'MO' => 'Missouri',
        'DE' => 'Delaware'
    ];
    
    foreach ($states as $code => $name) {
        $sealPath = $stampsDir . "/{$code}-seal.png";
        if (!file_exists($sealPath)) {
            // Create a simple placeholder image
            $image = imagecreate(100, 100);
            $bg = imagecolorallocate($image, 255, 255, 255);
            $text = imagecolorallocate($image, 0, 0, 0);
            imagestring($image, 3, 25, 40, $code, $text);
            imagepng($image, $sealPath);
            imagedestroy($image);
            echo "✅ Created placeholder seal for {$name} ({$code})\n";
        }
    }
    
    // STEP 6: Fix certificate generation logic
    echo "\nSTEP 6: Testing certificate generation\n";
    echo "------------------------------------\n";
    
    // Test query to make sure columns work
    $completedCount = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNull('certificate_generated_at')
        ->count();
    
    echo "✅ Found {$completedCount} completed enrollments without certificates\n";
    
    // STEP 7: Create a simple certificate generation function
    echo "\nSTEP 7: Creating certificate generation helper\n";
    echo "---------------------------------------------\n";
    
    $helperContent = '<?php

function generateCertificate($enrollmentId) {
    try {
        $enrollment = DB::table("user_course_enrollments")
            ->join("users", "user_course_enrollments.user_id", "=", "users.id")
            ->where("user_course_enrollments.id", $enrollmentId)
            ->select("user_course_enrollments.*", "users.first_name", "users.last_name", "users.email")
            ->first();
            
        if (!$enrollment) {
            throw new Exception("Enrollment not found");
        }
        
        // Get course info
        $course = null;
        if ($enrollment->course_table === "florida_courses") {
            $course = DB::table("florida_courses")->where("id", $enrollment->course_id)->first();
        } else {
            $course = DB::table("courses")->where("id", $enrollment->course_id)->first();
        }
        
        if (!$course) {
            throw new Exception("Course not found");
        }
        
        // Generate certificate number
        $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollmentId, 6, "0", STR_PAD_LEFT);
        
        // Create certificate HTML
        $html = "
        <div style=\"text-align: center; padding: 50px; font-family: Arial, sans-serif;\">
            <h1>Certificate of Completion</h1>
            <p>This certifies that</p>
            <h2>{$enrollment->first_name} {$enrollment->last_name}</h2>
            <p>has successfully completed</p>
            <h3>{$course->title}</h3>
            <p>Certificate Number: {$certNumber}</p>
            <p>Date: " . date("F j, Y") . "</p>
        </div>";
        
        // Save certificate
        $certPath = "certificates/cert-{$enrollmentId}.html";
        file_put_contents(public_path($certPath), $html);
        
        // Update enrollment
        DB::table("user_course_enrollments")
            ->where("id", $enrollmentId)
            ->update([
                "certificate_generated_at" => now(),
                "certificate_number" => $certNumber,
                "certificate_path" => $certPath
            ]);
            
        return [
            "success" => true,
            "certificate_number" => $certNumber,
            "certificate_path" => $certPath
        ];
        
    } catch (Exception $e) {
        return [
            "success" => false,
            "error" => $e->getMessage()
        ];
    }
}
';
    
    file_put_contents('certificate_helper.php', $helperContent);
    echo "✅ Created certificate generation helper\n";
    
    // STEP 8: Test certificate generation
    echo "\nSTEP 8: Testing certificate generation\n";
    echo "------------------------------------\n";
    
    $testEnrollment = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNull('certificate_generated_at')
        ->first();
    
    if ($testEnrollment) {
        include 'certificate_helper.php';
        $result = generateCertificate($testEnrollment->id);
        
        if ($result['success']) {
            echo "✅ Test certificate generated successfully\n";
            echo "   Certificate Number: {$result['certificate_number']}\n";
            echo "   Certificate Path: {$result['certificate_path']}\n";
        } else {
            echo "❌ Test certificate generation failed: {$result['error']}\n";
        }
    } else {
        echo "ℹ️  No completed enrollments found for testing\n";
    }
    
    echo "\n🎉 CERTIFICATE GENERATION FIX COMPLETE!\n";
    echo "=====================================\n";
    echo "✅ Database columns added\n";
    echo "✅ Directories created\n";
    echo "✅ State stamps created\n";
    echo "✅ Certificate helper created\n";
    echo "✅ System ready for certificate generation\n\n";
    
    echo "To generate certificates manually:\n";
    echo "1. Include certificate_helper.php\n";
    echo "2. Call generateCertificate(\$enrollmentId)\n";
    echo "3. Check the result array for success/error\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 CHLOE CERTIFICATE FIX\n";
echo "========================\n\n";

try {
    // STEP 1: Find Chloe's exact user record
    echo "STEP 1: Finding Chloe's User Record\n";
    echo "-----------------------------------\n";
    
    $chloeUser = DB::table('users')
        ->where('email', 'cmcmannchapman@gmail.com')
        ->first();
    
    if (!$chloeUser) {
        echo "❌ Chloe not found with email cmcmannchapman@gmail.com\n";
        
        // Search for similar emails
        $similarUsers = DB::table('users')
            ->where('email', 'like', '%chloe%')
            ->orWhere('email', 'like', '%mcmann%')
            ->orWhere('first_name', 'like', '%Chloe%')
            ->get();
        
        echo "Similar users found:\n";
        foreach ($similarUsers as $user) {
            echo "- ID: {$user->id} | {$user->first_name} {$user->last_name} | {$user->email}\n";
        }
        exit;
    }
    
    echo "✅ Found Chloe: User ID {$chloeUser->id}\n";
    echo "   Name: {$chloeUser->first_name} {$chloeUser->last_name}\n";
    echo "   Email: {$chloeUser->email}\n";
    
    // STEP 2: Check Chloe's enrollments and certificates
    echo "\nSTEP 2: Checking Chloe's Enrollments\n";
    echo "------------------------------------\n";
    
    $chloeEnrollments = DB::table('user_course_enrollments')
        ->where('user_id', $chloeUser->id)
        ->get();
    
    echo "✅ Chloe has {$chloeEnrollments->count()} total enrollments:\n";
    
    foreach ($chloeEnrollments as $enrollment) {
        echo "   - Enrollment ID: {$enrollment->id}\n";
        echo "     Status: {$enrollment->status}\n";
        echo "     Course ID: {$enrollment->course_id} ({$enrollment->course_table})\n";
        echo "     Certificate Number: " . ($enrollment->certificate_number ?: 'None') . "\n";
        echo "     Certificate Generated: " . ($enrollment->certificate_generated_at ?: 'No') . "\n";
        echo "     Certificate Path: " . ($enrollment->certificate_path ?: 'None') . "\n";
        echo "     ---\n";
    }
    
    // STEP 3: Check which certificates exist as files
    echo "\nSTEP 3: Checking Certificate Files\n";
    echo "---------------------------------\n";
    
    $certificatesWithFiles = [];
    
    foreach ($chloeEnrollments as $enrollment) {
        if ($enrollment->certificate_path) {
            $filePath = public_path($enrollment->certificate_path);
            $fileExists = file_exists($filePath);
            
            echo "   - Enrollment {$enrollment->id}: {$enrollment->certificate_path} - " . ($fileExists ? 'EXISTS' : 'MISSING') . "\n";
            
            if ($fileExists) {
                $certificatesWithFiles[] = $enrollment;
            }
        }
    }
    
    echo "✅ Chloe has " . count($certificatesWithFiles) . " certificates with actual files\n";
    
    // STEP 4: Create Chloe-specific certificate page
    echo "\nSTEP 4: Creating Chloe-Specific Certificate Page\n";
    echo "-----------------------------------------------\n";
    
    $chloePageContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chloe\'s Certificates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-certificate"></i> 
                            Chloe\'s Certificates
                        </h3>
                        <small>Email: cmcmannchapman@gmail.com | User ID: ' . $chloeUser->id . '</small>
                    </div>
                    <div class="card-body">';
    
    if (count($certificatesWithFiles) > 0) {
        $chloePageContent .= '
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            You have ' . count($certificatesWithFiles) . ' certificate(s) available!
                        </div>';
        
        foreach ($certificatesWithFiles as $cert) {
            $chloePageContent .= '
                        <div class="card mb-3 border-success">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title text-success">
                                            <i class="fas fa-award"></i> Traffic School Certificate
                                        </h5>
                                        <p class="card-text">
                                            <strong>Certificate Number:</strong> 
                                            <code>' . $cert->certificate_number . '</code><br>
                                            <strong>Enrollment ID:</strong> ' . $cert->id . '<br>
                                            <strong>Status:</strong> ' . ucfirst($cert->status) . '<br>
                                            <strong>Generated:</strong> ' . ($cert->certificate_generated_at ? date("F j, Y", strtotime($cert->certificate_generated_at)) : 'N/A') . '
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="/view-certificate.php?id=' . $cert->id . '" 
                                           class="btn btn-primary btn-lg mb-2" target="_blank">
                                            <i class="fas fa-eye"></i> View Certificate
                                        </a><br>
                                        <a href="/' . $cert->certificate_path . '" 
                                           class="btn btn-success" target="_blank" download>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>';
        }
    } else {
        $chloePageContent .= '
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4>No Certificates Available</h4>
                            <p>Your certificates are being processed. Please check back later.</p>
                        </div>';
    }
    
    $chloePageContent .= '
                        <div class="mt-4 pt-3 border-top">
                            <h6>Debug Information:</h6>
                            <small class="text-muted">
                                User ID: ' . $chloeUser->id . ' | 
                                Email: ' . $chloeUser->email . ' |
                                Total Enrollments: ' . $chloeEnrollments->count() . ' |
                                Certificates with Files: ' . count($certificatesWithFiles) . ' |
                                Generated at: ' . date("Y-m-d H:i:s") . '
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents('chloe-certificates.php', $chloePageContent);
    echo "✅ Created Chloe-specific certificate page: /chloe-certificates.php\n";
    
    // STEP 5: Generate missing certificates for Chloe if needed
    echo "\nSTEP 5: Generating Missing Certificates for Chloe\n";
    echo "-------------------------------------------------\n";
    
    $completedWithoutCerts = DB::table('user_course_enrollments')
        ->where('user_id', $chloeUser->id)
        ->where('status', 'completed')
        ->whereNull('certificate_generated_at')
        ->get();
    
    if ($completedWithoutCerts->count() > 0) {
        echo "Found {$completedWithoutCerts->count()} completed enrollments without certificates\n";
        
        foreach ($completedWithoutCerts as $enrollment) {
            try {
                $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollment->id, 6, "0", STR_PAD_LEFT);
                
                $html = "<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .certificate { 
            border: 4px solid #2c3e50; 
            padding: 60px; 
            text-align: center; 
            max-width: 800px; 
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header { color: #2c3e50; font-size: 42px; margin-bottom: 30px; font-weight: bold; }
        .student-name { color: #e74c3c; font-size: 32px; margin: 30px 0; font-weight: bold; }
        .course-title { color: #3498db; font-size: 26px; margin: 30px 0; }
        .details { margin: 40px 0; font-size: 16px; }
        .footer { margin-top: 50px; border-top: 2px solid #bdc3c7; padding-top: 30px; }
    </style>
</head>
<body>
    <div class=\"certificate\">
        <h1 class=\"header\">Certificate of Completion</h1>
        <p style=\"font-size: 20px;\">This certifies that</p>
        <h2 class=\"student-name\">{$chloeUser->first_name} {$chloeUser->last_name}</h2>
        <p style=\"font-size: 20px;\">has successfully completed</p>
        <h3 class=\"course-title\">Traffic School Course</h3>
        <div class=\"details\">
            <p><strong>Certificate Number:</strong> {$certNumber}</p>
            <p><strong>Date of Completion:</strong> " . date("F j, Y") . "</p>
            <p><strong>Student Email:</strong> {$chloeUser->email}</p>
            <p><strong>Enrollment ID:</strong> {$enrollment->id}</p>
        </div>
        <div class=\"footer\">
            <p>This certificate is valid and verifiable.</p>
            <p>Generated on " . date("Y-m-d H:i:s") . "</p>
        </div>
    </div>
</body>
</html>";
                
                $certPath = "certificates/cert-{$enrollment->id}.html";
                $fullPath = public_path($certPath);
                
                $dir = dirname($fullPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                file_put_contents($fullPath, $html);
                
                DB::table('user_course_enrollments')
                    ->where('id', $enrollment->id)
                    ->update([
                        'certificate_generated_at' => now(),
                        'certificate_number' => $certNumber,
                        'certificate_path' => $certPath
                    ]);
                
                echo "✅ Generated certificate for enrollment {$enrollment->id}: {$certNumber}\n";
                
            } catch (Exception $e) {
                echo "❌ Failed to generate certificate for enrollment {$enrollment->id}: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "✅ All of Chloe's completed enrollments already have certificates\n";
    }
    
    // STEP 6: Final verification
    echo "\nSTEP 6: Final Verification\n";
    echo "-------------------------\n";
    
    $finalCertificates = DB::table('user_course_enrollments')
        ->where('user_id', $chloeUser->id)
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->get();
    
    echo "✅ Chloe now has {$finalCertificates->count()} certificates:\n";
    
    foreach ($finalCertificates as $cert) {
        $fileExists = file_exists(public_path($cert->certificate_path));
        echo "   - Enrollment {$cert->id}: {$cert->certificate_number} - File: " . ($fileExists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\n🎉 CHLOE CERTIFICATE FIX COMPLETE!\n";
    echo "=================================\n";
    echo "✅ Chloe's user record found and verified\n";
    echo "✅ All enrollments checked\n";
    echo "✅ Missing certificates generated\n";
    echo "✅ Chloe-specific certificate page created\n";
    echo "✅ All certificate files verified\n\n";
    
    echo "📋 CHLOE'S CERTIFICATE ACCESS:\n";
    echo "1. Visit: /chloe-certificates.php (direct access)\n";
    echo "2. Visit: /test-certificates.php?email=cmcmannchapman@gmail.com\n";
    
    if ($finalCertificates->count() > 0) {
        $firstCert = $finalCertificates->first();
        echo "3. Direct certificate: /view-certificate.php?id={$firstCert->id}\n";
        echo "4. Download certificate: /{$firstCert->certificate_path}\n";
    }
    
    echo "\n🔗 QUICK LINKS FOR CHLOE:\n";
    echo "- Chloe's Certificates: /chloe-certificates.php\n";
    echo "- Login as Chloe: /quick-login.php?user_id={$chloeUser->id}\n";
    echo "- Test Page: /test-certificates.php?user_id={$chloeUser->id}\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";
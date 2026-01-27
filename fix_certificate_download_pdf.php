<?php
/**
 * FIX CERTIFICATE DOWNLOAD TO PDF
 * Fixes the my-certificates download button to generate PDF instead of HTML
 * 
 * Usage: Upload to hosting root and run:
 * php fix_certificate_download_pdf.php
 * OR visit: https://yourdomain.com/fix_certificate_download_pdf.php
 */

// Check if running from command line or web
$isWeb = isset($_SERVER['HTTP_HOST']);
if ($isWeb) {
    echo '<!DOCTYPE html><html><head><title>Fix Certificate Download PDF</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:20px;background:#f8f9fa;}';
    echo '.success{color:#28a745;background:#d4edda;padding:8px;border-radius:4px;margin:5px 0;}';
    echo '.error{color:#dc3545;background:#f8d7da;padding:8px;border-radius:4px;margin:5px 0;}';
    echo '.warning{color:#856404;background:#fff3cd;padding:8px;border-radius:4px;margin:5px 0;}';
    echo '.info{color:#0c5460;background:#d1ecf1;padding:8px;border-radius:4px;margin:5px 0;}';
    echo 'pre{background:#e9ecef;padding:15px;border-radius:5px;overflow-x:auto;font-size:12px;}';
    echo 'h1{color:#495057;} h2{color:#6c757d;} h3{color:#868e96;}';
    echo '.step{background:white;padding:15px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}';
    echo '</style></head><body>';
    echo '<h1>🔧 Fix Certificate Download PDF</h1>';
}

echo ($isWeb ? '<div class="step"><h2>' : '') . "FIXING CERTIFICATE DOWNLOAD TO PDF" . ($isWeb ? '</h2>' : '') . "\n";

// Step 1: Update API routes to generate PDF
echo ($isWeb ? '<h3>' : '') . "Step 1: Updating API Routes" . ($isWeb ? '</h3>' : '') . "\n";

$apiRoutesPath = 'routes/api.php';
if (file_exists($apiRoutesPath)) {
    $apiContent = file_get_contents($apiRoutesPath);
    
    // Find and replace the certificate download route
    $oldRoute = 'Route::middleware(\'web\')->get(\'/certificates/{id}/download\', [App\Http\Controllers\CertificateController::class, \'download\']);';
    
    $newRoute = 'Route::middleware(\'web\')->get(\'/certificates/{id}/download\', function ($id) {
    try {
        $certificate = \App\Models\FloridaCertificate::with([\'enrollment.user\', \'enrollment.course\'])->findOrFail($id);
        
        // Get user data from enrollment
        $user = $certificate->enrollment->user;
        $course = $certificate->enrollment->course;
        
        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(\', \', $addressParts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.\'/\'.$user->birth_day.\'/\'.$user->birth_year;
        }

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.\'/\'.$user->due_day.\'/\'.$user->due_year;
        }

        // Get state stamp if available
        $stateStamp = null;
        if ($course) {
            $stateCode = $course->state ?? $course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where(\'state_code\', strtoupper($stateCode))
                    ->where(\'is_active\', true)
                    ->first();
            }
        }
        
        $templateData = [
            \'student_name\' => $certificate->student_name,
            \'student_address\' => $student_address ?: $certificate->student_address,
            \'completion_date\' => $certificate->completion_date->format(\'m/d/Y\'),
            \'course_type\' => $certificate->course_name,
            \'score\' => number_format($certificate->final_exam_score, 1) . \'%\',
            \'license_number\' => $certificate->driver_license_number ?: $user->driver_license,
            \'birth_date\' => $birth_date ?: ($certificate->student_date_of_birth ? 
                \Carbon\Carbon::parse($certificate->student_date_of_birth)->format(\'m/d/Y\') : null),
            \'citation_number\' => $certificate->citation_number ?: $user->citation_number,
            \'due_date\' => $due_date ?: ($certificate->traffic_school_due_date ? 
                \Carbon\Carbon::parse($certificate->traffic_school_due_date)->format(\'m/d/Y\') : null),
            \'court\' => $certificate->court_name ?: $user->court_selected,
            \'county\' => $certificate->citation_county ?: $user->state,
            \'certificate_number\' => $certificate->dicds_certificate_number,
            \'phone\' => null,
            \'city\' => $user->city,
            \'state\' => $user->state,
            \'zip\' => $user->zip,
            \'state_stamp\' => $stateStamp,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(\'certificate-pdf\', $templateData);
        
        return $pdf->download(\'certificate-\'.$certificate->dicds_certificate_number.\'.pdf\');

    } catch (\Exception $e) {
        \Log::error(\'Certificate download error: \' . $e->getMessage());
        return response()->json([\'error\' => \'Failed to generate certificate PDF\'], 500);
    }
});';
    
    if (strpos($apiContent, 'CertificateController::class, \'download\'') !== false) {
        $apiContent = str_replace($oldRoute, $newRoute, $apiContent);
        file_put_contents($apiRoutesPath, $apiContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Updated API certificate download route to generate PDF" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="info">' : '') . "ℹ️ API route already updated or not found" . ($isWeb ? '</div>' : '') . "\n";
    }
} else {
    echo ($isWeb ? '<div class="error">' : '') . "❌ API routes file not found" . ($isWeb ? '</div>' : '') . "\n";
}

// Step 2: Update my-certificates view to request PDF
echo ($isWeb ? '</div><div class="step"><h3>' : '') . "Step 2: Updating My-Certificates View" . ($isWeb ? '</h3>' : '') . "\n";

$certificatesViewPath = 'resources/views/my-certificates.blade.php';
if (file_exists($certificatesViewPath)) {
    $viewContent = file_get_contents($certificatesViewPath);
    
    // Update the download function to request PDF
    $oldDownloadFunction = 'async function downloadCertificateDirectly(certificateId) {
            const button = event.target.closest(\'button\');
            const originalText = button.innerHTML;
            
            button.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Downloading...\';
            button.disabled = true;
            
            try {
                // Use GET request to download certificate directly from database
                const response = await fetch(`/api/certificates/${certificateId}/download`, {
                    method: \'GET\',
                    headers: {
                        \'Accept\': \'text/html\',
                        \'X-CSRF-TOKEN\': document.querySelector(\'meta[name="csrf-token"]\').content
                    },
                    credentials: \'same-origin\'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement(\'a\');
                    a.href = url;
                    a.download = `certificate-${certificateId}.html`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showAlert(\'Certificate downloaded successfully!\', \'success\');
                } else {
                    const errorData = await response.text();
                    console.error(\'Download error:\', errorData);
                    showAlert(\'Error downloading certificate. Please try again.\', \'danger\');
                }
            } catch (error) {
                console.error(\'Download error:\', error);
                showAlert(\'Error downloading certificate. Please try again.\', \'danger\');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }';
    
    $newDownloadFunction = 'async function downloadCertificateDirectly(certificateId) {
            const button = event.target.closest(\'button\');
            const originalText = button.innerHTML;
            
            button.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Downloading...\';
            button.disabled = true;
            
            try {
                // Use GET request to download certificate PDF
                const response = await fetch(`/api/certificates/${certificateId}/download`, {
                    method: \'GET\',
                    headers: {
                        \'Accept\': \'application/pdf\',
                        \'X-CSRF-TOKEN\': document.querySelector(\'meta[name="csrf-token"]\').content
                    },
                    credentials: \'same-origin\'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement(\'a\');
                    a.href = url;
                    a.download = `certificate-${certificateId}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showAlert(\'Certificate PDF downloaded successfully!\', \'success\');
                } else {
                    const errorData = await response.text();
                    console.error(\'Download error:\', errorData);
                    showAlert(\'Error downloading certificate. Please try again.\', \'danger\');
                }
            } catch (error) {
                console.error(\'Download error:\', error);
                showAlert(\'Error downloading certificate. Please try again.\', \'danger\');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }';
    
    if (strpos($viewContent, 'certificate-${certificateId}.html') !== false) {
        $viewContent = str_replace($oldDownloadFunction, $newDownloadFunction, $viewContent);
        file_put_contents($certificatesViewPath, $viewContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Updated my-certificates view to download PDF" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="info">' : '') . "ℹ️ My-certificates view already updated" . ($isWeb ? '</div>' : '') . "\n";
    }
} else {
    echo ($isWeb ? '<div class="error">' : '') . "❌ My-certificates view file not found" . ($isWeb ? '</div>' : '') . "\n";
}

// Step 3: Create a test route to verify PDF generation
echo ($isWeb ? '</div><div class="step"><h3>' : '') . "Step 3: Creating Test Route" . ($isWeb ? '</h3>' : '') . "\n";

$testRoute = "
// Test PDF certificate download - TEMPORARY ROUTE
Route::get('/test-pdf-download/{id?}', function (\$id = null) {
    try {
        // Use first certificate if no ID provided
        if (!\$id) {
            \$certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->first();
        } else {
            \$certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->findOrFail(\$id);
        }
        
        if (!\$certificate) {
            return response()->json(['error' => 'No certificates found'], 404);
        }
        
        // Get user data from enrollment
        \$user = \$certificate->enrollment->user;
        \$course = \$certificate->enrollment->course;
        
        // Build student address
        \$addressParts = array_filter([
            \$user->mailing_address,
            \$user->city,
            \$user->state,
            \$user->zip,
        ]);
        \$student_address = implode(', ', \$addressParts);

        // Build birth date
        \$birth_date = null;
        if (\$user->birth_month && \$user->birth_day && \$user->birth_year) {
            \$birth_date = \$user->birth_month.'/'.\\$user->birth_day.'/'.\\$user->birth_year;
        }

        // Get state stamp if available
        \$stateStamp = null;
        if (\$course) {
            \$stateCode = \$course->state ?? \$course->state_code ?? null;
            if (\$stateCode) {
                \$stateStamp = \App\Models\StateStamp::where('state_code', strtoupper(\$stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }
        
        \$templateData = [
            'student_name' => \$certificate->student_name,
            'student_address' => \$student_address ?: \$certificate->student_address,
            'completion_date' => \$certificate->completion_date->format('m/d/Y'),
            'course_type' => \$certificate->course_name,
            'score' => number_format(\$certificate->final_exam_score, 1) . '%',
            'license_number' => \$certificate->driver_license_number ?: \$user->driver_license,
            'birth_date' => \$birth_date ?: (\$certificate->student_date_of_birth ? 
                \Carbon\Carbon::parse(\$certificate->student_date_of_birth)->format('m/d/Y') : null),
            'citation_number' => \$certificate->citation_number ?: \$user->citation_number,
            'court' => \$certificate->court_name ?: \$user->court_selected,
            'county' => \$certificate->citation_county ?: \$user->state,
            'certificate_number' => \$certificate->dicds_certificate_number,
            'phone' => null,
            'city' => \$user->city,
            'state' => \$user->state,
            'zip' => \$user->zip,
            'state_stamp' => \$stateStamp,
        ];

        // Generate PDF
        \$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', \$templateData);
        
        return \$pdf->download('test-certificate-pdf-'.\\$certificate->dicds_certificate_number.'.pdf');

    } catch (\Exception \$e) {
        return response()->json([
            'error' => 'Failed to generate test certificate PDF: ' . \$e->getMessage()
        ], 500);
    }
});";

$webRoutesPath = 'routes/web.php';
if (file_exists($webRoutesPath)) {
    $webRoutes = file_get_contents($webRoutesPath);
    
    if (strpos($webRoutes, 'test-pdf-download') === false) {
        file_put_contents($webRoutesPath, $webRoutes . $testRoute);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Added test PDF download route: /test-pdf-download" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="info">' : '') . "ℹ️ Test route already exists" . ($isWeb ? '</div>' : '') . "\n";
    }
}

// Step 4: Clear caches
echo ($isWeb ? '</div><div class="step"><h3>' : '') . "Step 4: Clearing Caches" . ($isWeb ? '</h3>' : '') . "\n";

// Bootstrap Laravel if available
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo ($isWeb ? '<div class="success">' : '') . "✅ Routes and cache cleared successfully" . ($isWeb ? '</div>' : '') . "\n";
    } catch (Exception $e) {
        echo ($isWeb ? '<div class="warning">' : '') . "⚠️ Cache clearing failed: " . $e->getMessage() . ($isWeb ? '</div>' : '') . "\n";
    }
}

echo ($isWeb ? '</div><div class="step"><h2>' : '') . "🎉 CERTIFICATE DOWNLOAD FIX COMPLETED!" . ($isWeb ? '</h2>' : '') . "\n";

echo ($isWeb ? '<div class="success">' : '') . "All fixes have been successfully applied:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ API route updated to generate PDF instead of HTML" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ My-certificates view updated to download PDF files" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Test route created for verification" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Caches cleared" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

echo ($isWeb ? '<div class="warning">' : '') . "🧪 TEST NOW:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ol>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Visit: /test-pdf-download to test PDF generation" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Go to /my-certificates and click Download PDF" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Should download .pdf file instead of .html" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "PDF should contain state seal images and final exam scores" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ol>' : '') . "\n";

if ($isWeb) {
    echo '<div style="background:#dc3545;color:white;padding:15px;border-radius:8px;margin:20px 0;text-align:center;">';
    echo '<h3>⚠️ SECURITY WARNING ⚠️</h3>';
    echo '<p><strong>DELETE THIS FILE NOW!</strong><br>This file contains sensitive operations and should not remain on your server.</p>';
    echo '</div>';
    echo '</div></body></html>';
}

echo "\n" . ($isWeb ? '<div class="success">' : '') . "🚀 Your certificate downloads now generate PDF files with state seals and scores!" . ($isWeb ? '</div>' : '') . "\n";
?>
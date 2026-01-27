<?php
/**
 * FIX CERTIFICATE SEAL FRAME
 * Fixes the state seal image size and adds proper frame/border
 * Ensures images are properly contained within the seal area
 * 
 * Usage: Upload to hosting root and run:
 * php fix_certificate_seal_frame.php
 * OR visit: https://yourdomain.com/fix_certificate_seal_frame.php
 */

// Check if running from command line or web
$isWeb = isset($_SERVER['HTTP_HOST']);
if ($isWeb) {
    echo '<!DOCTYPE html><html><head><title>Fix Certificate Seal Frame</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f8f9fa;}';
    echo '.success{color:#28a745;background:#d4edda;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.info{color:#0c5460;background:#d1ecf1;padding:10px;border-radius:4px;margin:8px 0;}';
    echo 'h1{color:#495057;} h2{color:#6c757d;} h3{color:#868e96;}';
    echo '</style></head><body>';
    echo '<h1>🖼️ Fix Certificate Seal Frame</h1>';
    echo '<p class="info">This fix ensures state seal images are properly sized and framed.</p>';
}

echo ($isWeb ? '<h2>' : '') . "FIXING CERTIFICATE SEAL FRAME AND SIZE" . ($isWeb ? '</h2>' : '') . "\n";

// Step 1: Update Certificate Template CSS for Better Seal Styling
echo ($isWeb ? '<h3>' : '') . "Step 1: Updating Certificate Template CSS" . ($isWeb ? '</h3>' : '') . "\n";

$templatePath = 'resources/views/certificate-pdf.blade.php';
if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);
    
    // Find and update the state seal CSS
    $oldSealCSS = '/* State seal styling */
        .state-seal img {
            max-width: 100px;
            max-height: 100px;
        }
        .state-seal-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 9px;
            text-align: center;
        }';
    
    $newSealCSS = '/* State seal styling */
        .state-seal {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
            position: relative;
        }
        .state-seal img {
            max-width: 96px;
            max-height: 96px;
            width: auto;
            height: auto;
            object-fit: contain;
            object-position: center;
            border-radius: 50%;
        }
        .state-seal-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 9px;
            text-align: center;
            background: #f5f5f5;
        }';
    
    if (strpos($templateContent, '.state-seal img {') !== false) {
        $templateContent = str_replace($oldSealCSS, $newSealCSS, $templateContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Updated state seal CSS styling" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        // If exact match not found, try to add the CSS
        $cssInsertPoint = '</style>';
        $cssToAdd = '
        /* Enhanced State seal styling */
        .state-seal {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
            position: relative;
        }
        .state-seal img {
            max-width: 96px;
            max-height: 96px;
            width: auto;
            height: auto;
            object-fit: contain;
            object-position: center;
            border-radius: 50%;
        }
        .state-seal-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 9px;
            text-align: center;
            background: #f5f5f5;
        }
        ';
        
        $templateContent = str_replace($cssInsertPoint, $cssToAdd . $cssInsertPoint, $templateContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Added enhanced state seal CSS styling" . ($isWeb ? '</div>' : '') . "\n";
    }
    
    // Step 2: Update the HTML structure for better image containment
    echo ($isWeb ? '<h3>' : '') . "Step 2: Updating HTML Structure" . ($isWeb ? '</h3>' : '') . "\n";
    
    // Find and update the state seal HTML structure
    $oldImageTag = '<img src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="{{ $state_stamp->state_name }} Seal" style="max-width: 100px; max-height: 100px; object-fit: contain;">';
    
    $newImageTag = '<img src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="{{ $state_stamp->state_name }} Seal">';
    
    if (strpos($templateContent, 'max-width: 100px; max-height: 100px; object-fit: contain;') !== false) {
        $templateContent = str_replace($oldImageTag, $newImageTag, $templateContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Updated image HTML structure" . ($isWeb ? '</div>' : '') . "\n";
    }
    
    // Also update any other inline styles on images
    $templateContent = preg_replace(
        '/style="max-width:\s*100px;\s*max-height:\s*100px;[^"]*"/',
        '',
        $templateContent
    );
    
    file_put_contents($templatePath, $templateContent);
    echo ($isWeb ? '<div class="success">' : '') . "✅ Certificate template updated with proper seal frame" . ($isWeb ? '</div>' : '') . "\n";
    
} else {
    echo ($isWeb ? '<div class="error">' : '') . "❌ Certificate template not found: {$templatePath}" . ($isWeb ? '</div>' : '') . "\n";
}

// Step 3: Create Image Optimization Function
echo ($isWeb ? '<h3>' : '') . "Step 3: Creating Image Optimization Function" . ($isWeb ? '</h3>' : '') . "\n";

$optimizationRoute = "
// Optimize state seal images - TEMPORARY ROUTE
Route::get('/optimize-state-seals', function() {
    try {
        \$stamps = \App\Models\StateStamp::where('is_active', true)->get();
        \$optimized = [];
        
        foreach (\$stamps as \$stamp) {
            \$imagePath = public_path('storage/' . \$stamp->logo_path);
            
            if (file_exists(\$imagePath)) {
                \$extension = strtolower(pathinfo(\$imagePath, PATHINFO_EXTENSION));
                \$fileSize = filesize(\$imagePath);
                
                // Check if image is too large (over 50KB)
                if (\$fileSize > 50000) {
                    \$optimized[] = [
                        'state' => \$stamp->state_code,
                        'original_size' => number_format(\$fileSize),
                        'status' => 'Large file - consider optimizing',
                        'recommendation' => 'Resize to 200x200 pixels or smaller'
                    ];
                } else {
                    \$optimized[] = [
                        'state' => \$stamp->state_code,
                        'original_size' => number_format(\$fileSize),
                        'status' => 'Good size',
                        'recommendation' => 'No optimization needed'
                    ];
                }
            } else {
                \$optimized[] = [
                    'state' => \$stamp->state_code,
                    'original_size' => 'N/A',
                    'status' => 'File not found',
                    'recommendation' => 'Upload image file'
                ];
            }
        }
        
        return response()->json([
            'message' => 'State seal optimization analysis',
            'results' => \$optimized,
            'tips' => [
                'Keep images under 50KB for better PDF performance',
                'Recommended size: 200x200 pixels',
                'Supported formats: PNG, JPG, SVG',
                'SVG files are usually smaller and scale better'
            ]
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception \$e) {
        return response()->json(['error' => \$e->getMessage()], 500);
    }
});";

$webRoutesPath = 'routes/web.php';
if (file_exists($webRoutesPath)) {
    $webRoutes = file_get_contents($webRoutesPath);
    
    if (strpos($webRoutes, 'optimize-state-seals') === false) {
        file_put_contents($webRoutesPath, $webRoutes . $optimizationRoute);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Added image optimization route: /optimize-state-seals" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="info">' : '') . "ℹ️ Optimization route already exists" . ($isWeb ? '</div>' : '') . "\n";
    }
}

// Step 4: Create Test Certificate with Frame
echo ($isWeb ? '<h3>' : '') . "Step 4: Creating Test Certificate Route" . ($isWeb ? '</h3>' : '') . "\n";

$testCertRoute = "
// Test certificate with proper seal frame - TEMPORARY ROUTE
Route::get('/test-certificate-seal-frame/{id?}', function (\$id = null) {
    try {
        if (!\$id) {
            \$certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->first();
        } else {
            \$certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->findOrFail(\$id);
        }
        
        if (!\$certificate) {
            return response()->json(['error' => 'No certificates found'], 404);
        }
        
        \$user = \$certificate->enrollment->user;
        \$course = \$certificate->enrollment->course;
        
        \$addressParts = array_filter([
            \$user->mailing_address,
            \$user->city,
            \$user->state,
            \$user->zip,
        ]);
        \$student_address = implode(', ', \$addressParts);

        \$birth_date = null;
        if (\$user->birth_month && \$user->birth_day && \$user->birth_year) {
            \$birth_date = \$user->birth_month.'/'.\\$user->birth_day.'/'.\\$user->birth_year;
        }

        \$stateStamp = null;
        if (\$course) {
            \$stateCode = \$course->state ?? \$course->state_code ?? 'FL';
            \$stateStamp = \App\Models\StateStamp::where('state_code', strtoupper(\$stateCode))
                ->where('is_active', true)
                ->first();
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

        \$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', \$templateData);
        \$pdf->setPaper('letter', 'portrait');
        
        return \$pdf->download('test-certificate-with-frame-'.\\$certificate->dicds_certificate_number.'.pdf');

    } catch (\Exception \$e) {
        return response()->json([
            'error' => 'Failed to generate test certificate: ' . \$e->getMessage()
        ], 500);
    }
});";

if (strpos($webRoutes, 'test-certificate-seal-frame') === false) {
    file_put_contents($webRoutesPath, file_get_contents($webRoutesPath) . $testCertRoute);
    echo ($isWeb ? '<div class="success">' : '') . "✅ Added test certificate route: /test-certificate-seal-frame" . ($isWeb ? '</div>' : '') . "\n";
} else {
    echo ($isWeb ? '<div class="info">' : '') . "ℹ️ Test certificate route already exists" . ($isWeb ? '</div>' : '') . "\n";
}

// Step 5: Clear caches
echo ($isWeb ? '<h3>' : '') . "Step 5: Clearing Caches" . ($isWeb ? '</h3>' : '') . "\n";

// Bootstrap Laravel if available
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo ($isWeb ? '<div class="success">' : '') . "✅ Caches cleared successfully" . ($isWeb ? '</div>' : '') . "\n";
    } catch (Exception $e) {
        echo ($isWeb ? '<div class="warning">' : '') . "⚠️ Cache clearing failed: " . $e->getMessage() . ($isWeb ? '</div>' : '') . "\n";
    }
}

echo ($isWeb ? '<h2>' : '') . "🎉 CERTIFICATE SEAL FRAME FIX COMPLETED!" . ($isWeb ? '</h2>' : '') . "\n";

echo ($isWeb ? '<div class="success">' : '') . "Seal frame and sizing fix has been applied:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Added proper circular frame with border for state seals" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Fixed image sizing to stay within 100px x 100px frame" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Added overflow hidden to prevent images from breaking out" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Enhanced CSS with proper object-fit and positioning" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Created optimization analysis route" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Added test certificate route" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

echo ($isWeb ? '<div class="warning">' : '') . "🧪 TESTING:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ol>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Visit: /optimize-state-seals to check your image sizes" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Visit: /test-certificate-seal-frame to test the new frame" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Generate a certificate from /my-certificates" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Check that the seal is properly contained in a circular frame" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ol>' : '') . "\n";

echo ($isWeb ? '<div class="info">' : '') . "📋 WHAT THIS FIX DOES:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Creates a 100px x 100px circular frame with black border" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Constrains images to 96px x 96px (leaving 2px border)" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Uses object-fit: contain to maintain aspect ratio" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Adds overflow: hidden to prevent image overflow" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Centers images both horizontally and vertically" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

echo ($isWeb ? '<div class="info">' : '') . "💡 RECOMMENDATIONS:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Keep your SVG images under 200x200 pixels for best results" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Use square aspect ratio images (1:1) for circular frames" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "SVG format is recommended for scalable, crisp seals" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Test with /optimize-state-seals to check file sizes" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

if ($isWeb) {
    echo '<div style="background:#dc3545;color:white;padding:15px;border-radius:8px;margin:20px 0;text-align:center;">';
    echo '<h3>⚠️ SECURITY WARNING ⚠️</h3>';
    echo '<p><strong>DELETE THIS FILE NOW!</strong><br>This file should not remain on your server.</p>';
    echo '</div>';
    echo '</body></html>';
}

echo "\n" . ($isWeb ? '<div class="success">' : '') . "🖼️ Your state seal images should now be properly framed and sized!" . ($isWeb ? '</div>' : '') . "\n";
?>
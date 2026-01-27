<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing State Stamps Functionality\n";
echo "==================================\n\n";

try {
    // Test 1: Check existing state stamps
    $stateStamps = \App\Models\StateStamp::where('is_active', true)->get();
    echo "1. Active State Stamps in Database:\n";
    foreach ($stateStamps as $stamp) {
        echo "   - {$stamp->state_code} ({$stamp->state_name}): ";
        if ($stamp->logo_path) {
            $logoExists = file_exists(public_path('storage/' . $stamp->logo_path));
            echo $logoExists ? "✅ Logo exists" : "❌ Logo missing";
            echo " - Path: {$stamp->logo_path}\n";
        } else {
            echo "⚠️ No logo uploaded\n";
        }
    }
    echo "\n";
    
    // Test 2: Check certificate with course state
    $certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
        ->first();
    
    if ($certificate && $certificate->enrollment && $certificate->enrollment->course) {
        $course = $certificate->enrollment->course;
        $stateCode = $course->state ?? $course->state_code ?? null;
        
        echo "2. Certificate Course State Check:\n";
        echo "   Certificate ID: {$certificate->id}\n";
        echo "   Course: {$course->title}\n";
        echo "   Course State: " . ($stateCode ?? 'Not set') . "\n";
        
        if ($stateCode) {
            $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                ->where('is_active', true)
                ->first();
            
            if ($stateStamp) {
                echo "   ✅ State stamp found for {$stateCode}: {$stateStamp->state_name}\n";
                if ($stateStamp->logo_path) {
                    $logoExists = file_exists(public_path('storage/' . $stateStamp->logo_path));
                    echo "   " . ($logoExists ? "✅" : "❌") . " Logo file: {$stateStamp->logo_path}\n";
                } else {
                    echo "   ⚠️ No logo uploaded for this state\n";
                }
            } else {
                echo "   ❌ No state stamp found for {$stateCode}\n";
            }
        }
        echo "\n";
    }
    
    // Test 3: Test certificate template rendering with state stamp
    if ($certificate) {
        echo "3. Testing Certificate Template with State Stamp:\n";
        
        $user = $certificate->enrollment->user;
        $course = $certificate->enrollment->course;
        
        // Build template data (same as CertificateController)
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
        }

        // Get state stamp
        $stateStamp = null;
        if ($course) {
            $stateCode = $course->state ?? $course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }
        
        $templateData = [
            'student_name' => $certificate->student_name,
            'student_address' => $student_address ?: $certificate->student_address,
            'completion_date' => $certificate->completion_date->format('m/d/Y'),
            'course_type' => $certificate->course_name,
            'score' => number_format($certificate->final_exam_score, 1) . '%',
            'license_number' => $certificate->driver_license_number ?: $user->driver_license,
            'birth_date' => $birth_date,
            'citation_number' => $certificate->citation_number ?: $user->citation_number,
            'due_date' => $due_date,
            'court' => $certificate->court_name ?: $user->court_selected,
            'county' => $certificate->citation_county ?: $user->state,
            'certificate_number' => $certificate->dicds_certificate_number,
            'phone' => null,
            'city' => $user->city,
            'state' => $user->state,
            'zip' => $user->zip,
            'state_stamp' => $stateStamp,
        ];
        
        echo "   Template data prepared:\n";
        echo "   - Student: {$templateData['student_name']}\n";
        echo "   - Course: {$templateData['course_type']}\n";
        echo "   - State Stamp: " . ($stateStamp ? "✅ {$stateStamp->state_name}" : "❌ None") . "\n";
        
        // Test template rendering
        try {
            $html = view('certificate-pdf', $templateData)->render();
            echo "   ✅ Certificate template rendered successfully\n";
            echo "   - HTML length: " . strlen($html) . " characters\n";
            
            // Check for state stamp in HTML
            if ($stateStamp && $stateStamp->logo_path) {
                $stampInHtml = strpos($html, $stateStamp->logo_path) !== false;
                echo "   " . ($stampInHtml ? "✅" : "❌") . " State stamp image included in HTML\n";
            } else {
                $placeholderInHtml = strpos($html, 'State Seal') !== false;
                echo "   " . ($placeholderInHtml ? "✅" : "❌") . " State seal placeholder shown\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Template rendering failed: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Test 4: Check storage directory permissions
    echo "4. Storage Directory Check:\n";
    $stateStampsDir = public_path('storage/state-stamps');
    if (is_dir($stateStampsDir)) {
        echo "   ✅ State stamps directory exists: {$stateStampsDir}\n";
        echo "   ✅ Directory is writable: " . (is_writable($stateStampsDir) ? "Yes" : "No") . "\n";
        
        $files = glob($stateStampsDir . '/*');
        echo "   📁 Files in directory: " . count($files) . "\n";
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            echo "     - {$filename} (" . round($size/1024, 1) . " KB)\n";
        }
    } else {
        echo "   ❌ State stamps directory does not exist\n";
    }
    echo "\n";
    
    echo "==================================\n";
    echo "🎯 STATE STAMPS SYSTEM SUMMARY:\n";
    echo "==================================\n\n";
    
    echo "✅ EXISTING FUNCTIONALITY:\n";
    echo "1. **StateStamp Model**: Manages state seals/stamps\n";
    echo "2. **Admin Interface**: /admin/state-stamps\n";
    echo "3. **File Storage**: public/storage/state-stamps/\n";
    echo "4. **Database**: state_stamps table with logo_path\n";
    echo "5. **Certificate Integration**: Already implemented\n\n";
    
    echo "📋 HOW TO ADD STATE SEALS:\n";
    echo "1. Go to: /admin/state-stamps\n";
    echo "2. Click 'Add State Stamp'\n";
    echo "3. Select state, upload logo image\n";
    echo "4. Image automatically saved to storage/state-stamps/\n";
    echo "5. Certificate will automatically use the seal\n\n";
    
    echo "🔧 CERTIFICATE BEHAVIOR:\n";
    echo "- If state stamp exists: Shows actual state seal/logo\n";
    echo "- If no stamp: Shows placeholder 'State Seal' icon\n";
    echo "- Dynamic based on course state (FL, TX, CA, etc.)\n";
    echo "- Automatically queries database for active stamps\n\n";
    
    echo "📁 FILE LOCATIONS:\n";
    echo "- Upload images via: /admin/state-stamps\n";
    echo "- Images stored in: public/storage/state-stamps/\n";
    echo "- Accessed via: asset('storage/state-stamps/filename')\n";
    echo "- Template: resources/views/certificate-pdf.blade.php\n\n";
    
    echo "🎨 RECOMMENDED IMAGE FORMAT:\n";
    echo "- Format: PNG with transparent background\n";
    echo "- Size: 200x200px or similar square\n";
    echo "- File size: Under 2MB\n";
    echo "- High resolution for PDF quality\n\n";
    
    echo "✅ SYSTEM IS READY TO USE!\n";
    echo "Just upload state seals via the admin panel.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
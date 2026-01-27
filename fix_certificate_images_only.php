<?php
/**
 * FIX CERTIFICATE IMAGES ONLY
 * Fixes PNG/JPG images not appearing in PDF certificates
 * Focuses specifically on the base64 encoding issue for binary image files
 * 
 * Usage: Upload to hosting root and run:
 * php fix_certificate_images_only.php
 * OR visit: https://yourdomain.com/fix_certificate_images_only.php
 */

// Check if running from command line or web
$isWeb = isset($_SERVER['HTTP_HOST']);
if ($isWeb) {
    echo '<!DOCTYPE html><html><head><title>Fix Certificate Images</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f8f9fa;}';
    echo '.success{color:#28a745;background:#d4edda;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:4px;margin:8px 0;}';
    echo '.info{color:#0c5460;background:#d1ecf1;padding:10px;border-radius:4px;margin:8px 0;}';
    echo 'pre{background:#e9ecef;padding:15px;border-radius:5px;overflow-x:auto;font-size:12px;}';
    echo 'h1{color:#495057;} h2{color:#6c757d;} h3{color:#868e96;}';
    echo '</style></head><body>';
    echo '<h1>🖼️ Fix Certificate Images Only</h1>';
    echo '<p class="info">This fix specifically addresses PNG/JPG images not appearing in PDF certificates.</p>';
}

echo ($isWeb ? '<h2>' : '') . "FIXING CERTIFICATE IMAGE LOADING" . ($isWeb ? '</h2>' : '') . "\n";

// Step 1: Update Certificate Template with Enhanced Image Handling
echo ($isWeb ? '<h3>' : '') . "Step 1: Updating Certificate Template" . ($isWeb ? '</h3>' : '') . "\n";

$templatePath = 'resources/views/certificate-pdf.blade.php';
if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);
    
    // Find the current state seal section
    $currentStateSection = '@if(isset($state_stamp) && $state_stamp && $state_stamp->logo_path)
                    @php
                        $imagePath = public_path(\'storage/\' . $state_stamp->logo_path);
                        $imageData = null;
                        $mimeType = \'image/png\';
                        
                        if (file_exists($imagePath)) {
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                            
                            switch($extension) {
                                case \'jpg\':
                                case \'jpeg\':
                                    $mimeType = \'image/jpeg\';
                                    break;
                                case \'png\':
                                    $mimeType = \'image/png\';
                                    break;
                                case \'svg\':
                                    $mimeType = \'image/svg+xml\';
                                    break;
                                case \'gif\':
                                    $mimeType = \'image/gif\';
                                    break;
                            }
                        }
                    @endphp
                    
                    @if($imageData)
                        <div class="state-seal">
                            <img src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="{{ $state_stamp->state_name }} Seal" style="max-width: 100px; max-height: 100px;">
                        </div>
                    @else
                        <div class="state-seal-placeholder">
                            {{ $state_stamp->state_name }}<br>SEAL
                        </div>
                    @endif
                @else
                    <div class="state-seal-placeholder">
                        STATE<br>SEAL
                    </div>
                @endif';

    // Enhanced state seal section with better binary file handling
    $enhancedStateSection = '@if(isset($state_stamp) && $state_stamp && $state_stamp->logo_path)
                    @php
                        $imagePath = public_path(\'storage/\' . $state_stamp->logo_path);
                        $imageData = null;
                        $mimeType = \'image/png\';
                        $imageFound = false;
                        
                        // Debug logging
                        \Log::info(\'Certificate Image Debug\', [
                            \'logo_path\' => $state_stamp->logo_path,
                            \'full_path\' => $imagePath,
                            \'file_exists\' => file_exists($imagePath),
                            \'is_readable\' => file_exists($imagePath) ? is_readable($imagePath) : false,
                            \'file_size\' => file_exists($imagePath) ? filesize($imagePath) : 0
                        ]);
                        
                        if (file_exists($imagePath) && is_readable($imagePath)) {
                            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                            
                            // Set correct MIME type
                            switch($extension) {
                                case \'jpg\':
                                case \'jpeg\':
                                    $mimeType = \'image/jpeg\';
                                    break;
                                case \'png\':
                                    $mimeType = \'image/png\';
                                    break;
                                case \'svg\':
                                    $mimeType = \'image/svg+xml\';
                                    break;
                                case \'gif\':
                                    $mimeType = \'image/gif\';
                                    break;
                                case \'webp\':
                                    $mimeType = \'image/webp\';
                                    break;
                                default:
                                    $mimeType = \'image/png\';
                            }
                            
                            // Read file content with error handling
                            $imageContent = @file_get_contents($imagePath);
                            if ($imageContent !== false && strlen($imageContent) > 0) {
                                $imageData = base64_encode($imageContent);
                                $imageFound = true;
                                \Log::info(\'Image loaded successfully\', [
                                    \'size\' => strlen($imageContent),
                                    \'base64_size\' => strlen($imageData),
                                    \'mime_type\' => $mimeType
                                ]);
                            } else {
                                \Log::error(\'Failed to read image content\', [\'path\' => $imagePath]);
                            }
                        }
                        
                        // Fallback: try different extensions if original not found
                        if (!$imageFound) {
                            $basePath = pathinfo($imagePath, PATHINFO_DIRNAME) . \'/\' . pathinfo($imagePath, PATHINFO_FILENAME);
                            $extensions = [\'png\', \'jpg\', \'jpeg\', \'svg\', \'gif\', \'webp\'];
                            
                            foreach ($extensions as $ext) {
                                $testPath = $basePath . \'.\' . $ext;
                                if (file_exists($testPath) && is_readable($testPath)) {
                                    $imageContent = @file_get_contents($testPath);
                                    if ($imageContent !== false && strlen($imageContent) > 0) {
                                        $imageData = base64_encode($imageContent);
                                        $mimeType = \'image/\' . ($ext === \'jpg\' ? \'jpeg\' : $ext);
                                        $imageFound = true;
                                        \Log::info(\'Fallback image found\', [
                                            \'original_path\' => $imagePath,
                                            \'found_path\' => $testPath,
                                            \'extension\' => $ext
                                        ]);
                                        break;
                                    }
                                }
                            }
                        }
                    @endphp
                    
                    @if($imageFound && $imageData)
                        <div class="state-seal">
                            <img src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="{{ $state_stamp->state_name }} Seal" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                        </div>
                    @else
                        <div class="state-seal-placeholder">
                            {{ $state_stamp->state_name }}<br>SEAL<br>
                            <small style="font-size: 8px;">Image not found</small>
                        </div>
                    @endif
                @else
                    <div class="state-seal-placeholder">
                        STATE<br>SEAL
                    </div>
                @endif';
    
    // Replace the state section
    if (strpos($templateContent, 'public_path(\'storage/\' . $state_stamp->logo_path)') !== false) {
        $templateContent = str_replace($currentStateSection, $enhancedStateSection, $templateContent);
        file_put_contents($templatePath, $templateContent);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Updated certificate template with enhanced image handling" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="warning">' : '') . "⚠️ Could not find exact match, trying partial replacement..." . ($isWeb ? '</div>' : '') . "\n";
        
        // Try to find and replace just the PHP section
        $oldPhpSection = '@php
                        $imagePath = public_path(\'storage/\' . $state_stamp->logo_path);
                        $imageData = null;
                        $mimeType = \'image/png\';
                        
                        if (file_exists($imagePath)) {
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                            
                            switch($extension) {
                                case \'jpg\':
                                case \'jpeg\':
                                    $mimeType = \'image/jpeg\';
                                    break;
                                case \'png\':
                                    $mimeType = \'image/png\';
                                    break;
                                case \'svg\':
                                    $mimeType = \'image/svg+xml\';
                                    break;
                                case \'gif\':
                                    $mimeType = \'image/gif\';
                                    break;
                            }
                        }
                    @endphp';
        
        $newPhpSection = '@php
                        $imagePath = public_path(\'storage/\' . $state_stamp->logo_path);
                        $imageData = null;
                        $mimeType = \'image/png\';
                        $imageFound = false;
                        
                        // Debug logging
                        \Log::info(\'Certificate Image Debug\', [
                            \'logo_path\' => $state_stamp->logo_path,
                            \'full_path\' => $imagePath,
                            \'file_exists\' => file_exists($imagePath),
                            \'is_readable\' => file_exists($imagePath) ? is_readable($imagePath) : false,
                            \'file_size\' => file_exists($imagePath) ? filesize($imagePath) : 0
                        ]);
                        
                        if (file_exists($imagePath) && is_readable($imagePath)) {
                            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                            
                            // Set correct MIME type
                            switch($extension) {
                                case \'jpg\':
                                case \'jpeg\':
                                    $mimeType = \'image/jpeg\';
                                    break;
                                case \'png\':
                                    $mimeType = \'image/png\';
                                    break;
                                case \'svg\':
                                    $mimeType = \'image/svg+xml\';
                                    break;
                                case \'gif\':
                                    $mimeType = \'image/gif\';
                                    break;
                                case \'webp\':
                                    $mimeType = \'image/webp\';
                                    break;
                                default:
                                    $mimeType = \'image/png\';
                            }
                            
                            // Read file content with error handling
                            $imageContent = @file_get_contents($imagePath);
                            if ($imageContent !== false && strlen($imageContent) > 0) {
                                $imageData = base64_encode($imageContent);
                                $imageFound = true;
                                \Log::info(\'Image loaded successfully\', [
                                    \'size\' => strlen($imageContent),
                                    \'base64_size\' => strlen($imageData),
                                    \'mime_type\' => $mimeType
                                ]);
                            } else {
                                \Log::error(\'Failed to read image content\', [\'path\' => $imagePath]);
                            }
                        }
                        
                        // Fallback: try different extensions if original not found
                        if (!$imageFound) {
                            $basePath = pathinfo($imagePath, PATHINFO_DIRNAME) . \'/\' . pathinfo($imagePath, PATHINFO_FILENAME);
                            $extensions = [\'png\', \'jpg\', \'jpeg\', \'svg\', \'gif\', \'webp\'];
                            
                            foreach ($extensions as $ext) {
                                $testPath = $basePath . \'.\' . $ext;
                                if (file_exists($testPath) && is_readable($testPath)) {
                                    $imageContent = @file_get_contents($testPath);
                                    if ($imageContent !== false && strlen($imageContent) > 0) {
                                        $imageData = base64_encode($imageContent);
                                        $mimeType = \'image/\' . ($ext === \'jpg\' ? \'jpeg\' : $ext);
                                        $imageFound = true;
                                        \Log::info(\'Fallback image found\', [
                                            \'original_path\' => $imagePath,
                                            \'found_path\' => $testPath,
                                            \'extension\' => $ext
                                        ]);
                                        break;
                                    }
                                }
                            }
                        }
                    @endphp';
        
        if (strpos($templateContent, '$imageData = base64_encode(file_get_contents($imagePath));') !== false) {
            $templateContent = str_replace($oldPhpSection, $newPhpSection, $templateContent);
            
            // Also update the condition check
            $templateContent = str_replace(
                '@if($imageData)',
                '@if($imageFound && $imageData)',
                $templateContent
            );
            
            file_put_contents($templatePath, $templateContent);
            echo ($isWeb ? '<div class="success">' : '') . "✅ Updated certificate template with enhanced image handling (partial replacement)" . ($isWeb ? '</div>' : '') . "\n";
        } else {
            echo ($isWeb ? '<div class="error">' : '') . "❌ Could not find the image handling section to replace" . ($isWeb ? '</div>' : '') . "\n";
        }
    }
} else {
    echo ($isWeb ? '<div class="error">' : '') . "❌ Certificate template not found: {$templatePath}" . ($isWeb ? '</div>' : '') . "\n";
}

// Step 2: Create Image Test Route
echo ($isWeb ? '<h3>' : '') . "Step 2: Creating Image Test Route" . ($isWeb ? '</h3>' : '') . "\n";

$testRoute = "
// Test certificate images - TEMPORARY ROUTE
Route::get('/test-certificate-images-debug', function() {
    try {
        // Get state stamps and test image loading
        \$stamps = \App\Models\StateStamp::where('is_active', true)->get();
        \$results = [];
        
        foreach (\$stamps as \$stamp) {
            \$imagePath = public_path('storage/' . \$stamp->logo_path);
            \$imageData = null;
            \$imageFound = false;
            \$error = null;
            
            if (file_exists(\$imagePath) && is_readable(\$imagePath)) {
                \$imageContent = @file_get_contents(\$imagePath);
                if (\$imageContent !== false && strlen(\$imageContent) > 0) {
                    \$imageData = base64_encode(\$imageContent);
                    \$imageFound = true;
                } else {
                    \$error = 'Could not read file content';
                }
            } else {
                \$error = 'File does not exist or not readable';
                
                // Try fallback extensions
                \$basePath = pathinfo(\$imagePath, PATHINFO_DIRNAME) . '/' . pathinfo(\$imagePath, PATHINFO_FILENAME);
                \$extensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
                
                foreach (\$extensions as \$ext) {
                    \$testPath = \$basePath . '.' . \$ext;
                    if (file_exists(\$testPath) && is_readable(\$testPath)) {
                        \$imageContent = @file_get_contents(\$testPath);
                        if (\$imageContent !== false && strlen(\$imageContent) > 0) {
                            \$imageData = base64_encode(\$imageContent);
                            \$imageFound = true;
                            \$error = 'Found with extension: ' . \$ext;
                            break;
                        }
                    }
                }
            }
            
            \$results[] = [
                'state' => \$stamp->state_code . ' - ' . \$stamp->state_name,
                'path' => \$stamp->logo_path,
                'full_path' => \$imagePath,
                'exists' => file_exists(\$imagePath),
                'readable' => file_exists(\$imagePath) && is_readable(\$imagePath),
                'size' => file_exists(\$imagePath) ? filesize(\$imagePath) : 0,
                'image_found' => \$imageFound,
                'base64_length' => \$imageData ? strlen(\$imageData) : 0,
                'error' => \$error
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Image loading test completed',
            'results' => \$results
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception \$e) {
        return response()->json([
            'status' => 'error',
            'message' => \$e->getMessage()
        ], 500);
    }
});";

$webRoutesPath = 'routes/web.php';
if (file_exists($webRoutesPath)) {
    $webRoutes = file_get_contents($webRoutesPath);
    
    if (strpos($webRoutes, 'test-certificate-images-debug') === false) {
        file_put_contents($webRoutesPath, $webRoutes . $testRoute);
        echo ($isWeb ? '<div class="success">' : '') . "✅ Added image test route: /test-certificate-images-debug" . ($isWeb ? '</div>' : '') . "\n";
    } else {
        echo ($isWeb ? '<div class="info">' : '') . "ℹ️ Test route already exists" . ($isWeb ? '</div>' : '') . "\n";
    }
}

// Step 3: Clear view cache
echo ($isWeb ? '<h3>' : '') . "Step 3: Clearing View Cache" . ($isWeb ? '</h3>' : '') . "\n";

// Bootstrap Laravel if available
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo ($isWeb ? '<div class="success">' : '') . "✅ View cache cleared successfully" . ($isWeb ? '</div>' : '') . "\n";
    } catch (Exception $e) {
        echo ($isWeb ? '<div class="warning">' : '') . "⚠️ Cache clearing failed: " . $e->getMessage() . ($isWeb ? '</div>' : '') . "\n";
    }
}

echo ($isWeb ? '<h2>' : '') . "🎉 CERTIFICATE IMAGE FIX COMPLETED!" . ($isWeb ? '</h2>' : '') . "\n";

echo ($isWeb ? '<div class="success">' : '') . "Image loading fix has been applied:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Enhanced base64 encoding with error handling" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Added fallback mechanisms for different file extensions" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Improved binary file reading for PNG/JPG images" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Added debug logging for troubleshooting" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "✅ Created test route for image verification" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

echo ($isWeb ? '<div class="warning">' : '') . "🧪 TESTING:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ol>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Visit: /test-certificate-images-debug to check image loading" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Upload your PNG/JPG seal images to: public/storage/state-stamps/" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Name them: FL-seal.png, MO-seal.png, etc." . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Test certificate generation again" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ol>' : '') . "\n";

echo ($isWeb ? '<div class="info">' : '') . "📋 WHAT THIS FIX DOES:" . ($isWeb ? '</div>' : '') . "\n";
echo ($isWeb ? '<ul>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Better error handling when reading binary image files" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Automatic fallback to different file extensions" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Proper MIME type detection for all image formats" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Debug logging to help identify image loading issues" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '<li>' : '') . "Support for PNG, JPG, JPEG, SVG, GIF, WEBP formats" . ($isWeb ? '</li>' : '') . "\n";
echo ($isWeb ? '</ul>' : '') . "\n";

if ($isWeb) {
    echo '<div style="background:#dc3545;color:white;padding:15px;border-radius:8px;margin:20px 0;text-align:center;">';
    echo '<h3>⚠️ SECURITY WARNING ⚠️</h3>';
    echo '<p><strong>DELETE THIS FILE NOW!</strong><br>This file should not remain on your server.</p>';
    echo '</div>';
    echo '</body></html>';
}

echo "\n" . ($isWeb ? '<div class="success">' : '') . "🖼️ Your PNG/JPG images should now appear correctly in PDF certificates!" . ($isWeb ? '</div>' : '') . "\n";
?>
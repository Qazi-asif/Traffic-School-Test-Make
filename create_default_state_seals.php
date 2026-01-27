<?php
/**
 * CREATE DEFAULT STATE SEALS
 * Creates placeholder state seal images for testing
 * 
 * Usage: php create_default_state_seals.php
 */

// Bootstrap Laravel
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel bootstrapped successfully\n";
} else {
    echo "❌ Cannot bootstrap Laravel\n";
    exit(1);
}

use Illuminate\Support\Facades\DB;

echo "🎨 CREATING DEFAULT STATE SEALS\n";
echo "===============================\n\n";

// Create storage directory
$storageDir = public_path('storage/state-stamps');
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
    echo "✅ Created directory: {$storageDir}\n";
}

// Function to create a simple SVG state seal
function createStateSeal($stateCode, $stateName, $filePath) {
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer circle -->
    <circle cx="100" cy="100" r="95" fill="#1e3a8a" stroke="#1f2937" stroke-width="3"/>
    
    <!-- Inner circle -->
    <circle cx="100" cy="100" r="80" fill="#3b82f6" stroke="#ffffff" stroke-width="2"/>
    
    <!-- State code -->
    <text x="100" y="85" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="24" font-weight="bold">' . $stateCode . '</text>
    
    <!-- State name -->
    <text x="100" y="110" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="12" font-weight="normal">' . strtoupper($stateName) . '</text>
    
    <!-- Official seal text -->
    <text x="100" y="130" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="10" font-weight="normal">OFFICIAL SEAL</text>
    
    <!-- Decorative stars -->
    <polygon points="100,45 102,51 108,51 103,55 105,61 100,57 95,61 97,55 92,51 98,51" fill="white"/>
    <polygon points="100,155 102,149 108,149 103,145 105,139 100,143 95,139 97,145 92,149 98,149" fill="white"/>
    <polygon points="55,100 61,102 61,108 57,103 51,105 55,100 51,95 57,97 61,92 61,98" fill="white"/>
    <polygon points="145,100 139,102 139,108 143,103 149,105 145,100 149,95 143,97 139,92 139,98" fill="white"/>
</svg>';
    
    file_put_contents($filePath, $svg);
}

// Get all state stamps from database
try {
    $stateStamps = DB::table('state_stamps')->get();
    $created = 0;
    
    foreach ($stateStamps as $stamp) {
        $filePath = public_path('storage/' . $stamp->logo_path);
        
        // Only create if file doesn't exist
        if (!file_exists($filePath)) {
            // Ensure directory exists
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            createStateSeal($stamp->state_code, $stamp->state_name, $filePath);
            echo "✅ Created seal for {$stamp->state_name} ({$stamp->state_code})\n";
            echo "   File: {$filePath}\n";
            $created++;
        } else {
            echo "ℹ️ Seal already exists for {$stamp->state_name}\n";
        }
    }
    
    echo "\n✅ Created {$created} new state seal images\n";
    
} catch (Exception $e) {
    echo "❌ State seal creation failed: " . $e->getMessage() . "\n";
}

// Create a test HTML file to preview the seals
echo "\nStep 2: Creating Preview HTML\n";
try {
    $previewHtml = '<!DOCTYPE html>
<html>
<head>
    <title>State Seals Preview</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .seal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .seal-item { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .seal-item img { max-width: 150px; height: 150px; border-radius: 50%; }
        .seal-item h3 { margin: 10px 0 5px 0; color: #333; }
        .seal-item p { margin: 0; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <h1>State Seals Preview</h1>
    <p>Preview of all generated state seals for certificate use.</p>
    <div class="seal-grid">';
    
    $stateStamps = DB::table('state_stamps')->orderBy('state_name')->get();
    foreach ($stateStamps as $stamp) {
        $imagePath = '/storage/' . $stamp->logo_path;
        $previewHtml .= '
        <div class="seal-item">
            <img src="' . $imagePath . '" alt="' . $stamp->state_name . ' Seal" onerror="this.style.display=\'none\'">
            <h3>' . $stamp->state_name . '</h3>
            <p>' . $stamp->state_code . '</p>
            <p><small>' . $stamp->logo_path . '</small></p>
        </div>';
    }
    
    $previewHtml .= '
    </div>
    <div style="margin-top: 40px; padding: 20px; background: white; border-radius: 8px;">
        <h2>Usage Instructions</h2>
        <ol>
            <li>These are placeholder SVG seals created automatically</li>
            <li>Replace with official state seals as needed</li>
            <li>Files are located in: <code>public/storage/state-stamps/</code></li>
            <li>Supported formats: PNG, JPG, SVG</li>
            <li>Recommended size: 200x200 pixels</li>
        </ol>
    </div>
</body>
</html>';
    
    $previewPath = public_path('state-seals-preview.html');
    file_put_contents($previewPath, $previewHtml);
    echo "✅ Created preview file: {$previewPath}\n";
    echo "   View at: " . url('/state-seals-preview.html') . "\n";
    
} catch (Exception $e) {
    echo "❌ Preview creation failed: " . $e->getMessage() . "\n";
}

echo "\n🎉 STATE SEALS CREATION COMPLETED!\n";
echo "==================================\n";
echo "✅ Default SVG state seals created\n";
echo "✅ Preview HTML file generated\n";
echo "✅ All files ready for certificate generation\n";

echo "\n🧪 NEXT STEPS:\n";
echo "1. View preview: /state-seals-preview.html\n";
echo "2. Replace placeholder seals with official ones if needed\n";
echo "3. Test certificate generation\n";
echo "4. Verify seals appear in PDF certificates\n";

echo "\n🗑️ DELETE THIS FILE AFTER RUNNING!\n";
?>
<?php

echo "🚀 CREATING STATE STAMP PLACEHOLDER IMAGES\n";
echo "==========================================\n\n";

$stateStampsDir = 'public/images/state-stamps';
if (!is_dir($stateStampsDir)) {
    mkdir($stateStampsDir, 0755, true);
    echo "✅ Created state-stamps directory\n";
}

// Create placeholder state seals if they don't exist
$states = [
    'FL' => 'Florida',
    'CA' => 'California', 
    'TX' => 'Texas',
    'MO' => 'Missouri',
    'DE' => 'Delaware'
];

foreach ($states as $code => $name) {
    $sealPath = $stateStampsDir . '/' . $code . '-seal.png';
    if (!file_exists($sealPath)) {
        // Create a simple placeholder seal
        $placeholder = imagecreate(96, 96);
        $bg = imagecolorallocate($placeholder, 255, 255, 255);
        $text_color = imagecolorallocate($placeholder, 0, 0, 0);
        $border_color = imagecolorallocate($placeholder, 52, 73, 94);
        
        // Draw circle border
        imageellipse($placeholder, 48, 48, 90, 90, $border_color);
        imageellipse($placeholder, 48, 48, 88, 88, $border_color);
        
        // Add state code
        $font_size = 5;
        $text_width = imagefontwidth($font_size) * strlen($code);
        $text_height = imagefontheight($font_size);
        $x = (96 - $text_width) / 2;
        $y = (96 - $text_height) / 2;
        imagestring($placeholder, $font_size, $x, $y - 10, $code, $text_color);
        
        // Add "SEAL" text
        $seal_text = "SEAL";
        $seal_width = imagefontwidth(3) * strlen($seal_text);
        $seal_x = (96 - $seal_width) / 2;
        imagestring($placeholder, 3, $seal_x, $y + 15, $seal_text, $text_color);
        
        imagepng($placeholder, $sealPath);
        imagedestroy($placeholder);
        echo "✅ Created placeholder seal for {$name} ({$code})\n";
    } else {
        echo "✅ {$name} seal exists\n";
    }
}

echo "\n🎉 STATE STAMP CREATION COMPLETE!\n";
echo "All state stamp placeholder images have been created.\n";
echo "Location: {$stateStampsDir}\n";
echo "\n🏁 Completed at " . date('Y-m-d H:i:s') . "\n";
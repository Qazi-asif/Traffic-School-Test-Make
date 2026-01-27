<?php
/**
 * FIX SEAL FRAME ONLY
 * Only fixes the certificate template to properly frame state seal images
 * No routes, no complications - just the CSS fix for image sizing
 * 
 * Usage: php fix_seal_frame_only.php
 */

echo "🖼️ FIXING SEAL FRAME ONLY\n";
echo "========================\n\n";

// Step 1: Update Certificate Template CSS Only
echo "Step 1: Updating Certificate Template CSS\n";

$templatePath = 'resources/views/certificate-pdf.blade.php';
if (!file_exists($templatePath)) {
    echo "❌ Certificate template not found: {$templatePath}\n";
    exit(1);
}

$templateContent = file_get_contents($templatePath);

// Add enhanced CSS for state seal frame before </style>
$cssToAdd = '
        /* Enhanced State Seal Frame */
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
            max-width: 96px !important;
            max-height: 96px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            object-position: center !important;
            border-radius: 48px;
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

// Insert the CSS before </style>
if (strpos($templateContent, '/* Enhanced State Seal Frame */') === false) {
    $templateContent = str_replace('</style>', $cssToAdd . '</style>', $templateContent);
    echo "✅ Added enhanced state seal CSS\n";
} else {
    echo "ℹ️ Enhanced CSS already exists\n";
}

// Remove any inline styles that might override our CSS
$templateContent = preg_replace(
    '/style="[^"]*max-width[^"]*"/i',
    '',
    $templateContent
);

$templateContent = preg_replace(
    '/style="[^"]*object-fit[^"]*"/i',
    '',
    $templateContent
);

// Save the updated template
file_put_contents($templatePath, $templateContent);
echo "✅ Certificate template updated with proper seal frame\n";

echo "\n🎉 SEAL FRAME FIX COMPLETED!\n";
echo "============================\n";
echo "✅ Added 100px x 100px circular frame with black border\n";
echo "✅ Images constrained to 96px x 96px (2px border space)\n";
echo "✅ Added overflow: hidden to prevent image spillover\n";
echo "✅ Used !important to override any conflicting styles\n";
echo "✅ Added border-radius to images for better circular fit\n";

echo "\n💡 WHAT THIS DOES:\n";
echo "• Creates a perfect circular frame for state seals\n";
echo "• Prevents images from being too large\n";
echo "• Maintains professional appearance\n";
echo "• Works with SVG, PNG, JPG images\n";

echo "\n🧪 TEST NOW:\n";
echo "1. Generate a certificate from /my-certificates\n";
echo "2. Check that the state seal is properly framed\n";
echo "3. Image should be contained within circular border\n";
echo "4. No overflow outside the designated area\n";

echo "\n🗑️ You can delete this file now.\n";
?>
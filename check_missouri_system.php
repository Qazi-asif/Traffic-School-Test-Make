<?php

echo "=== Missouri Form 4444 System Check ===\n";

// Check if required files exist
$requiredFiles = [
    'app/Services/MissouriForm4444PdfService.php',
    'resources/views/certificates/missouri-form-4444.blade.php',
    'resources/views/emails/missouri-form-4444.blade.php',
    'app/Listeners/GenerateMissouriForm4444.php',
    'resources/views/admin/missouri-forms.blade.php',
];

echo "\n1. Checking required files...\n";
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file}\n";
    } else {
        echo "❌ {$file} - MISSING\n";
    }
}

// Check if routes are added
echo "\n2. Checking routes...\n";
$webRoutes = file_get_contents('routes/web.php');
if (strpos($webRoutes, 'missouri/form4444') !== false) {
    echo "✅ Missouri routes found in web.php\n";
} else {
    echo "❌ Missouri routes missing from web.php\n";
}

// Check EventServiceProvider
echo "\n3. Checking EventServiceProvider...\n";
$eventProvider = file_get_contents('app/Providers/EventServiceProvider.php');
if (strpos($eventProvider, 'GenerateMissouriForm4444') !== false) {
    echo "✅ GenerateMissouriForm4444 listener registered\n";
} else {
    echo "❌ GenerateMissouriForm4444 listener not registered\n";
}

// Check MissouriController
echo "\n4. Checking MissouriController...\n";
$controller = file_get_contents('app/Http/Controllers/MissouriController.php');
if (strpos($controller, 'MissouriForm4444PdfService') !== false) {
    echo "✅ MissouriController updated with PDF service\n";
} else {
    echo "❌ MissouriController missing PDF service integration\n";
}

echo "\n=== System Check Complete ===\n";
echo "If all items show ✅, the Missouri Form 4444 system is properly installed.\n";
echo "You can now test it by completing a Missouri course or using the admin interface.\n";
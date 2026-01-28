<?php

// Fix all CSRF token JavaScript calls in create-course.blade.php
header('Content-Type: application/json');

try {
    $filePath = '../resources/views/create-course.blade.php';

    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $content = file_get_contents($filePath);

    // Replace all instances of the unsafe CSRF token access
    $oldPattern = "document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')";
    $newPattern = "getSafeCSRFToken()";

    $newContent = str_replace($oldPattern, $newPattern, $content);

    // Count replacements
    $replacements = substr_count($content, $oldPattern);

    if ($replacements > 0) {
        file_put_contents($filePath, $newContent);
        echo json_encode([
            'success' => true,
            'message' => "Successfully replaced $replacements instances of unsafe CSRF token access",
            'replacements' => $replacements,
            'details' => 'All CSRF token calls now use getSafeCSRFToken() function'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No unsafe CSRF token calls found',
            'replacements' => 0
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>
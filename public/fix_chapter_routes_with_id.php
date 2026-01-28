<?php

// Fix chapter save routes to include course ID
header('Content-Type: application/json');

try {
    $filePath = '../resources/views/create-course.blade.php';

    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $content = file_get_contents($filePath);

    // Replace the bypass routes to include course ID
    $replacements = [
        "url = '/api/chapter-save-bypass';" => "url = '/api/chapter-save-bypass/' + currentCourseId;",
    ];

    $totalReplacements = 0;
    $newContent = $content;

    foreach ($replacements as $old => $new) {
        $count = substr_count($newContent, $old);
        $newContent = str_replace($old, $new, $newContent);
        $totalReplacements += $count;
    }

    if ($totalReplacements > 0) {
        file_put_contents($filePath, $newContent);
        echo json_encode([
            'success' => true,
            'message' => "Successfully updated $totalReplacements chapter route calls to include course ID",
            'replacements' => $totalReplacements,
            'details' => 'Chapter save routes now include course ID parameter'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No chapter route calls found to update',
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
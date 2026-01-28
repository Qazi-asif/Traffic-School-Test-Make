<?php

// Fix chapter save routes to use bypass routes
header('Content-Type: application/json');

try {
    $filePath = '../resources/views/create-course.blade.php';

    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $content = file_get_contents($filePath);

    // Replace chapter creation routes with bypass routes
    $replacements = [
        "url = '/web/courses/' + courseId + '/chapters';" => "url = '/api/chapter-save-bypass';",
        "url = '/web/courses/' + currentCourseId + '/chapters';" => "url = '/api/chapter-save-bypass';",
        "url = '/web/chapters/' + chapterId;" => "url = '/api/chapter-update-bypass/' + chapterId;",
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
            'message' => "Successfully updated $totalReplacements chapter route calls",
            'replacements' => $totalReplacements,
            'details' => 'All chapter save/update calls now use bypass routes'
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
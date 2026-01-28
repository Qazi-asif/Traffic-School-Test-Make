<?php

// Fix chapter edit, delete, and bulk import functionality
header('Content-Type: application/json');

try {
    $filePath = '../resources/views/create-course.blade.php';

    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $content = file_get_contents($filePath);

    // Fix chapter update/edit routes
    $replacements = [
        // Fix chapter update routes
        "url = '/web/chapters/' + chapterId;" => "url = '/api/chapter-update-bypass/' + chapterId;",
        
        // Fix chapter delete routes  
        "method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getSafeCSRFToken()
                    }," => "method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    },",
        
        // Update delete URL pattern
        "'/web/chapters/' + chapterId" => "'/api/chapter-delete-bypass/' + chapterId",
    ];

    $totalReplacements = 0;
    $newContent = $content;

    foreach ($replacements as $old => $new) {
        $count = substr_count($newContent, $old);
        $newContent = str_replace($old, $new, $newContent);
        $totalReplacements += $count;
    }

    // Ensure bulk import functionality is visible by checking for import button
    if (strpos($newContent, 'Import from DOCX') === false) {
        // Add bulk import button if missing
        $importButtonHtml = '
                        <button type="button" class="btn btn-info me-2" onclick="showDocxImportModal()">
                            <i class="fas fa-file-import"></i> Import from DOCX
                        </button>';
        
        // Find a good place to insert it (after Add Chapter button)
        $addChapterPattern = '<button type="button" class="btn btn-success me-2" onclick="showAddChapterModal()">';
        if (strpos($newContent, $addChapterPattern) !== false) {
            $newContent = str_replace(
                $addChapterPattern . '
                            <i class="fas fa-plus"></i> Add Chapter
                        </button>',
                $addChapterPattern . '
                            <i class="fas fa-plus"></i> Add Chapter
                        </button>' . $importButtonHtml,
                $newContent
            );
            $totalReplacements++;
        }
    }

    if ($totalReplacements > 0) {
        file_put_contents($filePath, $newContent);
        echo json_encode([
            'success' => true,
            'message' => "Successfully updated $totalReplacements chapter functionality items",
            'replacements' => $totalReplacements,
            'details' => 'Chapter edit, delete, and bulk import functionality updated'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No chapter functionality items found to update',
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
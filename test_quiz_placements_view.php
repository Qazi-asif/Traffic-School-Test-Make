<?php

// Simple test to check if the view files are working
// You can run this in your browser by accessing: /test-quiz-placements-view

// Test data
$courses = collect([
    (object) ['id' => 1, 'title' => 'Test Course', 'state_code' => 'FL'],
]);

$placements = collect([
    (object) [
        'id' => 1,
        'quiz_title' => 'Test Quiz',
        'quiz_description' => 'Test Description',
        'after_chapter_id' => null,
        'order_index' => 1,
        'is_mandatory' => true,
        'is_active' => true,
    ]
]);

$chapters = collect([
    (object) ['id' => 1, 'title' => 'Chapter 1', 'order_index' => 1],
]);

$courseId = 1;

// Test if the view can be rendered
try {
    $view = view('admin.free-response-quiz-placements.index', compact('courses', 'placements', 'chapters', 'courseId'));
    echo "✅ View file is working correctly!<br>";
    echo "View path: resources/views/admin/free-response-quiz-placements/index.blade.php<br>";
    echo "Layout: layouts.app<br>";
    echo "Variables passed: courses, placements, chapters, courseId<br>";
} catch (Exception $e) {
    echo "❌ Error rendering view: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

?>
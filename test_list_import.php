<?php
// Test script to see what HTML is generated from a simple numbered list

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a simple test HTML with numbered items (simulating what's in the database)
$testHtml = "<p>1. Threats to Safety</p>
<p>Danger, be it real and immediate or merely perceived, is a trigger for the 'fight or flight' response in humans.</p>
<p>2. Encroachment on Personal Space</p>
<p>The perception of danger is often intertwined with the concept of personal space.</p>
<p>3. Over-confidence</p>
<p>Many drivers inflate their own confidence in their driving ability.</p>
<p>4. Misunderstanding and Miscommunication</p>
<p>There are no effective means of detailed communication between different drivers.</p>";

echo "=== ORIGINAL HTML ===\n";
echo $testHtml;

// Test the fixListNumbering method
$controller = new \App\Http\Controllers\ChapterController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('fixListNumbering');
$method->setAccessible(true);

$fixedHtml = $method->invoke($controller, $testHtml);

echo "\n\n=== FIXED HTML ===\n";
echo $fixedHtml;

echo "\n\n=== FORMATTED FOR DISPLAY ===\n";
echo htmlspecialchars($fixedHtml);

// Test what this would look like in a browser
echo "\n\n=== EXPECTED RESULT ===\n";
echo "This should create ONE <ol> with 4 <li> items that will display as:\n";
echo "1. Threats to Safety\n";
echo "2. Encroachment on Personal Space\n";
echo "3. Over-confidence\n";
echo "4. Misunderstanding and Miscommunication\n";
?>
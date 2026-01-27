<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;

echo "🧪 Testing Question API Fix\n\n";

// Create a mock request with chapter context
$request = new Request();
$request->merge(['chapter_id' => '169']);
$request->headers->set('X-Chapter-Id', '169');

// Create controller instance
$controller = new QuestionController();

echo "📋 Testing question ID 30 with chapter context (169):\n";

try {
    // Simulate the request context
    app()->instance('request', $request);
    
    $response = $controller->show(30);
    $data = json_decode($response->getContent(), true);
    
    echo "✅ Response received:\n";
    echo "ID: {$data['id']}\n";
    echo "Chapter: {$data['chapter_id']}\n";
    echo "Question: " . substr($data['question_text'], 0, 60) . "...\n";
    
    if ($data['chapter_id'] == 169 && strpos($data['question_text'], 'scan the road') !== false) {
        echo "🎉 SUCCESS! Correct question returned!\n";
    } else {
        echo "❌ FAILED! Wrong question returned.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📋 Testing question ID 30 WITHOUT chapter context:\n";

try {
    // Create request without chapter context
    $requestNoContext = new Request();
    app()->instance('request', $requestNoContext);
    
    $response = $controller->show(30);
    $data = json_decode($response->getContent(), true);
    
    echo "Response received:\n";
    echo "ID: {$data['id']}\n";
    echo "Chapter: {$data['chapter_id']}\n";
    echo "Question: " . substr($data['question_text'], 0, 60) . "...\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
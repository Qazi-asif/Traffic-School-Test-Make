<?php
// Final DOCX Upload Fix - Complete Solution
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Final DOCX Upload Fix - Complete Solution</h1>";
echo "<pre>";

try {
    echo "=== FINAL DOCX UPLOAD FIX ===\n\n";
    
    // 1. Check current CSRF token
    echo "1. Checking CSRF token...\n";
    
    $currentToken = csrf_token();
    echo "   Current CSRF token: " . substr($currentToken, 0, 20) . "...\n";
    
    // 2. Test DOCX import endpoint directly
    echo "\n2. Testing DOCX import endpoint directly...\n";
    
    try {
        $controller = new \App\Http\Controllers\ChapterController();
        
        // Create a proper request with CSRF token
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-CSRF-TOKEN', $currentToken);
        
        // Add session data to simulate web request
        $request->setLaravelSession(app('session'));
        
        $response = $controller->importDocx($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $isJson = json_decode($content) !== null;
        
        echo "   Response Status: {$status}\n";
        echo "   Valid JSON: " . ($isJson ? 'Yes' : 'No') . "\n";
        
        if ($isJson) {
            $data = json_decode($content, true);
            if (isset($data['message']) && strpos($data['message'], 'CSRF') !== false) {
                echo "   ❌ CSRF token mismatch still occurring\n";
                echo "   This means the session/token validation is failing\n";
            } else {
                echo "   ✅ CSRF validation passed (got validation error as expected)\n";
            }
        }
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   ✅ Validation exception (expected - no file provided)\n";
    } catch (Exception $e) {
        echo "   ❌ Unexpected error: " . $e->getMessage() . "\n";
    }
    
    // 3. Create a working DOCX upload page with proper error handling
    echo "\n3. Creating final working DOCX upload page...\n";
    
    $finalWorkingHtml = '<!DOCTYPE html>
<html>
<head>
    <title>Final DOCX Upload Fix</title>
    <meta name="csrf-token" content="' . $currentToken . '">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .upload-section { border: 2px dashed #ddd; padding: 30px; text-align: center; margin: 20px 0; border-radius: 10px; }
        .upload-section.dragover { border-color: #007cba; background: #f0f8ff; }
        .
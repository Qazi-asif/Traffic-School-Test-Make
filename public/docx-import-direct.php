<?php

// Direct DOCX import endpoint that bypasses all Laravel middleware
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle GET request for testing
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'message' => 'DOCX import endpoint is ready',
        'method' => 'GET',
        'instructions' => 'Send POST request with file to import DOCX',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Handle POST request for file upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST to upload files.']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded. Please select a DOCX file.']);
    exit;
}

$file = $_FILES['file'];

// Basic validation
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'File upload error: ' . $file['error']]);
    exit;
}

// Check file extension
$filename = $file['name'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if ($extension !== 'docx') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only DOCX files are allowed.']);
    exit;
}

try {
    // Check if ZipArchive is available
    if (!class_exists('ZipArchive')) {
        throw new Exception('ZipArchive class not available. Please install php-zip extension.');
    }

    // Basic DOCX processing without Laravel dependencies
    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== TRUE) {
        throw new Exception('Could not open DOCX file. File may be corrupted.');
    }

    // Extract document.xml
    $documentXml = $zip->getFromName('word/document.xml');
    if (!$documentXml) {
        throw new Exception('Could not find document content in DOCX file.');
    }

    $zip->close();

    // Basic text extraction
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress XML warnings
    $dom->loadXML($documentXml);
    
    // Extract text content
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    
    $paragraphs = $xpath->query('//w:p');
    $html = '';
    
    foreach ($paragraphs as $paragraph) {
        $textNodes = $xpath->query('.//w:t', $paragraph);
        $paragraphText = '';
        
        foreach ($textNodes as $textNode) {
            $paragraphText .= $textNode->textContent;
        }
        
        if (!empty(trim($paragraphText))) {
            $html .= '<p>' . htmlspecialchars($paragraphText, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
        }
    }
    
    if (empty($html)) {
        $html = '<p>Content imported from: ' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p>The document appears to be empty or contains only formatting.</p>';
    }

    // Success response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'images_imported' => 0,
        'message' => 'DOCX imported successfully (direct PHP endpoint)',
        'filename' => htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'),
        'file_size' => $file['size'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process DOCX: ' . $e->getMessage(),
        'message' => 'Please try again or contact support.',
        'filename' => isset($filename) ? $filename : 'unknown'
    ]);
}

?>
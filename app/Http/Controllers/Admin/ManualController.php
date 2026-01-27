<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ManualController extends Controller
{
    /**
     * Generate PDF version of the admin manual
     */
    public function generatePdf()
    {
        // Load the manual PDF view
        $pdf = Pdf::loadView('admin.manual-pdf');
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        
        // Return PDF download
        return $pdf->download('Traffic-School-Admin-Manual.pdf');
    }
    
    /**
     * Generate Word document version of the admin manual
     */
    public function generateWord()
    {
        try {
            // Read the markdown manual content
            $markdownContent = file_get_contents(base_path('ADMIN_USER_MANUAL.md'));
            
            // Create a simple HTML version for Word
            $htmlContent = $this->convertMarkdownToWordHtml($markdownContent);
            
            // Set headers for Word document download
            $filename = 'Traffic-School-Admin-Manual.doc';
            
            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Type: application/msword');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            
            echo $htmlContent;
            exit;
            
        } catch (\Exception $e) {
            // If Word generation fails, return error response
            return response()->json([
                'error' => 'Failed to generate Word document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Convert markdown content to Word-compatible HTML
     */
    private function convertMarkdownToWordHtml($markdown)
    {
        // Simple markdown to HTML conversion for Word compatibility
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Platform - Admin User Manual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 11pt;
            text-align: left;
            margin: 1in;
            padding: 0;
        }
        
        .cover-page {
            text-align: center;
            padding-top: 200px;
            page-break-after: always;
        }
        
        .cover-title {
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .cover-subtitle {
            font-size: 18pt;
            color: #7f8c8d;
            margin-bottom: 40px;
        }
        
        .cover-info {
            font-size: 12pt;
            color: #95a5a6;
            margin-top: 100px;
        }
        
        h1 {
            color: #2c3e50;
            font-size: 20pt;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 40px;
            margin-bottom: 20px;
            page-break-before: always;
            text-align: left;
            font-weight: bold;
        }
        
        h1:first-of-type {
            page-break-before: auto;
        }
        
        h2 {
            color: #34495e;
            font-size: 16pt;
            margin-top: 30px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        h3 {
            color: #7f8c8d;
            font-size: 14pt;
            margin-top: 25px;
            margin-bottom: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        h4 {
            color: #95a5a6;
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        p {
            margin-bottom: 12px;
            text-align: left;
            line-height: 1.6;
        }
        
        ul, ol {
            margin-bottom: 15px;
            padding-left: 25px;
            text-align: left;
        }
        
        li {
            margin-bottom: 5px;
            text-align: left;
            line-height: 1.5;
        }
        
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: "Courier New", monospace;
            font-size: 10pt;
            color: #e74c3c;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            overflow-x: auto;
            font-family: "Courier New", monospace;
            font-size: 9pt;
            line-height: 1.4;
            margin: 15px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .url {
            color: #3498db;
            text-decoration: underline;
        }
        
        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-title">Traffic School Platform</div>
        <div class="cover-subtitle">Administrator User Manual</div>
        <div class="cover-info">
            <p>Comprehensive Guide for System Administration</p>
            <p>Version 1.0 | December 2025</p>
        </div>
    </div>

    <!-- Main Content -->
';
        
        // Convert basic markdown elements to HTML
        $html .= $this->parseMarkdownContent($markdown);
        
        $html .= '
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Parse markdown content to HTML
     */
    private function parseMarkdownContent($markdown)
    {
        // Split into lines for processing
        $lines = explode("\n", $markdown);
        $html = '';
        $inCodeBlock = false;
        $inList = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines in code blocks
            if ($inCodeBlock && empty($line)) {
                $html .= "\n";
                continue;
            }
            
            // Handle code blocks
            if (strpos($line, '```') === 0) {
                if ($inCodeBlock) {
                    $html .= "</pre>\n";
                    $inCodeBlock = false;
                } else {
                    $html .= "<pre>";
                    $inCodeBlock = true;
                }
                continue;
            }
            
            if ($inCodeBlock) {
                $html .= htmlspecialchars($line) . "\n";
                continue;
            }
            
            // Handle headers
            if (preg_match('/^# (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<h1>" . htmlspecialchars($matches[1]) . "</h1>\n";
            } elseif (preg_match('/^## (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<h2>" . htmlspecialchars($matches[1]) . "</h2>\n";
            } elseif (preg_match('/^### (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<h3>" . htmlspecialchars($matches[1]) . "</h3>\n";
            } elseif (preg_match('/^#### (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<h4>" . htmlspecialchars($matches[1]) . "</h4>\n";
            }
            // Handle lists
            elseif (preg_match('/^- (.+)$/', $line, $matches)) {
                if (!$inList) {
                    $html .= "<ul>\n";
                    $inList = true;
                }
                $html .= "<li>" . htmlspecialchars($matches[1]) . "</li>\n";
            }
            // Handle numbered lists
            elseif (preg_match('/^\d+\. (.+)$/', $line, $matches)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<ol><li>" . htmlspecialchars($matches[1]) . "</li></ol>\n";
            }
            // Handle regular paragraphs
            elseif (!empty($line)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                // Convert inline code
                $line = preg_replace('/`([^`]+)`/', '<code>$1</code>', $line);
                // Convert URLs
                $line = preg_replace('/\/[a-zA-Z0-9\-\/]+/', '<span class="url">$0</span>', $line);
                $html .= "<p>" . htmlspecialchars($line) . "</p>\n";
            } else {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<br>\n";
            }
        }
        
        // Close any open lists
        if ($inList) {
            $html .= "</ul>\n";
        }
        
        return $html;
    }
    
    private function addWordSection($section, $title, $content)
    {
        // This method is no longer needed with the new HTML-based approach
        // Keeping for backward compatibility but not used
    }
    /**
     * Preview the manual in browser
     */
    public function preview()
    {
        return view('admin.manual-pdf');
    }
}
<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/admin/manual/pdf', function () {
    $markdownContent = file_get_contents(base_path('ADMIN_USER_MANUAL.md'));
    
    // Convert markdown to HTML (you'll need a markdown parser)
    // For now, we'll create a simple HTML version
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Admin User Manual</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
            h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            h2 { color: #34495e; margin-top: 30px; }
            h3 { color: #7f8c8d; }
            code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
            .toc { background: #ecf0f1; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        </style>
    </head>
    <body>
        <h1>Traffic School Platform - Admin User Manual</h1>
        <div class="toc">
            <h2>Table of Contents</h2>
            <ul>
                <li><a href="#getting-started">Getting Started</a></li>
                <li><a href="#course-management">Course Management</a></li>
                <li><a href="#chapter-management">Chapter Management</a></li>
                <li><a href="#question-management">Question Management</a></li>
                <li><a href="#student-management">Student Management</a></li>
                <li><a href="#state-integration">State Integration Management</a></li>
                <li><a href="#payment-revenue">Payment & Revenue Management</a></li>
                <li><a href="#certificate-management">Certificate Management</a></li>
                <li><a href="#system-administration">System Administration</a></li>
                <li><a href="#troubleshooting">Troubleshooting</a></li>
            </ul>
        </div>
        ' . nl2br(htmlspecialchars($markdownContent)) . '
    </body>
    </html>';
    
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    
    return $pdf->download('admin-user-manual.pdf');
})->middleware(['auth', 'admin']);
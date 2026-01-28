<!DOCTYPE html>
<html>
<head>
    <title>True Bulk Import - Multiple DOCX Files</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .file-drop-zone {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .file-drop-zone:hover {
            border-color: #0056b3;
            background: #e3f2fd;
        }
        .file-drop-zone.dragover {
            border-color: #28a745;
            background: #d4edda;
        }
        .file-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .progress-container {
            display: none;
        }
        .result-item {
            padding: 8px;
            margin: 4px 0;
            border-radius: 4px;
        }
        .result-success {
            background: #d4edda;
            border:
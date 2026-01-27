<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Traffic School">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta data-user-email="content="{{ auth()->user()->email ?? 'User' }}">
    <title>Course Player</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/strict-timer.js?v={{ time() }}"></script>
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            /* DRM Protection */
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -webkit-touch-callout: none;
        }
        
        /* Prevent text selection on course content */
        #chapter-content, #chapter-title, .chapter-text {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -webkit-touch-callout: none;
        }
        
        /* Watermark overlay */
        .drm-watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            opacity: 0.05;
            font-size: 48px;
            font-weight: bold;
            color: #000;
            overflow: hidden;
            white-space: nowrap;
            transform: rotate(-45deg);
            line-height: 1.5;
        }
        
        .drm-watermark-text {
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .card-header {
            background-color: var(--accent);
            color: white;
            border-bottom-color: var(--border);
        }
        .chapter-item {
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            border: 1px solid var(--border);
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .chapter-item:hover {
            background-color: var(--hover);
        }
        .chapter-item.active {
            background-color: var(--accent);
            color: white;
        }
        .chapter-item.completed {
            background-color: var(--success-bg) !important;
            border-left: 4px solid var(--success-dark) !important;
            color: white !important;
        }
        .chapter-item.locked {
            cursor: not-allowed;
            opacity: 0.5;
            background-color: var(--bg-secondary);
            color: var(--text-muted);
        }
        .chapter-item.locked:hover {
            background-color: var(--bg-secondary);
        }
        .chapter-item.completed::before {
            content: '✓ ';
            color: white;
            font-weight: bold;
            font-size: 1.2em;
            margin-right: 8px;
        }
        .chapter-item.completed .chapter-status {
            color: white;
            font-weight: 600;
            font-size: 0.85em;
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        
        /* Fixed button styles for better readability */
        .btn-course-action {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: white !important;
        }
        .btn-course-action:hover {
            background-color: #0b5ed7 !important;
            border-color: #0a58ca !important;
            color: white !important;
        }
        .btn-course-action:focus {
            background-color: #0b5ed7 !important;
            border-color: #0a58ca !important;
            color: white !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
        
        .progress {
            background-color: var(--bg-primary);
        }
        .progress-bar {
            background-color: var(--accent);
        }
        /* Form controls now handled by global themes.css */
        .chapter-text {
            line-height: 1.6;
            font-size: 16px;
        }
        .chapter-text h1, .chapter-text h2, .chapter-text h3, 
        .chapter-text h4, .chapter-text h5, .chapter-text h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
        }
        .chapter-text h1 { font-size: 2em; }
        .chapter-text h2 { font-size: 1.75em; }
        .chapter-text h3 { font-size: 1.5em; }
        .chapter-text h4 { font-size: 1.25em; }
        .chapter-text p {
            margin-bottom: 1em;
        }
        .chapter-text ul, .chapter-text ol {
            margin-bottom: 1em;
            padding-left: 1.5em;
        }
        .chapter-text li {
            margin-bottom: 0.3em;
            margin-left: 0;
        }
        
        /* Ensure ordered lists have proper numbering */
        .chapter-text ol {
            counter-reset: list-counter;
            list-style-type: decimal;
        }
        .chapter-text ol li {
            counter-increment: list-counter;
            list-style-type: decimal;
        }
        
        /* Style for converted lists */
        #chapter-content .converted-list {
            margin: 1em 0;
            padding-left: 2em;
        }
        #chapter-content .converted-list li {
            margin-bottom: 0.5em;
        }
        
        /* SIMPLE FIX: Auto-number paragraphs that start with "1." */
        #chapter-content {
            counter-reset: auto-number;
        }
        
        #chapter-content p:has-text("1."):first-of-type,
        #chapter-content p[data-list-item] {
            counter-increment: auto-number;
        }
        
        /* Hide the manual "1." and show auto counter */
        #chapter-content p:has-text("1.")::before {
            content: counter(auto-number) ". ";
            font-weight: bold;
        }
        
        /* Alternative approach - use JavaScript to add data attributes */
        #chapter-content p[data-auto-number]::before {
            content: attr(data-auto-number) ". ";
            font-weight: bold;
        }
        
        #chapter-content p[data-auto-number] {
            text-indent: -1em;
            padding-left: 1em;
        }
        .chapter-text img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        /* Support for text wrapping around images */
        .chapter-text img[style*="float: left"],
        .chapter-text img[align="left"] {
            float: left;
            margin: 0 15px 10px 0;
            max-width: 300px;
        }
        
        .chapter-text img[style*="float: right"],
        .chapter-text img[align="right"] {
            float: right;
            margin: 0 0 10px 15px;
            max-width: 300px;
        }
        
        /* Default styling for images in chapter-media divs */
        .chapter-media {
            margin: 1em 0;
            clear: both;
        }
        
        .chapter-media img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        /* Support Word document image positioning */
        #chapter-content img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        /* Images that should float left (common Word positioning) */
        #chapter-content img[style*="float"],
        #chapter-content img[align] {
            margin: 0 15px 10px 0;
            max-width: 300px;
        }
        
        /* Clear floats after paragraphs to prevent layout issues */
        #chapter-content p:after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* Ensure proper spacing around floating images */
        #chapter-content img[style*="float: left"] + p,
        #chapter-content img[style*="float: left"] ~ p {
            margin-top: 0;
        }
        
        /* Handle Word document specific image containers */
        #chapter-content div[style*="text-align"] img {
            margin: 0 15px 10px 0;
            max-width: 300px;
            float: left;
        }
        
        /* Responsive behavior for small screens */
        @media (max-width: 768px) {
            #chapter-content img[style*="float"] {
                float: none !important;
                display: block;
                margin: 10px auto !important;
                max-width: 100% !important;
            }
        }
        
        /* ========================================
           RESPONSIVE LAYOUT FIXES
        ======================================== */
        
        /* Main content responsive layout */
        .main-content {
            margin-left: 300px;
            max-width: calc(100% - 320px);
            transition: margin-left 0.3s ease, max-width 0.3s ease;
        }
        
        /* Mobile Navigation Toggle */
        .mobile-nav-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1050;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
        
        .mobile-nav-toggle:hover {
            background: var(--hover);
            transform: scale(1.05);
        }
        
        /* Responsive breakpoints */
        @media (max-width: 1199px) {
            /* Large tablets and small desktops */
            .main-content {
                margin-left: 280px;
                max-width: calc(100% - 300px);
            }
        }
        
        @media (max-width: 991px) {
            /* Tablets */
            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 1rem;
            }
            
            .mobile-nav-toggle {
                display: block;
            }
            
            /* Hide desktop sidebar on tablets and mobile */
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
        
        @media (max-width: 768px) {
            /* Mobile phones */
            .main-content {
                padding: 0.5rem;
                margin-top: 60px; /* Account for mobile nav toggle */
            }
            
            .mobile-nav-toggle {
                display: block;
            }
            
            /* Mobile-specific adjustments */
            .card {
                border-radius: 12px;
                margin-bottom: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            /* Stack elements vertically on mobile */
            .row {
                margin: 0;
            }
            
            .col-md-3, .col-md-9 {
                padding: 0;
                margin-bottom: 1rem;
            }
            
            /* Hide desktop-only elements */
            .desktop-only {
                display: none !important;
            }
            
            /* Mobile navigation overlay */
            .mobile-menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
                display: none;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
            
            /* Mobile sidebar */
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100%;
                background: var(--bg-secondary);
                z-index: 1041;
                transition: left 0.3s ease;
                overflow-y: auto;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }
            
            .sidebar.mobile-open {
                left: 0;
            }
            
            /* Override inline styles on mobile */
            .sidebar {
                left: -100% !important;
            }
            
            .sidebar.mobile-open {
                left: 0 !important;
            }
            
            /* Mobile sidebar header */
            .mobile-sidebar-header {
                padding: 1rem;
                background: var(--accent);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .mobile-sidebar-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 50%;
                transition: background 0.2s ease;
            }
            
            .mobile-sidebar-close:hover {
                background: rgba(255,255,255,0.2);
            }
        }
        
        @media (max-width: 576px) {
            /* Extra small phones */
            .main-content {
                padding: 0.25rem;
            }
            
            .mobile-nav-toggle {
                width: 44px;
                height: 44px;
                top: 0.75rem;
                left: 0.75rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            /* Smaller sidebar on very small screens */
            .sidebar {
                width: 260px;
            }
        }
        
        /* ========================================
           COMPREHENSIVE MOBILE OPTIMIZATION
        ======================================== */
        
        /* Mobile-First Base Styles */
        .mobile-optimized {
            font-size: 16px; /* Prevents zoom on iOS */
            line-height: 1.5;
        }
        
        /* Touch-Friendly Targets */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .touch-target-lg {
            min-height: 56px;
            padding: 16px 24px;
            font-size: 18px;
        }
        
        /* Mobile Navigation */
        .mobile-nav-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1050;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: var(--bg-secondary);
            transition: left 0.3s ease;
            z-index: 1041;
            padding: 2rem 1rem;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            left: 0;
        }
        
        .mobile-menu-item {
            display: block;
            padding: 1rem;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background 0.2s ease;
        }
        
        .mobile-menu-item:hover {
            background: var(--hover);
            color: var(--text-primary);
        }
        
        /* Mobile Buttons */
        .btn-mobile {
            min-height: 48px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .btn-mobile-lg {
            min-height: 56px;
            padding: 16px 32px;
            font-size: 18px;
        }
        
        .btn-mobile-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-mobile-primary:hover {
            background: var(--hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        
        /* Mobile Quiz Interface */
        .mobile-quiz-container {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .mobile-quiz-question {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            line-height: 1.4;
        }
        
        .mobile-quiz-option {
            display: block;
            width: 100%;
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            background: var(--bg-primary);
            border: 2px solid var(--border);
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 16px;
            color: var(--text-primary);
        }
        
        .mobile-quiz-option:hover {
            border-color: var(--accent);
            background: var(--hover);
            transform: translateY(-1px);
        }
        
        .mobile-quiz-option.selected {
            border-color: var(--accent);
            background: rgba(0,123,255,0.1);
            color: var(--accent);
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 576px) {
            /* Extra Small Devices (phones) */
            .mobile-nav-toggle {
                display: block;
            }
            
            .desktop-only {
                display: none !important;
            }
            
            .container-fluid {
                padding: 0.5rem;
            }
            
            .btn {
                min-height: 48px;
                padding: 12px 16px;
                font-size: 16px;
            }
            
            /* Stack buttons vertically on mobile */
            .btn-group-mobile .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            /* Optimize quiz for small screens */
            #quiz-section .form-check {
                margin-bottom: 1rem;
            }
            
            #quiz-section .form-check-input {
                transform: scale(1.3);
                margin-right: 1rem;
            }
            
            #quiz-section .form-check-label {
                font-size: 16px;
                line-height: 1.4;
                padding: 0.75rem;
                background: var(--bg-secondary);
                border: 2px solid var(--border);
                border-radius: 8px;
                display: block;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            #quiz-section .form-check-input:checked + .form-check-label {
                background: rgba(0,123,255,0.1);
                border-color: var(--accent);
                color: var(--accent);
            }
            
            /* Mobile pagination */
            .pagination-controls {
                flex-direction: column;
                gap: 1rem;
            }
            
            .pagination-buttons {
                width: 100%;
                display: flex;
                gap: 0.5rem;
            }
            
            .pagination-buttons .btn {
                flex: 1;
            }
            
            /* Hide complex settings on mobile */
            .settings-panel {
                display: none;
            }
            
            /* Mobile modal optimization */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .modal-content {
                border-radius: 12px;
            }
            
            .modal-header {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1rem;
                max-height: 70vh;
                overflow-y: auto;
            }
            
            .modal-footer {
                padding: 1rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .modal-footer .btn {
                width: 100%;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            /* Small Devices (large phones, small tablets) */
            .btn {
                min-height: 44px;
                padding: 10px 16px;
            }
            
            .pagination-controls {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 1rem;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            /* Medium Devices (tablets) */
            .container-fluid {
                max-width: 800px;
                margin: 0 auto;
            }
            
            /* Two-column layout for tablets */
            .tablet-two-column {
                display: grid;
                grid-template-columns: 1fr 300px;
                gap: 2rem;
            }
        }
        
        /* Touch Gesture Support */
        .swipe-container {
            touch-action: pan-y;
            user-select: none;
            position: relative;
        }
        
        .swipe-indicator {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: var(--accent);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        
        .swipe-indicator.left {
            left: 1rem;
        }
        
        .swipe-indicator.right {
            right: 1rem;
        }
        
        .swipe-indicator.active {
            opacity: 0.7;
        }
        
        /* iOS Safe Area Support */
        @supports (padding: max(0px)) {
            .container-fluid {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
            }
            
            .pagination-controls {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
        
        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .btn {
                border: 2px solid currentColor;
            }
            
            .card {
                border: 2px solid var(--text-primary);
            }
        }
        
        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        .chapter-text table {
            width: 100%;
            margin: 1em 0;
            border-collapse: collapse;
        }
        
        /* Pagination Styles */
        .content-pagination {
            margin: 20px 0;
            padding: 15px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
        }
        
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .pagination-info {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .pagination-buttons {
            display: flex;
            gap: 10px;
        }
        
        .pagination-btn {
            padding: 8px 16px;
            border: 1px solid var(--border);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: var(--hover);
            border-color: var(--accent);
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-progress {
            width: 100%;
            height: 6px;
            background-color: var(--bg-primary);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .pagination-progress-bar {
            height: 100%;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }
        
        .content-page {
            display: none;
            min-height: 400px;
        }
        
        .content-page.active {
            display: block;
        }
        
        .page-break-indicator {
            display: none !important; /* Hide page break indicators */
        }
        
        /* Hide any text containing "Page Break" */
        .chapter-text p:has-text("Page Break"),
        .chapter-text div:has-text("Page Break"),
        .chapter-text span:has-text("Page Break") {
            display: none !important;
        }
        
        /* Hide centered page break text */
        .chapter-text p[style*="text-align: center"]:contains("Page Break"),
        .chapter-text p[style*="text-align:center"]:contains("Page Break") {
            display: none !important;
        }
        
        .pagination-settings {
            display: none;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .pagination-settings.show {
            display: block;
        }
        
        .settings-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .settings-row:last-child {
            margin-bottom: 0;
        }
        
        .settings-label {
            font-weight: 600;
            min-width: 120px;
        }
        
        .settings-control {
            flex: 1;
        }
        
        .range-input {
            width: 100%;
        }
        
        .range-value {
            font-weight: 600;
            color: var(--accent);
            min-width: 80px;
            text-align: right;
        }
        .chapter-text table th,
        .chapter-text table td {
            border: 1px solid var(--border);
            padding: 8px 12px;
        }
        .chapter-text table th {
            background-color: var(--bg-primary);
            font-weight: 600;
        }
        .chapter-text strong {
            font-weight: 600;
        }
        .chapter-text em {
            font-style: italic;
        }
        .chapter-text a {
            color: var(--accent);
            text-decoration: underline;
        }
        
        /* Ensure all course content text has proper contrast */
        #chapter-content, #chapter-content * {
            color: var(--text-secondary) !important;
        }
        
        #chapter-content h1, #chapter-content h2, #chapter-content h3,
        #chapter-content h4, #chapter-content h5, #chapter-content h6,
        #chapter-content strong, #chapter-content b {
            color: var(--text-primary) !important;
        }
        
        /* Remove any hardcoded gray highlights */
        #chapter-content *[style*="color: gray"],
        #chapter-content *[style*="color: grey"],
        #chapter-content *[style*="background"] {
            color: var(--text-secondary) !important;
            background: transparent !important;
        }
        
        /* Ensure good contrast for highlighted text */
        #chapter-content mark, #chapter-content .highlight {
            background: var(--warning-light) !important;
            color: var(--text-primary) !important;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        /* Admin Quiz Notice Styles */
        .admin-quiz-notice {
            background: rgba(25, 135, 84, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        
        /* Security Timer Styles */
        .security-timer-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        #security-timer-display {
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            min-width: 60px;
            display: inline-block;
            text-align: center;
        }
        
        #security-timer-display.text-warning {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107 !important;
        }
        
        #security-timer-display.text-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545 !important;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        /* Admin Quiz Notice Styles */
        .admin-quiz-notice {
            background: rgba(25, 135, 84, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        
        /* Tab Styles */
        .nav-tabs .nav-link {
            color: var(--text-primary);
            background-color: transparent;
            border: none;
            border-bottom: 2px solid transparent;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--accent);
        }
        
        .nav-tabs .nav-link.active {
            color: white;
            background-color: transparent;
            border-bottom-color: white;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    <div class="container-fluid mt-4 main-content" id="main-content">
        <!-- Mobile Navigation Toggle -->
        <button class="mobile-nav-toggle d-lg-none" id="mobile-nav-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Course Timer Display -->
        <div class="alert alert-info mb-3" id="timer-display" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-clock me-2"></i>
                    <strong>Chapter Timer:</strong> <span id="timer-text">00:00</span>
                    <span class="ms-3 text-muted">Required: <span id="required-time">0</span> minutes</span>
                </div>
                <div>
                    <span class="badge bg-warning" id="timer-status">In Progress</span>
                </div>
            </div>
            <div class="progress mt-2" style="height: 5px;">
                <div class="progress-bar" id="timer-progress" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
        
        <!-- Admin Notice for Timer Bypass -->
        <div class="alert alert-success mb-3" id="admin-timer-notice" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user-shield me-2"></i>
                    <strong>Admin Mode:</strong> Chapter timer disabled for administrative access
                    <span class="ms-3 text-muted">Normal duration: <span id="admin-chapter-duration">0</span> minutes</span>
                </div>
                <div>
                    <span class="badge bg-success">Timer Bypassed</span>
                </div>
            </div>
        </div>
        
        <div id="app">
        @if(isset($showCompletionOnly) && $showCompletionOnly)
        <!-- Course Completion Result View -->
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-success shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Course Completed</h4>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-trophy" style="font-size: 4rem; color: #28a745;"></i>
                            </div>
                            <h3 class="mb-3">Congratulations!</h3>
                            <p class="lead mb-4">You have successfully completed this course.</p>
                            
                            <div class="alert alert-info mb-4">
                                @php
                                    try {
                                        $completedDate = is_string($enrollment->completed_at) 
                                            ? \Carbon\Carbon::parse($enrollment->completed_at)->format('F j, Y \a\t g:i A')
                                            : $enrollment->completed_at->format('F j, Y \a\t g:i A');
                                    } catch (\Exception $e) {
                                        $completedDate = $enrollment->completed_at;
                                    }
                                @endphp
                                <strong>Completion Date:</strong> {{ $completedDate }}
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Course Status</h6>
                                            <p class="card-text">
                                                <span class="badge bg-success">Completed</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Final Exam Score</h6>
                                            <p class="card-text">
                                                @if($examResult)
                                                    <span class="badge bg-info" style="font-size: 1.1rem; padding: 0.5rem 1rem;">{{ $examResult->score }}%</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="/my-enrollments" class="btn btn-primary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Back to My Enrollments
                                </a>
                                <a href="/my-certificates" class="btn btn-success btn-lg">
                                    <i class="fas fa-certificate me-2"></i>View Certificate
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div id="app">
            <course-player></course-player>
        </div>
        @endif
        @if(!isset($showCompletionOnly) || !$showCompletionOnly)
        <div id="fallback-content">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="chapters-tab" data-bs-toggle="tab" data-bs-target="#chapters-content" type="button" role="tab">
                                        Chapters
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="chapters-content" role="tabpanel">
                                    <div id="chapters-list">
                                        <p>Loading chapters...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 id="chapter-title" class="mb-0">Course Content</h5>
                            <button class="btn btn-sm btn-outline-light" onclick="togglePaginationSettings()" title="Pagination Settings">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                        <div class="card-body" id="chapter-content">
                            <p>Select a chapter to begin learning.</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <button class="btn btn-secondary" id="prevBtn" onclick="previousChapter()">
                                <i class="fas fa-chevron-left me-2"></i>Previous
                            </button>
                            <button class="btn btn-primary" id="nextBtn" onclick="nextChapter()">
                                Next<i class="fas fa-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <script>
        // User information for admin check
        const isAdmin = @json(auth()->check() && auth()->user()->isAdmin());
        window.isAdmin = isAdmin; // Make it globally available for strict timer
        console.log('User is admin:', isAdmin);
        
        // Environment check for DRM protection
        const appEnv = '{{ config("app.env") }}';
        const isDevelopment = appEnv === 'local' || appEnv === 'development';
        const isProduction = appEnv === 'production';
        console.log('App Environment:', appEnv, '| Development:', isDevelopment, '| Production:', isProduction);
        
        // Helper function to parse options from various formats
        function parseOptions(optionsData) {
            let options = [];
            
            // Parse if string
            if (typeof optionsData === 'string') {
                try {
                    optionsData = JSON.parse(optionsData);
                } catch (e) {
                    return [optionsData];
                }
            }
            
            if (Array.isArray(optionsData)) {
                // Check if array contains objects with label/text
                if (optionsData.length > 0 && typeof optionsData[0] === 'object' && optionsData[0] !== null) {
                    if (optionsData[0].label !== undefined) {
                        options = optionsData.map(o => `${o.label}. ${o.text || ''}`).filter(t => t.trim() !== '. ');
                    } else if (optionsData[0].text !== undefined) {
                        options = optionsData.map(o => o.text);
                    } else {
                        options = optionsData.map(o => Object.values(o).join('. '));
                    }
                } else {
                    options = optionsData;
                }
            } else if (typeof optionsData === 'object' && optionsData !== null) {
                options = Object.entries(optionsData).map(([k, v]) => `${k}. ${v}`);
            }
            
            return options;
        }
        
        // Helper function to check if answers match (handles both letter and text answers)
        function answersMatch(userAnswer, correctAnswer, questionOptions) {
            if (!userAnswer || !correctAnswer) {
                console.log('❌ Missing answer:', { userAnswer, correctAnswer });
                return false;
            }
            
            // Normalize both answers to uppercase letters
            const userNorm = userAnswer.toString().trim().toUpperCase();
            const correctNorm = correctAnswer.toString().trim().toUpperCase();
            
            console.log('🔍 Comparing answers:', { 
                userAnswer: userAnswer, 
                correctAnswer: correctAnswer,
                userNorm: userNorm, 
                correctNorm: correctNorm 
            });
            
            // Direct letter match (A, B, C, D, E)
            if (userNorm === correctNorm) {
                console.log('✅ Direct match found');
                return true;
            }
            
            // If correct answer is full text, try to find its letter equivalent
            if (!/^[A-E]$/.test(correctNorm)) {
                console.log('🔍 Correct answer is not a letter, trying to convert:', correctNorm);
                let options = parseOptions(questionOptions);
                console.log('🔍 Available options:', options);
                
                for (let i = 0; i < options.length; i++) {
                    const optText = options[i].toString().replace(/^[A-E]\.\s*/i, '').trim();
                    if (optText.toLowerCase() === correctNorm.toLowerCase()) {
                        const expectedLetter = String.fromCharCode(65 + i); // A, B, C, D, E
                        console.log('🔍 Found matching option text, expected letter:', expectedLetter);
                        const result = userNorm === expectedLetter;
                        console.log(result ? '✅ Match found after conversion' : '❌ No match after conversion');
                        return result;
                    }
                }
                console.log('❌ No matching option text found');
            }
            
            console.log('❌ No match found');
            return false;
        }
        
        // Helper function to get display text for answer (converts letters to full text)
        function getAnswerDisplayText(answer, questionOptions) {
            if (!answer) return 'No answer';
            
            let options = parseOptions(questionOptions);
            
            // If answer is a letter and we have options, convert to full text
            if (/^[A-E]$/i.test(answer) && options && options.length > 0) {
                const letterIndex = answer.toUpperCase().charCodeAt(0) - 65;
                if (letterIndex >= 0 && letterIndex < options.length) {
                    return options[letterIndex].replace(/^[A-E]\.\s*/i, '').trim();
                }
            }
            
            return answer;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        // Get enrollment ID from URL path (/course-player/19) or query parameter (?enrollmentId=19)
        const pathParts = window.location.pathname.split('/');
        const enrollmentId = pathParts[2] || urlParams.get('enrollmentId');
        let courseStateCode = ''; // Will be set when enrollment is loaded
        
        let currentEnrollment = null;
        let chapters = [];
        let strictDurationEnabled = false;
        let chaptersCompletedCount = 0; // Track completed chapters for sequential questions
        
        async function loadCourseData() {
            console.log('Loading course data for enrollment ID:', enrollmentId);
            
            if (!enrollmentId) {
                document.getElementById('chapters-list').innerHTML = '<p class="text-danger">No enrollment ID provided.</p>';
                return;
            }
            
            try {
                const response = await fetch(`/web/enrollments/${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    console.error('Response not ok:', response.status, response.statusText);
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error(`Failed to load course data: ${response.status} ${response.statusText}`);
                }
                
                currentEnrollment = await response.json();
                
                // Set course state code for Delaware quiz rotation
                courseStateCode = currentEnrollment.course?.state_code || '';
                
                // Check if course data is available
                if (!currentEnrollment.course || !currentEnrollment.course.id) {
                    throw new Error('Course data not found for this enrollment');
                }
                
                // Load strict duration setting
                strictDurationEnabled = currentEnrollment.course.strict_duration_enabled || false;
                window.strictDurationEnabled = strictDurationEnabled;
                console.log('Strict duration enabled:', strictDurationEnabled);
                
                // Load chapters
                const chaptersResponse = await fetch(`/web/courses/${currentEnrollment.course.id}/chapters?enrollmentId=${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (chaptersResponse.ok) {
                    chapters = await chaptersResponse.json();
                    console.log('✅ Chapters loaded:', chapters.length, 'chapters');
                    console.log('📋 Chapters data:', chapters);
                    
                    // Initialize chapter completion count for security questions
                    chaptersCompletedCount = chapters.filter(chapter => chapter.is_completed).length;
                    console.log(`Initialized with ${chaptersCompletedCount} completed chapters`);
                    
                    displayChapters();
                    
                    // Auto-select the appropriate chapter based on progress
                    const resumeChapter = findResumeChapter();
                    if (resumeChapter) {
                        console.log('🎯 Auto-resuming from chapter:', resumeChapter.id, resumeChapter.title);
                        selectChapter(resumeChapter.id);
                    } else {
                        // Fallback to first available chapter
                        const firstAvailableChapter = chapters.find((chapter, index) => isChapterUnlocked(index));
                        if (firstAvailableChapter) {
                            console.log('📚 Starting from first available chapter:', firstAvailableChapter.id);
                            selectChapter(firstAvailableChapter.id);
                        }
                    }
                } else {
                    console.error('❌ Chapters response not ok:', chaptersResponse.status);
                    const errorText = await chaptersResponse.text();
                    console.error('❌ Error response:', errorText);
                    throw new Error(`Failed to load chapters: ${chaptersResponse.status}`);
                }
                
            } catch (error) {
                console.error('❌ Error loading course data:', error);
                console.error('❌ Error message:', error.message);
                console.error('❌ Error stack:', error.stack);
                document.getElementById('chapters-list').innerHTML = '<p class="text-danger">Error loading course data: ' + error.message + '</p>';
            }
        }
        
        async function displayChapters() {
            const container = document.getElementById('chapters-list');
            
            if (chapters.length === 0) {
                container.innerHTML = '<p>No chapters available.</p>';
                return;
            }
            
            // Build chapters HTML first
            const chaptersHtml = chapters.map((chapter, index) => {
                // Admin users can access any chapter, regular users follow unlock rules
                const isLocked = !isAdmin && !isChapterUnlocked(index);
                const isCompleted = chapter.is_completed || false;
                
                // Only show completed styling if NOT locked
                const completedClass = (isCompleted && !isLocked) ? 'completed' : '';
                const lockedClass = isLocked ? 'locked' : '';
                const completedBadge = (isCompleted && !isLocked) ? '<span class="chapter-status">Completed</span>' : '';
                const lockedBadge = isLocked ? '<span class="chapter-status text-muted">🔒 Locked</span>' : '';
                
                // Admin users get special badge for unlocked chapters
                const adminBadge = isAdmin && !isCompleted && !isLocked && index > 0 ? '<span class="chapter-status text-info">👨‍💼 Admin Access</span>' : '';
                
                const clickHandler = isLocked ? 'showLockedChapterMessage()' : getChapterClickHandler(chapter);
                
                return `
                    <div class="chapter-item ${completedClass} ${lockedClass}" onclick="${clickHandler}" data-chapter-id="${chapter.id}">
                        <strong>${index + 1}. ${chapter.title}</strong>
                        ${completedBadge}
                        ${lockedBadge}
                        ${adminBadge}
                        <br>
                        <small class="text-muted">${getChapterTypeLabel(chapter)} • ${chapter.duration} minutes</small>
                    </div>
                `;
            }).join('');
            
            // Remove the hardcoded Questions and Final Exam sections since they're now handled dynamically
            container.innerHTML = chaptersHtml;
        }

        // Helper function to get the appropriate click handler for different chapter types
        function getChapterClickHandler(chapter) {
            switch (chapter.chapter_type) {
                case 'free_response_quiz':
                    return `loadFreeResponseQuizChapter('${chapter.id}', ${chapter.placement_id})`;
                case 'chapter_break':
                    return `selectChapter('${chapter.id}')`;
                case 'final_exam':
                    return `selectChapter('${chapter.id}')`;
                default:
                    return `selectChapter('${chapter.id}')`;
            }
        }

        // Helper function to get chapter type label
        function getChapterTypeLabel(chapter) {
            switch (chapter.chapter_type) {
                case 'free_response_quiz':
                    return 'Free Response Quiz';
                case 'chapter_break':
                    return 'Break';
                case 'final_exam':
                    return 'Final Exam';
                default:
                    return 'Chapter';
            }
        }

        // Function to load free response quiz chapter
        async function loadFreeResponseQuizChapter(chapterId, placementId) {
            if (!enrollmentId) {
                alert('Enrollment ID not found');
                return;
            }
            
            // Set current chapter ID for navigation
            currentChapterId = chapterId;
            
            // Find the chapter data
            const chapter = chapters.find(c => c.id === chapterId);
            if (!chapter) {
                alert('Chapter not found');
                return;
            }
            
            // Update chapter title
            document.getElementById('chapter-title').textContent = chapter.title;
            
            // Load questions for this specific placement
            await loadFreeResponseQuestionsForPlacement(placementId);
        }

        async function loadFreeResponseQuestionsForPlacement(placementId) {
            try {
                // Get free response questions for this specific placement
                const response = await fetch(`/api/free-response-questions?enrollment_id=${enrollmentId}&placement_id=${placementId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load questions');
                }
                
                const data = await response.json();
                const questions = data.questions || [];
                const existingAnswers = data.existingAnswers || {};
                const isSubmitted = data.isSubmitted || false;
                const submittedAt = data.submittedAt;
                const placement = data.placement;
                
                // Store placement data for later use
                window.currentQuizPlacement = placement;
                
                if (questions.length === 0) {
                    document.getElementById('chapter-content').innerHTML = `
                        <div class="alert alert-info">
                            <h5>No Questions Available</h5>
                            <p>There are no free response questions for this course yet.</p>
                            ${placement && placement.use_random_selection ? 
                                `<p><small class="text-muted">Random Selection: ${placement.questions_to_select} questions from pool of ${data.totalQuestionsInPool}</small></p>` : 
                                ''
                            }
                        </div>
                    `;
                    return;
                }
                
                // Show selection info if using random selection
                const selectionInfo = placement && placement.use_random_selection ? 
                    `<div class="alert alert-info mb-3">
                        <i class="fas fa-random me-2"></i>
                        <strong>Random Selection:</strong> You have been assigned ${data.questionsSelected} questions from a pool of ${data.totalQuestionsInPool} questions.
                    </div>` : '';
                
                // Build questions HTML with conditional read-only mode
                const questionsHtml = questions.map((question, index) => {
                    const existingAnswer = existingAnswers[question.id] || '';
                    const isReadOnly = isSubmitted ? 'readonly' : '';
                    const disabledClass = isSubmitted ? 'bg-light' : '';
                    
                    return `
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Question ${index + 1}</h6>
                                ${isSubmitted ? '<span class="badge bg-success ms-2">Submitted</span>' : ''}
                            </div>
                            <div class="card-body">
                                <p class="question-text">${question.question_text}</p>
                                <div class="mb-3">
                                    <label class="form-label">Your Answer (50-100 words required):</label>
                                    <textarea 
                                        class="form-control free-response-answer ${disabledClass}" 
                                        data-question-id="${question.id}"
                                        rows="6" 
                                        placeholder="${isSubmitted ? 'Answer submitted' : 'Type your answer here (50-100 words)...'}"
                                        ${isReadOnly}
                                    >${existingAnswer}</textarea>
                                    <div class="form-text">
                                        <span class="word-count" data-question-id="${question.id}">0</span> words (50-100 required)
                                        ${isSubmitted ? '<span class="text-success ms-3"><i class="fas fa-check-circle"></i> Submitted</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
                
                // Show different content based on submission status
                if (isSubmitted) {
                    // Check if 24-hour enforcement is enabled
                    const show24HourNotice = placement && placement.enforce_24hour_grading;
                    
                    document.getElementById('chapter-content').innerHTML = `
                        <div class="free-response-container">
                            <div class="alert alert-success mb-4">
                                <h5><i class="fas fa-check-circle me-2"></i>Free Response Questions - Completed</h5>
                                <p class="mb-2">You have successfully submitted your answers on ${new Date(submittedAt).toLocaleDateString()}.</p>
                                <p class="mb-0"><strong>Note:</strong> Answers cannot be edited after submission.</p>
                            </div>
                            
                            ${show24HourNotice ? `
                                <div class="alert alert-warning mb-4">
                                    <h5><i class="fas fa-clock me-2"></i>24-Hour Grading Period Active</h5>
                                    <p class="mb-0">Your results are under instructor review. Final grades and detailed feedback will be available within 24 hours.</p>
                                </div>
                            ` : ''}
                            
                            ${selectionInfo}
                            
                            <div class="submitted-answers">
                                ${questionsHtml}
                            </div>
                            
                            <div class="text-center mt-4">
                                <button class="btn btn-primary btn-lg" onclick="loadCourseData()">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Course
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('chapter-content').innerHTML = `
                        <div class="free-response-container">
                            <div class="alert alert-info mb-4">
                                <h5><i class="fas fa-edit me-2"></i>Free Response Questions</h5>
                                <p class="mb-0">Please answer all questions thoughtfully. Each answer must be between 50-100 words.</p>
                            </div>
                            
                            ${selectionInfo}
                            
                            <form id="free-response-form">
                                ${questionsHtml}
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Answers
                                    </button>
                                </div>
                            </form>
                        </div>
                    `;
                    
                    // Initialize word counters and form submission only if not submitted
                    initializeFreeResponseForm();
                }
                
            } catch (error) {
                console.error('Error loading free response questions:', error);
                document.getElementById('chapter-content').innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Error Loading Questions</h5>
                        <p>Failed to load free response questions. Please try again.</p>
                    </div>
                `;
            }
        }
        
        function initializeFreeResponseForm() {
            // Initialize word counters
            document.querySelectorAll('.free-response-answer').forEach(textarea => {
                const questionId = textarea.dataset.questionId;
                const wordCountEl = document.querySelector(`.word-count[data-question-id="${questionId}"]`);
                
                // Update word count on input
                textarea.addEventListener('input', function() {
                    const words = this.value.trim().split(/\s+/).filter(word => word.length > 0);
                    const wordCount = this.value.trim() === '' ? 0 : words.length;
                    wordCountEl.textContent = wordCount;
                    
                    // Change color based on word count (50-100 required)
                    if (wordCount < 50) {
                        wordCountEl.style.color = 'orange';
                        wordCountEl.parentElement.classList.add('text-warning');
                        wordCountEl.parentElement.classList.remove('text-danger', 'text-success');
                    } else if (wordCount > 100) {
                        wordCountEl.style.color = 'red';
                        wordCountEl.parentElement.classList.add('text-danger');
                        wordCountEl.parentElement.classList.remove('text-warning', 'text-success');
                        
                        // Show warning but don't prevent typing
                        if (wordCount > 120) {
                            // Only show alert if significantly over limit
                            if (!this.dataset.warningShown) {
                                alert('Warning: Your answer is getting quite long. Please try to keep it between 50-100 words for best results.');
                                this.dataset.warningShown = 'true';
                            }
                        }
                    } else {
                        wordCountEl.style.color = 'green';
                        wordCountEl.parentElement.classList.add('text-success');
                        wordCountEl.parentElement.classList.remove('text-warning', 'text-danger');
                        this.dataset.warningShown = 'false';
                    }
                });
                
                // Initialize word count
                textarea.dispatchEvent(new Event('input'));
            });
            
            // Handle form submission
            document.getElementById('free-response-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                await submitFreeResponseAnswers();
            });
        }
        
        async function submitFreeResponseAnswers() {
            const textareas = document.querySelectorAll('.free-response-answer');
            const answers = [];
            let hasErrors = false;
            
            // Validate all answers
            textareas.forEach(textarea => {
                const questionId = textarea.dataset.questionId;
                const answerText = textarea.value.trim();
                const words = answerText.split(/\s+/).filter(word => word.length > 0);
                const wordCount = answerText === '' ? 0 : words.length;
                
                if (wordCount < 50) {
                    alert(`Question ${questionId}: Please write at least 50 words. Current: ${wordCount} words.`);
                    hasErrors = true;
                    return;
                }
                
                if (wordCount > 100) {
                    // Allow submission but warn user
                    if (!confirm(`Question ${questionId}: Your answer has ${wordCount} words (recommended: 50-100). Do you want to submit anyway?`)) {
                        hasErrors = true;
                        return;
                    }
                }
                
                answers.push({
                    question_id: questionId,
                    answer_text: answerText,
                    word_count: wordCount
                });
            });
            
            if (hasErrors) {
                return;
            }
            
            if (answers.length === 0) {
                alert('Please answer at least one question.');
                return;
            }
            
            try {
                // Show loading state
                const submitBtn = document.querySelector('#free-response-form button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                submitBtn.disabled = true;
                
                const response = await fetch('/api/free-response-answers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId,
                        answers: answers
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Mark chapter as completed
                    await completeChapter();
                    
                    // Show success message
                    document.getElementById('chapter-content').innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Answers Submitted Successfully!</h5>
                            <p class="mb-2">Your free response answers have been submitted for instructor review.</p>
                            <p class="mb-0">You can now continue to the next chapter.</p>
                        </div>
                        <div class="text-center mt-4">
                            <button class="btn btn-primary btn-lg" onclick="loadCourseData()">
                                <i class="fas fa-arrow-right me-2"></i>Continue to Next Chapter
                            </button>
                        </div>
                    `;
                } else {
                    throw new Error(result.message || 'Failed to submit answers');
                }
                
            } catch (error) {
                console.error('Error submitting answers:', error);
                alert('Error submitting answers: ' + error.message);
                
                // Restore button state
                const submitBtn = document.querySelector('#free-response-form button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }
        }
        
        async function submitFreeResponseAnswers() {
            const answers = {};
            let hasErrors = false;
            let errorMessages = [];
            
            // Collect answers and validate
            document.querySelectorAll('.free-response-answer').forEach(textarea => {
                const questionId = textarea.dataset.questionId;
                const answer = textarea.value.trim();
                const words = answer.split(/\s+/).filter(word => word.length > 0);
                const wordCount = answer === '' ? 0 : words.length;
                
                if (wordCount < 50) {
                    hasErrors = true;
                    textarea.classList.add('is-invalid');
                    errorMessages.push(`Question ${questionId}: Too few words (${wordCount}/50 minimum)`);
                } else if (wordCount > 100) {
                    hasErrors = true;
                    textarea.classList.add('is-invalid');
                    errorMessages.push(`Question ${questionId}: Too many words (${wordCount}/100 maximum)`);
                } else {
                    textarea.classList.remove('is-invalid');
                    answers[questionId] = answer;
                }
            });
            
            if (hasErrors) {
                alert('Please ensure all answers are between 50-100 words:\n\n' + errorMessages.join('\n'));
                return;
            }
            
            try {
                const response = await fetch('/free-response-quiz/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId,
                        answers: answers
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    document.getElementById('chapter-content').innerHTML = `
                        <div class="alert alert-success text-center">
                            <h4><i class="fas fa-check-circle me-2"></i>Answers Submitted Successfully!</h4>
                            <p class="mb-3">${result.message}</p>
                            <button class="btn btn-primary" onclick="loadCourseData()">
                                <i class="fas fa-arrow-left me-2"></i>Back to Course
                            </button>
                        </div>
                    `;
                    
                    // Update the Questions section to show completed status
                    updateQuestionsCompletionStatus();
                    
                } else {
                    alert('Error: ' + result.error);
                }
                
            } catch (error) {
                console.error('Error submitting answers:', error);
                alert('Failed to submit answers. Please try again.');
            }
        }
        
        function updateQuestionsCompletionStatus() {
            // Refresh the chapters display to show completion status
            displayChapters();
        }
        
        // Check if user has completed all free response questions
        async function checkQuestionsCompletion() {
            try {
                const response = await fetch(`/api/free-response-questions?enrollment_id=${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (!response.ok) {
                    return false;
                }
                
                const data = await response.json();
                const questions = data.questions || [];
                const isSubmitted = data.isSubmitted || false;
                
                // If no questions exist, consider it completed
                if (questions.length === 0) {
                    return true;
                }
                
                // Return true if answers have been submitted
                return isSubmitted;
                
            } catch (error) {
                console.error('Error checking questions completion:', error);
                return false;
            }
        }
        
        function isChapterUnlocked(chapterIndex) {
            // First chapter is always unlocked
            if (chapterIndex === 0) return true;
            
            // Check if all previous chapters are completed
            for (let i = 0; i < chapterIndex; i++) {
                if (!chapters[i].is_completed) {
                    return false;
                }
            }
            return true;
        }

        function findResumeChapter() {
            console.log('🔍 Finding resume chapter from progress...');
            
            // If no chapters, return null
            if (!chapters || chapters.length === 0) {
                console.log('❌ No chapters available');
                return null;
            }
            
            // Find the first incomplete chapter that is unlocked
            for (let i = 0; i < chapters.length; i++) {
                const chapter = chapters[i];
                
                // Skip if chapter is completed
                if (chapter.is_completed) {
                    console.log(`✅ Chapter ${i + 1} (${chapter.title}) is completed, skipping...`);
                    continue;
                }
                
                // Check if this chapter is unlocked
                if (isChapterUnlocked(i)) {
                    console.log(`🎯 Found resume point: Chapter ${i + 1} (${chapter.title}) - incomplete but unlocked`);
                    return chapter;
                } else {
                    console.log(`🔒 Chapter ${i + 1} (${chapter.title}) is locked, cannot resume here`);
                    break; // If we hit a locked chapter, we can't go further
                }
            }
            
            // If all chapters are completed, check if there are special sections (Questions, Final Exam)
            const allChaptersCompleted = chapters.every(chapter => chapter.is_completed);
            if (allChaptersCompleted) {
                console.log('🎉 All chapters completed, checking for Questions/Final Exam...');
                
                // Check if Questions section should be available
                // This logic should match the displayChapters() function
                // For now, return the last chapter so user can access Questions/Final Exam
                const lastChapter = chapters[chapters.length - 1];
                console.log('📚 Returning last chapter for Questions/Final Exam access:', lastChapter.title);
                return lastChapter;
            }
            
            console.log('❌ Could not determine resume chapter');
            return null;
        }
        
        function showLockedChapterMessage() {
            alert('⚠️ Please complete the previous chapters first before accessing this chapter.');
        }
        
        let currentChapterId = null;
        let chapterTimer = null;
        let timerStartTime = null;
        let timerElapsed = 0;
        let timerRequired = 0;
        let timerInterval = null;
        let timerRunning = false;
        let timeRemaining = 0;
        let quizTimerInterval = null;
        
        // Pagination variables
        let contentPages = [];
        let currentPage = 0;
        let wordsPerPage = 800; // Approximately 3-4 minutes of reading
        
        function selectChapter(chapterId) {
            console.log('🔍 selectChapter called with:', chapterId, 'Type:', typeof chapterId);
            
            // Convert to string if needed
            const chapterIdStr = String(chapterId);
            
            const chapter = chapters.find(c => String(c.id) === chapterIdStr);
            console.log('🔍 Found chapter:', chapter);
            
            if (!chapter) {
                console.error('Chapter not found:', chapterId);
                console.log('Available chapters:', chapters.map(c => ({ id: c.id, title: c.title })));
                return;
            }
            
            // Handle chapter breaks
            if (chapter.chapter_type === 'chapter_break') {
                console.log('⏸️ Chapter break detected:', chapter);
                handleChapterBreak(chapter);
                return;
            }
            
            // Handle final exam differently
            if (chapterIdStr === 'final-exam') {
                // Check if course is already completed
                if (currentEnrollment && (currentEnrollment.completed_at || currentEnrollment.status === 'completed')) {
                    console.log('🔒 Course is completed, redirecting to completion view');
                    showCourseCompleted();
                    return;
                }
                
                // Check if final exam is already passed
                if (currentEnrollment && currentEnrollment.final_exam_completed) {
                    console.log('🔒 Final exam already completed, checking pass status');
                    // Load final exam will handle showing appropriate message
                }
                
                loadFinalExam();
                return;
            }
            
            // Check if chapter is unlocked (admin users can access any chapter)
            const chapterIndex = chapters.findIndex(c => String(c.id) === chapterIdStr);
            console.log('🔍 Chapter index:', chapterIndex, 'Is unlocked:', isChapterUnlocked(chapterIndex));
            
            if (!isAdmin && !isChapterUnlocked(chapterIndex)) {
                console.warn(`Chapter ${chapterId} is locked. Chapter index: ${chapterIndex}`);
                console.warn('Previous chapters:', chapters.slice(0, chapterIndex).map(c => ({ id: c.id, completed: c.is_completed })));
                showLockedChapterMessage();
                return;
            } else if (isAdmin && !isChapterUnlocked(chapterIndex)) {
                console.log('👨‍💼 Admin user - bypassing chapter lock for testing');
            }
            
            console.log(`✅ Loading chapter ${chapterId} (index: ${chapterIndex})`);
            currentChapterId = chapterId;
            
            // Show timer display if strict duration is enabled (but not for admin users)
            if (strictDurationEnabled && !isAdmin) {
                console.log('⏱️ Strict duration enabled, showing timer');
                const timerDisplay = document.getElementById('timer-display');
                if (timerDisplay) {
                    timerDisplay.style.display = 'block';
                }
                // Hide admin notice
                const adminNotice = document.getElementById('admin-timer-notice');
                if (adminNotice) {
                    adminNotice.style.display = 'none';
                }
            } else if (isAdmin && strictDurationEnabled) {
                console.log('👨‍💼 Admin user - showing admin notice instead of timer');
                // Hide timer display for admin users
                const timerDisplay = document.getElementById('timer-display');
                if (timerDisplay) {
                    timerDisplay.style.display = 'none';
                }
                // Show admin notice with chapter duration
                const adminNotice = document.getElementById('admin-timer-notice');
                const adminDuration = document.getElementById('admin-chapter-duration');
                if (adminNotice) {
                    adminNotice.style.display = 'block';
                }
                if (adminDuration && chapter.duration) {
                    adminDuration.textContent = chapter.duration;
                }
            } else {
                // Hide both timer and admin notice if strict duration is not enabled
                const timerDisplay = document.getElementById('timer-display');
                const adminNotice = document.getElementById('admin-timer-notice');
                if (timerDisplay) timerDisplay.style.display = 'none';
                if (adminNotice) adminNotice.style.display = 'none';
            }
            
            // Check for timer configuration (skip for admin users)
            if (!isAdmin) {
                checkChapterTimer(chapterId);
            }
            
            // Start timer for this chapter (skip for admin users)
            if (!isAdmin) {
                startChapterTimer(chapterId);
            }
            
            console.log('📖 Loading chapter:', chapter);
            console.log('📖 Video URL:', chapter.video_url);
            console.log('📖 Original content:', chapter.content);
            
            document.getElementById('chapter-title').textContent = chapter.title;
            
            // Process chapter content to ensure media displays properly
            let processedContent = chapter.content;
            
            console.log('📖 Content before processing:', processedContent);
            
            // TEST: Add a visible indicator that image processing is running
            console.log('🔥 IMAGE FIX SYSTEM ACTIVE - Processing content for broken images...');
            
            // FIX: Replace broken file:// image references with placeholder images
            // This handles images copied from Word documents that have local file paths
            const placeholderImage = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIiBzdHJva2U9IiNkZWUyZTYiIHN0cm9rZS13aWR0aD0iMiIgcng9IjgiLz48dGV4dCB4PSI1MCUiIHk9IjQwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE2IiBmaWxsPSIjNjc4M2E2IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+8J+TtyBJbWFnZTwvdGV4dD48dGV4dCB4PSI1MCUiIHk9IjYwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOTlhM2I0IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm90IEF2YWlsYWJsZTwvdGV4dD48L3N2Zz4=';
            
            // Count file:// references for debugging
            const fileReferences = (processedContent.match(/file:\/\/\/[^"'\s>]*/g) || []).length;
            if (fileReferences > 0) {
                console.log(`🔍 Found ${fileReferences} file:// references to fix`);
                console.log('Sample file:// reference:', processedContent.match(/file:\/\/\/[^"'\s>]*/)?.[0]);
            }
            
            // Replace file:// image references (from Word documents) - more comprehensive
            processedContent = processedContent.replace(
                /src="file:\/\/\/[^"]*"/g, 
                `src="${placeholderImage}" alt="Image Not Available" style="max-width: 300px; height: auto; border: 2px dashed #dee2e6; border-radius: 8px; padding: 10px; background: #f8f9fa; display: inline-block;"`
            );
            processedContent = processedContent.replace(
                /src='file:\/\/\/[^']*'/g, 
                `src='${placeholderImage}' alt='Image Not Available' style='max-width: 300px; height: auto; border: 2px dashed #dee2e6; border-radius: 8px; padding: 10px; background: #f8f9fa; display: inline-block;'`
            );
            
            // Remove entire VML shape blocks that contain file:// references
            processedContent = processedContent.replace(
                /<!--\s*\[if gte vml 1\]>.*?<v:shape[^>]*>.*?<v:imagedata[^>]*src="file:\/\/\/[^"]*"[^>]*>.*?<\/v:shape>.*?<!\[endif\]-->/gs,
                `<div style="max-width: 300px; height: 150px; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; background: #f8f9fa; display: inline-block; text-align: center; margin: 10px;"><div style="color: #6783a6; font-size: 16px; margin-bottom: 5px;">📷 Image</div><div style="color: #99a3b4; font-size: 12px;">Not Available</div></div>`
            );
            
            // Remove VML imagedata references
            processedContent = processedContent.replace(
                /<v:imagedata[^>]*src="file:\/\/\/[^"]*"[^>]*>/g,
                ''
            );
            
            // Also handle any remaining file:// references in any attribute
            processedContent = processedContent.replace(
                /file:\/\/\/[^"'\s>]*/g,
                placeholderImage
            );
            
            console.log('🖼️ Fixed file:// image references with placeholders');
            
            // AGGRESSIVE IMAGE FIX - Replace ANY broken images with professional placeholders
            // This catches everything the above might have missed
            processedContent = processedContent.replace(
                /<img[^>]*>/g,
                function(match) {
                    // If image has file:// or empty src, replace with professional placeholder
                    if (match.includes('file://') || match.includes('src=""') || match.includes("src=''")) {
                        return '<div style="width: 200px; height: 120px; border: 2px dashed #dee2e6; background: #f8f9fa; display: inline-block; text-align: center; line-height: 120px; color: #6c757d; font-size: 14px; margin: 10px; border-radius: 8px;">📷 Image Not Available</div>';
                    }
                    return match;
                }
            );
            
            // Also add professional placeholders for any remaining VML or broken content
            processedContent = processedContent.replace(
                /<!--\[if !vml\]-->.*?<!--\[endif\]-->/gs,
                '<div style="width: 200px; height: 120px; border: 2px dashed #dee2e6; background: #f8f9fa; display: inline-block; text-align: center; line-height: 120px; color: #6c757d; font-size: 14px; margin: 10px; border-radius: 8px;">📷 Image Not Available</div>'
            );
            
            console.log('🖼️ Applied professional image placeholders');
            
            // Convert all storage URLs to files URLs - handle various formats
            processedContent = processedContent.replace(/src='\/storage\/course-media\//g, "src='/files/");
            processedContent = processedContent.replace(/href='\/storage\/course-media\//g, "href='/files/");
            processedContent = processedContent.replace(/src="\/storage\/course-media\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="\/storage\/course-media\//g, 'href="/files/');
            processedContent = processedContent.replace(/src="\/storage\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="\/storage\//g, 'href="/files/');
            
            // Also handle URLs without leading slash
            processedContent = processedContent.replace(/src='storage\/course-media\//g, "src='/files/");
            processedContent = processedContent.replace(/href='storage\/course-media\//g, "href='/files/");
            processedContent = processedContent.replace(/src="storage\/course-media\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="storage\/course-media\//g, 'href="/files/');
            processedContent = processedContent.replace(/src="storage\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="storage\//g, 'href="/files/');
            
            // FINAL CLEANUP: Handle any remaining broken images or empty image tags
            // Replace any img tags that still have empty or broken src attributes
            processedContent = processedContent.replace(
                /<img[^>]*src=""[^>]*>/g,
                `<div style="max-width: 300px; height: 150px; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; background: #f8f9fa; display: inline-block; text-align: center; margin: 10px;"><div style="color: #6783a6; font-size: 16px; margin-bottom: 5px;">📷 Image</div><div style="color: #99a3b4; font-size: 12px;">Not Available</div></div>`
            );
            
            // Replace any img tags that still have file:// in them (backup cleanup)
            processedContent = processedContent.replace(
                /<img[^>]*src="[^"]*file:\/\/[^"]*"[^>]*>/g,
                `<div style="max-width: 300px; height: 150px; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; background: #f8f9fa; display: inline-block; text-align: center; margin: 10px;"><div style="color: #6783a6; font-size: 16px; margin-bottom: 5px;">📷 Image</div><div style="color: #99a3b4; font-size: 12px;">Not Available</div></div>`
            );
            
            console.log('📖 Content after processing:', processedContent);
            
            // Check if there's any media in the content
            const hasImages = processedContent.includes('<img');
            const hasVideos = processedContent.includes('<video');
            const hasAudio = processedContent.includes('<audio');
            const hasPDFs = processedContent.includes('.pdf');
            console.log('📖 Media found - Images:', hasImages, 'Videos:', hasVideos, 'Audio:', hasAudio, 'PDFs:', hasPDFs);
            
            // Extract PDF links and convert them to embedded viewers
            let pdfContent = '';
            const pdfRegex = /<a[^>]*href="([^"]*\.pdf)"[^>]*>([^<]*)<\/a>/gi;
            let match;
            while ((match = pdfRegex.exec(processedContent)) !== null) {
                const pdfUrl = match[1];
                const linkText = match[2];
                pdfContent += `
                    <div class="mb-3">
                        <h6><i class="fas fa-file-pdf text-danger"></i> ${linkText}</h6>
                        <div class="pdf-container" style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                            <iframe src="${pdfUrl}" width="100%" height="600px" style="border: none;">
                                <p>Your browser does not support PDFs. <a href="${pdfUrl}" target="_blank">Download the PDF</a>.</p>
                            </iframe>
                        </div>
                        <div class="mt-2 d-flex justify-content-end gap-2">
                            <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Open in New Tab
                            </a>
                            <a href="${pdfUrl}" download class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('chapter-content').innerHTML = `
                <!-- Pagination Settings Panel -->
                <div id="pagination-settings" class="pagination-settings">
                    <h6><i class="fas fa-cog"></i> Reading Experience Settings</h6>
                    <div class="settings-row">
                        <div class="settings-label">Content per page:</div>
                        <div class="settings-control">
                            <input type="range" id="words-per-page-range" class="range-input" 
                                   min="400" max="1600" step="100" value="${wordsPerPage}">
                        </div>
                        <div id="words-per-page-value" class="range-value">${wordsPerPage} words (~${Math.ceil(wordsPerPage/200)} min)</div>
                    </div>
                    <div class="settings-row">
                        <div class="settings-label"></div>
                        <div class="settings-control">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Adjust how much content appears on each page. Lower values = more pages, easier reading.
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="resetPaginationSettings(event)">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-keyboard"></i> Use Ctrl + ← → to navigate pages
                        </small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><strong>Course Progress:</strong> ${Math.min(currentEnrollment.progress_percentage || 0, 100).toFixed(0)}%</span>
                        ${currentEnrollment.quiz_average ? `<span><strong>Quiz Average:</strong> ${currentEnrollment.quiz_average}%</span>` : ''}
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: ${currentEnrollment.progress_percentage || 0}%">
                            ${currentEnrollment.progress_percentage || 0}%
                        </div>
                    </div>
                </div>
                ${chapter.video_url ? `
                    <div class="mb-3">
                        <video src="${chapter.video_url.replace(/\/storage\/course-media\//, '/files/').replace(/\/storage\//, '/files/')}" controls width="100%" style="max-height: 400px;" preload="metadata">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                ` : ''}
                ${pdfContent}
                ${renderPaginatedContent(processedContent, chapter.duration)}
                <div id="quiz-section" class="mt-4" style="display: none;"></div>
                <div class="mt-4" id="action-button-container">
                    <!-- Button will be set dynamically based on quiz availability -->
                </div>
            `;
            
            // Initialize pagination settings after content is loaded
            setTimeout(() => {
                initializePaginationSettings();
                setupSettingsPanelEvents();
                // Initial action button setup (will be updated again after questions load)
                updateActionButtons();
                
                // Start reading time countdown using chapter duration
                const duration = chapter && chapter.duration ? parseInt(chapter.duration) : 60;
                console.log('Starting countdown with duration:', duration, 'minutes');
                startReadingCountdown(duration);
            }, 100);
            
            // Load questions for this chapter (but don't display them yet)
            loadChapterQuestions(chapter.id);
            
            // Check if user has already taken this quiz
            checkExistingQuizResult(chapter.id);
            
            // Highlight selected chapter
            document.querySelectorAll('.chapter-item').forEach(item => {
                item.classList.remove('active');
            });
            
            document.querySelector(`.chapter-item[data-chapter-id="${chapterId}"]`)?.classList.add('active');
            
            // Add error handling for media elements
            setTimeout(() => {
                const videos = document.querySelectorAll('#chapter-content video');
                videos.forEach(video => {
                    video.onerror = function() {
                        console.error('Video failed to load:', video.src);
                        video.outerHTML = `<div class="alert alert-warning">Video could not be loaded: ${video.src}</div>`;
                    };
                });
                
                const images = document.querySelectorAll('#chapter-content img');
                images.forEach(img => {
                    img.onerror = function() {
                        console.error('Image failed to load:', img.src);
                        img.outerHTML = `<div class="alert alert-warning">Image could not be loaded: ${img.src}</div>`;
                    };
                });
                
                // Fix numbered lists that are stored as individual paragraphs
                fixNumberedListsSimple();
                
                // Update navigation buttons
                updateNavigationButtons();
            }, 100);
        }

        function nextChapter() {
            // Go to next page in pagination, not next chapter
            console.log('Next button clicked. Current page:', currentPage, 'Total pages:', contentPages.length);
            nextPage();
        }

        function previousChapter() {
            // Go to previous page in pagination, not previous chapter
            console.log('Previous button clicked. Current page:', currentPage, 'Total pages:', contentPages.length);
            previousPage();
        }

        function updateNavigationButtons() {
            // Show/hide buttons based on pagination state
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            if (!prevBtn || !nextBtn) return;
            
            // Always show buttons if chapter is loaded
            if (currentChapterId) {
                prevBtn.disabled = currentPage === 0;
                nextBtn.disabled = currentPage === contentPages.length - 1;
                prevBtn.style.display = 'block';
                nextBtn.style.display = 'block';
            }
        }
        
        // Pagination Functions
        function splitContentIntoPages(content) {
            // Create a temporary div to parse HTML content
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            
            // Get all text content and count words
            const textContent = tempDiv.textContent || tempDiv.innerText || '';
            const wordCount = textContent.trim().split(/\s+/).length;
            
            // If content is short enough, return as single page
            if (wordCount <= wordsPerPage) {
                return [content];
            }
            
            // Split content into pages based on paragraphs and headings
            const elements = Array.from(tempDiv.children);
            const pages = [];
            let currentPageContent = '';
            let currentWordCount = 0;
            let lastWasHeading = false;
            
            for (let i = 0; i < elements.length; i++) {
                const element = elements[i];
                const elementText = element.textContent || element.innerText || '';
                const elementWordCount = elementText.trim().split(/\s+/).length;
                const isHeading = /^h[1-6]$/i.test(element.tagName);
                
                // If adding this element would exceed the page limit and we have content
                // BUT don't break if the last element was a heading (keep heading with content)
                if (currentWordCount + elementWordCount > wordsPerPage && currentPageContent && !lastWasHeading) {
                    pages.push(currentPageContent);
                    currentPageContent = element.outerHTML;
                    currentWordCount = elementWordCount;
                } else {
                    currentPageContent += element.outerHTML;
                    currentWordCount += elementWordCount;
                }
                
                lastWasHeading = isHeading;
            }
            
            // Add the last page if there's content
            if (currentPageContent) {
                pages.push(currentPageContent);
            }
            
            return pages.length > 0 ? pages : [content];
        }
        
        function renderPaginatedContent(content, chapterDuration = 60) {
            contentPages = splitContentIntoPages(content);
            currentPage = 0;
            
            if (contentPages.length <= 1) {
                // No pagination needed
                return `<div class="chapter-text">${content}</div>`;
            }
            
            // Create paginated content structure
            let paginatedHTML = `
                <div class="content-pagination">
                    <div class="pagination-controls">
                        <div class="pagination-info">
                            Page <span id="current-page-num">1</span> of <span id="total-pages">${contentPages.length}</span>
                            <span class="text-muted">• Reading time: <span id="reading-time-countdown">${chapterDuration}:00</span></span>
                        </div>
                        <div class="pagination-buttons">
                            <button id="prev-page-btn" class="pagination-btn" onclick="previousPage()" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <button id="next-page-btn" class="pagination-btn" onclick="nextPage()">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="pagination-progress">
                        <div id="pagination-progress-bar" class="pagination-progress-bar" style="width: ${(1/contentPages.length)*100}%"></div>
                    </div>
                </div>
                
                <div id="paginated-content">
                    ${contentPages.map((pageContent, index) => `
                        <div class="content-page ${index === 0 ? 'active' : ''}" data-page="${index}">
                            <div class="chapter-text">${pageContent}</div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            return paginatedHTML;
        }
        
        function updatePaginationControls() {
            const currentPageNum = document.getElementById('current-page-num');
            const prevBtn = document.getElementById('prev-page-btn');
            const nextBtn = document.getElementById('next-page-btn');
            const progressBar = document.getElementById('pagination-progress-bar');
            
            if (currentPageNum) currentPageNum.textContent = currentPage + 1;
            if (prevBtn) prevBtn.disabled = currentPage === 0;
            if (nextBtn) nextBtn.disabled = currentPage === contentPages.length - 1;
            if (progressBar) {
                const progress = ((currentPage + 1) / contentPages.length) * 100;
                progressBar.style.width = `${progress}%`;
            }
            
            // Update action buttons based on pagination state
            updateActionButtons();
            
            // Update bottom navigation buttons
            updateNavigationButtons();
        }
        
        let readingTimeInterval = null;
        let readingTimeRemaining = 0;
        
        function startReadingCountdown(minutes) {
            readingTimeRemaining = minutes * 60; // Convert to seconds
            
            // Clear any existing interval
            if (readingTimeInterval) clearInterval(readingTimeInterval);
            
            // Update immediately
            updateReadingTimeDisplay();
            
            // Start countdown
            readingTimeInterval = setInterval(() => {
                if (readingTimeRemaining > 0) {
                    readingTimeRemaining--;
                    updateReadingTimeDisplay();
                }
            }, 1000);
        }
        
        function updateReadingTimeDisplay() {
            const mins = Math.floor(readingTimeRemaining / 60);
            const secs = readingTimeRemaining % 60;
            const display = `${mins}:${secs.toString().padStart(2, '0')}`;
            const element = document.getElementById('reading-time-countdown');
            if (element) {
                element.textContent = display;
            }
        }
        
        function showPage(pageIndex) {
            if (pageIndex < 0 || pageIndex >= contentPages.length) return;
            
            // Hide all pages
            document.querySelectorAll('.content-page').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show current page
            const targetPage = document.querySelector(`[data-page="${pageIndex}"]`);
            if (targetPage) {
                targetPage.classList.add('active');
                targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            currentPage = pageIndex;
            updatePaginationControls();
        }
        
        // Update action buttons based on pagination and quiz state
        function updateActionButtons() {
            const actionContainer = document.getElementById('action-button-container');
            if (!actionContainer) {
                console.error('Action container not found');
                return;
            }
            
            const isOnLastPage = contentPages.length <= 1 || currentPage === contentPages.length - 1;
            const hasQuestions = currentQuestions && currentQuestions.length > 0;
            
            // Debug information
            console.log('🔍 updateActionButtons debug:', {
                isOnLastPage,
                hasQuestions,
                contentPagesLength: contentPages.length,
                currentPage,
                currentQuestionsLength: currentQuestions ? currentQuestions.length : 'null',
                currentChapterId,
                isAdmin
            });
            
            if (hasQuestions && isOnLastPage) {
                // Only show quiz button on the last page of the chapter
                const timerActive = window.strictTimer && window.strictTimer.isActive;
                const timerComplete = window.strictTimer && window.strictTimer.isTimerComplete();
                
                // STRICT ENFORCEMENT: If timer is active, quiz is disabled (unless admin)
                // This applies regardless of strictDurationEnabled setting
                const isQuizDisabled = !isAdmin && timerActive && !timerComplete;
                const disabledAttr = isQuizDisabled ? 'disabled' : '';
                const disabledClass = isQuizDisabled ? 'opacity-50' : '';
                
                let buttonText = '<i class="fas fa-play"></i> Take Quiz';
                let buttonTitle = '';
                
                if (isQuizDisabled) {
                    const timeRemaining = window.strictTimer ? Math.ceil(Math.max(0, window.strictTimer.requiredTime - window.strictTimer.elapsedTime)) : 0;
                    buttonText = `<i class="fas fa-clock"></i> Quiz Available After Timer<br><small>Time remaining: ${Math.floor(timeRemaining/60)}:${String(timeRemaining%60).padStart(2, '0')}</small>`;
                    buttonTitle = 'You must complete the chapter timer before taking the quiz';
                }
                
                // Show admin notice if timer would normally be active
                const adminNotice = isAdmin && timerActive && !timerComplete ? 
                    '<br><small class="text-info"><i class="fas fa-user-shield"></i> Admin: Timer bypassed</small>' : '';
                
                console.log('📝 Showing quiz button on last page', { isQuizDisabled, timerActive, timerComplete, isAdmin });
                actionContainer.innerHTML = `
                    <button onclick="showQuiz()" class="btn btn-primary btn-lg ${disabledClass}" ${disabledAttr} title="${buttonTitle}">
                        ${buttonText}
                        ${adminNotice}
                    </button>
                `;
            } else if (isOnLastPage) {
                // Show complete button only on last page or single page (no quiz available)
                const timerActive = window.strictTimer && window.strictTimer.isActive;
                const timerComplete = window.strictTimer && window.strictTimer.isTimerComplete();
                
                console.log('🔍 Complete button check:', {
                    timerActive,
                    timerComplete,
                    isAdmin,
                    strictTimerExists: !!window.strictTimer,
                    elapsedTime: window.strictTimer?.elapsedTime,
                    requiredTime: window.strictTimer?.requiredTime
                });
                
                // STRICT ENFORCEMENT: If timer is active, complete button is disabled (unless admin)
                const isDisabled = !isAdmin && timerActive && !timerComplete;
                const disabledAttr = isDisabled ? 'disabled' : '';
                const disabledClass = isDisabled ? 'opacity-50' : '';
                const timeRemaining = window.strictTimer ? Math.ceil(Math.max(0, window.strictTimer.requiredTime - window.strictTimer.elapsedTime)) : 0;
                const title = isDisabled ? 'You must wait for the timer to complete before marking this chapter as complete' : '';
                
                // Show admin notice if timer would normally be active
                const adminNotice = isAdmin && timerActive && !timerComplete ? 
                    '<br><small class="text-info"><i class="fas fa-user-shield"></i> Admin: Timer bypassed</small>' : '';
                
                console.log('✅ Showing complete button', { isDisabled, timerActive, timerComplete, isAdmin, disabledAttr });
                actionContainer.innerHTML = `
                    <button onclick="completeChapter()" class="btn btn-course-action btn-lg ${disabledClass}" ${disabledAttr} title="${title}">
                        <i class="fas fa-check-circle"></i> Mark Chapter as Complete
                        ${isDisabled ? '<br><small>Timer: ' + Math.floor(timeRemaining/60) + ':' + String(timeRemaining%60).padStart(2, '0') + ' remaining</small>' : ''}
                        ${adminNotice}
                    </button>
                `;
            } else {
                // Show message to continue reading (no quiz information shown)
                console.log('📖 Showing continue reading message');
                actionContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-book-open"></i> 
                        <strong>Continue Reading:</strong> Please read all content before completing this chapter.
                        <br><small class="text-muted">Use the "Next" button above to continue to page ${currentPage + 2} of ${contentPages.length}.</small>
                    </div>
                `;
            }
        }
        
        function nextPage() {
            if (currentPage < contentPages.length - 1) {
                showPage(currentPage + 1);
            }
        }
        
        function previousPage() {
            if (currentPage > 0) {
                showPage(currentPage - 1);
            }
        }
        
        // Keyboard navigation for pagination
        document.addEventListener('keydown', function(e) {
            if (contentPages.length > 1) {
                if (e.key === 'ArrowLeft' && e.ctrlKey) {
                    e.preventDefault();
                    previousPage();
                } else if (e.key === 'ArrowRight' && e.ctrlKey) {
                    e.preventDefault();
                    nextPage();
                }
            }
        });
        
        // Pagination Settings Functions
        function togglePaginationSettings() {
            console.log('🔧 Toggling pagination settings...');
            const settingsPanel = document.getElementById('pagination-settings');
            console.log('🔧 Settings panel found:', !!settingsPanel);
            
            if (settingsPanel) {
                const isCurrentlyVisible = settingsPanel.classList.contains('show');
                settingsPanel.classList.toggle('show');
                console.log('🔧 Settings panel toggled:', !isCurrentlyVisible ? 'shown' : 'hidden');
            } else {
                console.warn('🔧 Settings panel not found - may need to load a chapter first');
            }
        }
        
        function updateWordsPerPage(value) {
            wordsPerPage = parseInt(value);
            const valueDisplay = document.getElementById('words-per-page-value');
            if (valueDisplay) {
                valueDisplay.textContent = `${value} words (~${Math.ceil(value/200)} min)`;
            }
            
            // Save to localStorage
            localStorage.setItem('coursePlayerWordsPerPage', value);
            
            // Re-render current content if available
            if (currentChapterId) {
                const currentChapter = chapters.find(c => c.id === currentChapterId);
                if (currentChapter) {
                    // Re-load the chapter with new pagination settings
                    selectChapter(currentChapterId);
                }
            }
        }
        
        function resetPaginationSettings(event) {
            if (event) {
                event.stopPropagation();
            }
            
            wordsPerPage = 800;
            const rangeInput = document.getElementById('words-per-page-range');
            if (rangeInput) {
                rangeInput.value = 800;
            }
            updateWordsPerPage(800);
            localStorage.removeItem('coursePlayerWordsPerPage');
        }
        
        // Load saved settings on page load
        function loadPaginationSettings() {
            const savedWordsPerPage = localStorage.getItem('coursePlayerWordsPerPage');
            if (savedWordsPerPage) {
                wordsPerPage = parseInt(savedWordsPerPage);
            }
        }
        
        // Initialize pagination settings after content is loaded
        function initializePaginationSettings() {
            const rangeInput = document.getElementById('words-per-page-range');
            const valueDisplay = document.getElementById('words-per-page-value');
            
            if (rangeInput) {
                rangeInput.value = wordsPerPage;
                
                // Remove any existing event listeners to prevent duplicates
                rangeInput.removeEventListener('input', handleSliderChange);
                rangeInput.removeEventListener('change', handleSliderChange);
                
                // Add event listeners with proper event handling
                rangeInput.addEventListener('input', handleSliderChange);
                rangeInput.addEventListener('change', handleSliderChange);
                
                // Prevent the settings panel from closing when interacting with slider
                rangeInput.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                rangeInput.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
            }
            
            if (valueDisplay) {
                valueDisplay.textContent = `${wordsPerPage} words (~${Math.ceil(wordsPerPage/200)} min)`;
            }
        }
        
        // Handle slider changes
        function handleSliderChange(e) {
            e.stopPropagation();
            updateWordsPerPage(e.target.value);
        }
        
        // Setup event handlers for the settings panel
        function setupSettingsPanelEvents() {
            const settingsPanel = document.getElementById('pagination-settings');
            
            if (settingsPanel) {
                // Prevent settings panel from closing when clicking inside it
                settingsPanel.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Also prevent mousedown events from bubbling
                settingsPanel.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Add click handler to document to close settings when clicking outside
            document.addEventListener('click', function(e) {
                const settingsPanel = document.getElementById('pagination-settings');
                const settingsButton = e.target.closest('[onclick*="togglePaginationSettings"]');
                
                // If clicking outside settings panel and not on the settings button, close panel
                if (settingsPanel && settingsPanel.classList.contains('show') && !settingsButton) {
                    if (!settingsPanel.contains(e.target)) {
                        settingsPanel.classList.remove('show');
                    }
                }
            });
        }
        
        async function completeChapter(chapterId = null) {
            console.log('🚀 completeChapter called with:', chapterId);
            
            // Use current chapter if no chapterId provided
            const targetChapterId = chapterId || currentChapterId;
            
            console.log('🎯 Target chapter ID:', targetChapterId, 'Current chapter ID:', currentChapterId);
            console.log('📊 Enrollment ID:', enrollmentId);
            
            if (!targetChapterId) {
                console.error('❌ No chapter ID available');
                alert('Please select a chapter first');
                return Promise.reject('No chapter ID available');
            }

            // Check strict duration enforcement (bypass for admin users)
            const timerActive = window.strictTimer && window.strictTimer.isActive;
            
            console.log('⏱️ Timer check:', { 
                isAdmin, 
                strictDurationEnabled: window.strictDurationEnabled, 
                timerActive,
                strictTimer: window.strictTimer
            });
            
            if (!isAdmin && window.strictDurationEnabled && timerActive) {
                console.log('⏱️ Timer still active for non-admin user');
                alert('You must complete the full chapter duration before marking as complete.');
                return Promise.reject('Strict duration not met');
            } else if (isAdmin && window.strictDurationEnabled && timerActive) {
                console.log('👨‍💼 Admin user - bypassing chapter timer requirement');
            }
            
            try {
                console.log('📡 Making API request to complete chapter:', targetChapterId);
                
                const response = await fetch(`/web/enrollments/${enrollmentId}/complete-chapter/${targetChapterId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        time_spent: 60
                    })
                });
                
                console.log('📡 Response status:', response.status, response.statusText);
                
                const data = await response.json();
                console.log('📡 Response data:', data);
                
                if (response.ok) {
                    console.log('✅ Chapter completed successfully');
                    
                    // Update the chapter in the local chapters array
                    const chapterIndex = chapters.findIndex(c => c.id === targetChapterId);
                    if (chapterIndex !== -1) {
                        chapters[chapterIndex].is_completed = true;
                        chaptersCompletedCount++; // Increment completed chapters count
                        console.log('✅ Updated chapter in local array');
                        console.log('📊 Chapters completed count:', chaptersCompletedCount);
                    } else {
                        console.warn('⚠️ Chapter not found in local array');
                    }
                    
                    // Update the progress percentage
                    if (currentEnrollment) {
                        currentEnrollment.progress_percentage = data.progress_percentage;
                        console.log('✅ Updated progress percentage:', data.progress_percentage);
                    }
                    
                    // Refresh the chapters display to show green mark and unlock next chapter
                    await displayChapters();
                    
                    alert('Chapter marked as complete!');
                    
                    // Trigger security verification after chapter completion
                    triggerSecurityAfterChapter();
                    
                    // Auto-load next chapter - improved logic with free response quiz support
                    const currentIndex = chapters.findIndex(c => String(c.id) === String(targetChapterId));
                    console.log('🔍 Current chapter index:', currentIndex, 'Total chapters:', chapters.length);
                    
                    if (currentIndex !== -1 && currentIndex < chapters.length - 1) {
                        const nextChapter = chapters[currentIndex + 1];
                        console.log('📖 Next chapter found:', nextChapter.id, nextChapter.title, 'Type:', nextChapter.chapter_type);
                        
                        // Only auto-load if the next chapter is unlocked
                        if (isAdmin || isChapterUnlocked(currentIndex + 1)) {
                            console.log('📖 Auto-loading next chapter:', nextChapter.id);
                            setTimeout(() => {
                                // Auto-load free response quiz without requiring click
                                if (nextChapter.chapter_type === 'free_response_quiz') {
                                    console.log('🎯 Auto-loading free response quiz');
                                    loadFreeResponseQuizChapter(nextChapter.id, nextChapter.placement_id);
                                } else {
                                    selectChapter(nextChapter.id);
                                }
                            }, 1000); // Small delay to ensure UI updates
                        } else {
                            console.log('⚠️ Next chapter is locked, staying on current chapter');
                        }
                    } else {
                        console.log('🎉 This was the last chapter or chapter not found in array');
                    }
                } else {
                    console.error('❌ Failed to complete chapter:', data);
                    
                    // Check if this is a quiz validation error
                    if (data.requires_quiz) {
                        alert('🔒 ' + (data.message || 'You must pass the chapter quiz before completing this chapter.'));
                        // Show the quiz if it's not already visible
                        if (document.getElementById('quiz-container') && document.getElementById('quiz-container').style.display === 'none') {
                            showQuiz();
                        }
                    } else {
                        const errorMsg = 'Failed to complete chapter: ' + (data.error || data.message || 'Unknown error');
                        alert(errorMsg);
                    }
                    return Promise.reject(data.error || data.message || 'Unknown error');
                }
            } catch (error) {
                console.error('❌ Error completing chapter:', error);
                alert('Failed to complete chapter. Please try again.');
                return Promise.reject(error);
            }
        }
        
        let currentQuestions = [];
        let questionAttempts = {};
        
        async function loadChapterQuestions(chapterId) {
            try {
                console.log('🔍 Loading questions for chapter:', chapterId);
                
                // Build URL with quiz_set parameter for Delaware courses
                let url = `/api/chapters/${chapterId}/questions`;
                
                // Check if this is a Delaware course and get current quiz set
                if (courseStateCode === 'DE') {
                    const quizSet = await getCurrentQuizSet(chapterId);
                    url += `?quiz_set=${quizSet}`;
                    console.log('🔍 Delaware course - loading quiz set:', quizSet);
                }
                
                const response = await fetch(url);
                console.log('🔍 Questions response status:', response.status);
                
                currentQuestions = await response.json();
                console.log('🔍 Questions loaded:', currentQuestions.length, currentQuestions);
                
                if (currentQuestions.length > 0) {
                    currentQuestions.forEach(q => questionAttempts[q.id] = 0);
                    console.log('✅ Quiz questions ready for chapter');
                } else {
                    console.log('⚠️ No questions found for this chapter');
                }
                
                // Update action buttons based on current state
                updateActionButtons();
            } catch (error) {
                console.error('❌ Error loading questions:', error);
                // Update action buttons (will show appropriate message based on pagination)
                updateActionButtons();
            }
        }
        
        // Get current quiz set for Delaware courses
        async function getCurrentQuizSet(chapterId) {
            try {
                const response = await fetch(`/api/chapters/${chapterId}/quiz-progress?enrollment_id=${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    return data.current_quiz_set || 1;
                }
            } catch (error) {
                console.error('Error getting quiz set:', error);
            }
            
            return 1; // Default to quiz set 1
        }
        
        function showQuiz() {
            if (currentQuestions.length === 0) {
                alert('No quiz available for this chapter.');
                return;
            }
            
            // Hide content and show quiz
            document.querySelector('.chapter-text').style.display = 'none';
            document.querySelector('#action-button-container').style.display = 'none';
            document.querySelector('#quiz-section').style.display = 'block';
            
            displayQuestions();
        }
        
        async function submitQuiz() {
            // Stop the quiz timer
            stopQuizTimer();
            
            // Check if all questions are answered
            const unanswered = currentQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions before submitting. ${unanswered.length} question(s) remaining.`);
                // Restart the timer if user wants to continue
                startQuizTimer();
                return;
            }
            
            // Collect answers
            const results = currentQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                const userAnswer = selected ? selected.value : null;
                const isCorrect = answersMatch(userAnswer, q.correct_answer, q.options);
                
                return {
                    question_id: q.id,
                    question_text: q.question_text,
                    user_answer: userAnswer,
                    correct_answer: q.correct_answer,
                    is_correct: isCorrect,
                    explanation: q.explanation || ''
                };
            });
            
            const correctCount = results.filter(r => r.is_correct).length;
            const wrongCount = results.length - correctCount;
            const percentage = ((correctCount / results.length) * 100).toFixed(2);
            
            // Save results to database
            let quizSaveResponse = null;
            try {
                const response = await fetch('/api/chapter-quiz-results', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        chapter_id: currentChapterId,
                        enrollment_id: enrollmentId,
                        total_questions: results.length,
                        correct_answers: correctCount,
                        wrong_answers: wrongCount,
                        percentage: percentage,
                        answers: results
                    })
                });
                quizSaveResponse = await response.json();
                console.log('Quiz results saved:', quizSaveResponse);
                
                // Handle Delaware quiz rotation - show results first, then switch to Quiz Set 2
                if (quizSaveResponse.delaware_quiz_rotation && quizSaveResponse.switch_to_quiz_set === 2) {
                    showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse, true);
                    return;
                }
                
            } catch (error) {
                console.error('Error saving quiz results:', error);
            }
            
            // Show results popup
            showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse);
        }
        
        function displayQuestions() {
            console.log('🔧 displayQuestions v2.0 - Duplicate letter fix applied');
            const container = document.getElementById('quiz-section');
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chapter Quiz - ${currentQuestions.length} Questions</h5>
                        <div class="d-flex align-items-center gap-3">
                            <button onclick="hideQuiz()" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left"></i> Back to Content
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Answer all questions and click "Submit Quiz" to complete this chapter.
                        </div>
                        <form id="quiz-form">
                            ${currentQuestions.map((q, index) => {
                                let options = parseOptions(q.options);
                                console.log(`Question ${index + 1} options BEFORE cleaning:`, options);
                                return `
                                    <div class="mb-4 question-item" data-question-id="${q.id}" data-correct="${q.correct_answer}">
                                        <h6>${index + 1}. ${q.question_text}</h6>
                                        ${options.map((opt, optIndex) => {
                                            // Clean the option text - remove any existing letter prefix (A., B., etc.)
                                            const cleanOpt = opt.toString().replace(/^[A-E]\.\s*/i, '').trim();
                                            const letter = String.fromCharCode(65 + optIndex); // A, B, C, D, E
                                            console.log(`  Option ${letter}: "${opt}" → "${cleanOpt}"`);
                                            
                                            return `
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question_${q.id}" id="q${q.id}_opt${optIndex}" value="${letter}">
                                                <label class="form-check-label" for="q${q.id}_opt${optIndex}">
                                                    ${letter}. ${cleanOpt}
                                                </label>
                                            </div>
                                        `}).join('')}
                                    </div>
                                `;
                            }).join('')}
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-course-action btn-lg" onclick="submitQuiz()" style>
                                    <i class="fas fa-check"></i> Submit Quiz
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
        }
        
        function hideQuiz() {
            // Stop the quiz timer (only if running for non-admin users)
            if (!isAdmin) {
                stopQuizTimer();
            }
            
            // Show content and hide quiz
            document.querySelector('.chapter-text').style.display = 'block';
            document.querySelector('#action-button-container').style.display = 'block';
            document.querySelector('#quiz-section').style.display = 'none';
        }
        
        async function submitQuiz() {
            // Stop the quiz timer
            stopQuizTimer();
            
            // Check if all questions are answered
            const unanswered = currentQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions before submitting. ${unanswered.length} question(s) remaining.`);
                // Restart the timer if user wants to continue
                startQuizTimer();
                return;
            }
            
            // Collect answers
            const results = currentQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                const userAnswer = selected ? selected.value : null;
                const isCorrect = answersMatch(userAnswer, q.correct_answer, q.options);
                
                return {
                    question_id: q.id,
                    question_text: q.question_text,
                    user_answer: userAnswer,
                    correct_answer: q.correct_answer,
                    is_correct: isCorrect,
                    explanation: q.explanation || ''
                };
            });
            
            const correctCount = results.filter(r => r.is_correct).length;
            const wrongCount = results.length - correctCount;
            const percentage = ((correctCount / results.length) * 100).toFixed(2);
            
            // Save results to database
            let quizSaveResponse = null;
            try {
                const response = await fetch('/api/chapter-quiz-results', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        chapter_id: currentChapterId,
                        enrollment_id: enrollmentId,
                        total_questions: results.length,
                        correct_answers: correctCount,
                        wrong_answers: wrongCount,
                        percentage: percentage,
                        answers: results
                    })
                });
                quizSaveResponse = await response.json();
                console.log('Quiz results saved:', quizSaveResponse);
                
                // Handle Delaware quiz rotation - show results first, then switch to Quiz Set 2
                if (quizSaveResponse.delaware_quiz_rotation && quizSaveResponse.switch_to_quiz_set === 2) {
                    showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse, true);
                    return;
                }
                
            } catch (error) {
                console.error('Error saving quiz results:', error);
            }
            
            // Show results popup
            showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse);
        }
        
        function hideQuiz() {
            // Stop the quiz timer (only if running for non-admin users)
            if (!isAdmin) {
                stopQuizTimer();
            }
            
            // Show content and hide quiz
            document.querySelector('.chapter-text').style.display = 'block';
            document.querySelector('#action-button-container').style.display = 'block';
            document.querySelector('#quiz-section').style.display = 'none';
        }
        
        async function checkExistingQuizResult(chapterId) {
            try {
                const response = await fetch(`/api/chapters/${chapterId}/quiz-result`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.quiz_result) {
                        // User has already taken this quiz, show the result
                        const actionContainer = document.getElementById('action-button-container');
                        actionContainer.innerHTML = `
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle"></i> You've already completed this chapter quiz with a score of <strong>${result.quiz_result.percentage}%</strong>
                            </div>
                            <button onclick="showQuiz()" class="btn btn-warning btn-lg me-3">
                                <i class="fas fa-redo"></i> Retake Quiz
                            </button>
                            <button onclick="completeChapter()" class="btn btn-success btn-lg">
                                <i class="fas fa-arrow-right"></i> Continue to Next Chapter
                            </button>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error checking quiz result:', error);
            }
        }
        
        async function submitQuizAndComplete() {
            const form = document.getElementById('quiz-form');
            if (!form) {
                completeChapter();
                return;
            }
            
            // Check if all questions are answered
            const unanswered = currentQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions before completing. ${unanswered.length} question(s) remaining.`);
                return;
            }
            
            // Collect answers
            const results = currentQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                const userAnswer = selected ? selected.value : null;
                const isCorrect = answersMatch(userAnswer, q.correct_answer, q.options);
                
                return {
                    question_id: q.id,
                    question_text: q.question_text,
                    user_answer: userAnswer,
                    correct_answer: q.correct_answer,
                    is_correct: isCorrect,
                    explanation: q.explanation || ''
                };
            });
            
            const correctCount = results.filter(r => r.is_correct).length;
            const wrongCount = results.length - correctCount;
            const percentage = ((correctCount / results.length) * 100).toFixed(2);
            
            // Save results to database
            let quizSaveResponse = null;
            try {
                const response = await fetch('/api/chapter-quiz-results', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        chapter_id: currentChapterId,
                        enrollment_id: enrollmentId,
                        total_questions: results.length,
                        correct_answers: correctCount,
                        wrong_answers: wrongCount,
                        percentage: percentage,
                        answers: results
                    })
                });
                quizSaveResponse = await response.json();
                console.log('Quiz results saved:', quizSaveResponse);
                
                // Handle Delaware quiz rotation - show results first, then switch to Quiz Set 2
                if (quizSaveResponse.delaware_quiz_rotation && quizSaveResponse.switch_to_quiz_set === 2) {
                    showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse, true);
                    return;
                }
                
            } catch (error) {
                console.error('Error saving quiz results:', error);
            }
            
            // Show results popup
            showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse);
        }
        
        function showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse, isDelawareRetry = false) {
            // Check if quiz was passed (80% or higher)
            const passed = percentage >= 80;
            
            // Store quiz result for later reference
            window.lastQuizResult = {
                passed: passed,
                percentage: percentage,
                correctCount: correctCount,
                totalQuestions: results.length
            };
            
            // Check if there's a next chapter
            const currentIndex = chapters.findIndex(c => c.id === currentChapterId);
            const hasNextChapter = currentIndex !== -1 && currentIndex < chapters.length - 1;
            
            // Determine button text and action based on quiz result and chapter position
            let buttonText, buttonIcon, buttonAction;
            
            // Delaware retry - special case
            if (isDelawareRetry) {
                buttonText = 'Try Quiz Set 2';
                buttonIcon = 'fas fa-redo';
                buttonAction = 'retryDelawareQuiz()';
            } else if (passed) {
                // Quiz passed - allow progression
                if (hasNextChapter) {
                    buttonText = 'Continue to Next Chapter';
                    buttonIcon = 'fas fa-arrow-right';
                    buttonAction = 'closeQuizResults()';
                } else {
                    buttonText = 'Complete Course';
                    buttonIcon = 'fas fa-trophy';
                    buttonAction = 'closeQuizResults()';
                }
            } else {
                // Quiz failed - only allow retake
                buttonText = 'Retake Quiz';
                buttonIcon = 'fas fa-redo';
                buttonAction = 'retakeQuiz()';
            }
            
            // Get quiz average if available
            const quizAverage = quizSaveResponse && quizSaveResponse.quiz_average ? quizSaveResponse.quiz_average : null;
            
            const modalHtml = `
                <div class="modal fade show" id="quizResultsModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Quiz Results</h5>
                                <button type="button" class="btn-close" onclick="closeQuizResults()"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="card text-center border-success">
                                            <div class="card-body">
                                                <h2 class="text-success">${correctCount}</h2>
                                                <p class="mb-0">Correct</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-danger">
                                            <div class="card-body">
                                                <h2 class="text-danger">${wrongCount}</h2>
                                                <p class="mb-0">Wrong</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-primary">
                                            <div class="card-body">
                                                <h2 class="text-primary">${percentage}%</h2>
                                                <p class="mb-0">Chapter Score</p>
                                            </div>
                                        </div>
                                    </div>
                                    ${quizAverage ? `
                                        <div class="col-md-3">
                                            <div class="card text-center border-info">
                                                <div class="card-body">
                                                    <h2 class="text-info">${quizAverage}%</h2>
                                                    <p class="mb-0">Course Average</p>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                ${quizAverage ? `
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-chart-line"></i> Your overall course quiz average is <strong>${quizAverage}%</strong>
                                    </div>
                                ` : ''}
                                
                                <h6 class="mb-3">Detailed Results:</h6>
                                ${results.map((r, i) => `
                                    <div class="card mb-3 ${r.is_correct ? 'border-success' : 'border-danger'}">
                                        <div class="card-body">
                                            <h6 class="card-title">${i + 1}. ${r.question_text}</h6>
                                            <p class="mb-2">
                                                <strong>Your Answer:</strong> 
                                                <span class="${r.is_correct ? 'text-success' : 'text-danger'}">${getAnswerDisplayText(r.user_answer, r.options)}</span>
                                                ${r.is_correct ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'}
                                            </p>
                                            ${!r.is_correct ? `
                                                <p class="mb-2">
                                                    <strong>Correct Answer:</strong> 
                                                    <span class="text-success">${getAnswerDisplayText(r.correct_answer, r.options)}</span>
                                                </p>
                                            ` : ''}
                                            ${r.explanation ? `
                                                <div class="alert alert-info mt-2 mb-0">
                                                    <strong>Explanation:</strong> ${r.explanation}
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                `).join('')}
                                
                                ${passed ? 
                                    '<div class="alert alert-success mt-4"><h5>🎉 Congratulations!</h5><p>You passed the quiz! You can now proceed to the next chapter.</p></div>' :
                                    '<div class="alert alert-danger mt-4"><h5>Quiz Not Passed</h5><p>You need 80% or higher to pass this chapter quiz. Please review the chapter content and retake the quiz to continue.</p><p><strong>Note:</strong> You must pass this quiz before proceeding to the next chapter.</p></div>'
                                }
                            </div>
                            <div class="modal-footer">
                                ${isDelawareRetry ? `<div class="alert alert-warning mb-2 w-100"><i class="fas fa-exclamation-triangle"></i> You did not pass Quiz Set 1. You will now attempt Quiz Set 2.</div>` : ''}
                                <button type="button" class="btn btn-primary btn-lg" onclick="${buttonAction}">
                                    <i class="${buttonIcon}"></i> ${buttonText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Don't auto-complete chapter here - let closeQuizResults handle it
        }
        
        // Delaware quiz retry function
        async function retryDelawareQuiz() {
            // Close the results modal
            const modal = document.getElementById('quizResultsModal');
            if (modal) modal.remove();
            
            // Reload questions for Quiz Set 2
            await loadChapterQuestions(currentChapterId);
            
            // Reset quiz form and show it again
            document.getElementById('quiz-form').reset();
            showQuiz();
        }
        
        function closeQuizResults() {
            const modal = document.getElementById('quizResultsModal');
            if (modal) {
                modal.remove();
            }
            
            // Get the quiz result from the last quiz attempt
            const lastQuizResult = window.lastQuizResult;
            
            // Only complete chapter and proceed if quiz was passed
            if (lastQuizResult && lastQuizResult.passed) {
                completeChapter().then(() => {
                    // Find current chapter index and load next chapter
                    const currentIndex = chapters.findIndex(c => c.id === currentChapterId);
                    
                    if (currentIndex !== -1 && currentIndex < chapters.length - 1) {
                        const nextChapter = chapters[currentIndex + 1];
                        // Small delay to ensure chapter completion is processed
                        setTimeout(() => {
                            selectChapter(nextChapter.id);
                        }, 500);
                    } else {
                        // This was the last chapter
                        alert('🎉 Congratulations! You have completed all chapters in this course!');
                    }
                }).catch(error => {
                    console.error('Error completing chapter:', error);
                    alert('There was an error completing the chapter. Please try again.');
                });
            } else {
                // Quiz was failed - don't complete chapter, just close modal
                console.log('Quiz not passed - chapter not completed');
            }
        }
        
        function retakeQuiz() {
            // Close the results modal
            const modal = document.getElementById('quizResultsModal');
            if (modal) {
                modal.remove();
            }
            
            // Clear previous quiz result
            window.lastQuizResult = null;
            
            // Show the quiz again
            showQuiz();
        }
        
        function checkAnswer(questionId) {
            const question = currentQuestions.find(q => q.id === questionId);
            const selected = document.querySelector(`input[name="question_${questionId}"]:checked`);
            const resultDiv = document.getElementById(`result_${questionId}`);
            
            if (!selected) {
                resultDiv.innerHTML = '<span class="text-warning">Please select an answer</span>';
                return;
            }
            
            const userAnswer = selected.value;
            const isCorrect = answersMatch(userAnswer, question.correct_answer, question.options);
            
            questionAttempts[questionId]++;
            
            if (isCorrect) {
                resultDiv.innerHTML = `<span class="text-success">✓ Correct!</span>`;
                document.getElementById(`checkBtn_${questionId}`).style.display = 'none';
                document.querySelectorAll(`input[name="question_${questionId}"]`).forEach(input => {
                    input.disabled = true;
                });
            } else {
                resultDiv.innerHTML = `<span class="text-danger">✗ Incorrect. Try again!</span>`;
                
                if (questionAttempts[questionId] >= 2) {
                    document.getElementById(`showBtn_${questionId}`).style.display = 'inline-block';
                }
            }
        }
        
        function showAnswer(questionId) {
            const question = currentQuestions.find(q => q.id === questionId);
            const resultDiv = document.getElementById(`result_${questionId}`);
            
            resultDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>Correct Answer:</strong> ${question.correct_answer}<br>
                    ${question.explanation ? `<strong>Explanation:</strong> ${question.explanation}` : ''}
                </div>
            `;
            
            document.getElementById(`checkBtn_${questionId}`).style.display = 'none';
            document.getElementById(`showBtn_${questionId}`).style.display = 'none';
            document.querySelectorAll(`input[name="question_${questionId}"]`).forEach(input => {
                input.disabled = true;
            });
        }
        
        function restartQuiz() {
            currentQuestions.forEach(q => {
                questionAttempts[q.id] = 0;
                document.querySelectorAll(`input[name="question_${q.id}"]`).forEach(input => {
                    input.checked = false;
                    input.disabled = false;
                });
                document.getElementById(`result_${q.id}`).innerHTML = '';
                document.getElementById(`checkBtn_${q.id}`).style.display = 'inline-block';
                document.getElementById(`showBtn_${q.id}`).style.display = 'none';
            });
        }
        
        async function checkChapterTimer(chapterId) {
            // Skip timer for admin users
            if (isAdmin) {
                console.log('👨‍💼 Admin user - skipping chapter timer check');
                return { success: true, timer_required: false, admin_bypass: true };
            }
            
            try {
                console.log('🔒 Starting strict timer for chapter:', chapterId);
                
                if (!window.strictTimer) {
                    console.warn('⚠️ StrictTimer not initialized, attempting to initialize...');
                    if (typeof StrictTimer !== 'undefined') {
                        window.strictTimer = new StrictTimer();
                        console.log('✅ StrictTimer initialized in checkChapterTimer');
                    } else {
                        console.error('❌ StrictTimer class not available!');
                        hideTimerDisplay();
                        return { success: false, error: 'StrictTimer not initialized' };
                    }
                }
                
                // Get chapter duration
                const chapter = chapters.find(c => String(c.id) === String(chapterId));
                const chapterDuration = chapter ? chapter.duration : null;
                const enforceMinimumTime = chapter ? chapter.enforce_minimum_time : false;
                const requiredMinTime = chapter ? chapter.required_min_time : null;
                
                console.log('📖 Chapter duration:', chapterDuration, 'minutes');
                console.log('🔒 Enforce minimum time:', enforceMinimumTime, 'Required min time:', requiredMinTime);
                
                // Pass enrollment ID, chapter duration, and enforce flag to the timer
                const result = await window.strictTimer.startTimer(chapterId, enrollmentId, chapterDuration, enforceMinimumTime, requiredMinTime);
                
                if (result.timer_required) {
                    console.log('✅ Strict timer activated');
                    // Timer display is handled by the StrictTimer class
                } else {
                    console.log('ℹ️ No timer required for this chapter');
                    hideTimerDisplay();
                }
                
                return result;
            } catch (error) {
                console.error('❌ Error starting strict timer:', error);
                hideTimerDisplay();
                return { success: false, error: error.message };
            }
        }
        
        function showTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            if (timerDisplay) {
                timerDisplay.style.display = 'block';
                document.getElementById('required-time').textContent = Math.floor(timerRequired / 60);
                updateTimerDisplay();
            }
        }
        
        function hideTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            if (timerDisplay) {
                timerDisplay.style.display = 'none';
            }
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }
        
        function startQuizTimer() {
            // Quiz timer implementation
            if (quizTimerInterval) {
                clearInterval(quizTimerInterval);
            }
            quizTimerInterval = setInterval(() => {
                // Timer logic if needed
            }, 1000);
        }
        
        function stopQuizTimer() {
            if (quizTimerInterval) {
                clearInterval(quizTimerInterval);
                quizTimerInterval = null;
            }
        }
        
        function handleChapterBreak(breakChapter) {
            console.log('⏸️ Handling chapter break:', breakChapter);
            
            const titleEl = document.getElementById('chapter-title');
            const contentEl = document.getElementById('chapter-content');
            const actionEl = document.getElementById('action-button-container');
            
            if (!titleEl || !contentEl || !actionEl) {
                console.error('Required elements not found');
                return;
            }
            
            titleEl.textContent = breakChapter.title;
            
            let timeRemaining = breakChapter.duration * 60; // Convert to seconds
            
            const updateTimer = () => {
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                const timerEl = document.getElementById('break-timer');
                if (timerEl) {
                    timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            };
            
            contentEl.innerHTML = `
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-pause-circle"></i> Break Time</h4>
                    <p>${breakChapter.content || 'Time for a break!'}</p>
                    <hr>
                    <p class="mb-2"><strong>Break Duration:</strong> <span id="break-timer">${breakChapter.duration}:00</span></p>
                    ${breakChapter.is_mandatory ? '<p class="mb-0 text-danger"><i class="fas fa-lock"></i> This is a mandatory break</p>' : ''}
                </div>
            `;
            
            const resumeBtn = document.createElement('button');
            resumeBtn.className = 'btn btn-primary btn-lg';
            resumeBtn.id = 'resume-btn';
            resumeBtn.innerHTML = '<i class="fas fa-play"></i> Resume Course';
            resumeBtn.onclick = () => resumeAfterBreak();
            resumeBtn.disabled = breakChapter.is_mandatory;
            
            actionEl.innerHTML = '';
            actionEl.appendChild(resumeBtn);
            
            // Hide quiz section if visible
            const quizSection = document.getElementById('quiz-section');
            if (quizSection) {
                quizSection.style.display = 'none';
            }
            
            // Start break timer - auto-advance for all breaks
            updateTimer();
            const breakInterval = setInterval(() => {
                timeRemaining--;
                updateTimer();
                
                if (timeRemaining <= 0) {
                    clearInterval(breakInterval);
                    resumeBtn.disabled = false;
                    resumeBtn.innerHTML = '<i class="fas fa-play"></i> Resume Course (Ready!)';
                    // Auto-advance after break ends
                    setTimeout(() => resumeAfterBreak(), 1000);
                }
            }, 1000);
        }
        
        function resumeAfterBreak() {
            console.log('▶️ Resuming after break, current chapter ID:', currentChapterId);
            
            // Mark break as completed
            const currentBreak = chapters.find(c => String(c.id) === String(currentChapterId));
            if (currentBreak && currentBreak.chapter_type === 'chapter_break') {
                fetch(`/student/break/${currentBreak.break_id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).catch(err => console.error('Error marking break as completed:', err));
            }
            
            // Find current break chapter index
            const currentIndex = chapters.findIndex(c => String(c.id) === String(currentChapterId));
            console.log('Current index:', currentIndex, 'Total chapters:', chapters.length);
            
            if (currentIndex >= 0 && currentIndex < chapters.length - 1) {
                const nextChapter = chapters[currentIndex + 1];
                console.log('Moving to next chapter:', nextChapter.id, nextChapter.title);
                selectChapter(nextChapter.id);
            } else {
                console.warn('Cannot find next chapter');
            }
        }
        
        function startTimerCountdown() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            timerStartTime = Date.now();
            timerRunning = true;
            
            timerInterval = setInterval(() => {
                timerElapsed = Math.floor((Date.now() - timerStartTime) / 1000);
                timeRemaining = Math.max(0, timerRequired - timerElapsed);
                updateTimerDisplay();
                
                // Check if timer is complete
                if (timerElapsed >= timerRequired) {
                    console.log('🎯 Timer completed!', {
                        timerElapsed,
                        timerRequired,
                        updateActionButtonsExists: typeof updateActionButtons === 'function',
                        strictTimerExists: !!window.strictTimer,
                        strictTimerComplete: window.strictTimer?.isTimerComplete()
                    });
                    
                    timerRunning = false;
                    document.getElementById('timer-status').textContent = 'Complete';
                    document.getElementById('timer-status').classList.remove('bg-warning');
                    document.getElementById('timer-status').classList.add('bg-success');
                    
                    // Update button to enable it now that timer is complete
                    updateActionButtons();
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            
            // Show timer display if strict duration is enabled
            if (strictDurationEnabled) {
                timerDisplay.style.display = 'block';
            }
            
            const minutes = Math.floor(timerElapsed / 60);
            const seconds = timerElapsed % 60;
            const timeText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            document.getElementById('timer-text').textContent = timeText;
            
            // Update progress bar
            const progress = Math.min((timerElapsed / timerRequired) * 100, 100);
            document.getElementById('timer-progress').style.width = progress + '%';
            
            // Update status
            if (timerElapsed >= timerRequired) {
                document.getElementById('timer-status').textContent = 'Complete';
                document.getElementById('timer-status').classList.remove('bg-warning');
                document.getElementById('timer-status').classList.add('bg-success');
            } else {
                document.getElementById('timer-status').textContent = 'In Progress';
                document.getElementById('timer-status').classList.remove('bg-success');
                document.getElementById('timer-status').classList.add('bg-warning');
            }
        }
        
        // Show fallback and load course data if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app course-player');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadCourseData();
            }
        }, 1000);
        
        // Old timer function - now handled by checkChapterTimer
        async function startChapterTimer(chapterId) {
            // This is now handled by checkChapterTimer in selectChapter
            console.log('Timer check for chapter:', chapterId);
        }
        
        // Final Exam Functions
        let finalExamQuestions = [];
        let finalExamAttempts = 0;
        let maxFinalExamAttempts = 2;
        
        async function loadFinalExam() {
            try {
                console.log('🔍 Loading final exam for enrollment:', enrollmentId);
                
                // Check if course is already completed
                if (currentEnrollment && currentEnrollment.completed_at) {
                    console.log('✅ Course is already completed, showing completion message');
                    showCourseCompleted();
                    return;
                }
                
                // Check if enrollment status is completed
                if (currentEnrollment && currentEnrollment.status === 'completed') {
                    console.log('✅ Enrollment status is completed, showing completion message');
                    showCourseCompleted();
                    return;
                }
                
                // Check for existing final exam result first
                const resultResponse = await fetch(`/api/final-exam/result/${enrollmentId}`);
                console.log('📊 Result response status:', resultResponse.status);
                
                const resultData = await resultResponse.json();
                console.log('📊 Result data:', resultData);
                
                if (resultData.result && resultData.result.passed) {
                    // Student has already passed, show results instead of exam
                    console.log('✅ Student has passed, showing success screen');
                    showFinalExamPassed(resultData.result);
                    return;
                }
                
                // Check previous attempts
                const attemptsResponse = await fetch(`/api/final-exam/attempts/${enrollmentId}`);
                console.log('🔢 Attempts response status:', attemptsResponse.status);
                
                const attemptsData = await attemptsResponse.json();
                console.log('🔢 Attempts data:', attemptsData);
                
                finalExamAttempts = attemptsData.attempts || 0;
                maxFinalExamAttempts = attemptsData.max_attempts || 2;
                
                // If student has existing result but didn't pass, show result with retry option
                if (resultData.result && !resultData.result.passed) {
                    console.log('⚠️ Student has failed result, showing retry screen');
                    showFinalExamFailed(resultData.result, finalExamAttempts, maxFinalExamAttempts);
                    return;
                }
                
                // Show final exam interface for first attempt
                console.log('🆕 No existing results, showing fresh exam interface');
                document.getElementById('chapter-content').innerHTML = `
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Final Exam</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5>Final Exam Instructions</h5>
                                <ul>
                                    <li>Randomly selected questions from the course material</li>
                                    <li>You need 80% to pass</li>
                                    <li>Maximum ${maxFinalExamAttempts} attempts allowed</li>
                                    <li>Different questions on retry</li>
                                </ul>
                                ${finalExamAttempts > 0 ? `<p><strong>Attempts used: ${finalExamAttempts}/${maxFinalExamAttempts}</strong></p>` : ''}
                            </div>
                            
                            ${finalExamAttempts >= maxFinalExamAttempts ? 
                                '<div class="alert alert-danger">You have used all attempts for the final exam. Contact support if you need additional attempts.</div>' :
                                '<button class="btn btn-primary btn-lg" onclick="startFinalExam()">Start Final Exam</button>'
                            }
                        </div>
                    </div>
                `;
                
                // Update chapter title
                document.getElementById('chapter-title').textContent = 'Final Exam';
                
            } catch (error) {
                console.error('❌ Error loading final exam:', error);
                document.getElementById('chapter-content').innerHTML = '<div class="alert alert-danger">Error loading final exam</div>';
            }
        }
        
        function showCourseCompleted() {
            document.getElementById('chapter-content').innerHTML = `
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Course Completed</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-trophy" style="font-size: 4rem; color: #28a745;"></i>
                        </div>
                        <h3 class="mb-3">Congratulations!</h3>
                        <p class="lead mb-4">You have successfully completed this course.</p>
                        
                        <div class="alert alert-info">
                            <strong>Course Status:</strong> <span class="badge bg-success">Completed</span>
                        </div>
                        
                        <div class="mt-4">
                            <a href="/generate-certificates" class="btn btn-primary btn-lg">
                                <i class="fas fa-certificate me-2"></i>Generate Certificate
                            </a>
                            <a href="/dashboard" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-home me-2"></i>Return to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // Update chapter title
            document.getElementById('chapter-title').textContent = 'Course Completed';
        }
        
        function showFinalExamPassed(result) {
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Final Exam - PASSED</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-trophy me-2"></i>Congratulations!</h5>
                            <p class="mb-0">You have successfully passed the final exam with a score of <strong>${result.score}%</strong></p>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Your Score</h6>
                                        <h3 class="text-success">${result.score}%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Status</h6>
                                        <h3 class="text-success">PASSED</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="/generate-certificates" class="btn btn-primary btn-lg">
                                <i class="fas fa-certificate me-2"></i>Generate Certificate
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // Update chapter title
            document.getElementById('chapter-title').textContent = 'Final Exam - Completed';
        }
        
        function showFinalExamFailed(result, attempts, maxAttempts) {
            const canRetry = attempts < maxAttempts;
            
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Final Exam - Previous Result</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="alert alert-warning">
                            <h5>Previous Attempt Result</h5>
                            <p class="mb-0">Your last attempt scored <strong>${result.score}%</strong> - You need 80% to pass</p>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Your Score</h6>
                                        <h3 class="text-warning">${result.score}%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Attempts Used</h6>
                                        <h3 class="text-info">${attempts}/${maxAttempts}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            ${canRetry ? 
                                `<button class="btn btn-primary btn-lg" onclick="startFinalExam()">
                                    <i class="fas fa-redo me-2"></i>Retake Final Exam
                                </button>` :
                                `<div class="alert alert-danger">
                                    You have used all ${maxAttempts} attempts. Contact support for additional attempts.
                                </div>`
                            }
                        </div>
                    </div>
                </div>
            `;
            
            // Update chapter title
            document.getElementById('chapter-title').textContent = 'Final Exam - Previous Result';
        }
        
        async function startFinalExam() {
            try {
                // Get enrollment ID from current enrollment
                const enrollmentId = currentEnrollment ? currentEnrollment.id : null;
                
                // First, check how many questions are available
                const checkResponse = await fetch(`/api/final-exam/count?enrollment_id=${enrollmentId}`);
                const countData = await checkResponse.json();
                const availableQuestions = countData.count || 0;
                
                console.log(`Available final exam questions: ${availableQuestions}`);
                
                // Use all available questions (minimum 1, maximum 25)
                let questionsToRequest = Math.min(25, Math.max(1, availableQuestions));
                
                if (availableQuestions === 0) {
                    alert('No final exam questions are available. Please contact support.');
                    return;
                }
                
                // Get the questions
                const response = await fetch(`/api/final-exam/random/${questionsToRequest}?enrollment_id=${enrollmentId}`);
                finalExamQuestions = await response.json();
                
                if (finalExamQuestions.length === 0) {
                    alert('Unable to load final exam questions. Please contact support.');
                    return;
                }
                
                console.log(`Starting final exam with ${finalExamQuestions.length} questions`);
                displayFinalExam();
                
            } catch (error) {
                console.error('Error starting final exam:', error);
                alert('Error starting final exam');
            }
        }
        
        function displayFinalExam() {
            // Track exam start time
            window.finalExamStartTime = Date.now();
            
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Final Exam - Attempt ${finalExamAttempts + 1}</h4>
                        <small>${finalExamQuestions.length} Questions | 80% Required to Pass</small>
                    </div>
                    <div class="card-body">
                        <form id="final-exam-form">
                            ${finalExamQuestions.map((q, index) => {
                                let options = parseOptions(q.options);
                                return `
                                <div class="mb-4 question-item" data-question-id="${q.id}">
                                    <h6>${index + 1}. ${q.question_text}</h6>
                                    ${options.map((opt, optIndex) => {
                                        const letter = String.fromCharCode(65 + optIndex);
                                        const optText = opt.replace(/^[A-E]\.\s*/i, '').trim();
                                        return `
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_${q.id}" id="q${q.id}_${letter}" value="${letter}">
                                            <label class="form-check-label" for="q${q.id}_${letter}">
                                                ${letter}. ${optText}
                                            </label>
                                        </div>
                                    `;}).join('')}
                                </div>
                            `;}).join('')}
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-success btn-lg" onclick="submitFinalExam()">
                                    Submit Final Exam
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }
        
        async function submitFinalExam() {
            // Check if all questions are answered
            const unanswered = finalExamQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions. ${unanswered.length} question(s) remaining.`);
                return;
            }
            
            // Calculate exam duration
            const examStartTime = window.finalExamStartTime || Date.now();
            const examDuration = Math.round((Date.now() - examStartTime) / 60000); // minutes
            
            // Collect answers
            const answers = finalExamQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                const isCorrect = answersMatch(selected.value, q.correct_answer, q.options);
                
                return {
                    question_id: q.id,
                    student_answer: selected.value,
                    correct_answer: q.correct_answer,
                    is_correct: isCorrect,
                    time_spent: Math.round(Math.random() * 60)
                };
            });
            
            const correctCount = answers.filter(a => a.is_correct).length;
            const percentage = Math.round((correctCount / answers.length) * 100);
            const passed = percentage >= 80;
            
            try {
                // First try the new system
                console.log('Attempting new final exam system...');
                const newResponse = await fetch('/final-exam/process-completion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId,
                        exam_answers: answers,
                        exam_duration: examDuration
                    })
                });
                
                // Check if response is JSON
                const contentType = newResponse.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await newResponse.json();
                    
                    // Check for errors (like already passed)
                    if (!newResponse.ok) {
                        if (result.error && result.redirect_url) {
                            console.log('Already completed, redirecting:', result.error);
                            alert(result.error);
                            window.location.href = result.redirect_url;
                            return;
                        }
                        throw new Error(result.error || 'Submission failed');
                    }
                    
                    if (result.success) {
                        console.log('Final exam submitted successfully!', result);
                        console.log('Progress:', result.progress_percentage + '%');
                        console.log('Course completed:', result.course_completed);
                        console.log('Redirecting to:', result.redirect_url);
                        
                        // Small delay to ensure backend processing completes
                        setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 500);
                        return;
                    }
                }
                
                throw new Error('New system returned non-JSON response');
                
            } catch (error) {
                console.log('New system failed, using fallback:', error.message);
                
                // Fallback to old system that we know works
                try {
                    await fetch('/api/final-exam/submit', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            enrollment_id: enrollmentId,
                            answers: answers,
                            score: percentage,
                            passed: passed,
                            attempt: finalExamAttempts + 1
                        })
                    });
                    
                    // Show enhanced results with grading period info
                    showEnhancedFinalExamResults(correctCount, percentage, passed);
                    
                    // Initialize star rating functionality
                    initializeStarRating();
                    
                } catch (fallbackError) {
                    console.error('Both systems failed:', fallbackError);
                    alert('Error submitting final exam. Please contact support.');
                }
            }
        }
        
        // Enhanced results function that shows grading period info
        function showEnhancedFinalExamResults(correctCount, percentage, passed) {
            const resultClass = passed ? 'success' : 'danger';
            const resultText = passed ? 'PASSED' : 'FAILED';
            const gradeLetter = getGradeLetter(percentage);
            
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-${resultClass} text-white">
                        <h4 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Final Exam Results</h4>
                    </div>
                    <div class="card-body">
                        <!-- Grading Period Notice (only show if enforcement is enabled) -->
                        <div class="alert alert-warning mb-4" id="grading-period-notice" style="display: none;">
                            <h5><i class="fas fa-clock me-2"></i>24-Hour Grading Period Active</h5>
                            <p class="mb-0">Your results are under instructor review. Final grades and detailed feedback will be available within 24 hours.</p>
                        </div>
                        
                        <!-- Results Summary -->
                        <div class="row text-center mb-4">
                            <div class="col-md-4">
                                <div class="card border-${resultClass}">
                                    <div class="card-body">
                                        <h2 class="text-${resultClass}">${resultText}</h2>
                                        <p class="mb-0">Overall Status</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h2 class="text-info">${percentage}%</h2>
                                        <p class="mb-0">${correctCount}/${finalExamQuestions.length} Correct</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h2 class="text-secondary">Grade ${gradeLetter}</h2>
                                        <p class="mb-0">Letter Grade</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Score Breakdown -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Score Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6>Chapter Quizzes</h6>
                                            <div class="h4 text-info">30%</div>
                                            <small class="text-muted">Weight in final grade</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6>Free Response</h6>
                                            <div class="h4 text-warning">20%</div>
                                            <small class="text-muted">Weight in final grade</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6>Final Exam</h6>
                                            <div class="h4 text-primary">50%</div>
                                            <small class="text-muted">Weight in final grade</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Next Steps -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>What Happens Next?</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Exam Submitted ✓</h6>
                                            <small class="text-muted">Just completed</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6>Instructor Review</h6>
                                            <small class="text-muted">Within 24 hours</small>
                                            <p class="mb-0">Your instructor will review your free response answers and provide detailed feedback.</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Final Grade & Certificate</h6>
                                            <small class="text-muted">After review completion</small>
                                            <p class="mb-0">You'll receive your final grade and certificate (if passing).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="text-center">
                            ${!passed && finalExamAttempts < maxFinalExamAttempts - 1 ? 
                                '<button class="btn btn-warning me-2" onclick="loadFinalExam()"><i class="fas fa-redo me-2"></i>Try Again</button>' : ''
                            }
                            <button class="btn btn-secondary" onclick="loadCourseData()">
                                <i class="fas fa-arrow-left me-2"></i>Back to Course
                            </button>
                        </div>
                        
                        <!-- Student Feedback Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Course Feedback</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Help us improve by sharing your experience with this course.</p>
                                
                                <form id="student-feedback-form">
                                    <div class="mb-3">
                                        <label class="form-label">Rate your overall experience:</label>
                                        <div class="rating-stars text-center mb-3">
                                            <i class="fas fa-star star" data-rating="1" style="font-size: 2rem; color: #e2e8f0; cursor: pointer; margin: 0 5px;"></i>
                                            <i class="fas fa-star star" data-rating="2" style="font-size: 2rem; color: #e2e8f0; cursor: pointer; margin: 0 5px;"></i>
                                            <i class="fas fa-star star" data-rating="3" style="font-size: 2rem; color: #e2e8f0; cursor: pointer; margin: 0 5px;"></i>
                                            <i class="fas fa-star star" data-rating="4" style="font-size: 2rem; color: #e2e8f0; cursor: pointer; margin: 0 5px;"></i>
                                            <i class="fas fa-star star" data-rating="5" style="font-size: 2rem; color: #e2e8f0; cursor: pointer; margin: 0 5px;"></i>
                                        </div>
                                        <input type="hidden" id="student_rating" name="student_rating" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="student_feedback" class="form-label">Your feedback:</label>
                                        <textarea id="student_feedback" name="student_feedback" rows="4" 
                                                  class="form-control" 
                                                  placeholder="Share your thoughts about the course content, difficulty, and overall experience..."
                                                  required></textarea>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary" onclick="submitStudentFeedback()">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                        </button>
                                    </div>
                                </form>
                                
                                <div id="feedback-success" class="alert alert-success mt-3" style="display: none;">
                                    <i class="fas fa-check-circle me-2"></i>Thank you for your feedback!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                .timeline {
                    position: relative;
                    padding-left: 30px;
                }
                .timeline-item {
                    position: relative;
                    margin-bottom: 20px;
                }
                .timeline-marker {
                    position: absolute;
                    left: -35px;
                    top: 5px;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                }
                .timeline::before {
                    content: '';
                    position: absolute;
                    left: -30px;
                    top: 10px;
                    bottom: 0;
                    width: 2px;
                    background: #dee2e6;
                }
                </style>
            `;
        }
        
        // Helper function to get grade letter
        function getGradeLetter(percentage) {
            if (percentage >= 90) return 'A';
            if (percentage >= 80) return 'B';
            if (percentage >= 70) return 'C';
            if (percentage >= 60) return 'D';
            return 'F';
        }
        
        // Submit student feedback function
        function submitStudentFeedback() {
            const rating = document.getElementById('student_rating').value;
            const feedback = document.getElementById('student_feedback').value;
            
            if (!rating) {
                alert('Please select a star rating.');
                return;
            }
            
            if (!feedback.trim()) {
                alert('Please provide your feedback.');
                return;
            }
            
            // Save feedback locally and show success
            localStorage.setItem('student_feedback_' + enrollmentId, JSON.stringify({
                rating: rating,
                feedback: feedback,
                timestamp: new Date().toISOString()
            }));
            
            // Hide form and show success
            document.getElementById('student-feedback-form').style.display = 'none';
            document.getElementById('feedback-success').style.display = 'block';
        }
        
        // Initialize star rating when results are shown
        function initializeStarRating() {
            setTimeout(() => {
                const stars = document.querySelectorAll('.star');
                const ratingInput = document.getElementById('student_rating');
                
                if (stars.length === 0 || !ratingInput) return;
                
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.getAttribute('data-rating');
                        ratingInput.value = rating;
                        
                        stars.forEach((s, index) => {
                            if (index < rating) {
                                s.style.color = '#ffd700';
                            } else {
                                s.style.color = '#e2e8f0';
                            }
                        });
                    });
                    
                    star.addEventListener('mouseover', function() {
                        const rating = this.getAttribute('data-rating');
                        
                        stars.forEach((s, index) => {
                            if (index < rating) {
                                s.style.color = '#ffd700';
                            } else {
                                s.style.color = '#e2e8f0';
                            }
                        });
                    });
                });
                
                const ratingContainer = document.querySelector('.rating-stars');
                if (ratingContainer) {
                    ratingContainer.addEventListener('mouseleave', function() {
                        const currentRating = ratingInput.value;
                        
                        stars.forEach((s, index) => {
                            if (index < currentRating) {
                                s.style.color = '#ffd700';
                            } else {
                                s.style.color = '#e2e8f0';
                            }
                        });
                    });
                }
            }, 100);
        }
        //         <div class="card">
        //             <div class="card-header bg-${resultClass} text-white">
        //                 <h4 class="mb-0">Final Exam Results</h4>
        //             </div>
        //             <div class="card-body text-center">
        //                 <h2 class="text-${resultClass}">${resultText}</h2>
        //                 <h3>${correctCount}/25 Correct (${percentage}%)</h3>
        //                 
        //                 ${passed ? 
        //                     '<div class="alert alert-success"><h5>🎉 Congratulations!</h5><p>You have successfully completed the course!</p></div>' :
        //                     `<div class="alert alert-danger">
        //                         <h5>You need 80% to pass</h5>
        //                         ${finalExamAttempts < maxFinalExamAttempts - 1 ? 
        //                             '<p>You have more attempts available with different questions.</p><button class="btn btn-warning" onclick="loadFinalExam()">Try Again</button>' :
        //                             '<p>You have used all attempts. Contact support if you need additional attempts.</p>'
        //                         }
        //                     </div>`
        //                 }
        //                 
        //                 <button class="btn btn-secondary mt-3" onclick="loadCourseData()">Back to Course</button>
        //             </div>
        //         </div>
        //     `;
        // }
        
        // Security Verification System
        let securityTimer = null;
        let securityTimeRemaining = 300; // 5 minutes in seconds
        let securityTimerInterval = null;
        let securityStartTime = null;
        let securitySessionId = null;
        let securityVerificationActive = false;
        
        function initializeSecurityVerification() {
            // Show security verification immediately on page load
            setTimeout(() => {
                showSecurityVerification();
            }, 2000); // Small delay to let page load
            
            // No longer schedule random intervals - questions will be triggered after chapter completion
        }
        
        // Remove the random scheduling function - we'll trigger after chapter completion
        function triggerSecurityAfterChapter() {
            // Trigger security verification after chapter completion
            console.log('🔐 triggerSecurityAfterChapter called');
            console.log('📊 Security verification active:', securityVerificationActive);
            console.log('📊 Chapters completed count:', chaptersCompletedCount);
            
            if (!securityVerificationActive) {
                console.log('🔐 Scheduling security verification...');
                setTimeout(() => {
                    console.log('🔐 Calling showSecurityVerification...');
                    showSecurityVerification();
                }, 1000); // Small delay after chapter completion
            } else {
                console.log('⚠️ Security verification already active, skipping');
            }
        }
        
        async function showSecurityVerification() {
            if (securityVerificationActive) {
                return; // Already showing
            }
            
            securityVerificationActive = true;
            
            try {
                // Send chapter completion count to get sequential questions
                const response = await fetch('/api/security/questions', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        chapter_count: chaptersCompletedCount
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load security questions');
                }
                
                const data = await response.json();
                securitySessionId = data.session_id;
                
                console.log(`Showing security questions for chapter completion count: ${chaptersCompletedCount}`);
                displaySecurityQuestions(data.questions);
                
                // Start the 5-minute security timer
                startSecurityTimer();
                
                // Show the modal with fallback
                showModal('securityModal');
                
            } catch (error) {
                console.error('Error loading security questions:', error);
                securityVerificationActive = false;
            }
        }
        
        function showModal(modalId) {
            const modalElement = document.getElementById(modalId);
            
            if (!modalElement) {
                console.error('Modal element not found:', modalId);
                return;
            }
            
            console.log('🔓 Showing modal:', modalId);
            
            // Try Bootstrap modal first with backdrop disabled
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
                    modal.show();
                    console.log('✅ Bootstrap modal shown');
                    return;
                } catch (e) {
                    console.warn('Bootstrap modal failed, using fallback:', e);
                }
            }
            
            // Fallback: manually show modal
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            modalElement.setAttribute('aria-hidden', 'false');
            
            // Add backdrop
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.classList.add('modal-open');
            
            console.log('✅ Fallback modal shown');
        }
        
        function hideModal(modalId) {
            const modalElement = document.getElementById(modalId);
            
            // Try Bootstrap modal first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                        return;
                    }
                } catch (e) {
                    console.warn('Bootstrap modal hide failed, using fallback:', e);
                }
            }
            
            // Fallback: manually hide modal
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true');
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
        
        // Security Timer Functions
        function startSecurityTimer() {
            securityTimeRemaining = 300; // Reset to 5 minutes
            securityStartTime = Date.now();
            
            // Clear any existing timer
            if (securityTimerInterval) {
                clearInterval(securityTimerInterval);
            }
            
            // Start countdown
            securityTimerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - securityStartTime) / 1000);
                securityTimeRemaining = Math.max(0, 300 - elapsed);
                
                updateSecurityTimerDisplay();
                
                // Auto-submit when time runs out
                if (securityTimeRemaining <= 0) {
                    clearInterval(securityTimerInterval);
                    autoSubmitSecurityAnswers();
                }
            }, 1000);
        }
        
        function updateSecurityTimerDisplay() {
            const minutes = Math.floor(securityTimeRemaining / 60);
            const seconds = securityTimeRemaining % 60;
            const timeText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            const timerElement = document.getElementById('security-timer-display');
            if (timerElement) {
                timerElement.textContent = timeText;
                
                // Change color when time is running low
                if (securityTimeRemaining <= 60) {
                    timerElement.classList.add('text-danger');
                    timerElement.classList.remove('text-warning');
                } else if (securityTimeRemaining <= 120) {
                    timerElement.classList.add('text-warning');
                    timerElement.classList.remove('text-danger');
                } else {
                    timerElement.classList.remove('text-danger', 'text-warning');
                }
            }
        }
        
        function stopSecurityTimer() {
            if (securityTimerInterval) {
                clearInterval(securityTimerInterval);
                securityTimerInterval = null;
            }
        }
        
        async function autoSubmitSecurityAnswers() {
            alert('⏰ Time\'s up! Your security verification will be automatically submitted.');
            await submitSecurityAnswers();
        }
        
        function displaySecurityQuestions(questions) {
            const container = document.getElementById('securityQuestions');
            let html = '<form id="securityForm">';
            
            questions.forEach((question, index) => {
                html += `
                    <div class="mb-3">
                        <label for="answer_${question.id}" class="form-label">
                            <strong>Question ${index + 1}:</strong> ${question.question}
                        </label>
                        <input type="text" class="form-control" id="answer_${question.id}" name="${question.id}" required>
                    </div>
                `;
            });
            
            html += '</form>';
            container.innerHTML = html;
            
            // Clear any previous errors
            document.getElementById('securityErrors').classList.add('d-none');
        }
        
        async function submitSecurityAnswers() {
            // Stop the security timer
            stopSecurityTimer();
            
            const form = document.getElementById('securityForm');
            const formData = new FormData(form);
            const answers = {};
            
            for (let [key, value] of formData.entries()) {
                answers[key] = value.trim();
            }
            
            try {
                const response = await fetch('/api/security/verify', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ answers: answers })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // All answers correct - close modal and continue
                    hideModal('securityModal');
                    securityVerificationActive = false;
                    
                    // Show success message briefly
                    showNotification('Security verification successful!', 'success');
                } else {
                    // Show errors
                    displaySecurityErrors(data.errors);
                }
                
            } catch (error) {
                console.error('Error verifying security answers:', error);
                showNotification('Error verifying answers. Please try again.', 'error');
            }
        }
        
        function displaySecurityErrors(errors) {
            const errorContainer = document.getElementById('securityErrors');
            let html = '<strong>Please correct the following:</strong><ul>';
            
            errors.forEach(error => {
                html += `<li>${error.message}</li>`;
            });
            
            html += '</ul>';
            errorContainer.innerHTML = html;
            errorContainer.classList.remove('d-none');
        }
        
        function showNotification(message, type = 'info') {
            // Create a temporary notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // ========================================
        // MOBILE OPTIMIZATION JAVASCRIPT
        // ========================================
        
        // Mobile Navigation
        function initializeMobileNavigation() {
            // Create mobile overlay if it doesn't exist
            if (!document.querySelector('.mobile-menu-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'mobile-menu-overlay';
                overlay.onclick = closeMobileMenu;
                document.body.appendChild(overlay);
            }
            
            // Ensure mobile nav toggle exists and is functional
            const mobileToggle = document.getElementById('mobile-nav-toggle');
            if (mobileToggle) {
                mobileToggle.onclick = toggleMobileMenu;
            }
            
            // Add close button functionality to existing sidebar
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                // Ensure mobile header exists
                let mobileHeader = sidebar.querySelector('.mobile-sidebar-header');
                if (!mobileHeader) {
                    mobileHeader = document.createElement('div');
                    mobileHeader.className = 'mobile-sidebar-header d-lg-none';
                    mobileHeader.innerHTML = `
                        <h5 class="mb-0">Navigation</h5>
                        <button class="mobile-sidebar-close" onclick="closeMobileMenu()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    sidebar.insertBefore(mobileHeader, sidebar.firstChild);
                }
            }
        }
        
        function toggleMobileMenu() {
            // Check if we have the existing sidebar from navbar component
            const existingSidebar = document.querySelector('.sidebar, .offcanvas, nav[class*="sidebar"]');
            
            if (existingSidebar) {
                // Work with existing sidebar
                existingSidebar.classList.toggle('mobile-open');
                
                // Create or toggle overlay
                let overlay = document.querySelector('.mobile-menu-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'mobile-menu-overlay';
                    overlay.onclick = closeMobileMenu;
                    document.body.appendChild(overlay);
                }
                
                overlay.classList.toggle('active');
                
                // Prevent body scroll when menu is open
                document.body.style.overflow = existingSidebar.classList.contains('mobile-open') ? 'hidden' : '';
            } else {
                // Fallback to creating mobile menu
                const overlay = document.querySelector('.mobile-menu-overlay');
                const menu = document.querySelector('.mobile-menu');
                
                if (overlay && menu) {
                    overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
                    menu.classList.toggle('active');
                    
                    // Prevent body scroll when menu is open
                    document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
                }
            }
        }
        
        function closeMobileMenu() {
            // Close existing sidebar
            const existingSidebar = document.querySelector('.sidebar, .offcanvas, nav[class*="sidebar"]');
            if (existingSidebar) {
                existingSidebar.classList.remove('mobile-open');
            }
            
            // Close overlay
            const overlay = document.querySelector('.mobile-menu-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                overlay.style.display = 'none';
            }
            
            // Close fallback menu
            const menu = document.querySelector('.mobile-menu');
            if (menu) {
                menu.classList.remove('active');
            }
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
        
        // Touch Gesture Support
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;
        
        function initializeTouchGestures() {
            const contentArea = document.getElementById('chapter-content');
            if (!contentArea) return;
            
            contentArea.classList.add('swipe-container');
            
            // Add swipe indicators
            const leftIndicator = document.createElement('div');
            leftIndicator.className = 'swipe-indicator left';
            leftIndicator.innerHTML = '<i class="fas fa-chevron-left"></i>';
            
            const rightIndicator = document.createElement('div');
            rightIndicator.className = 'swipe-indicator right';
            rightIndicator.innerHTML = '<i class="fas fa-chevron-right"></i>';
            
            contentArea.appendChild(leftIndicator);
            contentArea.appendChild(rightIndicator);
            
            // Touch event listeners
            contentArea.addEventListener('touchstart', handleTouchStart, { passive: true });
            contentArea.addEventListener('touchmove', handleTouchMove, { passive: true });
            contentArea.addEventListener('touchend', handleTouchEnd, { passive: true });
        }
        
        function handleTouchStart(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }
        
        function handleTouchMove(e) {
            if (!touchStartX || !touchStartY) return;
            
            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            
            const deltaX = currentX - touchStartX;
            const deltaY = currentY - touchStartY;
            
            // Show swipe indicators
            const leftIndicator = document.querySelector('.swipe-indicator.left');
            const rightIndicator = document.querySelector('.swipe-indicator.right');
            
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 30) {
                if (deltaX > 0 && leftIndicator) {
                    leftIndicator.classList.add('active');
                    rightIndicator.classList.remove('active');
                } else if (deltaX < 0 && rightIndicator) {
                    rightIndicator.classList.add('active');
                    leftIndicator.classList.remove('active');
                }
            }
        }
        
        function handleTouchEnd(e) {
            touchEndX = e.changedTouches[0].clientX;
            touchEndY = e.changedTouches[0].clientY;
            
            // Hide swipe indicators
            const indicators = document.querySelectorAll('.swipe-indicator');
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            handleSwipeGesture();
            
            // Reset touch coordinates
            touchStartX = 0;
            touchStartY = 0;
            touchEndX = 0;
            touchEndY = 0;
        }
        
        function handleSwipeGesture() {
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            const minSwipeDistance = 50;
            
            // Only process horizontal swipes
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
                if (deltaX > 0) {
                    // Swipe right - previous page
                    const prevBtn = document.getElementById('prevBtn');
                    if (prevBtn && !prevBtn.disabled) {
                        previousPage();
                        showSwipeFeedback('Previous page');
                    }
                } else {
                    // Swipe left - next page
                    const nextBtn = document.getElementById('nextBtn');
                    if (nextBtn && !nextBtn.disabled) {
                        nextPage();
                        showSwipeFeedback('Next page');
                    }
                }
            }
        }
        
        function showSwipeFeedback(message) {
            // Create temporary feedback element
            const feedback = document.createElement('div');
            feedback.className = 'swipe-feedback';
            feedback.textContent = message;
            feedback.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--accent);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 14px;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.2s ease;
            `;
            
            document.body.appendChild(feedback);
            
            // Animate in
            setTimeout(() => feedback.style.opacity = '1', 10);
            
            // Remove after delay
            setTimeout(() => {
                feedback.style.opacity = '0';
                setTimeout(() => document.body.removeChild(feedback), 200);
            }, 1000);
        }
        
        // Mobile-Optimized Quiz Interface
        function optimizeQuizForMobile() {
            const quizSection = document.getElementById('quiz-section');
            if (!quizSection) return;
            
            // Add mobile classes to quiz elements
            quizSection.classList.add('mobile-quiz-container');
            
            const questions = quizSection.querySelectorAll('.question-item');
            questions.forEach(question => {
                const questionText = question.querySelector('h6');
                if (questionText) {
                    questionText.classList.add('mobile-quiz-question');
                }
                
                const options = question.querySelectorAll('.form-check');
                options.forEach(option => {
                    option.classList.add('mobile-quiz-option');
                    
                    // Make entire option clickable
                    const input = option.querySelector('input[type=\"radio\"]');
                    const label = option.querySelector('label');
                    
                    if (input && label) {
                        option.addEventListener('click', () => {
                            input.checked = true;
                            
                            // Update visual state
                            options.forEach(opt => opt.classList.remove('selected'));
                            option.classList.add('selected');
                        });
                    }
                });
            });
            
            // Optimize submit button
            const submitBtn = quizSection.querySelector('button[onclick*=\"submitQuiz\"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-mobile', 'btn-mobile-lg', 'btn-mobile-primary');
                submitBtn.style.width = '100%';
                submitBtn.style.marginTop = '1rem';
            }
        }
        
        // Mobile-Optimized Modals
        function optimizeModalsForMobile() {
            // Override modal show function for mobile
            const originalShowModal = window.showModal;
            window.showModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                
                // Add mobile classes
                const dialog = modal.querySelector('.modal-dialog');
                const content = modal.querySelector('.modal-content');
                
                if (dialog) {
                    dialog.classList.add('modal-dialog-mobile');
                }
                
                if (content) {
                    content.classList.add('mobile-modal-content');
                }
                
                // Call original function
                if (originalShowModal) {
                    originalShowModal(modalId);
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                }
            };
        }
        
        // Responsive Image Optimization
        function optimizeImagesForMobile() {
            const images = document.querySelectorAll('#chapter-content img');
            images.forEach(img => {
                // Add responsive classes
                img.classList.add('img-fluid');
                
                // Add loading attribute for performance
                img.setAttribute('loading', 'lazy');
                
                // Add click to zoom on mobile
                if (window.innerWidth <= 768) {
                    img.style.cursor = 'pointer';
                    img.addEventListener('click', () => {
                        showImageModal(img.src, img.alt);
                    });
                }
            });
        }
        
        function showImageModal(src, alt) {
            const modal = document.createElement('div');
            modal.className = 'mobile-modal';
            modal.innerHTML = `
                <div class="mobile-modal-content" style="max-width: 95%; max-height: 95%;">
                    <div class="mobile-modal-header">
                        <h5>${alt || 'Image'}</h5>
                        <button class="mobile-modal-close" onclick="this.closest('.mobile-modal').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <img src="${src}" alt="${alt}" style="width: 100%; height: auto; border-radius: 8px;">
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        // Initialize all mobile optimizations
        function initializeMobileOptimizations() {
            // Check if we're on a mobile device
            const isMobile = window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                console.log('📱 Initializing mobile optimizations...');
                
                // Add mobile class to body
                document.body.classList.add('mobile-optimized');
                
                // Initialize mobile features
                initializeMobileNavigation();
                initializeTouchGestures();
                optimizeModalsForMobile();
                
                // Optimize content after it loads
                setTimeout(() => {
                    optimizeQuizForMobile();
                    optimizeImagesForMobile();
                }, 1000);
                
                console.log('✅ Mobile optimizations initialized');
            }
        }
        
        // Keyboard navigation for accessibility
        function initializeKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                // Only handle if not in input field
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                
                switch(e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        const prevBtn = document.getElementById('prevBtn');
                        if (prevBtn && !prevBtn.disabled) previousPage();
                        break;
                        
                    case 'ArrowRight':
                        e.preventDefault();
                        const nextBtn = document.getElementById('nextBtn');
                        if (nextBtn && !nextBtn.disabled) nextPage();
                        break;
                        
                    case 'Escape':
                        closeMobileMenu();
                        break;
                }
            });
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize mobile optimizations
            initializeMobileOptimizations();
            initializeKeyboardNavigation();
            
            // Ensure StrictTimer is initialized
            if (!window.strictTimer) {
                console.warn('⚠️ StrictTimer not yet initialized, attempting to initialize...');
                if (typeof StrictTimer !== 'undefined') {
                    window.strictTimer = new StrictTimer();
                    console.log('✅ StrictTimer initialized in DOMContentLoaded');
                } else {
                    console.error('❌ StrictTimer class not available');
                }
            } else {
                console.log('✅ StrictTimer already initialized');
            }
            
            // Load pagination settings from localStorage
            loadPaginationSettings();
            
            // Security questions will only be shown after chapter completion
            // No longer showing on page load
            
            // Add event listener for security form submission
            document.getElementById('submitSecurityAnswers').addEventListener('click', submitSecurityAnswers);
            
            // Handle Enter key in security form
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && document.getElementById('securityModal').classList.contains('show')) {
                    e.preventDefault();
                    submitSecurityAnswers();
                }
            });
        });
        
        // Clean up timer when page unloads
        window.addEventListener('beforeunload', function() {
            if (securityTimer) {
                clearTimeout(securityTimer);
            }
        });
        
    </script>
    </div>
    
    <!-- Security Verification Modal -->
    <div class="modal fade" id="securityModal" tabindex="-1" aria-labelledby="securityModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="securityModalLabel">
                        <i class="fas fa-shield-alt me-2"></i>Security Verification Required
                    </h5>
                    <div class="security-timer-container">
                        <i class="fas fa-clock me-2"></i>
                        <span class="fw-bold">Time Remaining: </span>
                        <span id="security-timer-display" class="fw-bold fs-5">05:00</span>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-clock"></i> <strong>Time Limit:</strong> You have 5 minutes to complete this security verification. 
                        The verification will be automatically submitted when time runs out.
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Identity Verification:</strong> Please answer the following security questions to continue with your course. These are the same answers you provided during registration.
                    </div>
                    
                    <div id="securityQuestions">
                        <!-- Questions will be loaded here -->
                    </div>
                    
                    <div id="securityErrors" class="alert alert-danger d-none">
                        <!-- Error messages will appear here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitSecurityAnswers">
                        <i class="fas fa-check me-2"></i>Verify Answers
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DRM Watermark -->
    <div class="drm-watermark" id="drmWatermark">
        <div class="drm-watermark-text"></div>
    </div>

    @vite(['resources/js/app.js'])
    <x-footer />
    
    <script>
        // ===== AGGRESSIVE DRM & COPYRIGHT PROTECTION =====
        
        // Disable all DevTools detection methods
        const disableDevTools = () => {
            // Detect DevTools with multiple methods
            let devToolsOpen = false;
            
            // Method 1: Size detection
            const checkSize = () => {
                if (window.outerHeight - window.innerHeight > 200 || 
                    window.outerWidth - window.innerWidth > 200) {
                    if (!devToolsOpen) {
                        devToolsOpen = true;
                        blurAllContent();
                    }
                } else {
                    if (devToolsOpen) {
                        devToolsOpen = false;
                        unblurContent();
                    }
                }
            };
            
            // Method 2: Debugger detection
            const checkDebugger = () => {
                const start = performance.now();
                debugger;
                const end = performance.now();
                if (end - start > 100) {
                    blurAllContent();
                }
            };
            
            // Method 3: Console detection
            const checkConsole = () => {
                const element = new Image();
                Object.defineProperty(element, 'id', {
                    get: () => {
                        blurAllContent();
                        throw new Error('DevTools detected');
                    }
                });
                console.log(element);
            };
            
            setInterval(checkSize, 500);
            setInterval(checkDebugger, 1000);
        };
        
        // Blur all content
        const blurAllContent = () => {
            document.body.style.filter = 'blur(20px)';
            document.body.style.pointerEvents = 'none';
            const warning = document.createElement('div');
            warning.id = 'devtools-warning';
            warning.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #ff0000;
                color: white;
                padding: 40px;
                border-radius: 10px;
                z-index: 99999;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
            `;
            warning.innerHTML = '⚠️ UNAUTHORIZED ACCESS DETECTED<br>Developer Tools are not permitted<br>Session will be terminated';
            document.body.appendChild(warning);
            
            // Terminate session after 5 seconds
            setTimeout(() => {
                window.location.href = '/logout';
            }, 5000);
        };
        
        const unblurContent = () => {
            document.body.style.filter = 'none';
            document.body.style.pointerEvents = 'auto';
            const warning = document.getElementById('devtools-warning');
            if (warning) warning.remove();
        };
        
        // Disable keyboard shortcuts (only in production)
        if (isProduction) {
            document.addEventListener('keydown', (e) => {
                // F12
                if (e.key === 'F12') {
                    e.preventDefault();
                    blurAllContent();
                    return false;
                }
                // Ctrl+Shift+I
                if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    blurAllContent();
                    return false;
                }
                // Ctrl+Shift+J
                if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                    e.preventDefault();
                    blurAllContent();
                    return false;
                }
                // Ctrl+Shift+C
                if (e.ctrlKey && e.shiftKey && e.key === 'C') {
                    e.preventDefault();
                    blurAllContent();
                    return false;
                }
                // Ctrl+Shift+K
                if (e.ctrlKey && e.shiftKey && e.key === 'K') {
                    e.preventDefault();
                    blurAllContent();
                    return false;
                }
                // Ctrl+I
                if (e.ctrlKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }
                // Ctrl+U (View Source)
                if (e.ctrlKey && e.key === 'U') {
                    e.preventDefault();
                    return false;
                }
            }, true);
        }
        
        // Disable right-click (only in production)
        if (isProduction) {
            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            }, true);
        }
        
        // Prevent text selection (only in production)
        if (isProduction) {
            document.addEventListener('selectstart', (e) => {
                e.preventDefault();
                return false;
            }, true);
        }
        
        // Prevent copy (only in production)
        if (isProduction) {
            document.addEventListener('copy', (e) => {
                e.preventDefault();
                alert('Content copying is disabled');
                return false;
            }, true);
        }
        
        // Prevent cut (only in production)
        if (isProduction) {
            document.addEventListener('cut', (e) => {
                e.preventDefault();
                return false;
            }, true);
        }
        
        // Prevent drag (only in production)
        if (isProduction) {
            document.addEventListener('dragstart', (e) => {
                e.preventDefault();
                return false;
            }, true);
        }
        
        // Disable inspect element (only in production)
        if (isProduction) {
            window.__defineGetter__('__proto__', function() {
                blurAllContent();
            });
        }
        
        // Start protection (only in production)
        if (isProduction) {
            disableDevTools();
        }
        
        // Log suspicious activity
        window.addEventListener('error', (e) => {
            if (e.message.includes('DevTools')) {
                logSuspiciousActivity('devtools_detected');
            }
        });
        
        function logSuspiciousActivity(type) {
            fetch('/api/content-access-log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: 'suspicious_activity_' + type,
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent
                })
            }).catch(err => console.error('Failed to log:', err));
        }
        
        // Fix numbered lists that are stored as individual paragraphs
        function fixNumberedLists() {
            const chapterContent = document.getElementById('chapter-content');
            if (!chapterContent) return;
            
            const paragraphs = Array.from(chapterContent.querySelectorAll('p'));
            let listGroups = [];
            let currentGroup = null;
            
            paragraphs.forEach((p, index) => {
                const text = p.textContent.trim();
                
                // Check if this paragraph looks like a numbered list item
                const numberedMatch = text.match(/^(\d+)\.\s*(.+)$/);
                const letteredMatch = text.match(/^([a-zA-Z])\.\s*(.+)$/);
                const romanMatch = text.match(/^([ivxlcdm]+)\.\s*(.+)$/i);
                const bulletMatch = text.match(/^[•\-\*→▪]\s*(.+)$/);
                
                if (numberedMatch || letteredMatch || romanMatch) {
                    // This is a numbered list item
                    const cleanText = numberedMatch ? numberedMatch[2] : (letteredMatch ? letteredMatch[2] : romanMatch[2]);
                    
                    if (!currentGroup || currentGroup.type !== 'ol') {
                        // Start a new ordered list group
                        currentGroup = {
                            type: 'ol',
                            items: [cleanText],
                            elements: [p],
                            startElement: p
                        };
                        listGroups.push(currentGroup);
                    } else {
                        // Add to current ordered list group
                        currentGroup.items.push(cleanText);
                        currentGroup.elements.push(p);
                    }
                } else if (bulletMatch) {
                    // This is a bullet list item
                    if (!currentGroup || currentGroup.type !== 'ul') {
                        // Start a new unordered list group
                        currentGroup = {
                            type: 'ul',
                            items: [bulletMatch[1]],
                            elements: [p],
                            startElement: p
                        };
                        listGroups.push(currentGroup);
                    } else {
                        // Add to current unordered list group
                        currentGroup.items.push(bulletMatch[1]);
                        currentGroup.elements.push(p);
                    }
                } else {
                    // Not a list item, end current group
                    currentGroup = null;
                }
            });
            
            // Convert groups to actual HTML lists (process in reverse order to avoid DOM issues)
            listGroups.reverse().forEach(group => {
                if (group.items.length > 0) {
                    const listElement = document.createElement(group.type);
                    listElement.className = 'converted-list';
                    
                    group.items.forEach(itemText => {
                        const li = document.createElement('li');
                        li.textContent = itemText;
                        listElement.appendChild(li);
                    });
                    
                    // Replace the first element with the list
                    group.startElement.parentNode.replaceChild(listElement, group.startElement);
                    
                    // Remove the other elements
                    group.elements.slice(1).forEach(el => {
                        if (el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    });
                }
            });
            
            console.log('Fixed', listGroups.length, 'list groups');
        }
        
        // SIMPLE SOLUTION: Just fix the numbering display with CSS counters
        function fixNumberedListsSimple() {
            const chapterContent = document.getElementById('chapter-content');
            if (!chapterContent) return;
            
            const paragraphs = chapterContent.querySelectorAll('p');
            let counter = 0;
            
            paragraphs.forEach(p => {
                const text = p.textContent.trim();
                
                // Check if this paragraph starts with "1. " (the broken numbering)
                if (text.match(/^1\.\s+/)) {
                    counter++;
                    
                    // Add data attribute for CSS counter
                    p.setAttribute('data-auto-number', counter);
                    
                    // Hide the original "1." text
                    const newText = text.replace(/^1\.\s*/, '');
                    p.innerHTML = newText;
                    
                    console.log(`Fixed item ${counter}: ${newText.substring(0, 30)}...`);
                }
            });
            
            console.log(`Fixed ${counter} numbered items`);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

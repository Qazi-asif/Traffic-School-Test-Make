<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DICDS - Driver Improvement Course Documentation System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        /* DICDS layout adjustments for navbar */
        .dicds-content {
            margin-left: 300px;
            max-width: calc(100% - 320px);
            padding: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dicds-content {
                margin-left: 0;
                max-width: 100%;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="dicds-content">
        @yield('content')
    </div>
    
    <div style="position: fixed; bottom: 20px; left: 320px; font-size: 12px; color: var(--text-secondary);">
        DICDS Version 1.1 &nbsp;&nbsp; User Manual Update 2.0 &nbsp;&nbsp;&nbsp;&nbsp; September 2007
    </div>
</body>
</html>

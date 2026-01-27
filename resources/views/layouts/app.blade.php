<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Learning Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        main {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }
    </style>
    @stack('styles')
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <main>
        @yield('content')
    </main>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Request notification permission on page load
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Notification permission granted');
                    new Notification('Notifications Enabled', {
                        body: 'You will now receive notifications from this site',
                        icon: '/favicon.ico'
                    });
                }
            });
        }
        
        // Function to show notification
        window.showNotification = function(title, body, icon = '/favicon.ico') {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, { body, icon });
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>

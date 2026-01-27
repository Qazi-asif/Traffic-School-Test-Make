<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="/api/pwa/manifest">
    <title>@yield('title', 'Traffic School')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    
    <style>
        /* Mobile-First Responsive Design */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
            padding: 12px 16px;
        }
        
        /* Font Size Classes */
        .font-small { font-size: 14px; }
        .font-medium { font-size: 16px; }
        .font-large { font-size: 18px; }
        .font-xlarge { font-size: 20px; }
        
        /* High Contrast Mode */
        .high-contrast {
            background: #000 !important;
            color: #fff !important;
        }
        .high-contrast .card {
            background: #333 !important;
            border-color: #fff !important;
        }
        .high-contrast .btn-primary {
            background: #fff !important;
            color: #000 !important;
        }
        
        /* Reduced Motion */
        .reduced-motion * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        
        /* Mobile Breakpoints */
        @media (max-width: 576px) {
            .container { padding: 0.5rem; }
            .btn { width: 100%; margin-bottom: 0.5rem; }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .container { padding: 1rem; }
        }
        
        /* Skip to main content */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #007bff;
            color: white;
            padding: 8px;
            text-decoration: none;
            z-index: 1000;
        }
        .skip-link:focus {
            top: 6px;
        }
        
        /* Screen reader only */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Offline Indicator -->
    <div id="offline-indicator" style="display: none;" class="alert alert-warning text-center mb-0" role="alert">
        <i class="fas fa-wifi-slash"></i> You're offline. Some features may not be available.
    </div>
    
    @include('components.navbar')
        <main id="main-content" tabindex="-1">
            @yield('content')
        </main>
    </div>
    
    <!-- PWA Install Prompt -->
    <div id="pwa-prompt" style="display: none;" class="position-fixed bottom-0 start-0 end-0 bg-white border-top p-3 text-center">
        <div class="mb-2">
            <i class="fas fa-mobile-alt text-primary fs-2"></i>
        </div>
        <h6>Install Traffic School App</h6>
        <p class="small mb-3">Add this app to your home screen for quick access.</p>
        <button onclick="installPWA()" class="btn btn-primary btn-sm me-2">Install</button>
        <button onclick="dismissPWA()" class="btn btn-secondary btn-sm">Not Now</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PWA Installation
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            if (!localStorage.getItem('pwa-dismissed') && !isStandalone()) {
                document.getElementById('pwa-prompt').style.display = 'block';
            }
        });
        
        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                    document.getElementById('pwa-prompt').style.display = 'none';
                });
            }
        }
        
        function dismissPWA() {
            document.getElementById('pwa-prompt').style.display = 'none';
            localStorage.setItem('pwa-dismissed', Date.now());
        }
        
        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true;
        }
        
        // Offline Detection
        function updateOnlineStatus() {
            const indicator = document.getElementById('offline-indicator');
            if (navigator.onLine) {
                indicator.style.display = 'none';
            } else {
                indicator.style.display = 'block';
            }
        }
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
        
        // Load Accessibility Preferences
        async function loadAccessibilityPreferences() {
            try {
                const response = await fetch('/web/accessibility/preferences');
                const prefs = await response.json();
                
                document.body.className = document.body.className.replace(/font-\w+|high-contrast|reduced-motion/g, '');
                document.body.classList.add(`font-${prefs.font_size || 'medium'}`);
                
                if (prefs.high_contrast_mode) {
                    document.body.classList.add('high-contrast');
                }
                
                if (prefs.reduced_animations) {
                    document.body.classList.add('reduced-motion');
                }
            } catch (error) {
                console.log('Could not load accessibility preferences');
            }
        }
        
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/api/pwa/service-worker')
                .then(registration => console.log('SW registered'))
                .catch(error => console.log('SW registration failed'));
        }
        
        loadAccessibilityPreferences();
    </script>
    
    @yield('scripts')
</body>
</html>

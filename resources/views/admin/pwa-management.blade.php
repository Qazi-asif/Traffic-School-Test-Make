<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PWA Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
        <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
            <h2>Progressive Web App Management</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>PWA Status</h5>
                        </div>
                        <div class="card-body">
                            <div id="pwaStatus">
                                <p><strong>Service Worker:</strong> <span id="swStatus">Checking...</span></p>
                                <p><strong>Installation:</strong> <span id="installStatus">Checking...</span></p>
                                <p><strong>Offline Support:</strong> <span id="offlineStatus">Checking...</span></p>
                            </div>
                            <button onclick="installPWA()" id="installBtn" class="btn btn-primary" style="display: none;">
                                <i class="fas fa-download"></i> Install App
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Manifest Information</h5>
                        </div>
                        <div class="card-body" id="manifestInfo">
                            <p>Loading manifest...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Cache Management</h5>
                        </div>
                        <div class="card-body">
                            <button onclick="clearCache()" class="btn btn-warning">
                                <i class="fas fa-trash"></i> Clear Cache
                            </button>
                            <button onclick="updateCache()" class="btn btn-info">
                                <i class="fas fa-sync"></i> Update Cache
                            </button>
                            <div id="cacheStatus" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deferredPrompt;

        async function checkPWAStatus() {
            // Check Service Worker
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.getRegistration();
                    document.getElementById('swStatus').innerHTML = registration ? 
                        '<span class="text-success">Active</span>' : 
                        '<span class="text-warning">Not Registered</span>';
                } catch (error) {
                    document.getElementById('swStatus').innerHTML = '<span class="text-danger">Error</span>';
                }
            } else {
                document.getElementById('swStatus').innerHTML = '<span class="text-danger">Not Supported</span>';
            }

            // Check Installation Status
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                               window.navigator.standalone === true;
            document.getElementById('installStatus').innerHTML = isStandalone ? 
                '<span class="text-success">Installed</span>' : 
                '<span class="text-warning">Not Installed</span>';

            // Check Offline Support
            document.getElementById('offlineStatus').innerHTML = navigator.onLine ? 
                '<span class="text-success">Online</span>' : 
                '<span class="text-warning">Offline</span>';
        }

        async function loadManifest() {
            try {
                const response = await fetch('/api/pwa/manifest');
                const manifest = await response.json();
                
                document.getElementById('manifestInfo').innerHTML = `
                    <p><strong>Name:</strong> ${manifest.name}</p>
                    <p><strong>Short Name:</strong> ${manifest.short_name}</p>
                    <p><strong>Description:</strong> ${manifest.description}</p>
                    <p><strong>Display Mode:</strong> ${manifest.display}</p>
                    <p><strong>Theme Color:</strong> ${manifest.theme_color}</p>
                    <p><strong>Background Color:</strong> ${manifest.background_color}</p>
                `;
            } catch (error) {
                document.getElementById('manifestInfo').innerHTML = '<p class="text-danger">Error loading manifest</p>';
            }
        }

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    deferredPrompt = null;
                    document.getElementById('installBtn').style.display = 'none';
                });
            }
        }

        async function clearCache() {
            if ('caches' in window) {
                try {
                    const cacheNames = await caches.keys();
                    await Promise.all(cacheNames.map(name => caches.delete(name)));
                    document.getElementById('cacheStatus').innerHTML = '<div class="alert alert-success">Cache cleared successfully</div>';
                } catch (error) {
                    document.getElementById('cacheStatus').innerHTML = '<div class="alert alert-danger">Error clearing cache</div>';
                }
            }
        }

        async function updateCache() {
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.getRegistration();
                    if (registration) {
                        await registration.update();
                        document.getElementById('cacheStatus').innerHTML = '<div class="alert alert-success">Cache updated successfully</div>';
                    }
                } catch (error) {
                    document.getElementById('cacheStatus').innerHTML = '<div class="alert alert-danger">Error updating cache</div>';
                }
            }
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installBtn').style.display = 'block';
        });

        window.addEventListener('online', () => {
            document.getElementById('offlineStatus').innerHTML = '<span class="text-success">Online</span>';
        });

        window.addEventListener('offline', () => {
            document.getElementById('offlineStatus').innerHTML = '<span class="text-warning">Offline</span>';
        });

        checkPWAStatus();
        loadManifest();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

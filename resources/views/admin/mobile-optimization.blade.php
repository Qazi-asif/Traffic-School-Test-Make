<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Optimization</title>
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
            <h2>Mobile Optimization Dashboard</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Device Information</h5>
                        </div>
                        <div class="card-body" id="deviceInfo">
                            <p>Loading device information...</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Screen Information</h5>
                        </div>
                        <div class="card-body" id="screenInfo">
                            <p>Loading screen information...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Active Device Sessions</h5>
                        </div>
                        <div class="card-body">
                            <div id="deviceSessions">
                                <p>Loading device sessions...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadDeviceInfo() {
            console.log('📱 Starting to load device information...');
            try {
                const deviceData = {
                    screen_width: window.screen.width,
                    screen_height: window.screen.height
                };
                
                console.log('📊 Device data to send:', deviceData);
                console.log('🔍 User Agent:', navigator.userAgent);
                console.log('🔍 CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);

                console.log('📡 Making GET request to /web/device-info');
                const response = await fetch('/web/device-info', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                console.log('📊 Device info response status:', response.status);
                console.log('📊 Device info response headers:', response.headers);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Device info response not OK:', response.status, errorText);
                    alert(`Error loading device info: ${response.status} - ${errorText}`);
                    document.getElementById('deviceInfo').innerHTML = '<p class="text-danger">Error loading device information</p>';
                    return;
                }
                
                const deviceInfo = await response.json();
                console.log('✅ Loaded device info:', deviceInfo);
                
                document.getElementById('deviceInfo').innerHTML = `
                    <p><strong>Device Type:</strong> ${deviceInfo.device_type}</p>
                    <p><strong>Is Mobile:</strong> ${deviceInfo.is_mobile ? 'Yes' : 'No'}</p>
                    <p><strong>User Agent:</strong> ${deviceInfo.user_agent}</p>
                `;
                
                const screenInfo = {
                    screenWidth: window.screen.width,
                    screenHeight: window.screen.height,
                    viewportWidth: window.innerWidth,
                    viewportHeight: window.innerHeight,
                    devicePixelRatio: window.devicePixelRatio,
                    touchSupport: 'ontouchstart' in window
                };
                
                console.log('📊 Screen info:', screenInfo);
                
                document.getElementById('screenInfo').innerHTML = `
                    <p><strong>Screen Width:</strong> ${screenInfo.screenWidth}px</p>
                    <p><strong>Screen Height:</strong> ${screenInfo.screenHeight}px</p>
                    <p><strong>Viewport Width:</strong> ${screenInfo.viewportWidth}px</p>
                    <p><strong>Viewport Height:</strong> ${screenInfo.viewportHeight}px</p>
                    <p><strong>Device Pixel Ratio:</strong> ${screenInfo.devicePixelRatio}</p>
                    <p><strong>Touch Support:</strong> ${screenInfo.touchSupport ? 'Yes' : 'No'}</p>
                    <p><strong>Current Breakpoint:</strong> ${detectBreakpoint()}</p>
                `;
                
                console.log('✅ Device and screen info loaded successfully');
                
            } catch (error) {
                console.error('❌ Failed to load device info:', error);
                alert(`Failed to load device info: ${error.message}`);
                document.getElementById('deviceInfo').innerHTML = '<p class="text-danger">Error loading device information</p>';
            }
        }

        function detectBreakpoint() {
            const width = window.innerWidth;
            let breakpoint = '';
            
            if (width < 576) breakpoint = 'xs (Mobile Small)';
            else if (width < 768) breakpoint = 'sm (Mobile Large)';
            else if (width < 1024) breakpoint = 'md (Tablet)';
            else if (width < 1200) breakpoint = 'lg (Desktop Small)';
            else breakpoint = 'xl (Desktop Large)';
            
            console.log(`📏 Current breakpoint: ${breakpoint} (width: ${width}px)`);
            return breakpoint;
        }

        window.addEventListener('resize', () => {
            console.log('📏 Window resized, updating breakpoint info');
            const currentBreakpoint = detectBreakpoint();
            const screenInfoElement = document.getElementById('screenInfo');
            if (screenInfoElement) {
                // Update the breakpoint info in the existing content
                const breakpointRegex = /<p><strong>Current Breakpoint:<\/strong>.*?<\/p>/;
                const currentHTML = screenInfoElement.innerHTML;
                const newBreakpointHTML = `<p><strong>Current Breakpoint:</strong> ${currentBreakpoint}</p>`;
                
                if (breakpointRegex.test(currentHTML)) {
                    screenInfoElement.innerHTML = currentHTML.replace(breakpointRegex, newBreakpointHTML);
                } else {
                    screenInfoElement.innerHTML += newBreakpointHTML;
                }
            }
        });

        console.log('🚀 Initializing mobile optimization page...');
        console.log('🔍 Current URL:', window.location.href);
        console.log('🔍 CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
        console.log('🔍 Initial viewport size:', window.innerWidth, 'x', window.innerHeight);
        loadDeviceInfo();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

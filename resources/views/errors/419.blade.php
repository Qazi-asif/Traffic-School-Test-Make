<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Session Expired</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .error-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="display-4 mb-4">Session Expired</h1>
        <p class="lead mb-4">Your session has expired. Redirecting you back to continue...</p>
        
        <div class="spinner-border text-light mb-4" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        
        <div class="mb-3">
            <button onclick="refreshAndRetry()" class="btn btn-light btn-lg me-3">
                <i class="fas fa-refresh"></i> Try Again
            </button>
            <a href="/login" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
        
        <small class="text-light opacity-75">
            Auto-refreshing in <span id="countdown">3</span> seconds...
        </small>
    </div>

    <script>
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                refreshAndRetry();
            }
        }, 1000);
        
        function refreshAndRetry() {
            // Clear any cached tokens
            if (window.csrfHandler) {
                window.csrfHandler.refreshCSRFToken();
            }
            
            // Go back to the previous page or login
            if (document.referrer && !document.referrer.includes('/login')) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/login';
            }
        }
        
        // Refresh CSRF token immediately
        fetch('/api/csrf-token')
            .then(response => response.json())
            .then(data => {
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }
            })
            .catch(console.error);
    </script>
</body>
</html>